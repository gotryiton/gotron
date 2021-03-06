<?php

namespace TestApp;

use Gotron\Config,
    Gotron\Assets;

class AssetsTest extends UnitTest {

    public static function tearDownBeforeClass(){
        $config = Config::instance();
        $config->set('assets_dictionary', ["js" => ["test.js" => "a712hjnk21"], "css" => ["test.css" => "a2821jk1a2"], "images" => ["test.jpg" => "3x819jk1a2"]]);
    }

    public function test_javascript_unset() {
        $this->assertEquals('/assets/js/test.js', Assets::javascript('test'));
    }

    public function test_javascript_multi_level_relative() {
        $config = Config::instance();
        $config->set('assets.js_location', '/devassets/js/');
        $this->assertEquals('/devassets/js/test/test.js', Assets::javascript('test/test'));
        $this->assertEquals('/devassets/js/test/123/test.js', Assets::javascript('test/123/test'));
        $this->assertEquals('/devassets/js/test/123/test/321/test.js', Assets::javascript('test/123/test/321/test'));
    }

    public function test_css_unset() {
        $this->assertEquals('/assets/css/test.css', Assets::css('test'));
    }

    public function test_javascript() {
        $config = Config::instance();
        $config->set('assets.js_location', 'http://test_js_location/');

        $this->assertEquals('http://test_js_location/test.js', Assets::javascript('test'));
    }

    public function test_css() {
        $config = Config::instance();
        $config->set('assets.css_location', 'http://test_css_location/');

        $this->assertEquals('http://test_css_location/test.css', Assets::css('test'));
    }

    public function test_absolute_domain_image() {
        $config = Config::instance();
        $this->assertFalse($config->get('assets.images_location', true));
        $config->set('cdn.domain', 'http://cdn.gotron.com/');
        $this->assertEquals('http://cdn.gotron.com/assets/images/test.jpg', Assets::image('test.jpg', true));
    }

    public function test_hash() {
        $config = Config::instance();
        $config->set('assets_dictionary', ["js" => ["test.something.js" => "a712hjnk21"], "css" => ["test.css" => "a2821jk1a2"], "images" => ["test.jpg" => "3x819jk1a2"]]);
        $config->set('assets.hashed', true);

        $config->set('assets.images_location', 'http://test_images_location/');

        $this->assertEquals('http://test_js_location/test.something_a712hjnk21.js', Assets::javascript('test.something'));
        $this->assertEquals('http://test_css_location/test_a2821jk1a2.css', Assets::css('test'));
        $this->assertEquals('http://test_images_location/test_3x819jk1a2.jpg', Assets::image('test.jpg'));

        $config->set('assets_dictionary', false);
        $config->set('assets.hashed', false);
    }

    public function test_protocol_relative_domain() {
        $config = Config::instance();
        $config->set('assets.images_location', '//test_images_location/');
        $this->assertEquals('//test_images_location/test.jpg', Assets::image('test.jpg'));
    }

    public function test_protocol_absolute_domain() {
        $config = Config::instance();
        $config->set('assets.images_location', '//test_images_location/');
        $this->assertEquals('http://test_images_location/test.jpg', Assets::image('test.jpg', true));
    }
}

?>
