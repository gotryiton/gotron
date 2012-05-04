<?php

namespace Gotron;

class Presenter {

	public $options = array();

    public function __construct($model, $options = array()) {
        $this->model = $model;
        foreach ($options as $key => $value){
        	$this->$key = $value;
        }        
    }

    public static function to_array($model, $options = array()) {
        $instance = new static($model, $options);
        return $instance->as_array();
    }

}

?>