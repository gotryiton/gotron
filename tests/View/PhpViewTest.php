<?php

namespace TestApp;

use Gotron\Config,
    Gotron\View\PhpView;

class PhpViewTest extends UnitTest {

    public function test_render() {
        $data = array('name' => 'anybody');
        $content = PhpView::render($data, file_join(__DIR__ , "..", "helpers/test_app/app/views/some/test.php"));
        $this->assertEquals(
            "<div>\n    This is a test view created by anybody \n</div>", 
            $content['content']
        );

        $headers = array(
            'Content-type: text/html'
        );
        $this->assertEquals($headers, $GLOBALS['headers']);
    }
}

?>
