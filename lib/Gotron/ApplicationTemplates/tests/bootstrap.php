<?php

namespace @app_namespace;

define('APP_ENVIRONMENT', 'test');
define('FIXTURE_PATH', __DIR__ . '/fixtures/');

$_SERVER["REMOTE_ADDR"] = 'localhost';

require __DIR__ . '/../vendor/gotron/lib/GTIOUnit/init.php';
require __DIR__ . '/../config/application.php';

foreach (glob(__DIR__ . "/helpers/*.php") as $filename) {
    require_once $filename;
}

$GLOBALS['PHPUNIT_BEFORE_RUN'] = function() {
    /**
     * Bootstrap anything needed just before test run
     **/
};

$GLOBALS['PHPUNIT_AFTER_RUN'] = function() {
    /**
     * Tear down anything after test run
     **/
};

?>
