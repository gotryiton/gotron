<?php

namespace @app_namespace;

@app_class::configure(function($config) {
    $config->set('site_domain', 'test.@app_name.com');

    $config->set_constant('TESTING', true);
    $config->set('emails.disabled', true);
    $config->set('db.query_logging', true);
    $config->set('cookies.disabled', true);
    $config->set('headers.disabled', true);

    /*
     * Test specific configuration goes here
     */

});

?>
