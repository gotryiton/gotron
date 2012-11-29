<?php

namespace TestApp;

use Gotron\Model;

class Agent extends Model {

    static $create_query = "CREATE TABLE IF NOT EXISTS agents(id int primary key auto_increment, name varchar(255) not null)";
    static $attr_accessible = array('name');

}

?>
