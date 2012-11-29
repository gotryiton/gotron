<?php

namespace GTIOUnit\UnitDB;

class Utils extends UnitDB {

    protected function get_drop_tables_query() {
        $result = $this->run_query("SHOW TABLES FROM {$this->database}", true);
        $drop_query = "";
        if (!empty($result)) {
            foreach ($result as $table) {
                $drop_query .= "DROP TABLE IF EXISTS " . $table[0] . " ; ";
            }
        }
        return ($drop_query) ? $drop_query : false;
    }

    protected function get_truncate_tables_query() {
        $result = $this->run_query("SHOW TABLES FROM {$this->database}", true);
        $drop_query = "";
        if (!empty($result)) {
            foreach ($result as $table) {
                $value = array_shift($table);
                if ($value != 'schema_migrations'){
                    $drop_query .= "TRUNCATE TABLE " . $value . " ; ";
                }
            }
        }

        return ($drop_query) ? $drop_query : false;
    }

    public function empty_db() {
        $sql = $this->getDropTablesQuery();
        if (!empty($sql)) {
            $query = $this->run_query($sql);
        }
    }

    public static function clear_db($database) {
        $instance = new static($database);
        $sql = $instance->get_truncate_tables_query();
        if (!empty($sql)) {
            $query = $instance->run_query($sql);
        }
    }

}

?>
