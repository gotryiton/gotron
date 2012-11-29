<?php

namespace TestApp;

use GTIOUnit,
    Gotron\Header;

class UnitTest extends GTIOUnit\UnitTest {

    public static function setUpBeforeClass() {
        TestApplication::initialize();
    }

    public static function tearDownAfterClass() {
        Header::flush();
    }
}

?>
