<?php

namespace ActiveRecord;

/**
 * Abstract class that all relationships must extend from.
 *
 * @package ActiveRecord
 * @see http://www.phpactiverecord.org/guides/associations
 */
abstract class AbstractRelationship implements InterfaceRelationship
{
	/**
	 * Name to be used that will trigger call to the relationship.
	 *
	 * @var string
	 */
	public $attribute_name;

	/**
	 * Class name of the associated model.
	 *
	 * @var string
	 */
	public $class_name;

	/**
	 * Name of the foreign key.
	 *
	 * @var string
	 */
	public $foreign_key = array();

	/**
	 * Options of the relationship.
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * Is the relationship single or multi.
	 *
	 * @var boolean
	 */
	protected $poly_relationship = false;

	/**
	 * List of valid options for relationships.
	 *
	 * @var array
	 */
	static protected $valid_association_options = array('class_name', 'class', 'foreign_key', 'conditions', 'select', 'readonly', 'touch');

	/**
	 * Constructs a relationship.
	 *
	 * @param array $options Options for the relationship (see {@link valid_association_options})
	 * @return mixed
	 */
	public function __construct($options=array())
	{
		$this->attribute_name = $options[0];
		$this->options = $this->merge_association_options($options);

		$relationship = strtolower(denamespace(get_called_class()));

		if ($relationship === 'hasmany' || $relationship === 'hasandbelongstomany')
			$this->poly_relationship = true;

		if (isset($this->options['conditions']) && !is_array($this->options['conditions']))
			$this->options['conditions'] = array($this->options['conditions']);

		if (isset($this->options['class']))
			$this->set_class_name($this->options['class']);
		elseif (isset($this->options['class_name']))
			$this->set_class_name($this->options['class_name']);

		$this->attribute_name = strtolower(Inflector::instance()->variablize($this->attribute_name));

		if (!$this->foreign_key && isset($this->options['foreign_key']))
			$this->foreign_key = is_array($this->options['foreign_key']) ? $this->options['foreign_key'] : array($this->options['foreign_key']);
	}

	protected function get_table()
	{
		return Table::load($this->class_name);
	}

	/**
	 * What is this relationship's cardinality?
	 *
	 * @return bool
	 */
	public function is_poly()
	{
		return $this->poly_relationship;
	}

	/**
	 * Eagerly loads relationships for $models.
	 *
	 * This method takes an array of models, collects PK or FK (whichever is needed for relationship), then queries
	 * the related table by PK/FK and attaches the array of returned relationships to the appropriately named relationship on
	 * $models.
	 *
	 * @param Table $table
	 * @param $models array of model objects
	 * @param $attributes array of attributes from $models
	 * @param $includes array of eager load directives
	 * @param $query_keys -> key(s) to be queried for on included/related table
	 * @param $model_values_keys -> key(s)/value(s) to be used in query from model which is including
	 * @return void
	 */
	protected function query_and_attach_related_models_eagerly(Table $table, $models, $attributes, $includes=array(), $query_keys=array(), $model_values_keys=array(), $related_primary_keys = array())
	{
		
		$values = array();
		$options = $this->options;
		$inflector = Inflector::instance();
		$query_key = $query_keys[0];
		if(!empty($related_primary_keys))
		    $related_primary_key = $related_primary_keys[0];
		else
		    $related_primary_key = null;

		$model_values_key = $model_values_keys[0];

		foreach ($attributes as $column => $value)
			$values[] = $value[$inflector->variablize($model_values_key)];

		$values = array($values);
		$conditions = SQLBuilder::create_conditions_from_underscored_string($table->conn,$query_key,$values);

		if (isset($options['conditions']) && strlen($options['conditions'][0]) > 1)
			Utils::add_condition($options['conditions'], $conditions);
		else
			$options['conditions'] = $conditions;

        $includes_belongs_to = false;
        if(!empty($includes)){
            if(is_array($includes) && ($key = array_search(strtolower(get_class($models[0])),$includes))){
                $includes_belongs_to = true;
                unset($includes[$key]);
            }
            else if(strtolower(get_class($models[0])) == $includes){
                $includes_belongs_to = true;
                $includes = NULL;
            }
            $options['include'] = $includes;
        }
        else{
            $options['include'] = array();
        }

		if (!empty($options['through'])) {
			// save old keys as we will be reseting them below for inner join convenience
			$pk = $this->primary_key;
			$fk = $this->foreign_key;

			$this->set_keys($this->get_table()->class->getName(), true);

			if (!isset($options['class_name'])) {
				$class = classify($options['through'], true);
				$through_table = $class::table();
			} else {
				$class = $options['class_name'];
				$relation = $class::table()->get_relationship($options['through']);
				$through_table = $relation->get_table();
			}
			$options['joins'] = $this->construct_inner_join_sql($through_table, true);

			$query_key = $this->primary_key[0];

			// reset keys
			$this->primary_key = $pk;
			$this->foreign_key = $fk;
		}

		$options = $this->unset_non_finder_options($options);
		$class = $this->class_name;
		$related_models = $class::find('all', $options);
		$used_models = array();
		$model_values_key = $inflector->variablize($model_values_key);
		$query_key = $inflector->variablize($query_key);

        $this->match_related_models_to_parents($related_models,$models,$query_key,$model_values_key,$related_primary_key,$includes_belongs_to);
	}

