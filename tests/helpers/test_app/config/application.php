<?php

namespace TestApp;

use Gotron\Application;

class TestApplication extends Application {

    const VERSION = '4.1.1';

    public static function configuration() {
        return function($config){
            $config->set('i18n.default_locale', 'en');
            $config->set('root_directory', __DIR__ . "/../");
            $config->set('namespace', 'TestApp');
        };
    }
}

?>
