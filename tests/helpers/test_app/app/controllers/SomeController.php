<?php

namespace TestApp;

use Gotron\Controller;

class SomeController extends Controller {

    public function index() {
        $data = array("name" => $this->parameters['name']);
        $this->render(array('json' => $data));
    }

    public function test_json() {
		$data = array("name" => $this->parameters['name']);
        $this->render(array('json' => $data));
    }

    public function test_php_no_layout() {
		$data = array("name" => $this->parameters['name']);
        $this->render($data, array('view' => 'test', 'layout' => false));
    }

    public function test_php_layout_default() {
		$data = array("name" => $this->parameters['name']);
        $this->render($data, array('view' => 'test'));
    }

    public function test_php_layout_set() {
		$data = array("name" => $this->parameters['name']);
        $this->render($data, array('view' => 'test', 'layout' => 'layout_set'));
    }

    public function test_route() {
        $this->render(array('json' => array('test' => 123456)));
    }

    protected function before() {
        echo "hello\n";
    }

    protected function after() {
        echo "\ngoodbye";
    }
}

?>