<?php

namespace TestApp;

use Gotron\View\JsonView;

class JsonViewTest extends UnitTest {

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
    }
    
    public static function tearDownAfterClass() {
        parent::tearDownAfterClass();
    }

    public function test_render() {
        $text = "This is a test json view";
        $data = array('text' => $text);
        $view = JsonView::render($data);
        $this->assertEquals('{"text":"This is a test json view"}', $view->content);
    }
}

?>
