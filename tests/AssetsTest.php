<?php

namespace TestApp;

use Gotron\Config,
    Gotron\Assets;

class AssetsTest extends UnitTest {

    public function test_javascript_unset() {
        $this->assertEquals('/assets/javascripts/test.js', Assets::javaScript('test.js'));
    }

    public function test_css_unset() {
        $this->assertEquals('/assets/css/test.css', Assets::css('test.css'));
    }

    public function test_javascript() {
        $config = Config::instance();
        $config->set('assets.js_location', 'http://test_js_location/');

        $this->assertEquals('http://test_js_location/test.js', Assets::javaScript('test.js'));
    }

    public function test_css() {
        $config = Config::instance();
        $config->set('assets.css_location', 'http://test_css_location/');

        $this->assertEquals('http://test_js_location/test.css', Assets::javaScript('test.css'));
    }
}

?>