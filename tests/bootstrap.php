<?php

namespace Gotron;

require __DIR__ . '/../lib/GTIOUnit/init.php';
require __DIR__ . '/helpers/UnitTestClass.php';
require __DIR__ . '/helpers/test_app/config/application.php';

$pid_location = __DIR__ . "/tmp/memcached.pid";

$GLOBALS['PHPUNIT_BEFORE_RUN'] = function() use ($pid_location) {
    $port = 11221;

    if(file_exists($pid_location)) {
        $pid = file_get_contents($pid_location);
        $command = "kill $pid 2> /dev/null";
        exec($command);
    }

    $command = "memcached -d -P $pid_location -p " . $port;
    exec($command);
};

$GLOBALS['PHPUNIT_AFTER_RUN'] = function() use ($pid_location) {
    if(file_exists($pid_location)) {
        $pid = file_get_contents($pid_location);
        $command = "kill $pid 2> /dev/null";
        exec($command);
        unlink($pid_location);
    }
};


?>
