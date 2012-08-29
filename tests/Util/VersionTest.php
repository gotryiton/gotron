<?php

namespace TestApp;

use Gotron\Util\Version;

class VersionTest extends UnitTest {

    public function test_parse() {
        $this->assertEquals("3.0.0", Version::parse("3"));
        $this->assertEquals("2.0.0", Version::parse("2.0"));
        $this->assertEquals("1.0.0", Version::parse("1.0.0"));
        $this->assertEquals("3.1.0", Version::parse("3.1"));
        $this->assertEquals("2.0.1", Version::parse("2.0.1"));
    }

    public function test_to_s() {
        $this->assertEquals("1.2.4", Version::parse("1.2.4")->to_s());
        $this->assertEquals("1.0.0", Version::parse("1")->to_s());
        $this->assertEquals("1.0.0", Version::parse("1.0")->to_s());
    }

    public function test_find_largest_version() {
        $list = ["3.1.2", "2.5.4", "3.0.0", "2.0.0", "5", "4", "4.2"];
        $version = Version::find_largest_version($list);
        $this->assertEquals("5.0.0", $version->to_s());

        $list = ["2.1.0", "2.56.4", "2.0.0", "2.6.33"];
        $version = Version::find_largest_version($list);
        $this->assertEquals("2.56.4", $version->to_s());
    }

    public function test_parse_multiple() {
        $list = ["2.1.0", "2.56.4", "2.0.0", "2.6.33"];
        $versions = Version::parse_multiple($list);

        $i = 0;
        foreach ($versions as $version) {
            $this->assertEquals($list[$i], $version);
            $i++;
        }
    }

    public function test_version_eq() {
        $version = Version::parse("3.0.0");
        $this->assertTrue($version->eq(Version::parse("3.0.0")));
        $this->assertTrue($version->eq(Version::parse("3")));
        $this->assertTrue($version->eq(Version::parse("3.0")));
        $this->assertFalse($version->eq(Version::parse("5.1.0")));
        $this->assertFalse($version->eq(Version::parse("4.14.0")));
        $this->assertFalse($version->eq(Version::parse("4.0.2")));
    }

    public function test_version_lt() {
        $version = Version::parse("3.0.0");
        $this->assertFalse($version->lt(Version::parse("3.0.0")));
        $this->assertFalse($version->lt(Version::parse("3")));
        $this->assertFalse($version->lt(Version::parse("3.0")));
        $this->assertTrue($version->lt(Version::parse("3.0.1")));
        $this->assertTrue($version->lt(Version::parse("3.2.0")));
        $this->assertTrue($version->lt(Version::parse("3.2.1")));
        $this->assertFalse($version->lt(Version::parse("2.9.9")));
        $this->assertFalse($version->lt(Version::parse("2.0.9")));
        $this->assertFalse($version->lt(Version::parse("2.0.0")));

        $version = Version::parse("2.2.1");
        $this->assertTrue($version->lt(Version::parse("3.0.1")));
        $this->assertTrue($version->lt(Version::parse("3.0.0")));
        $this->assertFalse($version->lt(Version::parse("2.2.1")));
        $this->assertFalse($version->lt(Version::parse("2.0")));
        $this->assertFalse($version->lt(Version::parse("2.0.0")));
        $this->assertFalse($version->lt(Version::parse("2.1.2")));
        $this->assertFalse($version->lt(Version::parse("2.1.0")));
    }

    public function test_version_lt_eq() {
        $version = Version::parse("3.0.0");
        $this->assertTrue($version->lt_eq(Version::parse("3.0.0")));
        $this->assertTrue($version->lt_eq(Version::parse("3")));
        $this->assertTrue($version->lt_eq(Version::parse("3.0")));
        $this->assertTrue($version->lt_eq(Version::parse("3.0.1")));
        $this->assertTrue($version->lt_eq(Version::parse("3.2.0")));
        $this->assertTrue($version->lt_eq(Version::parse("3.2.1")));
        $this->assertFalse($version->lt_eq(Version::parse("2.9.9")));
        $this->assertFalse($version->lt_eq(Version::parse("2.0.9")));
        $this->assertFalse($version->lt_eq(Version::parse("2.0.0")));

        $version = Version::parse("2.2.1");
        $this->assertTrue($version->lt_eq(Version::parse("3.0.1")));
        $this->assertTrue($version->lt_eq(Version::parse("3.0.0")));
        $this->assertTrue($version->lt_eq(Version::parse("2.2.1")));
        $this->assertFalse($version->lt_eq(Version::parse("2.0")));
        $this->assertFalse($version->lt_eq(Version::parse("2.0.0")));
        $this->assertFalse($version->lt_eq(Version::parse("2.1.2")));
        $this->assertFalse($version->lt_eq(Version::parse("2.1.0")));
    }

    public function test_version_gt() {
        $version = Version::parse("3.0.0");
        $this->assertFalse($version->gt(Version::parse("3.0.0")));
        $this->assertFalse($version->gt(Version::parse("3")));
        $this->assertFalse($version->gt(Version::parse("3.0")));
        $this->assertFalse($version->gt(Version::parse("3.0.1")));
        $this->assertFalse($version->gt(Version::parse("3.2.0")));
        $this->assertFalse($version->gt(Version::parse("3.2.1")));
        $this->assertTrue($version->gt(Version::parse("2.9.9")));
        $this->assertTrue($version->gt(Version::parse("2.0.9")));
        $this->assertTrue($version->gt(Version::parse("2.0.0")));

        $version = Version::parse("2.2.1");
        $this->assertFalse($version->gt(Version::parse("3.0.1")));
        $this->assertFalse($version->gt(Version::parse("3.0.0")));
        $this->assertFalse($version->gt(Version::parse("2.2.1")));
        $this->assertTrue($version->gt(Version::parse("2.0")));
        $this->assertTrue($version->gt(Version::parse("2.0.0")));
        $this->assertTrue($version->gt(Version::parse("2.1.2")));
        $this->assertTrue($version->gt(Version::parse("2.1.0")));
    }

    public function test_version_gt_eq() {
        $version = Version::parse("3.0.0");
        $this->assertTrue($version->gt_eq(Version::parse("3.0.0")));
        $this->assertTrue($version->gt_eq(Version::parse("3")));
        $this->assertTrue($version->gt_eq(Version::parse("3.0")));
        $this->assertFalse($version->gt_eq(Version::parse("3.0.1")));
        $this->assertFalse($version->gt_eq(Version::parse("3.2.0")));
        $this->assertFalse($version->gt_eq(Version::parse("3.2.1")));
        $this->assertTrue($version->gt_eq(Version::parse("2.9.9")));
        $this->assertTrue($version->gt_eq(Version::parse("2.0.9")));
        $this->assertTrue($version->gt_eq(Version::parse("2.0.0")));

        $version = Version::parse("2.2.1");
        $this->assertFalse($version->gt_eq(Version::parse("3.0.1")));
        $this->assertFalse($version->gt_eq(Version::parse("3.0.0")));
        $this->assertTrue($version->gt_eq(Version::parse("2.2.1")));
        $this->assertTrue($version->gt_eq(Version::parse("2.0")));
        $this->assertTrue($version->gt_eq(Version::parse("2.0.0")));
        $this->assertTrue($version->gt_eq(Version::parse("2.1.2")));
        $this->assertTrue($version->gt_eq(Version::parse("2.1.0")));
    }
}

?>
