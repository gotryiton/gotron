<?php

namespace TestApp;

use Gotron\Model;

class Publisher extends Model {

    static $create_query = "CREATE TABLE IF NOT EXISTS publishers(id int primary key auto_increment, name varchar(255) not null, created_at int(11), updated_at int(11))";
    static $attr_accessible = array('name');
    static $has_many = array('books');
}

?>