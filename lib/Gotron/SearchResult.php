<?php

namespace Gotron;

use ActiveRecord\Utils as ArUtils;

class SearchResult {

    public $results;
    public $hits;
    public $max_score;
    public $keys;
    public $total;
    public $offset;
    public $models = [];

    public static function from_search($results) {
        $instance = new static;
        $instance->results = $results;
        $instance->hits = $results['hits']['hits'];
        $instance->total = $results['hits']['total'];
        $instance->max_score = $results['hits']['max_score'];
        $instance->keys_from_hits($instance->hits);
        $instance->load_models();

        return $instance;
    }

    public function keys_from_hits($hits) {
        $this->keys = [];
        foreach ($hits as $hit) {
            $this->keys[$hit['_type']][] = $hit['_id'];
        }
    }

    public function load_models() {
        foreach ($this->keys as $name => $keys) {
            $namespace = Config::get('namespace');
            $model_name = "$namespace\\" . \ActiveRecord\classify($name);

            $models = call_user_func_array([$model_name, 'find_by_pk'], [$keys, [], true]);

            if (!is_array($models))
                $models = [$models];

            $this->models[ArUtils::pluralize($name)] = $models;
        }
    }

}


?>
