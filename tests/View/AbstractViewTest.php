<?php

namespace TestApp;

use Gotron\Header,
    Gotron\View\TestView;

class AbstractViewTest extends UnitTest {

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();

        require_once __DIR__ . "/../helpers/TestView.php";

    }
    
    public static function tearDownAfterClass() {
        parent::tearDownAfterClass();
    }

    public function test_render() {
        $text = "This is a test view";
        $data = array('text' => $text);
        $this->assertEquals($text, TestView::render($data));
    }

    public function test_headers() {
        Header::flush();

        $view = new TestView;
        $view->add_header("test", "header");
        $this->assertEquals(array("test" => "header"), $view->headers);
        $view->set_headers();

        $this->assertEquals(array('test: header', 'Content-type: text/test'), $GLOBALS['headers']);
    }

}

?>
