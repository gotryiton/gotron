<?php

namespace Gotron;

class Presenter extends Singleton {

    public static function as_array($model) {
        $instance = new static($model);
        return $instance->to_array();
    }

}

?>