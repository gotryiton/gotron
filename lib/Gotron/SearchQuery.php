<?php

namespace Gotron;

use ElasticSearch\Client;

class SearchQuery {

    public $connection = null;

    protected $search;
    protected $match;
    protected $index;
    protected $types;

    protected $term;
    protected $from;
    protected $size;
    protected $sort;
    protected $filter;
    protected $has_child;
    protected $has_parent;
    protected $query;
    protected $query_string;

    public function __construct($types = null, $index = null) {
        $this->types = $types;
        $this->index = (!is_null($index)) ? $index : Config::get('es.index');
    }

    public function connection($config = null) {
        if (!is_null($this->connection)) {
            return $this->connection;
        }
        else {
            return $this->new_connection($config);
        }
    }

    public function new_connection($config = null) {
        if (is_null($config)) {
            $app_config = Config::instance();
            $app_config = $app_config['es'];
            $config = [
                'servers' => $app_config['server'],
                'protocol' => $app_config['protocol'],
                'index' => $this->index,
                'type' => $this->types
            ];
        }

        $this->connection = Client::connection($config);
        return $this->connection;
    }

    public function term($field, $value) {
        if (is_null($this->search)) {
            $this->search = [$field => $value];
        }
        else {
            $this->search[$field] = $value;
        }

        return $this;
    }

    public function offset($offset) {
        $this->from = $offset;
        return $this;
    }

    public function limit($limit) {
        $this->size = $limit;
        return $this;
    }

    public function filter($filter = []) {
        $this->filter = $filter;
        return $this;
    }

    public function query_string($query_string = []) {
        $this->query_string = $query_string;
        return $this;
    }

    public function should($should = []) {
        $this->query_string = $query_string;
        return $this;
    }

    public function q($query = []) {
        $this->query = $query;
        return $this;
    }

    public function sort($sort = []) {
        $this->sort = $sort;
        return $this;
    }

    public function run() {
        $query = [];

        if (!is_null($this->query)) {
            $query['query'] = $this->query;
        }

        if (!is_null($this->query_string)) {
            $query['query']['query_string'] = $this->query_string;
        }

        $additional_params = [
            'size',
            'from',
            'sort'
        ];

        foreach ($additional_params as $param) {
            $query = $this->check_null($param, $query);
        }

        $this->last_query = $query;

        $result = $this->connection()->search($query);
        return SearchResult::from_search($result);
    }

    private function check_null($name, $array) {
        if (!is_null($this->$name)) {
            $array[$name] = $this->$name;
        }

        return $array;
    }
}

?>
