<?php

namespace Gotron;

class Presenter extends Singleton {

    public static function to_array($model) {
        $instance = new static($model);
        return $instance->as_array();
    }

}

?>