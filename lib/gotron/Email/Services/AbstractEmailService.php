<?php

namespace Gotron\Email\Services;

use Gotron\Exception;

abstract class AbstractEmailService {

    // const SERVICE = EMAIL_SERVICE;

    /**
     * Send email with service
     *
     * @param Email $email 
     * @return void
     * @author 
     */
    public static function send_email($email)
    {
        $instance = self::instance(self::SERVICE);
        return $instance->send($email);
    }

    public static function instance($service)
    {
        $class = self::load_adapter_class($service);
        return new $class();
    }

	private static function load_adapter_class($service)
	{
		$class = ucwords($service) . 'Service';
		$service_class = __NAMESPACE__ . "\\$class";
        $source = __DIR__ . "/$class.php";

        if (!file_exists($source)) {
            throw new Exception("$class not found!");
        }

        // require_once($source);
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