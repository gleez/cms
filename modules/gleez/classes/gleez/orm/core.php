<?php defined('SYSPATH') OR die('No direct access allowed.');

class Gleez_ORM_Core extends Kohana_ORM {
        
	const DELETE =  5;

	/**
	 * Initialization storage for ORM models
	 * @var array
	 */
	protected static $_init_cache = array();
	
	/**
	 * @var bool
	 */
	protected $_updated = FALSE;

	/**
	 * @var bool
	 */
	protected $_cache = FALSE;
	
        /**
	 * Ignored columns
	 * @var array
	 */
        protected $_ignored_columns = array();

	public function updated()
	{
		return $this->_updated;
	}

	public function cache()
	{
		$this->_cache = TRUE;
		$this->_reload_on_wakeup = FALSE;
		return $this;
	}
	
	/**
	 * Prepares the model database connection, determines the table name,
	 * and loads column information.
	 *
	 * @return void
	 */
	protected function _initialize()
	{
		// Set the object name and plural name
		$this->_object_name = strtolower(substr(get_class($this), 6));

		// Check if this model has already been initialized
		if ( ! $init = Arr::get(ORM::$_init_cache, $this->_object_name, FALSE))
		{
			$init = array(
				'_belongs_to' => array(),
				'_has_one'    => array(),
				'_has_many'   => array(),
			);

			// Set the object plural name if none predefined
			if ( ! isset($this->_object_plural))
			{
				$init['_object_plural'] = Inflector::plural($this->_object_name);
			}

			if ( ! $this->_errors_filename)
			{
				$init['_errors_filename'] = $this->_object_name;
			}

			if ( ! is_object($this->_db))
			{
				// Get database instance
				$init['_db'] = Database::instance($this->_db_group);
			}

			if (empty($this->_table_name))
			{
				// Table name is the same as the object name
				$init['_table_name'] = $this->_object_name;

				if ($this->_table_names_plural === TRUE)
				{
					// Make the table name plural
					$init['_table_name'] = Arr::get($init, '_object_plural', $this->_object_plural);
				}
			}

			if ( ! empty($this->_ignored_columns))
			{
				// Optimize for performance
				$init['_ignored_columns']= array_combine($this->_ignored_columns, $this->_ignored_columns);
			}
	
			$defaults = array();

			foreach ($this->_belongs_to as $alias => $details)
			{
				$defaults['model'] = $alias;
				$defaults['foreign_key'] = $alias.$this->_foreign_key_suffix;

				$init['_belongs_to'][$alias] = array_merge($defaults, $details);
			}

			foreach ($this->_has_one as $alias => $details)
			{
				$defaults['model'] = $alias;
				$defaults['foreign_key'] = $this->_object_name.$this->_foreign_key_suffix;

				$init['_has_one'][$alias] = array_merge($defaults, $details);
			}

			foreach ($this->_has_many as $alias => $details)
			{
				if ( ! isset($details['model']))
				{
					$defaults['model'] = Inflector::singular($alias);
				}
				
				$defaults['foreign_key'] = $this->_object_name.$this->_foreign_key_suffix;
				$defaults['through'] = NULL;
				
				if ( ! isset($details['far_key']))
				{
					$defaults['far_key'] = Inflector::singular($alias).$this->_foreign_key_suffix;
				}
				
				$init['_has_many'][$alias] = array_merge($defaults, $details);
			}
			
			ORM::$_init_cache[$this->_object_name] = $init;
		}

		// Assign initialized properties to the current object
		foreach ($init as $property => $value)
		{
			$this->{$property} = $value;
		}
	
		// Load column information
		$this->reload_columns();

		// Clear initial model state
		$this->clear();
	}
	
	/**
	 * Allows serialization of only the object data and state, to prevent
	 * "stale" objects being unserialized, which also requires less memory.
	 *
	 * @return string
	 */
	public function serialize()
	{
		// Store only information about the object
		foreach (array('_primary_key_value', '_object', '_changed', '_loaded', '_saved', '_sorting',  '_original_values', '_ignored_columns') as $var)
		{
			$data[$var] = $this->{$var};
		}

		return serialize($data);
	}
	
