<?php

namespace Gotron;

use ActiveRecord;

class Model extends ActiveRecord\Model {

    static $auto_include_in_dump = array();
    static $auto_exclude_in_dump = array();

    public function dump($args = array()) {
        $arr = $this->attributes();
        $ret = array();

        foreach (static::$auto_exclude_in_dump as $key){
            unset($arr[$key]);
        }

        $includes = array();
        if (isset($args['include']))
            $includes = $args['include'];

        $keys = array_unique(array_merge(array_keys($arr),static::$auto_include_in_dump,$includes));

        foreach ($keys as $key){
            $var_name = Helper::camelize($key);
            $ret[$var_name] = $this->_get_dump_value($key);
        }

        return $ret;
    }

    public function _get_dump_value($k) {
        $value = $this->$k;
        if (!is_array($value) && !is_object($value)) {
            return $value;
        }
        elseif (is_array($value)) {
            $var_value = array();
            foreach ($value as $o){
                if (isset($o) && ($o instanceof Model)) {
                    $var_value[] =$o->dump();
                }
                else {
                    $var_value[] = $o;
                }
            }

            return $var_value;
        }
        elseif (is_object($value)) {
            if (($value instanceof Model)) {
                $var_value = $this->$k->dump();
                return $var_value;
            }
        }
        return NULL;
    }

    public function to_array(array $options = array()) {
        $arr = $this->attributes();

        return $arr;
    }

}

?>
