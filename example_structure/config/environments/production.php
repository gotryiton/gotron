<?php

namespace TestApp;

TestApp::configure(function($config){
	$site_host = 'www.test_app.com';
	$mobile_host = 'm.test_app.com';
    $config->set('site_domain', $site_host);
	$config->set('site_host', $site_host);
	$config->set('site_url', "http://{$site_host}");
	$config->set('mobile_host', $mobile_host);
	$config->set('mobile_url', "http://{$mobile_host}");
});

?>