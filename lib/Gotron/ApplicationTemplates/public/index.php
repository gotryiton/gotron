<?php

namespace @app_namespace;

use Gotron\Dispatch\Router;

require __DIR__ . "/../config/application.php";

@app_class::initialize();

@app_class::initialize_routes();

@app_class::route();

?>
