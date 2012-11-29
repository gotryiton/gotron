<?php

namespace Gotron;

class Presenter {

    /**
     * Array of cached singleton objects.
     *
     * @var array
     */
    private static $instances = array();

    public $u = null;

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


    public static function to_array($model) {
        $root_presenter = Presenter::instance();

        $presenter = self::instance();

        $presenter->u = $root_presenter->u;

        return $presenter->as_array($model);
    }

    public static function inherit_user(&$u){
        $p = self::instance();
        $p->u = $u;
    }

}

?>