	/**
	 * Creates a new instance of specified {@link Model} with the attributes pre-loaded.
	 *
	 * @param Model $model The model which holds this association
	 * @param array $attributes Hash containing attributes to initialize the model with
	 * @return Model
	 */
	public function build_association(Model $model, $attributes=array())
	{
		$class_name = $this->class_name;
		return new $class_name($attributes);
	}

	/**
	 * Creates a new instance of {@link Model} and invokes save.
	 *
	 * @param Model $model The model which holds this association
	 * @param array $attributes Hash containing attributes to initialize the model with
	 * @return Model
	 */
	public function create_association(Model $model, $attributes=array())
	{
		$class_name = $this->class_name;
		$new_record = $class_name::create($attributes);
		return $this->append_record_to_associate($model, $new_record);
	}

	protected function append_record_to_associate(Model $associate, Model $record)
	{
		$association =& $associate->{$this->attribute_name};

		if ($this->poly_relationship)
			$association[] = $record;
		else
			$association = $record;

		return $record;
	}

	protected function merge_association_options($options)
	{
		$available_options = array_merge(self::$valid_association_options,static::$valid_association_options);
		$valid_options = array_intersect_key(array_flip($available_options),$options);

		foreach ($valid_options as $option => $v)
			$valid_options[$option] = $options[$option];

		return $valid_options;
	}

	protected function unset_non_finder_options($options)
	{
		foreach (array_keys($options) as $option)
		{
			if (!in_array($option, Model::$VALID_OPTIONS))
				unset($options[$option]);
		}
		return $options;
	}

	/**
	 * Infers the $this->class_name based on $this->attribute_name.
	 *
	 * Will try to guess the appropriate class by singularizing and uppercasing $this->attribute_name.
	 *
	 * @return void
	 * @see attribute_name
	 */
	protected function set_inferred_class_name($namespace)
	{
		$singularize = ($this instanceOf HasMany ? true : false);
        // NEED TO FIX THIS FOR NAMESPACING
		$this->set_class_name("$namespace\\" . classify($this->attribute_name, $singularize));
	}

	protected function set_class_name($class_name)
	{
		$reflection = Reflections::instance()->add($class_name)->get($class_name);

		if (!$reflection->isSubClassOf('ActiveRecord\\Model'))
			throw new RelationshipException("'$class_name' must extend from ActiveRecord\\Model");

		$this->class_name = $class_name;
	}

