<?php

namespace Gotron\Instrumentation;

/**
 * Measure performance of a block of code. similar to Instrumenter
 * in Rails ActiveSupport
 *
 * @package Gotron
 */
class Instrumenter {

    /**
     * Name to log with the Instrumenter
     *
     * @var string
     */
    public $name = "instrumenter";

    /**
     * Notifier to send messages
     *
     * @var string
     */
    private $notifier;
    

    public function __construct($name) {
        $this->name = $name;
    }

    /**
     * Singleton
     *
     * @return Instrumenter instance
     */
    public static function instance($name) {
        return new self($name);
    }

    /**
     * Starts the timer and returns the instance
     *
     * @param string $name 
     * @return Instrumenter
     */
    public static function start($name = "instrumenter") {
        $instance = self::instance($name);
        $instance->start = self::micro_time();
        return $instance;
    }

    /**
     * Ends the timer and sends the time and params to notifier
     *
     * @param array $params 
     * @return void
     */
    public function end($params = array()) {
        $this->notify($this->name, $this->start, self::micro_time(), $params);
    }

    /**
     * Send information to notifier
     *
     * @param string $tag 
     * @param float $start
     * @param float $end
     * @param array $params 
     */
    public function notify($tag, $start, $end, $params = array()) {
        $notifier = Notifier::instance();
        $notifier->notify_subscribers($tag, $start, $end, $params);
    }

    /**
     * Gets the time in microseconds
     *
     * @return float
     */
    protected static function micro_time() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

}

?>