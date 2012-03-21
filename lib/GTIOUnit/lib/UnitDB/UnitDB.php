<?php

namespace GTIOUnit\UnitDB;

use ActiveRecord\ConnectionManager;
 
class UnitDB {

    public $database = null;

    public function __construct($database = null){
        $this->database = $database;
    }

    protected function run_query($query, $fetch = false, $parameters = array()) {
        $connection = ConnectionManager::get_connection();

        if($fetch) {
            $query = $connection->query($query, $parameters);
            return $query->fetchAll();
        }
        else {
            $connection->query($query, $parameters);
            return true;
        }
    }
}
?>