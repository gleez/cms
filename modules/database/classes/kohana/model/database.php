<?php
/**
 * Database Model base class
 *
 * @package    Kohana\Database\Models
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
abstract class Kohana_Model_Database extends Model {

	/**
	 * Database instance
	 * @var Database
	 */
	protected $_db;

	/**
	 * Create a new model instance
	 *
	 * A [Database] instance or configuration group name can be passed to the model.
	 * If no database is defined, the "default" database group will be used.
	 *
	 * Example:
	 * ~~~
	 * $model = Model::factory($name);
	 * ~~~
	 *
	 * @param   string   $name  Model name
	 * @param   mixed    $db    Database instance object or string [Optional]
	 *
	 * @return  Model
	 */
	public static function factory($name, $db = NULL)
	{
		// Add the model prefix
		$class = 'Model_'.$name;

		return new $class($db);
	}

	/**
	 * Loads the database.
	 *
	 * Example:
	 * ~~~
	 * $model = new Foo_Model($db);
	 * ~~~
	 *
	 * @param  mixed  $db  Database instance object or string [Optional]
	 */
	public function __construct($db = NULL)
	{
		if ($db)
		{
			// Set the instance or name
			$this->_db = $db;
		}
		elseif ( ! $this->_db)
		{
			// Use the default name
			$this->_db = Database::$default;
		}

		if (is_string($this->_db))
		{
			// Load the database
			$this->_db = Database::instance($this->_db);
		}
	}

}
