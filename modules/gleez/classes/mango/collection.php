<?php defined('SYSPATH') OR die('No direct script access allowed.');
/**
 * Mango Collection
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
	 * Benchmark token
	 * @var string
	 */
	protected $_benchmark = NULL;

	/**
	 * A cache of MongoCollection instances for performance
	 * @var array
	 */
	protected static $_collections = array();

	/**
	 * Class constructor
	 *
	 * Instantiate a new collection object, can be used for querying, updating, etc..
	 *
	 * Example:<br>
	 * <code>
	 *   $posts = new Mango_Collection('posts');
	 * </code>
	 *
	 * @param  string   $name    The collection name
	 * @param  string   $db      The database configuration name [Optional]
	 * @param  boolean  $gridFS  Is the collection a gridFS instance? [Optional]
	 */
	public function __construct($name, $db = NULL, $gridFS = FALSE)
	{
		if (is_null($db))
		{
			$db = Mango::$default;
		}

		$this->_name   = $name;
		$this->_db     = $db;
		$this->_gridFS = $gridFS;
	}

	/**
	 * Return the collection name
	 *
	 * @return string
	 */
	public function  __toString()
	{
		return $this->_name;
	}

	/**
	 * Magic method override
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
		if (method_exists($this->getCollection(), $name))
		{
			if ($this->db()->profiling)
			{
				$json_args = array();
				foreach($arguments as $arg)
				{
					$json_args[] = JSON::encode($arg);
				}

				$this->_benchmark = Profiler::start("Mango::{$this->_db}", "db.{$this->_name}.{$name}(" . implode(', ', $json_args) . ")");
			}

			$return_value = call_user_func_array(array($this->getCollection(), $name), $arguments);

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
	 * Get the corresponding MongoCollection instance
	 *
	 * @return  MongoCollection
	 */
	public function getCollection()
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