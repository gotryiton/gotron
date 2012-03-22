<?php

namespace TestApp;

use Gotron\Application;

require __DIR__ . "/../../../../lib/Gotron/bootstrap.php";

class TestApplication extends Application {
	public static function configuration() {
        return function($config){
            $config->set('i18n.default_locale', 'en');
            $config->set('root_directory', __DIR__ . "/../");
            $config->set('namespace', 'TestApp');
        };
	}
}

?>
