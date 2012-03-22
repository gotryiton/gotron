<?php

namespace Gotron;

use ReflectionClass;

class Controller {

    public $view_path = null;

    public $parameters = array();

    public $options = array('view' => 'index', 'layout' => 'layout', 'cache' => false);

	/**
	 * Renders the view
	 *
     *  Calls the render method on the view
	 *
	 * @param array $parameters 
	 * @param string $view 
	 * @return string
	 */
    protected static function render(array $parameters, $options = array()) {
        $instance = new static();
        $instance->parse_options($options);
        $instance->view_type = static::get_view_type($parameters);
        $layout = $instance->options['layout'];
        if ($instance->view_type == "php") {
            $instance->view_path = $instance->fileize_view_path($instance->options['view']);
        }
        else if($instance->view_type == "json") {
            $parameters = $parameters['json'];
            $layout = false;
        }
        $view = ucfirst($instance->view_type) . "View";
        if(class_exists(__NAMESPACE__ . "\\View\\$view")) {
            if($layout === false) {
                $view_data = call_user_func(__NAMESPACE__ . "\\View\\$view::render", $parameters, $instance->view_path, false);
                echo $view_data['content'];
            }
            else {
                $controller_view = call_user_func(__NAMESPACE__ . "\\View\\$view::render", $parameters, $instance->view_path, false);
                $data = array(
                    'yield' => $controller_view['content'],
                    'includes' => $controller_view['includes']
                );
                $layout_path = $instance->get_layout($layout);
                $output = call_user_func(__NAMESPACE__ . "\\View\\$view::render", $data, $layout_path, false);
                echo $output['content'];
            }
        }
        else {
            throw new Exception("$view has not been defined");
        }
    }

    protected function parse_options(array $options) {
        foreach($options as $key => $value) {
            if(array_key_exists($key, $this->options)) {
                $this->options[$key] = $value;
            }
        }
    }

	/**
	 * Gets the type of view from the parameters array
	 *
	 * @param string $parameters 
	 * @return string
	 */
    protected function get_view_type($parameters) {
        if (array_key_exists('type', $parameters)) {
            return $parameters['type'];
        }
        else if (array_key_exists('json', $parameters)) {
            return 'json';
        }
        else{
            return 'php';
        }
    }

	/**
	 * Returns the lowercase controller name removing 'controller'
	 *
	 * @return string
	 */
    protected function controller_name() {
        $reflector = new ReflectionClass($this);
        $namespace = $reflector->getNamespaceName();
        $class = strtolower(get_called_class());
        return str_replace(array("controller", strtolower($namespace) . '\\'), "", $class);
    }

	/**
	 * Returns the base view directory for the controller
	 *
	 * @return string
	 */
    protected function standard_view_path() {
        return realpath(file_join(Config::get('root_directory'), Config::get('view_directory'), $this->controller_name()));
    }

    protected static function get_layout($layout = "layout") {
        return realpath(file_join(Config::get('root_directory'), Config::get('view_directory'), "layouts", "{$layout}.php"));
    }

	/**
	 * Turns the string $view into a full path
	 *
	 * @param string $page
	 * @return string
	 */
    protected function fileize_view_path($page) {
        return file_join($this->standard_view_path(), "{$page}.php");
    }

	/**
	 * Calls the method with the before and after functions called at the right time
	 *
	 * @param string $method
	 * @return void
	 */
	public function call_method($method = 'index') {
		$this->before();
		if (is_callable(array($this, $method))) {
			$this->$method();
			$this->after();
		}
		else {
			throw new Exception("Method $method does not exist in " . get_called_class());
		}
	}

	/**
	 * Called inside render method prior to rendering view
	 *
	 * 	Override in child class
	 *
	 * @return void
	 */
	protected function before() {}

	/**
	 * Called inside render method after rendering view
	 *
	 * 	Override in child class
	 *
	 * @return void
	 */
	protected function after() {}

}

?>