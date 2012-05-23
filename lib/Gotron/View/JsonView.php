<?php

namespace Gotron\View;

use Gotron\Exception,
    Gotron\Helper;

/**
 * View class used to create JSON views
 *
 * @package Framework
 */
class JsonView extends AbstractView{

    protected $etag = null;

	protected $content_type = "application/json";

    /**
     * Generate method to be implemented
     *
     * @return bool
     */
    public function generate(array $parameters, $injected_view = null) {
		$this->content = Helper::json_encode($parameters);
		return $this;
    }

    /**
     * Adds headers to the views header array
     *
     * @return void
     */
    public function get_headers() {
        $this->add_header('Cache-Control', 'no-cache, must-revalidate');
        $this->add_header('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
		return $this->headers;
    }

    /**
     * Sets the etag for 
     *
     * @param string $id 
     * @return void
     */
    public function set_etag($id) {
        $this->add_header('ETag', $etag);
	}

}

?>