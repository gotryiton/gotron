<?php

namespace TestApp;

use Gotron\i18n;

class i18nTests extends UnitTest {

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
    }

    public function testGetsLocalizedText() {
        $int = new i18n;
        
        $text = $int->get_text('test','text');
        $this->assertEquals('this is test text', $text);
        
        $this->assertEquals('this is test text', i18n::text('test','text'));
        $this->assertEquals('this is different test text', i18n::text('other_test','text'));
        
        $this->assertEquals('questo è un invito', i18n::text('invite','text','it'));
    }
}

?>