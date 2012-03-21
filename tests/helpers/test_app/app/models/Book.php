<?php

namespace TestApp;

use Gotron\Model;

class Book extends Model {

    static $create_query = "CREATE TABLE IF NOT EXISTS books(id int primary key auto_increment,title varchar(255) not null,author varchar(255) not null, publisher_id int not null, created_at int(11), updated_at int(11))";
    static $attr_accessible = array('title');
    static $belongs_to = array(
        array('publisher', 'touch' => true)
    );
    
    static $finders = array(
        'title_string' => array('conditions' => array('title = ?',1)),
        'title_hash' => array('conditions' => array('title' => 1)),
        'title_author_string' => array('conditions' => array('title=? AND author=?',1,'john')),
        'title_author_hash' => array('conditions' => array('title' => 1,'author' => 'john')),
        'title_twice' => array('conditions' => array('author=? AND (title=? OR title=?)',1,1,1)),
        'cache_test' => array('conditions' => array('id' => array(1,2,3)),'order' => 'id desc'),
        'title_array' => array('conditions' => array('title in (?) AND author = ?',array(),'john'))
    );

}

?>