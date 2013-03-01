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

    /**
     * Custom find for multiple key columns, used for bulk_insert
     *
     * @param array $keys
     * @param array $values
     * @param array $column_names
     * @return array
     **/
    private static function find_by_multiple_keys($keys, $values, $column_names) {
        $key_indices = [];
        $conditions = [];
        foreach ($keys as $column) {
            if (($index = array_search($column, $column_names)) !== false) {
                $key_indices[]= $index;
            }
            else {
                throw new Exception("Key {$column} does not exist in the column names passed");
            }

            $conditions[]= "{$column} = ?";
        }

        $conditions_string = '(' . implode(' AND ', $conditions) . ')';

        $parameter_values = [];
        foreach ($values as $values_array) {
            foreach ($key_indices as $key_index) {
                $parameter_values[]= $values_array[$key_index];
            }
        }

        $conditions = implode(' OR ', array_pad([], count($values), $conditions_string));

        $sql = 'SELECT * FROM ' . static::table()->table
            . ' WHERE ' . $conditions;

        return static::find_by_sql($sql, $parameter_values);
    }

    /**
     * Returns records in an array with keys as the valu of the
     * attributes for the key passed
     *
     * Will not key records for multiple attributes
     *
     * @param array $records
     * @param mixed $key
     * @return array
     **/
    public static function key_records($records, $key) {
        $keyed_records = [];
        if (!is_array($key)) {
            foreach ($records as $record) {
                $keyed_records[$record->$key] = $record;
            }
        }
        else {
            $keyed_records = $records;
        }

        return $keyed_records;
    }

    /**
     * Bulk insert data into the table
     *
     * This method does not run callbacks for the new records created
     *
     * If a key is passed, it will be checked for uniqueness
     *
     * @param array $column_names The column names to insert values into
     * @param array $values An array of arrays of values to insert, each array must be
     *   ordered to match column names
     * @param mixed $key Column name or array of column names to use to check if a record already exists
     * @return array The models inserted
     **/
    public static function bulk_insert($column_names, $values, $key) {
        if (!is_array($column_names)) {
            throw new Exception("Column names must be an array");
        }

        if (!is_array($values) || !is_array($values[0])) {
            throw new Exception("Values to insert must be an array of arrays");
        }

        if (!is_null($key) && (!is_string($key) && !is_array($key))) {
            throw new Exception("Key must be either a string or array");
        }

        if (!is_null($key)) {
            if (is_array($key)) {
                $existing_records = static::find_by_multiple_keys($key, $values, $column_names);
                $existing_keys = array_map(function($record) use($key) {
                    $keys = [];
                    foreach ($key as $column) {
                        $keys[]= $record->$column;
                    }

                    return $keys;
                }, $existing_records);
            }
            else {
                if (($index = array_search($key, $column_names)) !== false) {
                    $key_index = $index;
                }
                else {
                    throw new Exception("Key {$key} does not exist in the column names passed");
                }

                $parameter_values = array_map(function($value_array) use($key_index) { return $value_array[$key_index]; }, $values);
                $existing_records = static::all([$key => $parameter_values]);
                $existing_keys = array_map(function($record) use($key) { return [$record->$key]; }, $existing_records);
            }
        }
        else {
            $existing_records = [];
            $existing_keys = [];
        }

        if (!isset($key_index)) {
            foreach ($key as $column) {
                if (($index = array_search($column, $column_names)) !== false) {
                    $key_indices[]= $index;
                }
            }
        }
        else {
            $key_indices = [$key_index];
        }

        $values = array_filter($values, function($value) use($existing_keys, $key_indices) {
            $check_values = [];
            foreach ($key_indices as $index) {
                $check_values[]= $value[$index];
            }

            return !in_array($check_values, $existing_keys);
        });

        if (count($values) === 0) {
            return [
                'keyed_records' => static::key_records($existing_records, $key),
                'new_count' => 0
            ];
        }

        $columns_string = ' (' . implode(', ', $column_names) . ')';
        $sql = "INSERT INTO " . static::table()->table . $columns_string . ' VALUES';

        $values_string = '(' . implode(',', array_pad([], count($column_names), '?')) . ')';
        $sql .= implode(', ', array_pad([], count($values), $values_string));

        $params = [];
        foreach ($values as $record) {
            foreach ($record as $value) {
                $params[]= $value;
            }
        }

        static::table()->conn->query($sql, $params);

        if (is_array($key)) {
            $new_records = static::find_by_multiple_keys($key, $values, $column_names);
        }
        else {
            $key_values = array_map(function($value_array) use($key_index) { return $value_array[$key_index]; }, $values);
            $new_records = static::all([$key => $key_values]);
        }

        $all_records = array_merge($existing_records, $new_records);

        return [
            'keyed_records' => static::key_records($all_records, $key),
            'new_count' => count($new_records)
        ];
    }

}

?>
