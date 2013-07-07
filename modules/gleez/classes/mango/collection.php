<?php defined('SYSPATH') OR die('No direct script access allowed.');
/**
 * Mango Collection
 *
 * This class wraps the functionality of MongoCollection
 *
 * @method     mixed          batchInsert(array $a, array $options = array())
 * @method     array          findOne(array $query = array(), array $fields = array())
 * @method     array          getDBRef(array $ref)
 * @method     array          group(mixed $keys, array $initial ,MongoCode $reduce, array $options = array())
 * @method     boolean|array  insert(array $a, array $options = array())
 * @method     boolean|array  remove(array $criteria = array(), array $options = array())
 * @method     mixed          save(array $a, array $options = array())
 * @method     boolean|array  update(array $criteria, array $new_object, array $options = array())
 *
 * ### System Requirements
 *
 * - PHP 5.3 or higher
 * - MongoDB 2.4 or higher
 * - PHP-extension MongoDB 1.4 or higher
 *
 * @package    Gleez\Mango\Collection
 * @author     Sergey Yakovlev - Gleez
 * @version    0.2.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Mango_Collection {

	/**
	 * The name of the collection within the database or the gridFS prefix if gridFS is TRUE
	 * @var string
	 */
	protected $_name;

	/**
	 * The database configuration name
	 * Passed to [Mango::instance]
	 * @var string
	 */
	protected $_db;

	/**
	 * Whether or not this collection is a gridFS collection
	 * @var boolean
	 */
	protected $_gridFS = FALSE;

	/**
	 * The class name or instance of the corresponding document model or NULL if direct mode
	 * @var string
	 */
	protected $_model;

	/**
	 * The MongoCursor instance in use while iterating a collection
	 * @var MongoCursor
	 */
	protected $_cursor;

	/**
	 * The current query criteria (with field names translated)
	 * @var array
	 */
	protected $_query = array();

	/**
	 * The current query fields (a hash of 'field' => 1)
	 * @var array
	 */
	protected $_fields = array();

	/**
	 * The current query options
	 * @var array
	 */
	protected $_options = array();

	/**
	 * A cache of MongoCollection instances for performance
	 * @var array
	 */
	protected static $_collections = array();

	/**
	 * Benchmark token
	 * @var string
	 */
	protected $_benchmark = NULL;

	/**
	 * MongoCollection methods
	 * @var array
	 */
	protected static $_db_methods = array(
		'batchInsert',
		'findOne',
		'getDBRef',
		'group',
		'insert',
		'remove',
		'save',
		'update',
	);

	/**
	 * Class constructor
	 *
	 * Instantiate a new collection object, can be used for querying, updating, etc..
	 *
	 * @param  string          $name    The collection name [Optional]
	 * @param  string          $db      The database configuration name [Optional]
	 * @param  boolean         $gridFS  Is the collection a gridFS instance? [Optional]
	 * @param  boolean|string  $model   Class name of template model for new documents [Optional]
	 */
	public function __construct($name = NULL, $db = NULL, $gridFS = FALSE, $model = FALSE)
	{
		if ( ! is_null($name))
		{
			if (is_null($db))
			{
				$db = Mango::$default;
			}

			$this->_db     = $db;
			$this->_name   = $name;
			$this->_gridFS = $gridFS;
		}

		if ($model)
		{
			$this->_model = $model;
		}
	}

	/**
	 * Cloned objects have uninitialized cursors
	 */
	public function __clone()
	{
		// Reset the state of the query
		$this->reset(TRUE);
	}

	/**
	 * Magic method override
	 *
	 * Passes on method calls to either the MongoCursor or the MongoCollection.
	 *
	 * @param   string  $name       Name of the method being called
	 * @param   array   $arguments  Enumerated array containing the parameters passed to the `$name`
	 *
	 * @return  mixed
	 *
	 * @throws  Mango_Exception
	 *
	 * @uses    Profiler::start
	 * @uses    Profiler::stop
	 * @uses    JSON::encode
	 */
	public function __call($name, $arguments)
	{
		if ($this->_cursor AND method_exists($this->_cursor, $name))
		{
			return call_user_func_array(array($this->_cursor, $name), $arguments);
		}

		if (method_exists($this->collection(), $name))
		{
			if ($this->db()->profiling AND in_array($name, self::$_db_methods))
			{
				$json_args = array();
				foreach($arguments as $arg)
				{
					$json_args[]      = JSON::encode((is_array($arg) ? (object)$arg : $arg));
					$this->_benchmark = Profiler::start("Mango::{$this->_db}", "db.{$this->_name}.{$name}(".implode(',',$json_args).")");
				}
			}

			$return_value = call_user_func_array(array($this->collection(), $name), $arguments);

			if ($this->_benchmark)
			{
				// Stop the benchmark
				Profiler::stop($this->_benchmark);

				// Clean benchmark token
				$this->_benchmark = NULL;
			}

			return $return_value;
		}
		else
		{
			throw new Mango_Exception('Method :method not found',
				array(':method' => "Mango::{$name}")
			);
		}
	}

	/**
	 * Reset the state of the query
	 *
	 * [!!] This method must be called manually if re-using a collection for a new query.
	 *
	 * Pass `FALSE` to avoid full resetting:<br>
	 * <code>
	 *   $collection->reset(FALSE);
	 * </code>
	 *
	 * @param   boolean  $cursor_only  Resetting only MongoCursor? [Optional]
	 * @return  Mango_Collection
	 */
	public function reset($cursor_only = FALSE)
	{
		if( ! $cursor_only)
		{
			$this->_query = $this->_fields = $this->_options = array();
		}

		$this->_cursor = NULL;

		return $this;
	}

	/**
	 * Get the corresponding MongoCollection instance
	 *
	 * @return  MongoCollection
	 */
	public function collection()
	{
		$name = "{$this->_db}.{$this->_name}.{$this->_gridFS}";

		if ( ! isset(self::$_collections[$name]))
		{
			$method = ($this->_gridFS ? 'getGridFS' : 'selectCollection');
			self::$_collections[$name] = $this->db()->db()->$method($this->_name);
		}

		return self::$_collections[$name];
	}

	/**
	 * Get the Mango instance used for this collection
	 *
	 * @return Mango
	 */
	public function db()
	{
		return Mango::instance($this->_db);
	}

}