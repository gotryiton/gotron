<?php

require __DIR__ . "/../../vendor/Aura.Autoload/src.php";
require __DIR__ . '/lib/ActiveRecord/Utils.php';

$loader = new \Aura\Autoload\Loader;
$loader->setMode(0);
$loader->setPaths(array(
    'ActiveRecord\\' => __DIR__ . "/lib",
    '' => __DIR__ . "/test/models"
));

$loader->register();
?>
