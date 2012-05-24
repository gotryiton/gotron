<?php
/**
 * @package ActiveRecord
 */
namespace ActiveRecord;

/**
 * Class to allow find results to store totals (SQL_CALC_FOUND_ROWS) and act as an array
 * 
 */
 
use \ArrayAccess;
use \Iterator;
use \Countable;
 
class Finder implements ArrayAccess, Iterator, Countable
{
    protected $contents = array();
    
    public function __construct($array){
        $this->contents['list'] = $array['list'];
        $this->contents['total'] = $array['total'];
    }

    public function offsetExists($index) {
        return isset($this->contents['list'][$index]);
    }
 
    public function offsetGet($index) {
        if($this->offsetExists($index)) {
            return $this->contents['list'][$index];
        }
        return false;
    }
 
    public function offsetSet($index, $value) {
        if(is_null($index)) {
            $this->contents[] = $value;
        } else {
            $this->contents[$index] = $value;
        }
        return true;
 
    }
 
    public function offsetUnset($index) {
        unset($this->contents['list'][$index]);
        return true;
    }
 
    public function getContents() {
        return $this->contents['list'];
    }

    
    public function rewind() {
        reset($this->contents['list']);
    }

    public function current() {
        return current($this->contents['list']);
    }

    public function key() {
        return key($this->contents['list']);
    }

    public function next() {
        return next($this->contents['list']);
    }

    public function valid() {
        return $this->current() !== false;
    }    

    public function count() {
        return count($this->contents['list']);
    }
    
    public function total() {
        return $this->contents['total'];
    }    
}
?>
