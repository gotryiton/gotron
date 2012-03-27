<?php

namespace Gotron;

/**
 * Class to get and set cookies, uses namespaces
 *
 * @package Gotron
 */
class Cookie {

    /**
     * Set a cookie in the same way as setcookie function
     *  puts it in Globals if cookies.disabled is true
     *
     * @param string $name 
     * @param string $value 
     * @param int $expire 
     * @return bool
     */
    public static function set($name, $value, $expire = 0) {
        $name = static::namespaced($name);
        if(Config::bool('cookies.disabled')) {
            return static::set_cookie_in_globals($name, $value);
        }
        else {
            return setcookie($name, $value, $expire, "/", '.' . Config::get('site_domain'));
        }
    }

    /**
     * Read the cookie, uses GLOBALS if cookies are disabled
     *
     * @param string $name 
     * @return string
     */
    public static function read($name) {
        $name = static::namespaced($name);
        if(Config::bool('cookies.disabled')) {
            if(isset($GLOBALS['cookies'][$name])) {
                return $GLOBALS['cookies'][$name];
            }
            else {
                return null;
            }
        }
        else {
            return (isset($_COOKIE[$name])) ? $_COOKIE[$name] : null;
        }
    }

    /**
     * Checks if a cookie name is defined
     *
     * @param string $name
     * @return bool
     */
    public static function is_defined($name) {
        return self::read($name) !== null;
    }

    /**
     * Deletes the cookie
     *
     * @param string $name 
     * @return bool
     */
    public static function delete($name) {
        $name = static::namespaced($name);
        if(Config::bool('cookies.disabled')) {
            if(isset($GLOBALS['cookies'][$name])) {
                unset($GLOBALS['cookies'][$name]);
                return true;
            }
        }
        else {
           return static::set($name, '', time() - 3600);
        }
    }

    /**
     * Empty the GLOBALS array of cookies
     *
     * @return void
     */
    public static function flush() {
        if(Config::bool('cookies.disabled')) {
            $GLOBALS['cookies'] = array();
        }
    }

    /**
     * Get the name with namespace prepended
     *
     * @return string
     */
    protected static function namespaced($name) {
        if($namespace = Config::get('cookies.namespace', true)) {
            return "{$namespace}_{$name}";
        }
        else{
            return $name;
        }
    }

    /**
     * Sets cookie in globals array
     *  used in testing
     *
     * @param string $name 
     * @param string $value 
     * @return bool
     */
    protected static function set_cookie_in_globals($name, $value) {
        $GLOBALS['cookies'][$name] = $value;
        return true;
    }

}

?>