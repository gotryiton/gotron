<?php

namespace TestApp;

use Gotron\Config;

class ApplicationTest extends UnitTest {

    public function testCheckMaintenance() {
        $maintenance_file = file_join(Config::get('root_directory'), "../MAINTENANCE");
        $this->assertFalse(Config::bool('show_maintenance'));
        touch($maintenance_file);
        $app = TestApplication::instance();
        $app->check_maintenance();
        $this->assertTrue(Config::bool('show_maintenance'));
        unlink($maintenance_file);
        $app->check_maintenance();
        $this->assertFalse(Config::bool('show_maintenance'));
    }

    public function testConfigure() {
        $this->assertEquals("test.test.com", Config::get('site_domain'));
        TestApplication::configure(function($config){
            $config->set("site_domain", "test.gotryiton.com");
        });

        $this->assertEquals("test.gotryiton.com", Config::get('site_domain'));
    }

    public function testConfig() {
        $config = TestApplication::config();
        $this->assertInstanceOf('Gotron\Config', $config);

        $this->assertEquals('test.gotryiton.com', TestApplication::config('site_domain'));
    }

    public function testDefineRoutes() {
        $routes = array(
            '/' => 'Homepage:index',
            '/test' => 'Test:index'
        );

        TestApplication::define_routes($routes);
        $this->assertEquals($routes, Config::get('routes'));
        
        TestApplication::initialize_routes();
        $this->assertNotEquals($routes, Config::get('routes'));
    }

}

?>