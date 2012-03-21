<?php

namespace TestApp;

use Gotron\Cache;

require __DIR__ . "/helpers/TestClass.php";
require __DIR__ . "/helpers/TestClassTwo.php";

class CacheTest extends UnitTest {

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        Cache::flush();
    }

    /**
     * Testing the cache closure functionality
     *
     */
    public function testGet() {
        $string = "testing the cache";
        $key = "test_cache";
        
        $this->assertFalse(Cache::fetch($key));
        
        $response = Cache::get($key, function() use($string){
            return $string;
        });
        
        $this->assertEquals($string, $response);
        
        $this->assertEquals($string, Cache::fetch($key));
    }

    /**
     * Testing the ability to get key from an object and concatenate keys
     * from an array
     */
    public function testGetKey() {
        $class = new \TestClass;
        $this->assertEquals("test_class_cache_key", Cache::get_key($class));
    
        $class = new \TestClassTwo;
        $this->assertEquals("test_class_cache_key", Cache::get_key($class));
    
        $this->assertEquals("test_class_cache_key/123456", Cache::get_key(array($class, "123456")));
    }
}

?>