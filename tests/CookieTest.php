<?php

namespace TestApp;

use Gotron\Cookie,
    Gotron\Config;

class CookieTest extends UnitTest {

    public function test_set() {
        $value = '111111';
        $this->assertTrue(Cookie::set('test_cookie', $value));
    }

    public function test_get() {
        $value = '222222';
        Cookie::set('test_cookie_two', $value);
        $this->assertEquals($value, Cookie::read('test_cookie_two'));
    }

    public function test_get_namespaced() {
        $value = '333333';
        $config = Config::instance();
        $config->set('cookies.namespace', 'test_namespace');
        Cookie::set('test_cookie_three', $value);
        $this->assertEquals($value, Cookie::read('test_cookie_three'));
        $this->assertEquals($value, $GLOBALS['cookies']['test_namespace_test_cookie_three']);
    }

    public function test_delete() {
        $value = '444444';
        $config = Config::instance();
        $config->set('cookies.namespace', 'test_namespace');
        Cookie::set('test_cookie_four', $value);
        Cookie::delete('test_cookie_four');
        $this->assertNull(Cookie::read('test_cookie_four'));
        $this->assertFalse(array_key_exists('test_cookie_four', $GLOBALS['cookies']));
    }

    public function test_is_defined() {
        $value = '555555';
        $config = Config::instance();
        $config->set('cookies.namespace', 'test_namespace');
        Cookie::set('test_cookie_five', $value);
        $this->assertTrue(Cookie::is_defined('test_cookie_five'));
        $this->assertFalse(Cookie::is_defined('test_cookie_not_defined'));
    }

}

?>