	/**
	 * Handles setting of column
	 *
	 * @param  string $column Column name
	 * @param  mixed  $value  Column value
	 * @return void
	 */
	public function set($column, $value)
	{
		if (in_array($column, $this->_serialize_columns))
		{
			$value = $this->_serialize_value($value);
		}
	
		if (array_key_exists($column, $this->_ignored_columns))
		{
			// No processing for ignored columns, just store it
			$this->_object[$column] = $value;
		}
		elseif (array_key_exists($column, $this->_object))
		{
			// Filter the data
			$value = $this->run_filter($column, $value);

			// See if the data really changed
			if ($value !== $this->_object[$column])
			{
				$this->_object[$column] = $value;

				// Data has changed
				$this->_changed[$column] = $column;

				// Object is no longer saved or valid
				$this->_saved = $this->_valid = FALSE;
			}
		}
		elseif (isset($this->_belongs_to[$column]))
		{
			// Update related object itself
			$this->_related[$column] = $value;

			// Update the foreign key of this model
			$this->_object[$this->_belongs_to[$column]['foreign_key']] = ($value instanceof ORM) ? $value->pk() : NULL;

			$this->_changed[$column] = $this->_belongs_to[$column]['foreign_key'];
		}
		else
		{
			throw new Kohana_Exception('The :property: property does not exist in the :class: class',
				array(':property:' => $column, ':class:' => get_class($this)));
		}

		return $this;
	}
	
	/**
	 * Set values from an array with support for one-one relationships.  This method should be used
	 * for loading in post data, etc.
	 *
	 * @param  array $values   Array of column => val
	 * @param  array $expected Array of keys to take from $values
	 * @return ORM
	 */
	public function values(array $values, array $expected = NULL)
	{
		// Default to expecting everything except the primary key
		if ($expected === NULL)
		{
			$expected = array_keys($this->_table_columns);

			// Don't set the primary key by default
			unset($values[$this->_primary_key]);
		}
        
                if ( ! empty($this->_ignored_columns) )
		{
			// merge the columns needed to process
			$expected = array_merge($expected, array_keys($this->_ignored_columns) );
		}
        
		foreach ($expected as $key => $column)
		{
			if (is_string($key))
			{
				// isset() fails when the value is NULL (we want it to pass)
				if ( ! array_key_exists($key, $values))
					continue;

				// Try to set values to a related model
				$this->{$key}->values($values[$key], $column);
			}
			else
			{
				// isset() fails when the value is NULL (we want it to pass)
				if ( ! array_key_exists($column, $values))
					continue;

				// Update the column, respects __set()
				$this->$column = $values[$column];
			}
		}

		return $this;
	}

	/**
	 * Returns the values of this object as an array, including any related one-one
	 * models that have already been loaded using with()
	 *
	 * @return array
	 */
	public function as_array()
	{
		$object = array();
		$extra = array('url', 'edit_url', 'delete_url');

		foreach ($this->_object as $column => $value)
		{
			// Call __get for any user processing
			$object[$column] = $this->__get($column);
		}

		foreach ($this->_related as $column => $model)
		{
			// Include any related objects that are already loaded
			$object[$column] = $model->as_array();
		}

		foreach ($extra as $column)
		{
			try
			{
				// Call __get for any user processing
				$object[$column] = $this->__get($column);
			}catch(Exception $e){}
		}
	
		return $object;
	}
	
	/**
	 * Binds another one-to-one object to this model.  One-to-one objects
	 * can be nested using 'object1:object2' syntax
	 *
	 * @param  string $target_path Target model to bind to
	 * @return void
	 */
	public function with($target_path)
	{
		if (isset($this->_with_applied[$target_path]))
		{
			// Don't join anything already joined
			return $this;
		}

		// Split object parts
		$aliases = explode(':', $target_path);
		$target = $this;
		foreach ($aliases as $alias)
		{
			// Go down the line of objects to find the given target
			$parent = $target;
			$target = $parent->_related($alias);

			if ( ! $target)
			{
				// Can't find related object
				return $this;
			}
		}

		// Target alias is at the end
		$target_alias = $alias;

		// Pop-off top alias to get the parent path (user:photo:tag becomes user:photo - the parent table prefix)
		array_pop($aliases);
		$parent_path = implode(':', $aliases);

		if (empty($parent_path))
		{
			// Use this table name itself for the parent path
			$parent_path = $this->_object_name;
		}
		else
		{
			if ( ! isset($this->_with_applied[$parent_path]))
			{
				// If the parent path hasn't been joined yet, do it first (otherwise LEFT JOINs fail)
				$this->with($parent_path);
			}
		}

		// Add to with_applied to prevent duplicate joins
		$this->_with_applied[$target_path] = TRUE;

		// Use the keys of the empty object to determine the columns
		foreach (array_keys($target->_object) as $column)
		{
			// Skip over ignored columns
			if( ! in_array($column, $target->_ignored_columns))
			{
				$name = $target_path.'.'.$column;
				$alias = $target_path.':'.$column;

				// Add the prefix so that load_result can determine the relationship
				$this->select(array($name, $alias));
			}

		}

		if (isset($parent->_belongs_to[$target_alias]))
		{
			// Parent belongs_to target, use target's primary key and parent's foreign key
			$join_col1 = $target_path.'.'.$target->_primary_key;
			$join_col2 = $parent_path.'.'.$parent->_belongs_to[$target_alias]['foreign_key'];
		}
		else
		{
			// Parent has_one target, use parent's primary key as target's foreign key
			$join_col1 = $parent_path.'.'.$parent->_primary_key;
			$join_col2 = $target_path.'.'.$parent->_has_one[$target_alias]['foreign_key'];
		}

		// Join the related object into the result
		$this->join(array($target->_table_name, $target_path), 'LEFT')->on($join_col1, '=', $join_col2);


		return $this;
	}
	
