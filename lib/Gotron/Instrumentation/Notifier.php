<?php

namespace Gotron\Instrumentation;

/**
 * Used to send Instrumenter messages to subscribers
 *
 * @package Gotron;
 */
class Notifier {

    /**
     * Array of subscribers grouped by tag
     *
     * @var array
     */
    public $subscribers = array();

    /**
     * Cached Singleton Instance
     */
    private static $instance;

    /**
     * Singleton
     *
     * @return Notifier instance
     */
    public static function instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Adds a subscriber to the list used for the tag
     *
     * @param string $tag
     * @param Subscriber $subscriber
     */
    public static function add_subscriber($tag, $subscriber) {
        $instance = self::instance();
        $subscriber = "Subscribers\\$subscriber";

        if (array_key_exists($tag, $instance->subscribers)) {
            $instance->subscribers[$tag][] = new $subscriber;
        }
        else {
            $instance->subscribers[$tag] = array(new $subscriber);
        }
    }

    /**
     * Publishes messages with subscribers
     *
     * @param string $tag
     * @param float $start
     * @param float $end
     * @param array $params
     * @return void
     * @author
     */
    public static function notify_subscribers($tag, $start, $end, $params = array()) {
        if (defined('UNIQUE_ID')) {
            $params["unique"] = UNIQUE_ID;
        }

        $instance = self::instance();
        if (array_key_exists($tag, $instance->subscribers)) {
            foreach ($instance->subscribers[$tag] as $subscriber) {
                $subscriber->publish($tag, $start, $end, $params);
            }
        }
    }
}

?>
