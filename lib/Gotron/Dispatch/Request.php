<?php

namespace Gotron\Dispatch;

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
    public $content_type = null;

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
    public $accept_header;

    /**
     * The parameters in the request
     *
     * @var array
     */
    public $params = array();

    /**
     * Files sent in the request
     *
     * @var array
     */
    public $files = array();

    public $app;

    /**
     * Options that can be set in the build method
     *
     * @var string
     */
    private static $allowed_options = array('full_url', 'path', 'params', 'files', 'content_type', 'accept_header', 'app');

    private static $mime_types = array(
        'application/json' => 'json',
        'text/html' => 'html'
    );

    /**
     * Builds a request object with an array of options
     *
     * @param array $options 
     * @return Request
     */
    public static function build($options) {
        $instance = new self;
        foreach (static::$allowed_options as $param) {
            if (array_key_exists($param, $options) && !empty($options[$param])) {
                $instance->$param = $options[$param];
            }
        }
        $instance->load_content_type_and_version($options);
        $instance->load_json_header_body();
        return $instance;
    }

    /**
     * Pulls the Accept header and parses version and content type 
     *
     * @return bool
     */
    public function load_content_type_and_version($options = array()) {
        if (!is_null($this->accept_header)) {
            if(preg_match("/v\d\-/", substr($this->accept_header, strpos($this->accept_header, "/") + 1 ), $matches)) {
                $this->version = (int)str_replace(array("v", "-"), "", $matches[0]);
            }
            if (empty($options['content_type'])) {
                $this->content_type = preg_replace("/v\d\-/", "", $this->accept_header);
            }            
            if (is_null($this->content_type))
                $this->content_type = 'text/html';


            return true;
        }
        return false;
    }

    /**
     * Pulls the json header body and adds it to params if content_type is json
     *
     * @return void
     */
    public function load_json_header_body(){
        if ($this->simple_content_type()=='json'){
            foreach (json_decode(file_get_contents('php://input'), true) as $key => $value){
                 $this->params[$key] = $value;
            }
        }
    }

    /**
     * Returns a simplified content_type
     *  like "json" for "application/json"
     *
     * @return string
     */
    public function simple_content_type() {
        return array_key_exists($this->content_type, static::$mime_types) ? static::$mime_types[$this->content_type] : 'html';
    }

}

?>