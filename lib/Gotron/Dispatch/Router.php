<?php

namespace Gotron\Dispatch;

use Gotron\Config,
    ReflectionClass;

/**
 * Routing class
 *
 * Based on Ben's Magic PHP routing class https://github.com/pokeb/php-mvc-router/blob/master/helpers/router.php
 *
 * @package Gotron
 */
class Router {

	/**
	 * Routes the REQUEST_URI to the appropriate defined Route 
	 *
	 * @return void
	 */
	public static function route($app) {
	    $url = explode('?', $_SERVER['REQUEST_URI']);
		$path = mb_strtolower($url[0]);
        $json = false;

        if (preg_match("/\.json/", $path)) {
            $content_type = "application/json";
            $path = preg_replace("/\.json/", "", $path);
        }
        else {
            $content_type = null;
        }

		$path_components = explode('/', $path);

        if (Config::bool('show_maintenance')){
            $request = Request::build(array(
                "full_url" => $_SERVER['REQUEST_URI'],
                "path" => $path,
                "content_type" => (array_search('rest', $path_components) !== false) ? "application/json" : $content_type,
                "accept_header" => (array_key_exists('HTTP_ACCEPT', $_SERVER)) ? $_SERVER['HTTP_ACCEPT'] : null,
                "app" => $app
            ));

            static::perform_controller_action("Error", "maintenance", $request, $app);
            return;
        }
        elseif (Config::bool('show_error')){
            $request = Request::build(array(
                "full_url" => $_SERVER['REQUEST_URI'],
                "path" => $path,
                "content_type" => (array_search('rest', $path_components) !== false) ? "application/json" : $content_type,
                "accept_header" => (array_key_exists('HTTP_ACCEPT', $_SERVER)) ? $_SERVER['HTTP_ACCEPT'] : null,
                "app" => $app
            ));

            static::perform_controller_action("Error", "error_page", $request, $app);
            return;
        }
        
        $parameters = array();

        foreach ($_GET as $key => $value) {
		    $parameters[$key] = $value;
		}

		foreach ($_POST as $key => $value) {
		  	$parameters[$key] = $value;
		}

		//default actions are called 'index'
		$action = "index";
        $routes = Config::get('routes');
        $routes = static::strip_end_slashes($routes);

        if ($route = static::find_best_route($routes, $path)) {
            $controller = $routes[$route];
            $action = "index";
            $standard_components = array();
            $optional_components = array();

            $controller_array = explode(":", $controller);
            $controller = $controller_array[0];

            if (count($controller_array) == 2) {
                $action = $controller_array[1];
            }

            if (preg_match('/([^\(\)]+)/', $route, $standard_matches) !== 0) {
                $standard_components = explode("/", $standard_matches[1]);
            }

            if (preg_match("/\((\S*)\)/", $route, $optional_matches) !== 0) {
                $optional_components = explode("/", $optional_matches[1]);
            }

            foreach ($standard_components as $i => $component) {
                if (substr($component, 0, 1) == ":") {
                    $parameters[substr($component, 1)] = $path_components[$i];
                }
                elseif ($component == "[action]") {
                    $action = str_replace("-", "_", $path_components[$i]);
                }
            }

            $custom_parameters = array();
            $boolean_parameters = array();
            foreach ($optional_components as $i => $component) {
                $path_key = $i + count($standard_components) - 1;
                if (substr($component, 0, 1) == ":" && array_key_exists($path_key, $path_components)) {
                    $parameters[substr($component, 1)] = $path_components[$path_key];
                }
                elseif (substr($component, 0, 1) == "~") {
                    $custom_parameters[] = substr($component, 1);
                }
                elseif (substr($component, 0, 2) == "=:") {
                    $boolean_parameters[] = substr($component, 2);
                }
                elseif (substr($component, 0, 2) == "*:") {
                    $array_parameter = substr($component, 2);
                }
            }

            foreach ($path_components as $i => $component) {
                if (($custom_location = array_search($component, $custom_parameters)) !== false) {
                    $parameters[$custom_parameters[$custom_location]] = $path_components[$i + 1];
                }
                elseif (($bool_location = array_search($component, $boolean_parameters)) !== false) {
                    $parameters[$boolean_parameters[$bool_location]] = true;
                }
                elseif (isset($array_parameter) && !in_array($component, $standard_components)) {
                    $parameters[$array_parameter][] = $path_components[$i];
                }
            }

            $request = Request::build(array(
                "full_url" => $_SERVER['REQUEST_URI'],
                "path" => $path,
                "params" => $parameters,
                "files" => $_FILES,
                "content_type" => $content_type,
                "accept_header" => (array_key_exists('HTTP_ACCEPT', $_SERVER)) ? $_SERVER['HTTP_ACCEPT'] : null,
                "app" => $app
            ));

            if (static::perform_controller_action($controller, $action, $request, $app)) {
                return true;
            }
        }
        if ($path_components[1] != '' && $path_components[1] != null) {
            $request = Request::build(array(
                "full_url" => $_SERVER['REQUEST_URI'],
                "path" => $path,
                "params" => array('path' => $path_components),
                "files" => $_FILES,
                "content_type" => $content_type,
                "accept_header" => (array_key_exists('HTTP_ACCEPT', $_SERVER)) ? $_SERVER['HTTP_ACCEPT'] : null,
                "app" => $app
            ));

			if(static::perform_controller_action('Static', 'index', $request, $app)) {
                return true;
            }
        }
        Error::error_404($app);
        return false;
	}

