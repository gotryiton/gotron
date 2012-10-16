<?php 

namespace TestApp;

use Gotron\SearchQuery,
    GTIOUnit\UnitDB\Fixture,
    ActiveRecord\ConnectionManager;

class MockConnection {

    public $response;

    public function search() {
        return $this->response;
    }
}

class SearchQueryTest extends UnitTest {

    public function setup() {
        $connection  = new MockConnection();
        $connection->response = ["hits" => ["total" => 0, "max_score" => 0, "hits" => []]];

        $this->query = new SearchQuery('book', 'testing_index');
        $this->query->connection = $connection;
    }

    public function test_query_string() {
        $result = $this->query->query_string('some query')->run();
        $this->assertEquals(['query' => ['query_string' => "some query"]], $this->query->last_query);
    }

    public function test_sort() {
        $result = $this->query->query_string('some query')->sort("date")->run();
        $this->assertEquals(['query' => ['query_string' => "some query"], "sort" => "date"], $this->query->last_query);
    }

    public function test_limit() {
        $result = $this->query->query_string('some query')->limit(12)->run();
        $this->assertEquals(['query' => ['query_string' => "some query"], "size" => 12], $this->query->last_query);
    }

    public function test_offset() {
        $result = $this->query->query_string('some query')->offset(22)->run();
        $this->assertEquals(['query' => ['query_string' => "some query"], "from" => 22], $this->query->last_query);
    }
}

?>
