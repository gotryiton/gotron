<?

namespace TestApp;

TestApplication::configure(function($config) {
    $site_host = 'test.test.com';
    $mobile_host = 'mobile.test.com';

    $config->set('site_domain', $site_host);
    $config->set('site_host', $site_host);
    $config->set('site_url', "http://{$site_host}");
    $config->set('mobile_host', $mobile_host);
    $config->set('mobile_url', "http://{$mobile_host}");
    $config->set('open_access_host', $mobile_host);
    $config->set('open_access_url', "http://{$mobile_host}");

    $config->set('beanstalk.host', 'localhost');
    $config->set('beanstalk.port', 11300);

    $config->set('cache.servers',
        array(array('host' => 'localhost', 'port' => '11211'))
    );

    $config->set('notifications.disabled', true);

    $config->set('external_tracking.enabled', true);

    $config->set('headers.disabled', true);
    $config->set('cookies.disabled', true);

    $config->set('email.from', 'test@test.com');

    $config->set('beanstalk.disabled', true);
    $config->set('beanstalk.testing', true);

    $config->set('db.query_logging', true);
});

?>