	protected function create_conditions_from_keys(Model $model, $condition_keys=array(), $value_keys=array())
	{
		$condition_string = implode('_and_', $condition_keys);
		$condition_values = array_values($model->get_values_for($value_keys));

		// return null if all the foreign key values are null so that we don't try to do a query like "id is null"
		if (all(null,$condition_values))
			return null;

		$conditions = SQLBuilder::create_conditions_from_underscored_string(Table::load(get_class($model))->conn,$condition_string,$condition_values);

		# DO NOT CHANGE THE NEXT TWO LINES. add_condition operates on a reference and will screw options array up
		if (isset($this->options['conditions']))
			$options_conditions = $this->options['conditions'];
		else
			$options_conditions = array();

		return Utils::add_condition($options_conditions, $conditions);
	}

	/**
	 * Creates INNER JOIN SQL for associations.
	 *
	 * @param Table $from_table the table used for the FROM SQL statement
	 * @param bool $using_through is this a THROUGH relationship?
	 * @param string $alias a table alias for when a table is being joined twice
	 * @return string SQL INNER JOIN fragment
	 */
	public function construct_inner_join_sql(Table $from_table, $using_through=false, $alias=null)
	{
		if ($using_through)
		{
			$join_table = $from_table;
			$join_table_name = $from_table->get_fully_qualified_table_name();
			$from_table_name = Table::load($this->class_name)->get_fully_qualified_table_name();
 		}
		else
		{
			$join_table = Table::load($this->class_name);
			$join_table_name = $join_table->get_fully_qualified_table_name();
			$from_table_name = $from_table->get_fully_qualified_table_name();
		}

		// need to flip the logic when the key is on the other table
		if ($this instanceof HasMany || $this instanceof HasOne)
		{
			$this->set_keys($from_table->class->getName());

			if ($using_through)
			{
				$foreign_key = $this->primary_key[0];
				$join_primary_key = $this->foreign_key[0];
			}
			else
			{
				$join_primary_key = $this->foreign_key[0];
				$foreign_key = $this->primary_key[0];
			}
		}
		else
		{
			$foreign_key = $this->foreign_key[0];
			$join_primary_key = $this->primary_key[0];
		}

		if (!is_null($alias))
		{
			$aliased_join_table_name = $alias = $this->get_table()->conn->quote_name($alias);
			$alias .= ' ';
		}
		else
			$aliased_join_table_name = $join_table_name;

		return "INNER JOIN $join_table_name {$alias}ON($from_table_name.$foreign_key = $aliased_join_table_name.$join_primary_key)";
	}

	/**
	 * This will load the related model data.
	 *
	 * @param Model $model The model this relationship belongs to
	 */
	abstract function load(Model $model);
	
	
	/*************************
	 * Methods added by GTIO * 
	 *************************/
	
	/**
	 * Eagerly loads relationships for $models from cache
	 *
	 * @param string $models 
	 * @param string $attributes 
	 * @param string $includes 
	 * @param string $query_keys 
	 * @param string $model_values_keys 
	 * @param string $related_primary_keys 
	 * @return void
	 * @author 
	 */
	public function load_from_cache_eagerly($models, $attributes, $includes=array(), $query_keys = array(), $model_values_keys = array(), $related_primary_keys = array())
	{
	    $class = $this->class_name;
	    $inflector = Inflector::instance();

	    $query_key = $query_keys[0];
		$model_values_key = $model_values_keys[0];
		if(!empty($related_primary_keys))
		    $related_primary_key = $related_primary_keys[0];
		else
		    $related_primary_key = null;

	    $uncached_models = array();
	    $id_list = array();
        foreach($models as $model)
        {
            if($model->has_related_ids_for($this->attribute_name)){
                foreach($model->related_ids_for($this->attribute_name) as $id)
                {
                    $id_list[]= $id; 
                }
            }
            else{
                $uncached_models[]= $model;
            }
        }

        $includes_belongs_to = false;
        if(!empty($includes)){
            if(is_array($includes) && ($key = array_search(strtolower(get_class($models[0])),$includes))){
                $includes_belongs_to = true;
                unset($includes[$key]);
            }
            else if(strtolower(get_class($models[0])) == $includes){
                $includes_belongs_to = true;
                unset($includes);
            }
        }

        if(count($id_list) > 0){
            $id_list = array_unique($id_list);
            $options = $this->options;
            if (!empty($includes))
    			$options['include'] = $includes;

            $related_models = $class::find_by_pk($id_list,$options);
            if(!is_array($related_models)){
                $related_models = array($related_models);
            }
        }
        else{
            $related_models = array();
        }

        $model_values_key = $inflector->variablize($model_values_key);
		$query_key = $inflector->variablize($query_key);
        $this->match_related_models_to_parents($related_models,$models,$query_key,$model_values_key,$related_primary_key,$includes_belongs_to);

        return $uncached_models;
	}

