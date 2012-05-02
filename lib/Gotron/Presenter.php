<?php

namespace Gotron;

class Presenter {

    public static function to_array($model, $options = array()) {
        $instance = new static($model, $options);
        return $instance->as_array();
    }

}

?>