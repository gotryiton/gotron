<?php

namespace @app_namespace;

use Gotron\Application;

require __DIR__ . "/../vendor/gotron/lib/Gotron/bootstrap.php";

class @app_class extends Application {

    const VERSION = "1.0.0";

    public static function configuration() {
        return function($config) {
            $config->set('namespace', '@app_namespace');
            $config->set('i18n.default_locale', 'en');
            $config->set('subdomain', 'www');

            /**
             * Configuration related to all environments goes here
             **/
        };
    }

}

?>
