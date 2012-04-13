<?php

namespace Gotron;

use ActiveRecord;

class Model extends ActiveRecord\Model {

    static $auto_load_associations_for_model = array();
	static $auto_load_associations_for_finder = array();
	static $auto_include_in_dump = array();
	static $auto_exclude_in_dump = array();
	static $finders = array();
    static $finder_default_ttl = 120;
    static $cache_ttl = 3600;
			
	public function __construct(  $attributes=array(), $guard_attributes=true, $instantiating_via_find=false, $new_record=true)
	{
		if (is_string($attributes) || is_int($attributes)){

			parent::__construct (array(), $guard_attributes, $instantiating_via_find, false);
			
            return $this->load_from_static_find_obj(static::find($attributes, array('limit'=>1)));
			
			
		}
		else {
			parent::__construct ($attributes, $guard_attributes, $instantiating_via_find, $new_record);
		}

	}


	private function load_from_static_find_obj($obj){
		$this->set_attributes($obj->attributes());
		$this->reset_dirty();
	}

	public function dump($args = array() ){
        
		$arr = $this->attributes();
        $ret = array();

        foreach(static::$auto_exclude_in_dump as $key){
            unset($arr[$key]);
        }

        $includes = array();
        if (isset($args['include']))
            $includes = $args['include'];

        $keys = array_unique(array_merge(array_keys($arr),static::$auto_include_in_dump,$includes));
                
        foreach($keys as $key){
            $varName = Util::camelize($key);
            $ret[$varName] = $this->_get_dump_value($key);
        }
		return $ret;
	}

    public function _get_dump_value($k){
        $value = $this->$k;
        if(!is_array($value) && !is_object($value))
        {
            return $value;
        }
        elseif (is_array($value)){
            $varValue = array();    
            foreach ($value as $o){
                if (isset($o) && ($o instanceof Model) ){
                    $varValue[] =$o->dump();
                }
                else {
                    $varValue[] = $o;
                }
            }
            return $varValue;
            
        }
        elseif (is_object($value)){
            if (($value instanceof Model) ) {
                $varValue = $this->$k->dump();
                return $varValue;
            }
        }
        return NULL;

    }
	
	public function to_array(){
		$arr = $this->attributes();
		
		return $arr;
	}
	
	/**
     * Takes an array of objects, filters by a unique attribute and returns an array of a limited number of objects
     * 
     * @param array $objects
     * @param string $limit
     * @param string $attribute 
     * @return array
     */
     
    public static function unique($objects,$attribute,$limit = 10){
        $used_attributes = array();
        $returned_objects = array();
        foreach($objects as $object){
            $value = $object->read_attribute($attribute);
            if(!in_array($value,$used_attributes)){
                $used_attributes[] = $object->read_attribute($attribute);
                $returned_objects[] = $object;
                if(count($returned_objects) == $limit){
                    return $returned_objects;
                }
            }
        }
        return $returned_objects;
    }
    
    /**
     * Loads options from predefined finder and runs a find on the Model
     *
     * - set or override conditions by setting conditions array
     * - set or override limit, offset, order by, etc.. by setting filters array
     * - if an attribute appears multiple times in the conditions string set the attribute    
     *   as an array with each value
     *
     * @param string $name 
     * @param array $additional_options
     * @param array $filters
     * @return array of model objects
     */
    public static function finder($name,$conditions = array(),$filters = array(), $ignore_cache = false){
        if(array_key_exists($name,static::$finders)){
            $cache_id = self::finder_cache_id($name,$conditions,$filters);
            $found_objects = false;
            if (!$ignore_cache) {
                if($finder = \ActiveRecord\Cache::fetch($cache_id))
                {
                    $default_options = static::$finders[$name];
                    if (!array_key_exists('include', $default_options )) {
                        $default_options['include'] = static::$auto_load_associations_for_finder;  
                    }
                    $found_ids = $finder['ids'];
                    $totals = $finder['totals'];

                    $pk_options = $default_options;
                    if(array_key_exists('conditions',$pk_options)) unset($pk_options['conditions']);
                    if(array_key_exists('joins',$pk_options)) unset($pk_options['joins']);
                    if(array_key_exists('order',$pk_options)) unset($pk_options['order']);
                    if(array_key_exists('limit',$pk_options)) unset($pk_options['limit']);
                    if(array_key_exists('group',$pk_options)) unset($pk_options['group']);
                    if(array_key_exists('sql',$pk_options)) unset($pk_options['sql']);
                    if(array_key_exists('totals',$pk_options)) unset($pk_options['totals']);
                    if (!empty($found_ids)) {
                        $found_objects = static::find_by_pk($found_ids,$pk_options,true);
                    }
                    else {
                        $found_objects = array();
                    }

                    if (!is_array($found_objects))
                        $found_objects = array($found_objects);
                    if (!is_null($totals)){
                        $found_objects = new \ActiveRecord\Finder(array('list' => $found_objects,'total' => $totals));
                    }
                }
            }
            if ($found_objects === false){
                $default_options = static::$finders[$name];
                if (array_key_exists('ttl', $default_options )) {
                    $cache_ttl =  $default_options['ttl'];
                    unset($default_options['ttl']);
                }else {
                    $cache_ttl = static::$finder_default_ttl;  
                } 
                if (!array_key_exists('include', $default_options )) {
                    $default_options['include'] = static::$auto_load_associations_for_finder;
                }
                if(array_key_exists('sql', $default_options) && count($default_options['sql'])){
                    $sql_parameters = (isset($conditions['sql_parameters'])) ? $conditions['sql_parameters'] : $default_options['sql'][1];
                    $sql = static::create_sql_from_finder($default_options, $filters);
                    $found_objects = static::find_by_sql($sql,$sql_parameters,true,$default_options['include']); 
                }
                else{
                    $options = static::create_options_from_finder($default_options,$conditions,$filters);
                    $found_objects = static::find('all',$options);
                }
                $object_ids = array();
                foreach($found_objects as $object){
                    $object_ids[]= $object->read_attribute($object->get_primary_key(true));
                }
                $totals = null;
                if(isset($filters['totals']) && $filters['totals'] == true)
                    $totals = $found_objects->total();
                $cached_array = array('ids' => $object_ids, 'totals' => $totals);
                if ($cache_ttl>0) \ActiveRecord\Cache::set($cache_id, $cached_array, $cache_ttl);
            }
            return $found_objects;

        }
        else{
            throw new Exception("Finder '$name' does not exist");
        }
    }

