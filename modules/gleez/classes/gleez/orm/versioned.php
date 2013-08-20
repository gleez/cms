<?php
/**
 * Object Relational Mapping (ORM) "versioned" extension
 *
 * Allows ORM objects to be revisioned instead of updated.
 *
 * @package    Gleez\ORM
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Gleez_ORM_Versioned extends ORM {

	protected $_last_version = NULL;

	/**
	 * The version state
	 * @var boolean
	 */
	protected $_restore =  FALSE;
        
	/**
	 * Overload `ORM::update` to support versioned data
	 *
	 * @param   Validation  $validation  Validation object
	 * @return  ORM
	 */
	public function update(Validation $validation = NULL)
	{
		if ( ! $this->_restore)
		{
			$this->_last_version = 1 + ($this->_last_version === NULL ? $this->_object['version'] : $this->_last_version);
			$this->__set('version', $this->_last_version);

			$object = DB::select()->from($this->_table_name)
					->where($this->_primary_key, '=', $this->pk())
					->execute($this->_db)->current();
		}
		
		parent::update($validation);

		// Create version only if its general update not version restore
		if ($this->_saved AND ! $this->_restore)
		{                        
			$data = array();
			foreach ($object as $key => $value)
			{
				if ($key === $this->_primary_key OR array_key_exists($key, $this->_ignored_columns))
					continue;
                                
				if ($key === 'version')
				{
					// Always use the current version
					$value = $this->_last_version;
				}
			
                                //make sure only column names except primary key is stored in revision
				if(array_key_exists($key, $this->_table_columns))
                                        $data[$key] = $value;
			}
                
			$data[$this->foreign_key()] = $this->id;
                       
                        DB::insert($this->version_table())
                                        ->columns(array_keys($data))
                                        ->values(array_values($data))
                                        ->execute($this->_db);
		}

		return $this;
	}

	/**
	 * Restores the object with data from stored version
	 *
	 * @param   integer  version number you want to restore
	 * @return  ORM
	 */
	public function restore($version)
	{
		if ( ! $this->loaded())
			return $this;
               
                $query = DB::select()->from($this->version_table())
                                ->where($this->foreign_key(), '=', $this->pk())
                                ->where('version', '=', $version)
                                ->limit(1)
                                ->execute($this->_db);

		if (count($query))
		{
			$row = $query->current();

			foreach ($row as $key => $value)
			{
				if ($key === $this->_primary_key OR $key === $this->foreign_key() OR $key == 'version_log')
				{
					// Do not overwrite the primary key
					continue;
				}

				if ($key === 'version')
				{
					// Always use the current version
					//$value = $this->version;
				}

				$this->__set($key, $value);
			}
		
			//this var used to detect is it general update or version update
			$this->_restore = true;
			$this->update();
		}

		return $this;
	}

	/**
	 * Loads a version from current object
	 *
	 * @chainable
	 * @return  ORM
	 */
        public function version( $version = FALSE )
        {
                if ( ! $this->loaded())
                        return $this;

                $query = DB::select()->from($this->version_table())
                                ->where($this->foreign_key(), '=', $this->pk())
                                ->where('version', '=', $version)
                                ->limit(1)
                                ->execute($this->_db);
                
                if (count($query))
                {
                        $this->values($query->current());
                }

                return $this;
        }

	/**
	 * Overloads ORM::delete() to delete all versioned entries of current object
	 * and the object itself
	 *
	 * @param   integer  id of the object you want to delete
	 * @return  ORM
	 */
	public function delete()
	{
                if ( ! $this->_loaded)
			throw new Gleez_Exception('Cannot delete :model model because it is not loaded.', array(':model' => $this->_object_name));
	
                // Use primary key value
		$id = $this->pk();

		if ($status = parent::delete())
		{
                        // Delete the object
			DB::delete($this->version_table())
                                        ->where($this->foreign_key(), '=', $id)
                                        ->execute($this->_db);
		}

		return $status;
	}
        
	/**
	 * Determines the name of a foreign key for a specific table.
	 *
	 * @return  string
	 */
	public function foreign_key()
	{
                return Inflector::singular($this->_table_name).$this->_foreign_key_suffix;
        }

	/**
	 * Determines the name of a revision specific table.
	 *
	 * @return  string
	 */
	public function version_table()
	{
                return $this->_table_name.'_versions';
        }
        
}