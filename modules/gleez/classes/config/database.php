<?php
/**
 * Database source for the Gleez config system
 *
 * @package    Gleez\Configuration\Database
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Config_Database implements Config_Source {

	/**
	 * @var string
	 */
	protected $_db_instance = 'default';

	/**
	 * @var string
	 */
	protected $_table_name  = 'config';

	/**
	 * @var array
	 */
	protected $_loaded_keys = array();

	/**
	 * Constructs the database source object
	 *
	 * @param  array  $config  Configuration for the source [Optional]
	 */
	public function __construct(array $config = NULL)
	{
		if (isset($config['instance']))
		{
			$this->_db_instance = $config['instance'];
		}

		if (isset($config['table_name']))
		{
			$this->_table_name = $config['table_name'];
		}
	}

	/**
	 * Tries to load the specified configuration group
	 *
	 * Returns FALSE if group does not exist or an array if it does
	 *
	 * @param  string $group Configuration group
	 * @return boolean|array
	 */
	public function load($group)
	{
		/**
		 * Prevents the catch-22 scenario where the database config reader attempts to load the
		 * database connections details from the database.
		 *
		 * @link http://dev.kohanaframework.org/issues/4316
		 */
		if (in_array($group, array('database', 'cache', 'session', 'userguide', 'auth', 'inputfilter','inflector','media','pagination')))
		{
			return FALSE;
		}

		$query = DB::select('config_key', 'config_value')
			->from($this->_table_name)
			->where('group_name', '=', $group)
			->execute($this->_db_instance);

		$config = count($query) ? array_map('unserialize', $query->as_array('config_key', 'config_value')) : FALSE;

		if ($config !== FALSE)
		{
			$this->_loaded_keys[$group] = array_combine(array_keys($config), array_keys($config));
		}

		return $config;
	}

	/**
	 * Writes the passed config for $group
	 *
	 * @param string      $group  The config group
	 * @param string      $key    The config key to write to
	 * @param array       $config The configuration to write
	 * @return boolean
	 */
	public function write($group, $key, $config)
	{
		//avoid race condition
		if( in_array($group, array('cache', 'database') ) )
		{
			return FALSE;
		}

		$config = serialize($config);

		// Check to see if we've loaded the config from the table already
		if (isset($this->_loaded_keys[$group][$key]))
		{
			$this->_update($group, $key, $config);
		}
		else
		{
			// Attempt to run an insert query
			// This may fail if the config key already exists in the table
			// and we don't know about it
			try
			{
				$this->_insert($group, $key, $config);
			}
			catch (Database_Exception $e)
			{
				// Attempt to run an update instead
				$this->_update($group, $key, $config);
			}
		}

		return TRUE;
	}

	/**
	 * Insert the config values into the table
	 *
	 * @param   string  $group   The config group
	 * @param   string  $key     The config key to write to
	 * @param   array   $config  The serialized configuration to write
	 * @return  Config_Database
	 */
	protected function _insert($group, $key, $config)
	{
		DB::insert($this->_table_name, array('group_name', 'config_key', 'config_value'))
			->values(array($group, $key, $config))
			->execute($this->_db_instance);

		return $this;
	}

	/**
	 * Update the config values in the table
	 *
	 * @param   string  $group  The config group
	 * @param   string  $key    The config key to write to
	 * @param   array   $config The serialized configuration to write
	 * @return  Config_Database
	 */
	protected function _update($group, $key, $config)
	{
		DB::update($this->_table_name)
			->set(array('config_value' => $config))
			->where('group_name', '=', $group)
			->where('config_key', '=', $key)
			->execute($this->_db_instance);

		return $this;
	}

	/**
	 * Delete the config key in the table
	 *
	 * @param   string  $group  The config group
	 * @param   string  $key    The config key to delete
	 * @return  Config_Database
	 */
	public function delete($group, $key)
	{
		DB::delete($this->_table_name)
			->where('group_name', '=', $group)
			->where('config_key', '=', $key)
			->execute($this->_db_instance);

		return $this;
	}

}