	/**
	 * Look for a controller file matching the request, and failing that, a view
	 *
	 * @param string $controller 
	 * @param string $action
	 * @param Request $request 
     * @param Application $app 
	 * @return bool
	 */
	public static function perform_controller_action($controller, $action, $request, $app) {
        $reflector = new ReflectionClass($app);
        $namespace = $reflector->getNamespaceName();
        $app->autoload_presenters($request->version);
        $controller_class = "{$namespace}\\{$controller}Controller";
        if(class_exists($controller_class)) {
            self::load_newrelic($request, $controller, $action);
            if (method_exists($controller_class, $action)) {
                $controller = new $controller_class();
                $controller->params = $request->params;
                $controller->parameters = &$controller->params;
                $controller->request = $request;
                $controller->call_method($action);
                return true;
            }
            else {
                Error::send('500', $request);
            }
        }
	}

	/**
	 * Renders a view from a class path and action
	 *
	 * @param string $class_path
	 * @param string $action
	 */
	public static function render_view($class_path, $action) {
		$view_path = file_join(Config::get('root_directory'), "app/views/{$class_path}/{$action}.php");
		if (file_exists($view_path)) {
			$controller = new Controller();
			require_once($view_path);
			return true;
		}
		return false;
	}

    /**
     * Takes a route and transforms it to a regex pattern
     *
     * @param string $route 
     * @return string (regex pattern)
     */
    public static function compile_route($route) {
        $replacements = array(
            "\(\S+\)" => '${0}?', // Adds '?' to optional '()' group of params
            "(\/\*\:\w+|\/\~\w+|\/\=\:\w+)" => '(${0})?', // Adds ()? to each optional param
            "(\/\*\:)(\w+)" => '(/\w+)*', // Catch-all array parameter
            "(\/\=\:\w+)" => "/\w+", // Boolean parameter
            "\:\w+" => "[\w\-]+", // Named parameter (:name)
            "\[\w+\]" => "[\w\-]+", // Variable action ([action])
            "(\/\~)(\w+)" => '(/${2}/\w+)', // Custom Parameters (~parameter)
            "\/" => '\/'
        );

        foreach ($replacements as $pattern => $replace) {
            $route = preg_replace("/$pattern/", $replace, $route);
        }
        // Setting the route to match the entire string once using the start and end of the line
        return "^($route){1}$";
    }

    public static function compile_routes($routes) {
        $compiled_routes = array();
        foreach ($routes as $route => $controller) {
            $compiled_routes[$route] = array('compiled' => static::compile_route($route), 'controller' => $controller);
        }
        return $compiled_routes;
    }

    /**
     * Takes the array of routes and finds a match
     *
     * @param array $routes 
     * @return array route
     */
    public static function find_best_route($routes, $path) {
        $routes = static::compile_routes($routes);
        $path = strlen($path) > 1 ? rtrim($path, "/") : $path;
        foreach ($routes as $route => $route_array) {
            if (preg_match("/{$route_array['compiled']}/", $path) !== 0) {
                return $route;
            }
        }

        return false;
    }

    /**
     * Calls the necessary newrelic functions
     *
     * @param Request $request 
     * @param string $controller 
     * @param string $action 
     * @return bool
     */
    public static function load_newrelic($request, $controller, $action) {
        if(function_exists('newrelic_name_transaction')) {
            newrelic_name_transaction($controller . "/" . $action);
        }
        if(function_exists('newrelic_add_custom_parameter')){
            if(is_array($request->params)){
                foreach($request->params as $key => $value){
                    newrelic_add_custom_parameter($key,$value);
                }
            }
        }
        return true;
    }

    public static function strip_end_slashes($routes) {
        $new_routes = array();
        foreach ($routes as $route => $controller) {
            $new_routes[(strlen($route) > 1) ? rtrim($route, "/") : $route] = $controller;
        }
        return $new_routes;
    }

}

