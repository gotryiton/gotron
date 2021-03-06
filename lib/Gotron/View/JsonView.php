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
        if (!empty($parameters)) {
            $this->content = Helper::json_encode($parameters);
        }
        else {
            $this->content = "";
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
