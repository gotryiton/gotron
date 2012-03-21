<?php

namespace Gotron\View;

use Gotron\Exception;

/**
 * View class used to generate standard php views
 *
 * @package Framework
 */
class PhpView extends AbstractView{

    public $includes = array();

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
            if(isset($include)) {
                $this->includes = $include;
            }
		}
        else {
            throw new Exception("Cannot find view {$this->view_path}");
        }
        return array('content' => $this->content, 'includes' => $this->includes);
    }

    /**
     * Adds headers to the views header array
     *
     * @return void
     */
    protected function get_headers()
    {
        $this->add_header('Content-type', 'text/html');
		return $this->headers;
    }

}

?>