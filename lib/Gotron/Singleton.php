<?php

namespace Gotron;

/**
 * Singleton pattern, as used in php-activerecord
 *
 * @package Gotron
 */
abstract class Singleton {
    /**
     * Array of cached singleton objects.
     *
     * @var array
     */
    private static $instances = array();

    /**
     * Static method for instantiating a singleton object.
     *
     * @return object
     */
    final public static function instance() {
        $class_name = get_called_class();

        if (!isset(self::$instances[$class_name]))
            self::$instances[$class_name] = new $class_name;

        return self::$instances[$class_name];
    }

    /**
     * Singleton objects should not be cloned.
     *
     * @return void
     */
    final private function __clone() {}

}
?>
