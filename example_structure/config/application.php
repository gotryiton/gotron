<?php

namespace TestApp;

use GTIO\Application;

require __DIR__ . "/../vendor/GTIO/lib/GTIO/bootstrap.php";

class GtioTestApplication extends Application {
	public static function configuration() {
        return function($config) {
            $config->set('i18n.default_locale', 'en');
        };
	}
}

?>
