<?php

namespace Gotron;

class Helper {

    function camelize($string, $pascalCase = false) {
        $string = str_replace(array('-', '_'), ' ', $string); 
        $string = ucwords($string); 
        $string = str_replace(' ', '', $string);

        if(!$pascalCase) {
            return lcfirst($string);
        }

        return $string;
    }

    function uncamelize($input) { 
        return preg_replace(
            '/(^|[a-z])([A-Z0-9])/e',
            'strtolower(strlen("\\1") ? "\\1_\\2" : "\\2")',
            $input
        ); 
    }

    public static function handle_error($errno, $errstr, $errfile, $errline, array $errcontext) {
        // error was suppressed with the @-operator
        if (0 === error_reporting()) {
            return false;
        }
        Logging::log($errstr,'json_view');
    }

    public static function json_encode($data) {
        set_error_handler(array('static', 'handle_error'));
        $data = json_encode($data);
		restore_error_handler();
        return $data;
    }
}

?>