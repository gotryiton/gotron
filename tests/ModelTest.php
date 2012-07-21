<?php

namespace TestApp;

use ActiveRecord\ConnectionManager,
    GTIOUnit\UnitDB\Fixture,
    GTIOUnit\UnitDB\Utils,
    Gotron\Config,
    Gotron\Cache;

class ModelTest extends UnitTest {

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();

        $config = Config::instance();
        $config->set('model_directory', 'tests/GTIO/helpers/mdoels');

        Utils::clear_db($config['database']);

        Cache::flush();

        $connection = ConnectionManager::get_connection();
        $connection->query(Book::$create_query);
        $connection->query(Publisher::$create_query);
        $connection->query(Agent::$create_query);
        
        $fix = new Fixture(__DIR__ . "/fixtures/");
        $fix->create('book');
        $fix->create('book',array('id' => 2,'author' => 'paul'));
        $fix->create('book',array('id' => 3,'title' => 'nothing'));
    }
    
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        $connection = ConnectionManager::get_connection();
        $connection->query("DROP TABLE IF EXISTS books");
        $connection->query("DROP TABLE IF EXISTS publishers");
        $connection->query("DROP TABLE IF EXISTS agents");
    }
        
    public function setUp(){
        $this->expectOutputString('');
    }
    
    public function testGetByFinderWithOneConditionString() {
        $books = Book::finder('title_string',array('title' => 'something'));
        $this->assertEquals(2,count($books));
    }
    
    public function testGetByFinderWithOneConditionHash() {
        $books = Book::finder('title_hash',array('title' => 'something'));
        $this->assertEquals(2,count($books));
    }
    
    public function testGetByFinderWithMultipleUserDefinedConditionsString() {
        $books = Book::finder('title_author_string',array('title' => 'something','author' => 'john'));
        $this->assertEquals(1,count($books));
    }
    
    public function testGetByFinderWithMultipleUserDefinedConditionsHash() {
        $books = Book::finder('title_author_hash',array('title' => 'something','author' => 'john'));
        $this->assertEquals(1,count($books));
    }
    
    public function testGetByFinderWithOneUserDefinedConditionAndOnePreDefinedString() {
        $books = Book::finder('title_author_string',array('title' => 'something'));
        $this->assertEquals(1,count($books));
    }
    
    public function testGetByFinderWithOneUserDefinedConditionAndOnePreDefinedHash() {
        $books = Book::finder('title_author_hash',array('title' => 'something'));
        $this->assertEquals(1,count($books));
    }
    
    public function testGetByFinderWithTwoOfTheSameAttributes() {
        $books = Book::finder('title_twice',array('title' => array('something','nothing'),'author' => 'john'));
        $this->assertEquals(2,count($books));
    }
    
    public function testGetByFinderWithArrayInConditions() {
        $books = Book::finder('title_array',array('title' => array('something','nothing')));
        $this->assertEquals(2,count($books));
    }

    public function testGetByFinderWithMultipleIdFields() {
        $fix = new Fixture(__DIR__ . "/fixtures/");
        $fix->create('publisher', array('id' => 26));
        $fix->create('book', array('id' => 100, 'publisher_id' => 26));
        $fix->create('book',array('id' => 101,'author' => 'dave', 'publisher_id' => 26));
        $fix->create('book',array('id' => 102, 'title' => 'nothing', 'publisher_id' => 26));
        $books = Book::finder('multiple_ids', array('publisher_id' => '26', 'id' => 100));
        $this->assertEquals(2, count($books));
    }

    public function testGetByFinderWithMultipleIdFieldsMultiArray() {
        $fix = new Fixture(__DIR__ . "/fixtures/");
        $fix->create('book', array('id' => 103, 'publisher_id' => 26));
        $books = Book::finder('multiple_id_array', array('publisher_id' => '26', 'id' => array(100, 103)));
        $this->assertEquals(2, count($books));
    }

    public function testLoadAndTouchModel() {
        $fix = new Fixture(__DIR__ . "/fixtures/");
        $fix->create('publisher', array('id' => 22));
        $fix->create('book', array('id' => 10, 'publisher_id' => 22));
        $fix->create('book',array('id' => 11,'author' => 'paul', 'publisher_id' => 22));
        $fix->create('book',array('id' => 12,'title' => 'nothing', 'publisher_id' => 22));

        $publisher = Publisher::find(22);
        $book = Book::find(10);

        $current_updated_time = $publisher->updated_at;
        $book->author = "Some new author";
        $book->save();

        $publisher->reload();

        $this->assertGreaterThan($current_updated_time, $publisher->updated_at);
        

    }

    public function testCacheKeyWithUpdatedAt() {
        $fix = new Fixture(__DIR__ . "/fixtures/");
        $fix->create('book',array('id' => 13,'title' => 'whatever', 'publisher_id' => 22, 'updated_at' => 1234321));
        $book = Book::find(13);
        $this->assertEquals("book/13/1234321", $book->cache_key());
    }

    public function testCacheKeyWithoutUpdatedAt() {
        $fix = new Fixture(__DIR__ . "/fixtures/");
        $fix->create('agent');
        $agent = Agent::find(1);
        $this->assertEquals("agent/1", $agent->cache_key());
    }



}

?>