	/**
	 * Initializes the Database Builder to given query type
	 *
	 * @param  integer $type Type of Database query
	 * @return ORM
	 */
	protected function _build($type)
	{
		// Construct new builder object based on query type
		switch ($type)
		{
			case Database::SELECT:
				$this->_db_builder = DB::select();
			break;
			case Database::UPDATE:
				$this->_db_builder = DB::update(array($this->_table_name, $this->_object_name));
			break;
			case Database::DELETE:
				$this->_db_builder = DB::delete(array($this->_table_name, $this->_object_name));
			break;
			case ORM::DELETE:
				$this->_db_builder = DB::delete($this->_table_name);
		}

		// Process pending database method calls
		foreach ($this->_db_pending as $method)
		{
			$name = $method['name'];
			$args = $method['args'];

			$this->_db_applied[$name] = $name;

			call_user_func_array(array($this->_db_builder, $name), $args);
		}

		return $this;
	}

	/**
	 * Finds and loads a single database row into the object.
	 *
	 * @chainable
	 * @return ORM
	 */
	public function find1111()
	{
		if ($this->_loaded)
			throw new Kohana_Exception('Method find() cannot be called on loaded objects');

		if ( ! empty($this->_load_with))
		{
			foreach ($this->_load_with as $alias)
			{
				// Bind auto relationships
				$this->with($alias);
			}
		}

		if($this->_cache)
		{
			// Get the cache instace from this table
			$cache = Cache::instance($this->_table_name);
	
			//$callback = function($value) { return implode("\t", $value); };
			// The query hash
			$query_hash = sha1( Arr::multi_implode("|", $this->_db_pending) );
	
			//Try to get from cache. if fails query db and save to cache
			if( ! $return = $cache->get($query_hash) )
			{
				//cache false, fetch from db
				$this->_build(Database::SELECT);
				$return = $this->_load_result(FALSE);

				//save to cache
				$cache->set($query_hash, $return);
			}
		}
		else
		{
			$this->_build(Database::SELECT);
			$return = $this->_load_result(FALSE);
		}

		return $return;
	}

	/**
	 * Finds multiple database rows and returns an iterator of the rows found.
	 *
	 * @return Database_Result
	 */
	public function find_all111()
	{
		if ($this->_loaded)
			throw new Kohana_Exception('Method find_all() cannot be called on loaded objects');

		if ( ! empty($this->_load_with))
		{
			foreach ($this->_load_with as $alias)
			{
				// Bind auto relationships
				$this->with($alias);
			}
		}

		if($this->_cache)
		{
			// Get the cache instace from this table
			$cache = Cache::instance("{$this->_table_name}_all");

			// The query hash
			$query_hash = sha1( Arr::multi_implode("|", $this->_db_pending) );
	
			//Try to get from cache. if fails query db and save to cache
			if( ! $return = $cache->get($query_hash) )
			{
				//cache false, fetch from db
				$this->_build(Database::SELECT);
				$return = $this->_load_result(TRUE);

				//save to cache
				$cache->set($query_hash, $return);
			}
		}
		else
		{
			$this->_build(Database::SELECT);
			$return = $this->_load_result(TRUE);
		}

		return $return;
	}

