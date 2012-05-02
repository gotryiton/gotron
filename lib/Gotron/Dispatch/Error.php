<?php

namespace Gotron\Dispatch;

use Gotron\Header;

/**
 * Sends errors to the ErrorController
 *
 * @package Gotron
 */
class Error {

    public static function error_401($namespace) {
        Header::set("HTTP/1.0 401 Unauthorized");
        static::call_error_page(401, $namespace);
    }

    public static function error_403($namespace) {
        Header::set("HTTP/1.0 403 Forbidden");
        static::call_error_page(403, $namespace);
    }

    public static function error_404($namespace) {
        Header::set("HTTP/1.0 404 Not Found");
        static::call_error_page(404, $namespace);
    }

    public static function error_500($namespace) {
        Header::set("HTTP/1.0 500 Internal Server Error");
        static::call_error_page(500, $namespace);
    }

    public static function call_error_page($page, $namespace) {
        $request = Request::build(array(
            "full_url" => $_SERVER['REQUEST_URI'],
            "params" => array('page' => $page)
        ));

        Router::perform_controller_action('Error', 'error_page', $request, $namespace);
    }

}

?>