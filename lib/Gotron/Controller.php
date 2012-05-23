<?php

namespace Gotron;

use ReflectionClass,
    Gotron\Dispatch\Error,
	Gotron\Dispatch\Response;

class Controller {

    public $view_path = null;

    public $params = array();

    public $parameters = array();

    public $options = array('view' => 'index', 'layout' => 'layout', 'cache' => false, 'status' => 200);

	protected $dont_render = false;

    public $request = null;

    /**
     * Empty filters
     *
     * @var string
     */
    protected $before_filter = array();
    protected $after_filter = array();

    /**
     * Stores whether the controller has already rendered
     *
     * @var string
     */
    private $rendered = false;

    /**
     * List of exceptions that should be code and the HTTP status to send
     * for them
     *
     * @var array
     */
	private static $catchable_exceptions = array(
        "ActiveRecord\RecordNotFound" => 404,
    );

	/**
	 * Renders the view
	 *
     *  Calls the render method on the view
	 *
	 * @param array $parameters 
	 * @param string $view 
	 * @return string
	 */
    public function render(array $parameters, $options = array()) {
        $this->rendered = true;
        $this->parse_options($options);
        $this->view_type = static::get_view_type($parameters);
        $layout = $this->options['layout'];
        if ($this->view_type == "php") {
            $this->view_path = $this->fileize_view_path($this->options['view']);
        }
        else if($this->view_type == "json") {
            $parameters = $parameters['json'];
            $layout = false;
        }
        $view_name = ucfirst($this->view_type) . "View";
        if(class_exists(__NAMESPACE__ . "\\View\\$view_name")) {
            if($layout === false) {
                $view = call_user_func(__NAMESPACE__ . "\\View\\$view_name::render", $parameters, $this->view_path);
            }
            else {
                $main_view = call_user_func(__NAMESPACE__ . "\\View\\$view_name::render", $parameters, $this->view_path);
                $layout_path = $this->get_layout($layout);
                $view = call_user_func(__NAMESPACE__ . "\\View\\$view_name::render", $parameters, $layout_path, false, $main_view);
            }

			$response = Response::build_from_view($view, $this->options['status'], !$this->dont_render);
			if($this->dont_render) {
                $GLOBALS['controller_content'] = $response->content;
				$GLOBALS['controller_data'] = $parameters;
			}
			$response->send();
        }
        else {
            throw new Exception("$view has not been defined");
        }
    }

    /**
     * Parse the list of options sent to the controller
     *
     * @param array $options
     * @return void
     */
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
		if(isset($this->class_name)) {
			$denamespaced = $this->class_name;
		}
		else {
			$reflector = new ReflectionClass($this);
			$namespace = $reflector->getNamespaceName();
	        $class = get_called_class();
			$denamespaced = str_replace($namespace . '\\', "", $class);
		}
        return str_replace(array("_controller"), "", Helper::uncamelize($denamespaced));
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
        try {
            $this->before();
    		$this->invoke_filter('before', $method, true);
    		if (is_callable(array($this, $method))) {
                if (!$this->rendered) {
        			$this->$method();
                    $this->invoke_filter('after', $method);
                    $this->after();
                }
    		}
            else {
                $this->render_error("500");
            }
        }
        catch(\Exception $e) {
			echo $e;
            $exception_type = get_class($e);
            if (array_key_exists($exception_type, self::$catchable_exceptions)) {
                $error_status = self::$catchable_exceptions[$exception_type];
                $this->render_error($error_status);
            }
            else {
                $this->render_error("500");
            }
        }
	}

    /**
     * Calls the appropriate filter callbacks for the controller
     *
     * @param string $type 
     * @param string $method 
     * @param bool $check_rendered Should it check if the controller has already rendered
     * @return void
     */
    protected function invoke_filter($type, $method, $check_rendered = false) {
        $name = "{$type}_filter";
        if (isset($this->$name)) {
            $callbacks = $this->$name;
            foreach ($callbacks as $key => $value) {
                if (is_array($value)) {
                    $callback = $key;
                    $methods = $value;
                    if (array_search($method, $methods) !== false) {
                        if (method_exists($this, $callback) && (($check_rendered && !$this->rendered) || !$check_rendered)) {
                            call_user_method($callback, $this);
                        }
                    }
                }
                else {
                    $callback = $value;
                    if (method_exists($this, $callback) && (($check_rendered && !$this->rendered) || !$check_rendered)) {
                        call_user_method($callback, $this);
                    }
                }
            }
        }
        else {
            throw new Exception("$name is not defined for " . get_called_class());
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

	/**
	 * Sets the controller to not actually render output when render() is called
	 * useful for testing
	 *
	 * @return void
	 */
	public function dont_render($class = null) {
		if(!is_null($class)) {
			$this->class_name = $class;
		}
		$this->dont_render = true;
	}

    /**
     * Calls the closure defined for the content_type specified for the current request
     *
     * @param string $respond_array
     * @return mixed
     */
    protected function respond_to($respond_array) {
        if ($content_type = $this->request->simple_content_type()) {
            if (array_key_exists($content_type, $respond_array)) {
                return $respond_array[$content_type]();
            }
		}
		$this->render_error('406');
    }

    protected function render_error($status_code = "500") {
        $this->rendered = true;
        Error::send($status_code, $this->request);
    }

}

?>