<?php

namespace TestApp;

use GTIO\Router;

require __DIR__ . "/../config/application.php";
GtioTestApplication::initialize();

Router::route('TestApp');

?>