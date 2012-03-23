<?php

namespace TestApp;

use Gotron\Config;

class HelperTest extends UnitTest {

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        $config = Config::instance();
        $config->set('assets.js_location', '/test/assets/directory/javascripts');
        $config->set('assets.css_location', '/test/assets/directory/css');
    }

    public function test_javascript_tag() {
        $this->assertEquals("<script type=\"text/javascript\" src=\"/test/assets/directory/javascripts/test_javascript.js\" ></script>\n", javascript_tag('test_javascript'));
    }

    public function test_css_tag() {
        $this->assertEquals("<link rel=\"stylesheet\" type=\"text/css\" href=\"/test/assets/directory/css/test_css.css\" />\n", css_tag('test_css'));
    }

    public function test_javascript_includes() {
        $includes = array('test_js', 'test_js_two');
        $equals = "<script type=\"text/javascript\" src=\"/test/assets/directory/javascripts/test_js.js\" ></script>\n<script type=\"text/javascript\" src=\"/test/assets/directory/javascripts/test_js_two.js\" ></script>\n";
        $this->assertEquals($equals, javascript_includes(array('js' => $includes)));
    }

    public function test_css_includes() {
        $includes = array('test_css', 'test_css_two');
        $equals = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/test/assets/directory/css/test_css.css\" />\n<link rel=\"stylesheet\" type=\"text/css\" href=\"/test/assets/directory/css/test_css_two.css\" />\n";
        $this->assertEquals($equals, css_includes(array('css' => $includes)));
    }

}

?>
