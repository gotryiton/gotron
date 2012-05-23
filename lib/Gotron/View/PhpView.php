<?php

namespace Gotron\View;

use Gotron\Exception;

/**
 * View class used to generate standard php views
 *
 * @package Framework
 */
class PhpView extends AbstractView{

	public $injected = array();

	private static $allowed_injected_variables = array('include', 'title', 'meta_tags');

	protected $content_type = "text/html";

    /**
     * Generates the PhpView
     *
     * @return string
     */
    public function generate(array $parameters) {
        if (is_file($this->view_path)) {
            extract($parameters);
			ob_start();
			include $this->view_path;
			$this->content = ob_get_clean();

			foreach (self::$allowed_injected_variables as $injected_variable) {
				if (isset($$injected_variable)) {
					$this->injected[$injected_variable] = $$injected_variable;
				}

			}
		}
        else {
            throw new Exception("Cannot find view {$this->view_path}");
        }
		return $this;
    }

    /**
     * Adds headers to the views header array
     *
     * @return void
     */
    public function get_headers() {
		return $this->headers;
    }

}

?>