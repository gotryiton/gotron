<?php

namespace Gotron\Dispatch;

use Gotron\Header;

/**
 * Sends errors to the ErrorController
 *
 * @package Gotron
 */
class Error {

    public static function response($status, $request) {
        $request->params = ['status' => $status];

        return Router::perform_controller_action('Error', 'error_page', $request, $request->app);
    }

    public static function error_401($request) {;
        return static::response(401, $request);
    }

    public static function error_403($request) {
        return static::response(403, $request);
    }

    public static function error_404($request) {
        return static::response(404, $request);
    }

    public static function error_500($request) {
        return static::response(500, $request);
    }

}

?>