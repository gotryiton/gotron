<?php

namespace TestApp;

class UtilityTest extends UnitTest {

    public function test_file_join() {
        $this->assertEquals('one/two/three', file_join('one', 'two', 'three'));
        $this->assertEquals('/one/two/three', file_join('/one', 'two', 'three'));
        $this->assertEquals('//one/two/three', file_join('//one', 'two', 'three'));
        $this->assertEquals('//one/two/three', file_join('//one', '/two', '/three'));
        $this->assertEquals('//one/two/three', file_join('//one', '//two', '/three'));

        $this->assertEquals('http://one/two/three', file_join('http://one', '//two', '/three'));
    }

}

?>
