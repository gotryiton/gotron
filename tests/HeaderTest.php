<?php

namespace TestApp;

use Gotron\Header;

class HeaderTest extends UnitTest {

    public function test_set() {
        $value = 'test_header:111111';
        $this->assertTrue(Header::set($value));
        $this->assertEquals(array($value), $GLOBALS['headers']);
    }

}

?>