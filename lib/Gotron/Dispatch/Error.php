<?php

namespace Gotron\Dispatch;

use Gotron\Header;

/**
 * Sends errors to the ErrorController
 *
 * @package Gotron
 */
class Error {

    public static function send($status, $request) {
        $request = Request::build(array(
            "full_url" => $_SERVER['REQUEST_URI'],
            "params" => array('status' => $status),
            'content_type' => $request->content_type,
            'accept_header' => $request->accept_header,
            'app' => $request->app
        ));

        Router::perform_controller_action('Error', 'error_page', $request, $request->app);
    }

    public static function error_401($request) {;
        static::send(401, $request);
    }

    public static function error_403($request) {
        static::send(403, $request);
    }

    public static function error_404($request) {
        static::send(404, $request);
    }

    public static function error_500($request) {
        static::send(500, $request);
    }

}

?>