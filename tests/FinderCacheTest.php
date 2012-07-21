<?php

namespace TestApp;

use ActiveRecord\ConnectionManager,
    GTIOUnit\UnitDB\Fixture,
    GTIOUnit\UnitDB\Utils,
    Gotron\Config,
    Gotron\Cache;

class FinderCacheTest extends UnitTest {

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
        $fix->create('book',array('id' => 1,'author' => 'jon'));
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
    
    public function testFinderCacheWithOneConditionString() {
        $books = Book::finder('title_string_in_order',array('title' => 'something'));
        $this->assertEquals(2,count($books));

        $fix = new Fixture(__DIR__ . "/fixtures/");
        $fix->create('book',array('id' => 4,'title' => 'something'));

        $books = Book::finder('title_string_in_order',array('title' => 'something'));
        $this->assertEquals(2,count($books));
        
        Book::clear_finder_cache('title_string_in_order',array('title' => 'something'));

        $books = Book::finder('title_string_in_order',array('title' => 'something'));
        $this->assertEquals(3,count($books));
    }

    public function testFinderCacheClearing() {
        Cache::flush();

        $books = Book::finder('title_string_in_order',array('title' => 'something'), array('totals' => true,  'limit'=>1));
        $this->assertEquals(1,count($books));
        $this->assertEquals(4,$books[0]->id);
        $this->assertEquals(3,$books->total());

        $books = Book::finder('title_string_in_order',array('title' => 'something'));
        $this->assertEquals(3,count($books));

        $fix = new Fixture(__DIR__ . "/fixtures/");
        $fix->create('book',array('id' => 5,'title' => 'something'));

        $books = Book::finder('title_string_in_order',array('title' => 'something'), array('totals' => true,  'limit'=>1));
        $this->assertEquals(1,count($books));
        $this->assertEquals(4,$books[0]->id);
        $this->assertEquals(3,$books->total());

        $books = Book::finder('title_string_in_order',array('title' => 'something'));
        $this->assertEquals(3,count($books));

        Book::clear_finder_cache('title_string_in_order',array('title' => 'something'));
        usleep(1);

        $books = Book::finder('title_string_in_order',array('title' => 'something'), array('totals' => true, 'limit'=>1));
        $this->assertEquals(1,count($books));
        $this->assertEquals(5,$books[0]->id);
        $this->assertEquals(4,$books->total());

        $books = Book::finder('title_string_in_order',array('title' => 'something'));
        $this->assertEquals(4,count($books));

    }

    //  public function testFinderSpecificCacheClearing() {
    //     Cache::flush();

    //     $books = Book::finder('title_string_in_order',array('title' => 'something'), array('totals' => true,  'limit'=>1));
    //     $this->assertEquals(1,count($books));
    //     $this->assertEquals(5,$books[0]->id);
    //     $this->assertEquals(4,$books->total());

    //     $books = Book::finder('title_string_in_order',array('title' => 'something'));
    //     $this->assertEquals(4,count($books));

    //     $fix = new Fixture(__DIR__ . "/fixtures/");
    //     $fix->create('book',array('id' => 6,'title' => 'something'));

    //     $books = Book::finder('title_string_in_order',array('title' => 'something'), array('totals' => true,  'limit'=>1));
    //     $this->assertEquals(1,count($books));
    //     $this->assertEquals(5,$books[0]->id);
    //     $this->assertEquals(4,$books->total());

    //     $books = Book::finder('title_string_in_order',array('title' => 'something'));
    //     $this->assertEquals(4,count($books));

    //     Book::clear_specific_finder_cache('title_string_in_order',array('title' => 'something'), array('totals' => true,  'limit'=>1));

    //     $books = Book::finder('title_string_in_order',array('title' => 'something'), array('totals' => true, 'limit'=>1));
    //     $this->assertEquals(1,count($books));
    //     $this->assertEquals(6,$books[0]->id);
    //     $this->assertEquals(5,$books->total());

    //     $books = Book::finder('title_string_in_order',array('title' => 'something'));
    //     $this->assertEquals(4,count($books));
        
    // }
    
}

?>