	/**
	 * Creates a map of models grouped by an attribute
	 *
	 * @param string $attribute_name
	 * @param string $models
	 * @param string $child_attribute_name
	 * @param string $child_primary_key
	 * @param string $related_models
	 * @return array
	 */
	public function map_models($attribute_name,$models,$child_attribute_name = null, $child_primary_key = null, $related_models = array())
	{
	    $output = array();
	    if($this->is_poly()){
	        $map = array();
    	    foreach($related_models as $model)
    	    {
    	        $key = $model->read_attribute($child_primary_key);
    	        $output[$key] = array();
    	        $map[$model->read_attribute($child_attribute_name)][] = $key;
    	    }
    	    foreach($models as $model)
    	    {
                $key = $model->read_attribute($attribute_name);
                if(array_key_exists($key,$map)){
        	        $children = $map[$model->read_attribute($attribute_name)];
        	        if(!empty($children))
        	        {
            	        foreach($children as $child_id)
            	        {
            	           $output[$child_id][]= $model;
            	        }
            	    }
            	}
    	    }
    	}
    	else{
    	    foreach($models as $model)
    	    {
    	        $key = $model->read_attribute($attribute_name);
    	        $output[$key][] = $model;
    	    }
    	}
	    return $output;
	}

    /**
     * Matches related models with the parent in an eager load
     *
     * @param array  $related_models
     * @param array  $parents
     * @param string $query_key
     * @param string $model_values_key
     * @param string $related_primary_key
     * @param bool   $include_belongs_to
     * @return void
     */
	public function match_related_models_to_parents($related_models,$parents,$query_key,$model_values_key,$related_primary_key,$include_belongs_to = false)
	{
	    $class = $this->class_name;

	    $inflector = Inflector::instance();

        if($this->is_poly())
	        $parent_map = $this->map_models($model_values_key,$parents,$query_key,$related_primary_key,$related_models);
	    else
	        $parent_map = $this->map_models($model_values_key,$parents);

        $records_by_parent = array();

        foreach($parents as $parent)
            $records_by_parent[$parent->read_attribute($parent->get_primary_key(true))] = array('parent' => $parent,'related' => array());

        foreach($related_models as $related)
        {
            if($this->is_poly())
                $map_key = $related_primary_key;
            else
                $map_key = $query_key;

            $related_key = $related->read_attribute($map_key);

            if(array_key_exists($related_key,$parent_map)){
                foreach($parent_map[$related_key] as $parent_model)
                    $records_by_parent[$parent_model->read_attribute($parent_model->get_primary_key(true))]['related'][]= $related;
            }
        }

        $used_models = [];
        foreach($records_by_parent as $record)
        {
            $parent = $record['parent'];
            if(!empty($record['related'])) {
                foreach($record['related'] as $related) {
                    $hash = spl_object_hash($related);

                    if (in_array($hash, $used_models)) {
                        $parent->set_relationship_from_eager_load(clone($related), $this->attribute_name);
                    }
                    else {
                        $parent->set_relationship_from_eager_load($related, $this->attribute_name);
                        $used_models[] = $hash;
                    }

                    if($include_belongs_to){
                        $related->set_relationship_from_eager_load(clone($parent), strtolower(get_class($parent)));
                    }
                }
            }
            else{
                $parent->set_relationship_from_eager_load(null, $this->attribute_name);
            }
        }
	}
};

?>