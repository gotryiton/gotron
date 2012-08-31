<?php

namespace TestApp;

use Gotron\Config,
    Gotron\Cache,
    Gotron\Dispatch\Request,
    Gotron\Cookie;

class ControllerTest extends UnitTest {

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        Cache::flush();
    }

    public function test_call_method_php_no_layout() {
        $controller = new SomeController;
        $controller->params['name'] = 'somebody';
        $expected_output = "<div>\n    This is a test view created by somebody \n</div>";

        $controller->call_method('test_php_no_layout');
        $this->assertEquals($expected_output, $controller->response->body);
    }

    public function test_call_method_php_layout_default() {
        $controller = new SomeController;
        $controller->params['name'] = 'somebody';
        $expected_output = "This is the start of a test layout\n<div>\n    This is a test view created by somebody \n</div>This is the end of a test layout";

        $controller->call_method('test_php_layout_default');
        $this->assertEquals($expected_output, $controller->response->body);
    }
    
    public function test_call_method_php_layout_set() {
        $controller = new SomeController;
        $controller->params['name'] = 'somebody';
        $expected_output = "This is the start of a test set layout\n<div>\n    This is a test view created by somebody \n</div>This is the end of a test set layout";

        $controller->call_method('test_php_layout_set');
        $this->assertEquals($expected_output, $controller->response->body);
    }
    
    public function test_call_method_json() {
        $controller = new SomeController;
        $controller->params['name'] = 'somebody';
        $expected_output = "{\"name\":\"somebody\"}";

        $controller->call_method('test_json');
        $this->assertEquals($expected_output, $controller->response->body);
    }
    
    public function test_call_method_default() {
        $controller = new SomeController;
        $controller->params['name'] = 'index';
        $expected_output = "{\"name\":\"index\"}";

        $controller->call_method();
        $this->assertEquals($expected_output, $controller->response->body);
    }

    public function test_filters() {
        $controller = new SomeController;
        $controller->params['name'] = 'index';
        $expected_output = "{\"name\":\"index\"}";

        $controller->call_method('filter_test');
        $this->assertEquals($expected_output, $controller->response->body);
        $this->assertEquals(999999, $controller->before_test_variable);
        $this->assertEquals(111111, $controller->after_test_variable);
    }

    public function test_long_controller_name() {
        $controller = new SomeLongController;
        $controller->params['name'] = 'somebody';
        $expected_output = "This is the start of a test set layout\n<div>\n    This is a test view created by somebody \n</div>This is the end of a test set layout";

        $controller->call_method('test_php_layout_set');
        $this->assertEquals($expected_output, $controller->response->body);
    }

    public function test_etag_caching() {
        $controller = new SomeController;
        $controller->params['id'] = 123456;
        $controller->request = Request::build([]);

        $expected_output = '{"text":"Etag cache was not found"}';

        $controller->call_method('test_etag_caching');
        $this->assertEquals($expected_output, $controller->response->body);
        $this->assertEquals(200, $controller->response->status_code);
        $this->assertNotNull($controller->response->headers['ETag']);

        $etag = $controller->response->headers['ETag'];
        
        $controller = new SomeController;
        $controller->request = Request::build(['headers' => ['If-None-Match' => $etag]]);
        $controller->params['id'] = 123456;
        $expected_output = null;
        
        $controller->call_method('test_etag_caching');
        $this->assertEquals($expected_output, $controller->response->body);
        $this->assertEquals(304, $controller->response->status_code);
    }

    public function test_flash_message() {
        $controller = new SomeController;
        $controller->request = Request::build([]);
        $message = "This is my test flash message";
        $controller->redirect_to("/test/path", ['flash' => $message]);

        $this->assertEquals($message, Cookie::read('flash'));

        $controller = new SomeController;
        $controller->request = Request::build([]);
        $controller->call_method("test_route");
        $this->assertEquals($message, $controller->flash_message);
        $this->assertNull(Cookie::read('flash'));
    }

    public function test_presenter_version_multiple_files() {
        $app = TestApplication::instance();
        $controller = new SomeController;
        $controller->params['name'] = 'somebody';
        $controller->request = Request::build(['headers' => ["Accept" => "application/v4.0.1-json"]]);
        $controller->app = $app;
        $controller->app->version_by_request($controller->request->version);

        $expected_output = "{\"name\":\"multi_4.0.1\"}";
        $controller->call_method('test_respond_to_version_multi_presenter');
        $this->assertEquals($expected_output, $controller->response->body);
    }

    public function test_respond_to_no_version_in_request_and_respond_to() {
        $controller = new SomeController;
        $controller->params['name'] = 'somebody';
        $controller->request = Request::build(['accept_content_type' => 'application/json']);
        $expected_output = "{\"name\":\"somebody\"}";

        $controller->call_method('test_respond_to');
        $this->assertEquals($expected_output, $controller->response->body);

        $controller = new SomeController;
        $controller->params['name'] = 'somebody';
        $controller->request = Request::build(['accept_content_type' => 'text/html']);
        $expected_output = "<div>\n    This is a test view created by somebody \n</div>";

        $controller->call_method('test_respond_to');
        $this->assertEquals($expected_output, $controller->response->body);
    }

    public function test_respond_to_no_version_in_request() {
        $controller = new SomeController;
        $controller->params['name'] = 'somebody';
        $controller->request = Request::build(['accept_content_type' => 'application/json']);
        $expected_output = "{\"name\":\"4.0.0\"}";

        $controller->call_method('test_respond_to_version');
        $this->assertEquals($expected_output, $controller->response->body);
    }

    public function test_respond_to_version_in_request_and_respond_to_exact() {
        foreach (['4.0.1', '4.0.0', '3.0'] as $version) {
            $controller = new SomeController;
            $controller->params['name'] = 'somebody';
            $controller->request = Request::build(['headers' => ["Accept" => "application/v{$version}-json"]]);
            $expected_output = "{\"name\":\"{$version}\"}";
            $controller->call_method('test_respond_to_version');
            $this->assertEquals($expected_output, $controller->response->body);
        }
    }

    public function test_respond_to_version_in_request_and_respond_to_not_exact() {
        $controller = new SomeController;
        $controller->params['name'] = 'somebody';
        $controller->request = Request::build(['headers' => ["Accept" => "application/v3.0.5-json"]]);
        $expected_output = "{\"name\":\"3.0\"}";
        $controller->call_method('test_respond_to_version');
        $this->assertEquals($expected_output, $controller->response->body);
    }

    public function test_presenter_version_exact() {
        $app = TestApplication::instance();
        $controller = new SomeController;
        $controller->params['name'] = 'somebody';
        $controller->request = Request::build(['headers' => ["Accept" => "application/v4-json"]]);
        $controller->app = $app;
        $controller->app->version_by_request($controller->request->version);

        $expected_output = "{\"name\":\"4\"}";
        $controller->call_method('test_respond_to_version_presenter');
        $this->assertEquals($expected_output, $controller->response->body);
    }

    public function test_presenter_version_greater_than() {
        $app = TestApplication::instance();
        $controller = new SomeController;
        $controller->params['name'] = 'somebody';
        $controller->request = Request::build(['headers' => ["Accept" => "application/v4.0.1-json"]]);
        $controller->app = $app;
        $controller->app->version_by_request($controller->request->version);

        $expected_output = "{\"name\":\"4\"}";
        $controller->call_method('test_respond_to_version_presenter');
        $this->assertEquals($expected_output, $controller->response->body);
    }

}

?>