	/**
	 * Insert a new object to the database added event support
	 * @param  Validation $validation Validation object
	 * @return ORM
	 */
	public function create(Validation $validation = NULL)
	{
		if ($this->_loaded)
			throw new Kohana_Exception('Cannot create :model model because it is already loaded.', array(':model' => $this->_object_name));

		Module::event($this->_object_name .'_prevalid', $this, $validation);
	
		// Require model validation before saving
		if ( ! $this->_valid)
		{
			$this->check($validation);
		}
	
		Module::event($this->_object_name .'_presave', $this, $validation);
	
		$data = array();
		foreach ($this->_changed as $column)
		{
			// Generate list of column => values
			$data[$column] = $this->_object[$column];
		}

		if (is_array($this->_created_column))
		{
			// Fill the created column
			$column = $this->_created_column['column'];
			$format = $this->_created_column['format'];

			$data[$column] = $this->_object[$column] = ($format === TRUE) ? time() : date($format);
		}

		$result = DB::insert($this->_table_name)
			->columns(array_keys($data))
			->values(array_values($data))
			->execute($this->_db);

		if ( ! array_key_exists($this->_primary_key, $data))
		{
			// Load the insert id as the primary key if it was left out
			$this->_object[$this->_primary_key] = $this->_primary_key_value = $result[0];
		}

		// Object is now loaded and saved
		$this->_loaded = $this->_saved = TRUE;

		// All changes have been saved
		$this->_changed = array();
		$this->_original_values = $this->_object;

		Module::event($this->_object_name .'_save', $this);
	
		return $this;
	}

	/**
	 * Updates a single record or multiple records, added event support
	 *
	 * @chainable
	 * @param  Validation $validation Validation object
	 * @return ORM
	 */
	public function update(Validation $validation = NULL)
	{
		if ( ! $this->_loaded)
			throw new Kohana_Exception('Cannot update :model model because it is not loaded.', array(':model' => $this->_object_name));

		Module::event($this->_object_name .'_prevalid', $this, $validation);
	
		if (empty($this->_changed))
		{
			// Nothing to update
			return $this;
		}

		// Require model validation before saving
		if ( ! $this->_valid)
		{
			$this->check($validation);
		}

		Module::event($this->_object_name .'_presave', $this, $validation);
	
		$data = array();
		foreach ($this->_changed as $column)
		{
			// Compile changed data
			$data[$column] = $this->_object[$column];
		}

		if (is_array($this->_updated_column))
		{
			// Fill the updated column
			$column = $this->_updated_column['column'];
			$format = $this->_updated_column['format'];

			$data[$column] = $this->_object[$column] = ($format === TRUE) ? time() : date($format);
		}

		// Use primary key value
		$id = $this->pk();

		// Update a single record
		DB::update($this->_table_name)
			->set($data)
			->where($this->_primary_key, '=', $id)
			->execute($this->_db);

		if (isset($data[$this->_primary_key]))
		{
			// Primary key was changed, reflect it
			$this->_primary_key_value = $data[$this->_primary_key];
		}

		// Object has been saved
		$this->_saved   = $this->_updated = TRUE;

		// All changes have been saved
		$this->_changed = array();
		$this->_original_values = $this->_object;

		Module::event($this->_object_name .'_save', $this);

		return $this;
	}

	/**
	 * Deletes a single record or multiple records, ignoring relationships.
	 *
	 * @chainable
	 * @return ORM
	 */
	public function delete()
	{
		if ( ! $this->_loaded)
			throw new Kohana_Exception('Cannot delete :model model because it is not loaded.', array(':model' => $this->_object_name));

		// Use primary key value
		$id = $this->pk();

                Module::event($this->_object_name .'_predelete', $this);

		// Delete the object
		DB::delete($this->_table_name)
			->where($this->_primary_key, '=', $id)
			->execute($this->_db);

                Module::event($this->_object_name .'_delete', $this);
        
		return $this->clear();
	}
        
	/**
	 * Delete all objects in the associated table. This does NOT destroy
	 * relationships that have been created with other objects.
	 *
	 * @chainable
	 * @return  ORM
	 */
	public function delete_all()
	{
		if ( $this->_loaded)
			throw new Kohana_Exception('Cannot delete all :model model because it is loaded.', array(':model' => $this->_object_name));
	
		$this->_build(ORM::DELETE);
		$this->_db_builder->execute($this->_db);

		Module::event($this->_object_name .'_delete_all', $this);
	
		return $this->clear();
	}
	