    /**
     * Loads options from predefined finder and runs a find on the Model
     *
     * - clears the cache of a finder
     *
     * @param string $name 
     * @param array $additional_options
     * @param array $filters
     * @return array of model objects
     */
    public static function clear_finder_cache($name,$conditions = array(),$filters = array()){
        if(array_key_exists($name,static::$finders)){
            $cache_id = self::finder_cache_id($name,$conditions,$filters);
            return \ActiveRecord\Cache::delete($cache_id);
        }
        else{
            throw new Exception("Finder '$name' does not exist");
        }
    }

    protected static function finder_cache_id($name,$conditions,$filters)
    {
        return get_called_class().$name.md5(serialize($conditions) . serialize($filters));
    }
    
    /**
     * Creates a sql query from a finder for use in find_by_sql
     *
     * @param string $options
     * @param string $limits
     * @return string
     */
    public static function create_sql_from_finder($options, $limits = array()){
        $query = $options['sql'][0];
        $offset = 0;

        if(isset($limits['order']))
            $query .= " ORDER BY {$limits['order']}";
        else if(isset($options['order']))
            $query .= " ORDER BY {$options['order']}";

        if(isset($limits['offset']))
            $offset = $limits['offset'];
        else if(isset($options['offset']))
            $offset = $options['offset'];

        if(isset($limits['limit'])){
            if ($limits['limit'] === false) {
                $query .= " OFFSET $offset";
            }
            else {
                $query .= " LIMIT $offset,{$limits['limit']}";
            }
        }

        return $query;
    }

    /**
     * Creates the correct options array based on necessary conditions
     *
     * @param array $options 
     * @param array $additional_conditions 
     * @param array $limits 
     * @return array of options for find
     */
    public static function create_options_from_finder($options,$additional_conditions,$limits = array()){
        if(!ActiveRecord\is_hash($options['conditions'])){
            $string_conditions = $options['conditions'][0];
            foreach($additional_conditions as $attribute => $value){
                // Arrays need to be used for attributes that appear multiple times in the condition string
                if(is_array($value) && count($value) == ($count = preg_match_all("/\b$attribute/", $string_conditions, $matches, PREG_OFFSET_CAPTURE))){
                    $positions = array();
                    // Find every position of $attribute in $string_conditions
                    foreach($matches[0] as $match) {
                        $positions[]= $match[1];
                    }
                    $i = 0;
                    foreach($positions as $position){
                        // Get the key to replace in the conditions array by counting occurrences of '?' prior 
                        // to $position in $string_conditions and adding 1
                        $replace_key = substr_count(substr($string_conditions, 0, $position),'?') + 1;
                        $options['conditions'][$replace_key] = $value[$i];
                        $i++;
                    }
                }
                elseif (isset($value)) {
                    $number = preg_match("/\b$attribute/", $string_conditions, $matches, PREG_OFFSET_CAPTURE);
                    $occurrence = $matches[0][1];

                    if($occurrence == 0) {
                        $occurrence = 1;
                    }

                    $replace_key = substr_count(substr($string_conditions, 0, $occurrence - 1), '?') + 1;
                    $options['conditions'][$replace_key] = $value;
                }
            }
        }
        else{
            $options['conditions'] = array_merge($options['conditions'],$additional_conditions);
        }
        
        foreach($limits as $key => $value){
            if ($key=='limit' && $value === false) {
                unset($options[$key]);
            }
            elseif (isset($value)) {
                $options[$key] = $value;
            }
        }
        return $options;
    }
    
    /**
     * Takes an array of model objects and two attributes and 
     * returns single array in the form key_attribute => value_attribute
     *
     * @param array $models 
     * @param array $key_attribute
     * @param array $value_attribute
     * @return array
     */
    public static function models_to_array(array $models,$key_attribute,$value_attribute)
    {
        $ret = array();
        foreach($models as $model){
            $key = $model->read_attribute($key_attribute);
            $value = $model->read_attribute($value_attribute);
            $ret[$key] = $value;
        }
        return $ret;
    }


	public function get_available(){
		return $this->is_valid() && !$this->is_dirty();
	}

	public static function available($object)
	{
	    if(!is_null($object) && $object->available)
	        return true;
	    else
	        return false;
	}
}


?>