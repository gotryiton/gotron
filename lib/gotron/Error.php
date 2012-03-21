<?php

namespace Gotron;

/**
 * Sends errors to the ErrorController
 *
 * @package Gotron
 */
class Error {

    public static function error_401() {
        Header::set("HTTP/1.0 401 Unauthorized");
        Router::perform_controller_action('Error', 'error_page', null, array('page' => 401) );
    }

    public static function error_403() {
        Header::set("HTTP/1.0 403 Forbidden");
        Router::perform_controller_action('Error', 'error_page' , null , array('page' => 403));
    }

    public static function error_404() {
        Header::set("HTTP/1.0 404 Not Found");
        Router::perform_controller_action('Error', 'error_page', null, array('page' => 404));
    }

    public static function error_500() {
        Header::set("HTTP/1.0 500 Internal Server Error");
        Router::perform_controller_action('Error', 'error_page', null, array('page' => 500));
    }

}

?>