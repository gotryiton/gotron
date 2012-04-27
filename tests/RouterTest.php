<?php

namespace TestApp;

use Gotron\Router;

class RouterTest extends UnitTest {

    public function test_route() {
        $_SERVER['REQUEST_URI'] = "/some/test_route";
        $routes = array(
            '/' => 'Homepage:index',
            '/some/[action]' => 'Some'
        );

        TestApplication::define_routes($routes);

        $json = "hello\n{\"test\":123456}\ngoodbye";
        $this->expectOutputString($json);
        Router::route('TestApp');
    }

    public function test_perform_controller_action() {
        $json = "hello\n{\"test\":123456}\ngoodbye";
        $this->expectOutputString($json);
        Router::perform_controller_action("Some", "test_route", array(), array(), 'TestApp', array());
    }
    
    public function test_perform_controller_action_with_params() {
        $json = "hello\n{\"name\":\"someone\"}\ngoodbye";
        $this->expectOutputString($json);
        Router::perform_controller_action("Some", "test_json", array(), array("name" => "someone"), 'TestApp', array());
    }
    
}

?>
