<?php

namespace Gotron\Instrumentation\Subscribers;

/**
 * Abstract Subscriber class used to subscribe 
 * to Instrumenter notifications
 *
 * @package Gotron
 */
abstract class AbstractSubscriber {

    private static $instance;

    final public static function instance()
	{
		$class_name = get_called_class();

		if (!isset(self::$instance)) {
			self::$instance = new $class_name;
        }

		return self::$instance;
	}

    /**
     * Overwrite this with the actual code to publish this message
     *
     * @param string $tag 
     * @param float $start 
     * @param float $end 
     * @param array $params 
     * @return bool
     */
    abstract function publish($tag, $start, $end, $params = array());
}

?>