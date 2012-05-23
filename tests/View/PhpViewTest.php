<?php

namespace TestApp;

use Gotron\Config,
    Gotron\View\PhpView,
    Gotron\Header;

class PhpViewTest extends UnitTest {

    public function setup() {
        Header::flush();
    }

    public function test_render() {
        $data = array('name' => 'anybody');
        $view = PhpView::render($data, file_join(__DIR__ , "..", "helpers/test_app/app/views/some/test.php"));
        $this->assertEquals(
            "<div>\n    This is a test view created by anybody \n</div>", 
            $view->content
        );

        $this->assertEquals(array(), $GLOBALS['headers']);
    }

    public function test_render_with_partial() {
        $data = array('name' => 'anybody');
        $view = PhpView::render($data, file_join(__DIR__ , "..", "helpers/test_app/app/views/some/test_2.php"));
        $this->assertEquals(
            "<div>\n    This is a test view created by anybody \n</div>\n<div>Name is: anybody</div>", 
            $view->content
        );
        $this->assertEquals(array(), $view->headers);
    }
}

?>
