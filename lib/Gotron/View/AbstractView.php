<?php

namespace Gotron\View;

use Gotron\Header as Header;

/**
 * Abstract view class to be implemented by a number of View classes
 *
 * @package Framework
 */
abstract class AbstractView{

    /**
     * Full path to the view, from root view path
     *
     * @var string
     */

    public $view_path = null;

    /**
     * Flag to cache the view
     *
     * @var bool
     */
    public $cache = false;

    /**
     * The headers to be used with the view as key => value
     *
     * @var array
     */
    public $headers = array();

    /**
     * Initialize AbstactView and assign properties
     *
     * @param array $data 
     * @param string $view_path Relative path to the view (from view directory)
     * @param bool $cache 
     */
    public function __construct($view_path = null, $cache = false)
    {
        if(!is_null($view_path)) {
            $this->view_path = $view_path;
        }

        $this->cache = $cache;
    }

    /**
     * Returns the the view instance
     *
     * @param string $data 
     * @param string $view_path 
     * @param string $cache 
     * @return bool
     */
    public static function render($data = array(), $view_path = null, $cache = false, $headers = true) {
        $instance = new static($view_path, $cache);
        if($headers == true) {
            $instance->set_headers();
        }
        return $instance->generate($data);
    }

    /**
     * Adds the headers to the headers array
     *
     * @param string $key 
     * @param string $value 
     * @param bool $update Set to true to update existing keys
     * @return void
     */
    public function add_header($key, $value, $update = true)
    {
		if($update) {
		    $this->headers[$key] = $value;
		}
		else {
		    if(!array_key_exists($key, $this->headers)) {
		        $this->headers[$key] = $value;
		    }
		}
    }

	public function set_headers() {
		foreach($this->get_headers() as $key => $value) {
			Header::set("$key: $value");
		}
	}

    /**
     * Generate method to be implemented
     *   should return the generated view
     *
     * @return string
     */
    abstract function generate(array $parameters);

    /**
     * Headers method to be implemented
     *   sets the headers (using add_header)
     *
     * @return array
     */
    abstract protected function get_headers();

}

?>