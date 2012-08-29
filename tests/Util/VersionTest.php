<?php

namespace TestApp;

use Gotron\Util\Version;

class VersionTest extends UnitTest {

    public function test_parse_version() {
        $this->assertEquals("3.0.0", Version::parse_version("3"));
        $this->assertEquals("2.0.0", Version::parse_version("2.0"));
        $this->assertEquals("1.0.0", Version::parse_version("1.0.0"));
        $this->assertEquals("3.1.0", Version::parse_version("3.1"));
        $this->assertEquals("2.0.1", Version::parse_version("2.0.1"));
    }

}

?>
