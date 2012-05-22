<?php

namespace TestApp;

use Gotron\Dispatch\Router,
    Gotron\Dispatch\Request;

class RouterTest extends UnitTest {

    public function setup() {
        $this->app = TestApplication::instance();
    }

    public function test_route() {
        $_SERVER['REQUEST_URI'] = "/some/test_route";
        $routes = array(
            '/' => 'Homepage:index',
            '/some/[action]' => 'Some'
        );

        TestApplication::define_routes($routes);

        $json = "{\"test\":123456}";
        $this->expectOutputString($json);
        Router::route($this->app);
    }

    public function test_route_with_named_parameter() {
        $_SERVER['REQUEST_URI'] = "/some/test_named/654321";
        $routes = array(
            '/' => 'Homepage:index',
            '/some/[action]/:named' => 'Some',
            '/some/[action]' => 'Some'
        );

        TestApplication::define_routes($routes);

        $json = "{\"test\":654321}";
        $this->expectOutputString($json);
        Router::route($this->app);
    }

    public function test_route_with_multiple_named_parameters() {
        $_SERVER['REQUEST_URI'] = "/some/test_named_two/654321/100001";
        $routes = array(
            '/' => 'Homepage:index',
            '/some/[action]/:named' => 'Some',
            '/some/[action]/:named/:named_two' => 'Some',
            '/some/[action]' => 'Some'
        );

        TestApplication::define_routes($routes);

        $json = "{\"test\":654321,\"test_two\":100001}";
        $this->expectOutputString($json);
        Router::route($this->app);
    }

    public function test_route_with_custom_array_parameter() {
        $_SERVER['REQUEST_URI'] = "/some/test_array/654321/14/22/44";
        $routes = array(
            '/' => 'Homepage:index',
            '/some/[action]/:named' => 'Some',
            '/some/test_array/:named(/*:array_params)' => 'Some:test_array',
            '/some/[action]/:named/:named_two' => 'Some',
            '/some/[action]' => 'Some'
        );

        TestApplication::define_routes($routes);

        $json = "{\"test\":654321,\"test_two\":14,\"test_three\":22,\"test_four\":44}";
        $this->expectOutputString($json);
        Router::route($this->app);
    }

    public function test_route_with_optional_parameters() {
        $_SERVER['REQUEST_URI'] = "/some/test_custom/custom/999";
        $routes = array(
            '/' => 'Homepage:index',
            '/some/[action]/:named' => 'Some',
            '/some/[action](/~custom)' => 'Some',
            '/some/test_array/:named(/*:array_params)' => 'Some:test_array',
            '/some/[action]/:named/:named_two' => 'Some',
            '/some/[action]' => 'Some'
        );

        TestApplication::define_routes($routes);

        $json = "{\"test\":999}";
        $this->expectOutputString($json);
        Router::route($this->app);
    }

    public function test_route_with_optional_named_parameter() {
        $_SERVER['REQUEST_URI'] = "/some/test_optional_named/101010";
        $routes = array(
            '/' => 'Homepage:index',
            '/some/test_optional_named(/:optional_named_parameter)' => 'Some:optional_named',
            '/some/[action]/:named' => 'Some',
            '/some/[action](/~custom)' => 'Some',
            '/some/test_array/:named(/*:array_params)' => 'Some:test_array',
            '/some/[action]/:named/:named_two' => 'Some',
            '/some/[action]' => 'Some'
        );

        TestApplication::define_routes($routes);

        $json = "{\"name\":101010}";
        $this->expectOutputString($json);
        Router::route($this->app);
    }

    public function test_route_with_boolean_parameter() {
        $_SERVER['REQUEST_URI'] = "/some/test_bool/custom/999/test_bool";
        $routes = array(
            '/' => 'Homepage:index',
            '/some/[action]/:named' => 'Some',
            '/some/[action](/~custom/=:test_bool)' => 'Some',
            '/some/[action](/~custom)' => 'Some',
            '/some/test_array/:named(/*:array_params)' => 'Some:test_array',
            '/some/[action]/:named/:named_two' => 'Some',
            '/some/[action]' => 'Some'
        );

        TestApplication::define_routes($routes);

        $json = "{\"test\":true}";
        $this->expectOutputString($json);
        Router::route($this->app);
    }

    public function test_route_with_query_parameters() {
        $_SERVER['REQUEST_URI'] = "/some/test_query?test_query_param=889988";
        $_GET['test_query_param'] = 889988;
        $routes = array(
            '/' => 'Homepage:index',
            '/some/[action]/:named' => 'Some',
            '/some/[action](/~custom/=:test_bool)' => 'Some',
            '/some/[action](/~custom)' => 'Some',
            '/some/test_array/:named(/*:array_params)' => 'Some:test_array',
            '/some/[action]/:named/:named_two' => 'Some',
            '/some/[action]' => 'Some'
        );

        TestApplication::define_routes($routes);

        $json = "{\"test\":889988}";
        $this->expectOutputString($json);
        Router::route($this->app);
    }

    public function test_route_using_one_optional_parameter() {
        $_SERVER['REQUEST_URI'] = "/some/test_custom_only/custom/223344";
        $_GET['test_query_param'] = 889988;
        $routes = array(
            '/' => 'Homepage:index',
            '/some/[action]/:named' => 'Some',
            '/some/[action](/~custom/=:test_bool)' => 'Some',
            '/some/[action](/*:array_params/~custom)' => 'Some',
            '/some/[action](/~custom)' => 'Some',
            '/some/test_array/:named(/*:array_params)' => 'Some:test_array',
            '/some/[action]/:named/:named_two' => 'Some',
            '/some/[action]' => 'Some'
        );

        TestApplication::define_routes($routes);

        $json = "{\"test_custom\":223344}";
        $this->expectOutputString($json);
        Router::route($this->app);
    }

    public function test_perform_controller_action() {
        $json = "{\"test\":123456}";
        $this->expectOutputString($json);

        $request = Request::build(array(
            "full_url" => "http://gotron.com/some/test_route",
            "path" => "/some/test_route",
            "params" => array(),
            "content_type" => "application/json"
        ));

        Router::perform_controller_action("Some", "test_route", $request, $this->app);
    }

    public function test_perform_controller_action_with_params() {
        $json = "{\"name\":\"someone\"}";
        $this->expectOutputString($json);

        $request = Request::build(array(
            "full_url" => "http://gotron.com/some/test_route",
            "path" => "/some/test_json",
            "params" => array("name" => "someone"),
            "content_type" => "application/json"
        ));

        Router::perform_controller_action("Some", "test_json", $request, $this->app);
    }

    public function test_compile_route() {
        $route =  "/test/:some_parameter/[action](/*:params/~page)";
        $compiled = "^(\/test\/[\w\-]+\/[\w\-]+(((\/\w+)*)?((\/page\/\w+))?)?){1}$";
        $matched_route = "/test/125125/index/1a2fa/512a1224/125215/page/2";

        $compiled_route = Router::compile_route($route);
        $this->assertEquals($compiled, $compiled_route);
        $this->assertEquals(1, preg_match("/$compiled_route/", $matched_route));
    }

    public function test_find_best_route() {
        $path = "/some/test_route";
        $routes = array(
            '/' => 'Homepage:index',
            '/some' => 'Some:index',
            '/some/[action]' => 'Some',
            '/someother/route' => 'SomeOther'
        );
    
        $this->assertEquals('/some/[action]', Router::find_best_route($routes, $path));
    }
}

?>
