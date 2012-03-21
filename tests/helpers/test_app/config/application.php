<?php

namespace TestApp;

use Gotron\Application;

require __DIR__ . "/../../../../lib/gotron/bootstrap.php";

class TestApplication extends Application {
	public static function configuration() {
        return function($config){
            $config->set('i18n.default_locale', 'en');
            $config->set('root_directory', __DIR__ . "/../");
        };
	}
}

?>
