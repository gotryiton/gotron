<?php

namespace Gotron\View;

use Gotron\Exception;

/**
 * View class used to generate standard php views
 *
 * @package Framework
 */
class PhpView extends AbstractView {

    /**
     * Array of variables that will be injected into a layout view
     *
     * @var string
     */
    public $inject = array('includes' => array('css' => array(), 'js' => array(), 'meta' => array()));

    /**
     * Variables that are pulled from the injected view and sent to the layout
     *  - if attribute is key/value it will convert the
     *    name of the variable from the key to the value
     *
     * @var array
     */
    private static $allowed_injected_variables = array('include' => 'includes', 'title', 'meta_tags');

    protected $content_type = "text/html";

    /**
     * Generates the PhpView
     *
     * @return string
     */
    public function generate(array $parameters, $injected_view = null) {
        if (is_file($this->view_path)) {
            if ($injected_view instanceof PhpView) {
                // Pulls the data from the injected_view view into the layout
                extract($injected_view->inject);
                $yield = $injected_view->content;
            }
            extract($parameters);
            ob_start();
            include $this->view_path;
            $this->content = ob_get_clean();

            if (is_null($injected_view)) {
                foreach (self::$allowed_injected_variables as $key => $inject_variable) {
                    if (is_numeric($key)) {
                        $variable_name = $inject_variable;
                    }
                    else {
                        $variable_name = $key;
                    }
                    if (isset($$variable_name)) {
                        if (array_key_exists($inject_variable, $this->inject) && is_array($$variable_name)) {
                            $this->inject[$inject_variable] = array_merge($this->inject[$inject_variable], $$variable_name);
                        }
                        else {
                            $this->inject[$inject_variable] = $$variable_name;
                        }
                    }
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
