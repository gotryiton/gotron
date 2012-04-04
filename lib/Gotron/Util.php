<?php

namespace Gotron;

class Util {

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
}

?>