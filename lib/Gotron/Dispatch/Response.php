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
     * The content-type sent back
     *
     * @var string
     */
    public $content_type = "text/html";

	public $status_code = 200;

	public $headers = array();

	public $content = null;

	protected $render = true;

    public $app;

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

	public static function build_from_view($view, $status_code = 200, $render = true) {
		$instance = new self;
		$instance->headers = $view->headers;
		$instance->content_type = $view->content_type();
		$instance->content = $view->content;
		$instance->status_code = $status_code;
		$instance->render = $render;

		return $instance;
	}

	public function send() {
		$this->write_headers();
		if ($this->render) {
			echo $this->content;
		}
	}

	protected function write_headers() {
		Header::set("HTTP/1.1 {$this->status_code} " . self::$status_codes[$this->status_code], true, $this->status_code);
		Header::set("Content-type: {$this->content_type}");
		foreach ($this->headers as $key => $value) {
			Header::set("{$key}: {$value}");
		}
	}

}

?>