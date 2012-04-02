<?php

namespace Gotron;

/**
 * Sends errors to the ErrorController
 *
 * @package Gotron
 */
class Error {

    public static function error_401($namespace) {
        Header::set("HTTP/1.0 401 Unauthorized");
        Router::perform_controller_action('Error', 'error_page', null, array('page' => 401), $namespace);
    }

    public static function error_403($namespace) {
        Header::set("HTTP/1.0 403 Forbidden");
        Router::perform_controller_action('Error', 'error_page' , null , array('page' => 403), $namespace);
    }

    public static function error_404($namespace) {
        Header::set("HTTP/1.0 404 Not Found");
        Router::perform_controller_action('Error', 'error_page', null, array('page' => 404), $namespace);
    }

    public static function error_500($namespace) {
        Header::set("HTTP/1.0 500 Internal Server Error");
        Router::perform_controller_action('Error', 'error_page', null, array('page' => 500), $namespace);
    }

}

?>