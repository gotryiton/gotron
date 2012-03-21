<?php

namespace TestApp;

class HomeController extends ApplicationController {

    public function index() {
        $data = array("name" => "name");
        $this->render($data, array('view' => 'index'));
    }

    public function test() {
        $data = array("name" => "name");
        $this->render($data, array('view' => 'index'));
    }

}

?>