<?php

namespace Gotron;

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
	public static function route($namespace = "Gotron") {

	    $url = explode('?',$_SERVER['REQUEST_URI']);
		$path = mb_strtolower($url[0]);
		$path_components = explode('/', $path);
		$real_path_components = $path_components;

		if (Config::bool('show_maintenance_screen')){
		    $api = array_search('rest', $path_components) !== FALSE;
			static::perform_controller_action("Error", "maintenance", array(), array('api' => $api), $namespace);
		}
		else if (Config::bool('show_error_screen')){
		    $api = array_search('rest',$path_components) !== FALSE;
			static::perform_controller_action("Error", "error_page", array(), array('api' => $api), $namespace);
		}

		while (substr($path, -1) == '/') {
			$path = mb_substr($path,0,(mb_strlen($path)-1));
		}

		//default actions are called 'index'
		$action = "index";
		//Loop through all the routes we defined in globals.php, and try to find one that matches our request
		foreach (Config::get('routes') as $route => $controller) {
			$route_components = array();
			$optional_components = array();
			
			preg_match("/([^\(\)]+)(\([^\(\)]*\))*/",$route, $matches);
			
			if (isset($matches[1])) {
				$route_components = explode("/",$matches[1]);
			}
			
			if (isset($matches[2])) {
				$optional_components = explode("/",trim($matches[2], '()'));
			}
			
			$action = "index";
			$i=0;
			$j=0;
			$objects = array();
			$parameters = array();
			$goodRoute = true;
			$custom_parameters = array();
			$boolean_parameters = array();
			$array_parameter = NULL;
			$path_components = array_pad($path_components, count($route_components), '');
			
			//put the route components in 'args' within parameters 		
			$parameters['args'] = $route_components;
			
			//put the route components in 'args' within parameters 		
			$parameters['path'] = $real_path_components;
			
			//put the GET and POST into the parameters array 		
			foreach ($_GET as $key => $value) {
			    $parameters[$key] = $value;
			}
			foreach ($_POST as $key => $value) {
			  	$parameters[$key] = $value;
			}
			
			//Handle routes that call a specific action
			$controller_action_array = explode(":",$controller);
			$controller = $controller_action_array[0];
			if (count($controller_action_array) == 2) {
				$action = $controller_action_array[1];
			}
			
			$modified_route = array();
			$k = 0;
			//Loop through the route components to pull out any variables that are position independent (can appear anywhere in the path)
			foreach ($optional_components as $optional_component) {
				//this part of the route is a custom parameter
				if (substr($optional_component,0,1) == "~"){
					$custom_parameters[] = substr($optional_component,1);
					
				}
				//this part of the route is a boolean parameter
				elseif (substr($optional_component,0,2) == "=:"){
					$boolean_parameters[] = substr($optional_component,2);
					
				}
				//this part of the route is an array parameter
				elseif (substr($optional_component,0,2) == "*:"){
					$array_parameter = substr($optional_component,2);
					
				}
				$k++;
			}
			
			//Loop through each component of this route until we find a part that doesn't match, or we run out of url
			foreach ($route_components as $route_component) {
				//this part of the route is a variable
				if (substr($route_component,0,1) == ":" && $path_components[$i]!='') {
					$parameters[substr($route_component,1)] = $path_components[$i];
					//This part of the route is an action for a controller
				} 
				elseif ($route_component == "[action]") {
					if ($path_components[$i] != "") {
						$action = str_replace("-","_",$path_components[$i]);
					}
					//This part of the route will require that we create an object
				}
				elseif (substr($route_component,0,1) == "(" && substr($route_component,-1,1) == ")") {
					$reflection_obj = new ReflectionClass(substr($route_component,1,strlen($route_component)-2)); 
					$object = $reflection_obj->newInstanceArgs(array($path_components[$i]));
					$objects[] = $object;
					//Part of the url that isn't an action or an object didn't match, this definitely isn't the right route
				}
				elseif ($route_component != $path_components[$i] && str_replace("-","_",$route_component) != $path_components[$i]) {
					$goodRoute = false;
				}
				$i++;
			}
			
			//This route is a match for our request, let's get the controller working on it
			if ($goodRoute && ($i >= count($path_components) || $path_components[$i] == "" || count($optional_components)>0)) {
				$j=0;
				$lastParameter = '';
				//loop through custom parameters
				foreach ($path_components as $path_component){
					//This part of the route is a custom named parameter for a controller
					if ( in_array($path_component, $custom_parameters)){
						$i=array_search($path_component, $custom_parameters);
						//add to parameters list
						$parameters[$custom_parameters[$i]] = $path_components[$j+1];
						$lastParameter =  $path_components[$j+1];
					}
					elseif ( in_array($path_component, $boolean_parameters)) {
						$i=array_search($path_component, $boolean_parameters);
						$parameters[$boolean_parameters[$i]] = true;
					}
					elseif ( isset($array_parameter) && $path_components[$j]!="" && !in_array($path_components[$j], $route_components) && $path_component != $lastParameter) {
						$parameters[$array_parameter][] = $path_components[$j];
					}
					$j++;
				}
				if(static::perform_controller_action($controller, $action, $objects, $parameters, $namespace)) {
                    return;
                }
			}
		}
		
		
		
		$parameters = array();
		//put the route components in 'args' within parameters 		
		$parameters['args'] = $route_components;
		$parameters['path'] = $real_path_components;
		
		//put the GET and POST into the parameters array 		
		foreach ($_GET as $key => $value) {
		    $parameters[$key] = $value;
		}

		foreach ($_POST as $key => $value) {
		  	$parameters[$key] = $value;
		}

		foreach ($_FILES as $key => $value) {
		  	$parameters[$key] = $value;
		}
			
		if (!$goodRoute){
			$controller = ucfirst($path_components[1]);
				
			if ($path_components[2]!='' && $path_components[2]!=NULL) {
				$action = str_replace("-","_", $path_components[2]);
			}

			if(static::perform_controller_action($controller, $action, $objects, $parameters, $namespace)) {
                return;
            }
			
			if ($path_components[1]!='' && $path_components[1]!=NULL) {
				if(static::perform_controller_action('Staticpage', 'index', $objects, $parameters, $namespace)) {
                    return;
                }
			}
			
		}
        Error::error_404();
	}

	/**
	 * Look for a controller file matching the request, and failing that, a view
	 *
	 * @param string $class_path 
	 * @param string $action
	 * @param array $objects 
     * @param array $parameters 
	 * @return void
	 */
	public function perform_controller_action($class_path, $action, $objects, $parameters, $namespace) {
		//We treat 'new' the same as 'edit', since they generally contain a lot of the same code
		if ($action == "new") {
			$action = "edit";
		}

		//Let's look for a controller
		$class_path_components = explode("/",$class_path);
		$class = $class_path_components[count($class_path_components)-1];
		$controller_class = $namespace . "\\$class"."Controller";
        if(class_exists($controller_class)) {
			if (!method_exists($controller_class, $action)) {
				if (static::render_view($class_path,$action)) {
				    if(function_exists('newrelic_name_transaction')) newrelic_name_transaction($class_path . "/" . $action);
					exit;
				} 
				elseif (method_exists($controller_class,'index')){
					$action='index';
				}
				else {
					Error::error_500("$controller_class does not respond to $action");
				}
			}
			if(function_exists('newrelic_name_transaction')) newrelic_name_transaction($class_path . "/" . $action);
			$controller = new $controller_class();
			$controller->parameters = $parameters;

            if(function_exists('newrelic_add_custom_parameter')){
                if(is_array($parameters)){
                    foreach($parameters as $key => $value){
                       newrelic_add_custom_parameter($key,$value);
                    }
                }
            }

			$controller->call_method($action);
            return true;
        }
		
		//If no controller was found, we'll look for a view
		if (static::render_view($class_path,$action)) {
            return true;
		}
	}

	/**
	 * Renders a view from a class path and action
	 *
	 * @param string $class_path
	 * @param string $action
	 */
	public static function render_view($class_path, $action) {
		$view_path = file_join(Config::get('root_directory'), "/views/$class_path/".$action.".php");
		if (file_exists($view_path)) {
			$controller = new Controller();
			require_once($view_path);
			return true;
		}
		return false;
	}
}