	/**
	 * Validates the current model's data
	 *
	 * @param  Validation $extra_validation Validation object
	 * @return ORM
	 */
	public function check(Validation $extra_validation = NULL)
	{
		// Determine if any external validation failed
		$extra_errors = ($extra_validation AND ! $extra_validation->check());

		// Always build a new validation object
		$this->_validation();

                // add custom rules to $this->_validation();
		Module::event($this->_object_name .'_validation', $this->_validation, $extra_errors);
        
		$array = $this->_validation;

		if (($this->_valid = $array->check()) === FALSE OR $extra_errors)
		{
			$exception = new ORM_Validation_Exception($this->_object_name, $array);

			if ($extra_errors)
			{
				// Merge any possible errors from the external object
				$exception->add_object('_external', $extra_validation);
			}
			$this->_validation = NULL; //Fixed memory leak @http://dev.kohanaframework.org/issues/4286
			throw $exception;
		}

		$this->_validation = NULL; //Fixed memory leak @http://dev.kohanaframework.org/issues/4286
		return $this;
	}

	/**
	 * Adds a new relationship to between this model and another.
	 *
	 *     // Add the login role using a model instance
	 *     $model->add('roles', ORM::factory('role', array('name' => 'login')));
	 *     // Add the login role if you know the roles.id is 5
	 *     $model->add('roles', 5);
	 *     // Add multiple roles (for example, from checkboxes on a form)
	 *     $model->add('roles', array(1, 2, 3, 4));
	 *
	 * @param  string  $alias    Alias of the has_many "through" relationship
	 * @param  mixed   $far_keys Related model, primary key, or an array of primary keys
	 * @param  array    $data    additional data to store in "through"/pivot table
	 * @return ORM
	 */
	public function add($alias, $far_keys, $data = NULL)
	{
		$far_keys = ($far_keys instanceof ORM) ? $far_keys->pk() : $far_keys;

		$columns = array($this->_has_many[$alias]['foreign_key'], $this->_has_many[$alias]['far_key']);
		$foreign_key = $this->pk();

		if ($data !== NULL)
		{
			// Additional data stored in pivot table
			$columns = array_merge($columns, array_keys($data));
		}
		
		$query = DB::insert($this->_has_many[$alias]['through'], $columns);

		foreach ( (array) $far_keys as $key)
		{
			$values = array($foreign_key, $key);
			if ($data !== NULL)
			{
				// Additional data stored in pivot table
				$values  = array_merge($values, array_values($data));
			}

			$query->values($values);
		}

		$query->execute($this->_db);

		return $this;
	}
	
	/**
	 * Join a has-many-through related object/model
	 * 
	 * @param ORM $model Model of which the table has to be joined
	 */
	public function with_many(ORM $joined_model)
	{
		// Check both models for the correct many-to-many relationship
		if (isset($this->_has_many[$joined_model->_table_name])
			&& isset($this->_has_many[$joined_model->_table_name]['through'])
			&& isset($joined_model->_has_many[$this->_table_name])
			&& isset($joined_model->_has_many[$this->_table_name]['through'])
			&& $this->_has_many[$joined_model->_table_name]['through'] == $joined_model->_has_many[$this->_table_name]['through'])
		{
			// Get the objects relationship arrays
			$this_relationship = $this->_has_many[$joined_model->_table_name];
			$join_relationship = $joined_model->_has_many[$this->_table_name];

			$through_table = $this_relationship['through'];

			// First, join the "through" table
			$this
				->join($through_table)
				->on($this->_table_name.'.'.$this->_primary_key, '=', $through_table.'.'.$this_relationship['foreign_key']);

			// Then join the related table using a pivot ("through") table
			$this
				->join($joined_model->_table_name)
				->on($joined_model->_table_name.'.'.$joined_model->_primary_key, '=', $through_table.'.'.$join_relationship['foreign_key']);

			// Chainable
			return $this;
		}
		else
		{
			throw new Kohana_Exception('The :joined_model that you\'re trying to join is not correctly related by has-many-through relationship wih the :current_model', array(':joined_model' => get_class($joined_model), ':current_model' => get_class($this)));
		}
	}
    
	/**
	 * Fetches statistics for n days back, returns a simple totals array
	 *
	 * @param  int    days back
	 * @param  int    user id
	 * @return array  totals by week
	 */
	public function sparklines($user_id = FALSE, $days = 90) {

		$query = DB::query(Database::SELECT, '
			SELECT WEEK(FROM_UNIXTIME(created)) AS w, count(id) as total
			FROM '.$this->_table_name.'
			WHERE datediff(now(), FROM_UNIXTIME(created)) <= :days
			'.(is_numeric($user_id) ? ' AND author = '.$user_id : '').'
			GROUP BY WEEK(FROM_UNIXTIME(created))
		')->param(':days', $days);

		return array_values($query->execute()->as_array('w', 'total'));
	}

}