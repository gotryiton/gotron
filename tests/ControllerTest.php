<?php

namespace TestApp;

use Gotron\Config;

class ControllerTest extends UnitTest {

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
    }

    public function test_call_method_php_no_layout() {
        $controller = new SomeController;
        $controller->parameters['name'] = 'somebody';

        $this->expectOutputString("hello\n<div>\n    This is a test view created by somebody \n</div>\ngoodbye");
        $controller->call_method('test_php_no_layout');
    }

    public function test_call_method_php_layout_default() {
        $controller = new SomeController;
        $controller->parameters['name'] = 'somebody';
    
        $this->expectOutputString("hello\nThis is the start of a test layout\n<div>\n    This is a test view created by somebody \n</div>This is the end of a test layout\ngoodbye");
        $controller->call_method('test_php_layout_default');
    }
    
    public function test_call_method_php_layout_set() {
        $controller = new SomeController;
        $controller->parameters['name'] = 'somebody';
    
        $this->expectOutputString("hello\nThis is the start of a test set layout\n<div>\n    This is a test view created by somebody \n</div>This is the end of a test set layout\ngoodbye");
        $controller->call_method('test_php_layout_set');
    }
    
    public function test_call_method_json() {
        $controller = new SomeController;
        $controller->parameters['name'] = 'somebody';
        $json = "hello\n{\"name\":\"somebody\"}\ngoodbye";
        $this->expectOutputString($json);
        $controller->call_method('test_json');
    }
    
    public function test_call_method_default() {
        $controller = new SomeController;
        $controller->parameters['name'] = 'index';
        $json = "hello\n{\"name\":\"index\"}\ngoodbye";
        $this->expectOutputString($json);
        $controller->call_method();
    }

	public function test_long_controller_name() {
        $controller = new SomeLongController;
        $controller->parameters['name'] = 'somebody';

        $this->expectOutputString("This is the start of a test set layout\n<div>\n    This is a test view created by somebody \n</div>This is the end of a test set layout");
        $controller->call_method('test_php_layout_set');
    }

}

?>