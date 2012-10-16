<?php

namespace TestApp;

use Gotron\SearchResult,
    GTIOUnit\UnitDB\Fixture,
    ActiveRecord\ConnectionManager;

class SearchResultTest extends UnitTest {

    public function test_matches_in_result() {
        $connection = ConnectionManager::get_connection();
        $connection->query(Book::$create_query);

        $fix = new Fixture(__DIR__ . "/fixtures/");
        $fix->create('book');
        $fix->create('book', ['id' => 2, 'title' => 'something else']);
        $fix->create('book', ['id' => 3, 'title' => 'something again']);
        $fix->create('book', ['id' => 4, 'title' => 'else again']);

        $response = json_decode(file_get_contents(file_join(__DIR__, "helpers", "search", "query_string.json")), true);

        $result = SearchResult::from_search($response);

        $this->assertCount(3, $result->models['books']);
        $this->assertInstanceOf("TestApp\Book", $result->models['books'][0]);
    }

    public function test_empty_result() {
        $response = ["hits" => ["total" => 0, "max_score" => 0, "hits" => []]];
        $result = SearchResult::from_search($response);

        $this->assertCount(0, $result->models);
        $this->assertFalse(array_key_exists('books', $result->models));
    }


}

?>
