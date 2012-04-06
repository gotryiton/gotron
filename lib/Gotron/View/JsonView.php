<?php

namespace Gotron\View;

use Gotron\Exception,
    Gotron\Util;

/**
 * View class used to create JSON views
 *
 * @package Framework
 */
class JsonView extends AbstractView{

    protected $etag = null;

    /**
     * Generate method to be implemented
     *
     * @return bool
     */
    public function generate(array $parameters)
    {
        return array('content' => Util::json_encode($parameters));
    }

    /**
     * Adds headers to the views header array
     *
     * @return void
     */
    protected function get_headers()
    {
        $this->add_header('Content-type', 'application/json');
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
    public function set_etag($id)
    {
        $this->add_header('ETag', $etag);
	}

}

?>