<?php

namespace Gotron\Dispatch;

use Gotron\Cache,
    Gotron\Logging,
    Gotron\Util\Version;

/**
 * Represents a request received
 *
 * @package Gotron
 */
class Request {

    const DEFAULT_VERSION = "4.0.0";

    /**
     * The path requested
     *
     * @var string
     */
    public $path;

    /**
     * The content_type of the request body
     *
     * @var string
     */
    public $content_type = null;

    /**
     * The content_type to be sent in a response
     *
     * @var string
     */
    public $accept_content_type = null;

    /**
     * The version requested for the application
     *
     * @var integer
     */
    public $version = null;

    /**
     * The accept header from the request
     *
     * @var string
     */
    public $accept_header;

    /**
     * All headers from the request
     *
     * @var string
     */
    public $headers = array();

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

    /**
     * The request method used (GET, POST, PUT, DELETE, PATCH)
     *
     * @var string
     */
    public $method;

    /**
     * The application instance to be passed through the request
     *
     * @var string
     */
    public $app;

    /**
     * Options that can be set in the build method
     *
     * @var string
     */
    private static $allowed_options = array('full_url', 'path', 'params', 'files', 'accept_content_type', 'accept_header', 'app', 'method', 'headers');

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
        if (array_key_exists("Accept", $this->headers)) {
            $accepts = explode(",", $this->headers["Accept"]);
            foreach (array_reverse($accepts) as $accept) {
                // Versioned content_type gets the highest priority, by the original order of string
                if (preg_match("/(v((\d)(.*))\-)/", substr($accept, strpos($accept, "/") + 1 ), $matches)) {
                    $this->version = Version::parse($matches[2], true);
                    if (empty($options['accept_content_type'])) {
                        $this->accept_content_type = str_replace($matches[0], "", $accept);
                    }
                    break;
                }
                else {
                    if (empty($options['accept_content_type'])) {
                        $this->accept_content_type = $accept;
                    }
                }
            }
        }
        if (is_null($this->accept_content_type)) {
            $this->accept_content_type = 'text/html';
        }

        if (is_null($this->version)) {
            if (isset($this->app)) {
                $this->version = Version::parse(constant(get_class($this->app) . "::VERSION"));
            }
            else {
                $this->version = Version::parse(static::DEFAULT_VERSION);
            }
        }

        return true;
    }

    /**
     * Pulls the json header body and adds it to params if content_type is json
     *
     * @return void
     */
    public function load_json_header_body() {
        if ($this->body_content_type() == 'json'){
            $header_body = json_decode(file_get_contents('php://input'), true);
            if (is_array($header_body)) {
                foreach ($header_body as $key => $value){
                     $this->params[$key] = $value;
                }    
            }
        }
    }

    /**
     * Returns a simplified content_type
     *  like "json" for "application/json"
     *
     * @return string
     */
    public function simple_content_type($type) {
        if ( array_key_exists($type, static::$mime_types) ){
            return static::$mime_types[$type];
        }
        elseif ( !is_null($type) ) {
            foreach (static::$mime_types as $type_string => $mime_type){
                if (stripos($type, $type_string)!==false)
                    return $mime_type;
            }
        }
        return 'html';
    }

    /**
     * Simplified content type accepted by the client
     *
     * @return string
     */
    public function simple_accept_content_type() {
        return $this->simple_content_type($this->accept_content_type);
    }

    /**
     * Gets the simple content type of the request body
     *
     * @return mixed
     */
    public function body_content_type() {
        return (array_key_exists("Content-Type", $this->headers)) ? $this->simple_content_type($this->headers["Content-Type"]) : null;
    }

    /**
     * Checks for If-None-Match caching key
     *
     * @return mixed
     */
    public function if_none_match() {
        return array_key_exists('If-None-Match', $this->headers) ? $this->headers['If-None-Match'] : false;
    }

}

?>
