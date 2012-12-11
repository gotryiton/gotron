<?php

namespace TestApp;

use Gotron\Controller;

class SomeLongController extends Controller {

    public function test_php_layout_default() {
        $data = array("name" => $this->params['name']);
        $this->render($data, array('view' => 'test'));
    }

    public function test_php_layout_set() {
        $data = array("name" => $this->params['name']);
        $this->render($data, array('view' => 'test', 'layout' => 'layout_set'));
    }

}

?>
