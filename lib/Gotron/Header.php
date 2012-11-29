<?php

namespace Gotron;

/**
 * Class to get and set cookies, uses namespaces
 *
 * @package Gotron
 */
class Header {

    /**
     * Sets a header as header() method. Puts it in GLOBALS array if headers.disable is true
     *
     * @param string $header
     * @param string $replace
     * @param string $http_response_code
     * @return void
     */
    public static function set($header, $replace = true, $http_response_code = null) {
        if (Config::bool('headers.disabled')) {
            return static::set_header_in_globals($header, $replace, $http_response_code);
        }
        else {
            return header($header, $replace, $http_response_code);
        }
    }

    /**
     * Empty the GLOBALS array of headers
     *
     * @return void
     */
    public static function flush() {
        if (Config::bool('headers.disabled')) {
            $GLOBALS['headers'] = array();
        }
    }

    /**
     * Sets header in globals array
     *  used in testing
     *
     * @param string $name
     * @param string $value
     * @return bool
     */
    protected static function set_header_in_globals($header) {
        $GLOBALS['headers'][] = $header;

        return true;
    }

}

?>
