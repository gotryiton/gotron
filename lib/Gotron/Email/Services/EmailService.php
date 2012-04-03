<?php

namespace Gotron\Email\Services;

use Gotron\Exception,
    Gotron\Config;

abstract class EmailService {

    const SERVICE = "mailgun";

    /**
     * Send email with service
     *
     * @param Email $email 
     * @return void
     * @author 
     */
    public static function send_email($email) {
        if ($service = Config::get('email.service', true)) {
            $instance = self::instance($service);
        }
        else {
            $instance = self::instance(self::SERVICE);
        }
        return $instance->send($email);
    }

    public static function instance($service) {
        $class = self::load_adapter_class($service);
        return new $class();
    }

	private static function load_adapter_class($service) {
		$class = ucwords($service) . 'Service';
		$service_class = __NAMESPACE__ . "\\$class";
        $source = __DIR__ . "/$class.php";

        if (!file_exists($source)) {
            throw new Exception("$class not found!");
        }

		return $service_class;
	}

    /**
     * Send an email
     *
     * @param Email email
     * @return bool
     */

    protected abstract function send($email);
}

?>