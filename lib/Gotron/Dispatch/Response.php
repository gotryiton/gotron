<?php

namespace Gotron\Dispatch;

use Gotron\Header;

/**
 * Represents a response to send
 *
 * @package Gotron
 */
class Response {

   /**
     * Content-type sent back
     *
     * @var string
     */
    public $content_type = "text/html";

    /**
     * Status code sent for the response
     *
     * @var string
     */
	public $status_code = 200;

    /**
     * Headers to be sent with the response as key => value
     *
     * @var array
     */
	public $headers = array();

    /**
     * The content body
     *
     * @var string
     */
	public $body = null;

    /**
     * If the response should actually be rendered
     *
     * @var string
     */
	protected $render = true;

    /**
     * List of HTTP/1.1 status codes and definitions
     *
     * @var array
     */
    private static $status_codes = array(
        100 => 'Continue',
		101 => 'Switching Protocols',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Large',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout'
    );

    /**
     * Build a Response object from a view object
     *
     * @param View $view
     * @param int $status_code Status code of the response
     * @param bool $render If the view should be rendered
     * @return Response
     */
	public static function build_from_view($view, $status_code = 200, $render = true) {
		$instance = new self;
		$instance->headers = $view->get_headers();
		$instance->content_type = $view->content_type();
		$instance->content = $view->content;
		$instance->status_code = $status_code;
		$instance->render = $render;

		return $instance;
	}

    /**
     * Send the response to the client
     *
     * @return void
     */
	public function send() {
		$this->write_headers();
		if ($this->render) {
			echo $this->content;
		}
	}

    /**
     * Sends headers to the client
     *
     * @return void
     */
	protected function write_headers() {
		Header::set("HTTP/1.1 {$this->status_code} " . self::$status_codes[$this->status_code], true, $this->status_code);
		Header::set("Content-type: {$this->content_type}");
		foreach ($this->headers as $key => $value) {
			Header::set("{$key}: {$value}");
		}
	}

}

?>