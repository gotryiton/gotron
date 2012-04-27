<?php

namespace Gotron;

/**
 * Represents a request received
 *
 * @package Gotron
 */
class Request {

    /**
     * The path requested
     *
     * @var string
     */
    public $path;

    /**
     * The content_type requested
     *
     * @var string
     */
    public $content_type = "text/html";

    /**
     * The version requested for the application
     *
     * @var integer
     */
    public $version = 4;

    /**
     * The accept header from the request
     *
     * @var string
     */
    private $accept_header;

    /**
     * The parameters in the request
     *
     * @var string
     */
    public $parameters = array();


    public function build_request() {
        $this->load_content_type_and_version();
    }

    public function load_content_type_and_version() {
        if (array_key_exists("HTTP_ACCEPT", $_SERVER)) {
            $this->accept_header = $_SERVER['HTTP_ACCEPT'];
            if(preg_match("/v\d\-/", substr($this->accept_header, strpos($this->accept_header, "/") + 1 ), $matches)) {
                $this->version = (int)str_replace(array("v","-"), "", $matches[0]);
            }
            $this->content_type = preg_replace("/v\d\-/", "", $this->accept_header);
        }
    }

}

?>