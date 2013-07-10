<?php defined('SYSPATH') OR die('No direct script access allowed.');
/**
 * Mango Collection
 *
 * This class can be used directly as a wrapper for MongoCollection/MongoCursor
 *
 * ### Usage
 *
 * <pre>
 *   $collection = new Mongo_Collection('users');
 *   $users = collection->sort_desc('published')->limit(10)->as_array(); // array of arrays
 * </pre>
 *
 * ### System Requirements
 *
 * - PHP 5.3 or higher
 * - MongoDB 2.4 or higher
 * - PHP-extension MongoDB 1.4.0 or higher
 *
 * @package    Gleez\Mango\Collection
 * @author     Sergey Yakovlev - Gleez
 * @version    0.3.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Mango_Collection implements Iterator, Countable {

	/* Sort mode - ascending */
	const ASC = 1;

	/* Sort mode - descending */
	const DESC = -1;

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
	 * The cursor instance in use while iterating a collection
	 * @var MongoCursor
	 */
	protected $_cursor;

	/**
	 * The current query options
	 * @var array
	 */
	protected $_options = array();

	/**
	 * The current query fields (a hash of 'field' => 1)
	 * @var array
	 */
	protected $_fields = array();

	/**
	 * The current query criteria (with field names translated)
	 * @var array
	 */
	protected $_query = array();

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
	 * @param   string   $name    The collection name
	 * @param   string   $db      The database configuration name [Optional]
	 * @param   boolean  $gridFS  Is the collection a gridFS instance? [Optional]
	 *
	 * @throws  Mango_Exception
	 */
	public function __construct($name, $db = NULL, $gridFS = FALSE)
	{
		if ( ! extension_loaded('mongo'))
		{
			throw new Mango_Exception('The php-mongo extension is not installed or is disabled.');
		}

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
	 * Passes on method calls to either the MongoCursor or the MongoCollection
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

		if (method_exists($this->getCollection(), $name))
		{
			if ($this->getDB()->profiling)
			{
				$json_args = array();
				foreach($arguments as $arg)
				{
					$json_args[] = JSON::encode($arg);
				}

				$this->_benchmark = Profiler::start(__CLASS__."::{$this->_db}", "db.{$this->_name}.{$name}(" . implode(', ', $json_args) . ")");
			}

			$retval = call_user_func_array(array($this->getCollection(), $name), $arguments);

			if ($this->_benchmark)
			{
				// Stop the benchmark
				Profiler::stop($this->_benchmark);

				// Clean benchmark token
				$this->_benchmark = NULL;
			}

			return $retval;
		}
		else
		{
			throw new Mango_Exception('Method :method not found',
				array(':method' => "Mango::{$name}")
			);
		}
	}

	/**
	 * Cloned objects have uninitialized cursors
	 *
	 * @since   0.3.0
	 */
	public function __clone()
	{
		$this->reset(TRUE);
	}

	/**
	 * Is the query iterating yet?
	 *
	 * @link    http://www.php.net/manual/en/mongocursor.info.php MongoCursor::info
	 *
	 * @since   0.3.0
	 *
	 * @return  boolean
	 *
	 * @throws  Exception
	 */
	public function is_iterating()
	{
		if (empty($this->_cursor))
		{
			return FALSE;
		}

		/** @var $info array */
		$info = $this->_cursor->info();

		if ( ! isset($info['started_iterating']))
		{
			throw new Exception('Driver version >= 1.0.10 required');
		}

		return $info['started_iterating'];
	}

	/**
	 * Return a string representation of the full query (in Mongo shell syntax)
	 *
	 * @return  string
	 *
	 * @since   0.3.0
	 *
	 * @uses    JSON::encodeMongo
	 */
	public function shellQuery()
	{
		$query = array();

		if ($this->_query)
		{
			$query[] = JSON::encodeMongo($this->_query);
		}
		else
		{
			$query[] = '{}';
		}

		if($this->_fields)
		{
			$query[] = JSON::encodeMongo($this->_fields);
		}

		/** @var $query string */
		$query = "db.{$this->_name}.find(" . implode(',', $query) . ")";

		foreach ($this->_options as $key => $value)
		{
			$query .= ".$key(" . JSON::encodeMongo($value) . ")";
		}

		return $query;
	}

	/**
	 * Instantiates a cursor, after this is called the query cannot be modified
	 *
	 * [!!] This is automatically called when the iterator initializes (rewind).
	 *
	 * @since   0.3.0
	 *
	 * @return  Mango_Collection
	 *
	 * @throws  MongoCursorException
	 * @throws  MongoException
	 */
	public function load()
	{
		// Execute the query, add query to any thrown exceptions
		try
		{
			$this->_cursor = $this->getCollection()->find($this->_query, $this->_fields);
		}
		catch(MongoCursorException $e)
		{
			throw new MongoCursorException("{$e->getMessage()}: {$this->shellQuery()}", $e->getCode());
		}
		catch(MongoException $e)
		{
			throw new MongoException("{$e->getMessage()}: {$this->shellQuery()}", $e->getCode());
		}

		// Add cursor options
		foreach ($this->_options as $key => $value)
		{
			if(is_null($value))
			{
				$this->_cursor->$key();
			}
			else
			{
				$this->_cursor->$key($value);
			}
		}

		return $this;
	}

	/**
	 * Reset the state of the query
	 *
	 * [!!] This method must be called manually if re-using a collection for a new query
	 *
	 * @since   0.3.0
	 *
	 * @param   boolean  $cursor_only  Reset cursor only?
	 *
	 * @return  Mango_Collection
	 */
	public function reset($cursor_only = FALSE)
	{
		if ( ! $cursor_only)
		{
			$this->_query = $this->_fields = $this->_options = array();
		}

		$this->_cursor = NULL;

		return $this;
	}

	/**
	 * Returns the current query results as an array
	 *
	 * @since   0.3.0
	 *
	 * @param   boolean  $objects  Pass FALSE to get raw data
	 *
	 * @return  array
	 */
	public function as_array($objects = TRUE)
	{
		$array = array();

		// Iterate using wrapper
		if ($objects)
		{
			foreach ($this as $key => $value)
			{
				$array[$key] = $value;
			}
		}
		// Iterate bypassing wrapper
		else
		{
			$this->rewind();

			foreach ($this->_cursor as $key => $value)
			{
				$array[$key] = $value;
			}
		}

		return $array;
	}

	/**
	 * Get the corresponding MongoCollection instance
	 *
	 * @return  MongoCollection
	 *
	 * @uses    Mango::db
	 */
	public function getCollection()
	{
		$name = "{$this->_db}.{$this->_name}.{$this->_gridFS}";

		if ( ! isset(self::$_collections[$name]))
		{
			$method = ($this->_gridFS ? 'getGridFS' : 'selectCollection');
			self::$_collections[$name] = $this->getDB()->db()->$method($this->_name);
		}

		return self::$_collections[$name];
	}

	/**
	 * Get the Mango instance used for this collection
	 *
	 * @since   0.3.0
	 *
	 * @return  Mango
	 *
	 * @uses    Mango::instance
	 */
	public function getDB()
	{
		return Mango::instance($this->_db);
	}

	/**
	 * Get a cursor option to be set before executing the query
	 *
	 * @since   0.3.0
	 *
	 * @param   string  $name
	 *
	 * @return  mixed
	 */
	public function getOption($name)
	{
		if ($name == 'query')
		{
			return $this->_query;
		}
		if ($name == 'fields')
		{
			return $this->_fields;
		}

		return isset($this->_options[$name]) ? $this->_options[$name] : NULL;
	}

	/**
	 * Access the MongoCursor instance directly, triggers a load if there is none
	 *
	 * @since   0.3.0
	 *
	 * @return  MongoCursor
	 */
	public function getCursor()
	{
		$this->_cursor OR $this->load();

		return $this->_cursor;
	}

	/**
	 * Set a cursor option
	 *
	 * Will apply to currently loaded cursor if it has not started iterating.
	 * Also supports setting 'query' and 'fields'.
	 *
	 * @since   0.3.0
	 *
	 * @param   string  $name
	 * @param   mixed   $value
	 *
	 * @return  Mango_Collection
	 *
	 * @throws  MongoCursorException
	 */
	public function setOption($name, $value)
	{
		if ($name != 'batchSize' AND $name != 'timeout' AND $this->is_iterating())
		{
			throw new MongoCursorException('The cursor has already started iterating');
		}

		if ($name == 'query')
		{
			$this->_query = $value;
		}
		elseif ($name == 'fields')
		{
			$this->_fields = $value;
		}
		else
		{
			if ($this->_cursor)
			{
				if (is_null($value))
				{
					$this->_cursor->$name();
				}
				else
				{
					$this->_cursor->$name($value);
				}
			}

			$this->_options[$name] = $value;
		}

		return $this;
	}

	/**
	 * Limits the number of results returned
	 *
	 * @since   0.3.0
	 *
	 * @param   integer  $num  The number of results to return
	 *
	 * @return  Mango_Collection
	 */
	public function limit($num)
	{
		return $this->setOption('limit', (int)$num);
	}

	/**
	 * Skips a number of results
	 *
	 * @since   0.3.0
	 *
	 * @param  integer  $num  The number of results to skip
	 *
	 * @return  Mango_Collection
	 */
	public function skip($num)
	{
		return $this->setOption('skip', (int)$num);
	}

	/**
	 * Sorts the results by given fields
	 *
	 * @since   0.3.0
	 *
	 * @param   array|string    $fields  A sort criteria or a key (requires corresponding $value)
	 * @param   string|integer  $dir     The direction if $fields is a key [Optional]
	 *
	 * @return  Mango_Collection
	 *
	 * @throws  MongoCursorException
	 */
	public function sort($fields, $dir = self::ASC)
	{
		if ($this->_cursor)
		{
			throw new MongoCursorException('The cursor has already started iterating');
		}

		if ( ! isset($this->_options['sort']))
		{
			// Clear current sort option
			$this->_options['sort'] = array();
		}

		if ( ! is_array($fields))
		{
			$fields = array($fields => $dir);
		}

		// Translate field aliases
		foreach ($fields as $field => $direction)
		{
			if (is_string($direction))
			{
				if (strtolower($direction) == 'asc' || $direction == '1')
				{
					$direction = self::ASC;
				}
				else
				{
					$direction = self::DESC;
				}
			}
			if (is_integer($direction))
			{
				if ($direction >= 1)
				{
					$direction = self::ASC;
				}
				else
				{
					$direction = self::DESC;
				}
			}

			$this->_options['sort'][$field] = $direction;
		}

		return $this;
	}

	/**
	 * Sorts the results ascending by the given field
	 *
	 * @since   0.3.0
	 *
	 * @param   string  $field  The field name to sort by
	 *
	 * @return  Mango_Collection
	 */
	public function sortAsc($field)
	{
		$this->sort($field, self::ASC);
	}

	/**
	 * Sorts the results descending by the given field
	 *
	 * @since   0.3.0
	 *
	 * @param   string  $field  The field name to sort by
	 *
	 * @return  Mango_Collection
	 */
	public function sortDesc($field)
	{
		return $this->sort($field, self::DESC);
	}

	/**
	 * Checks if the cursor is reading a valid result
	 *
	 * @since   0.3.0
	 *
	 * See [Iterator::valid]
	 *
	 * @link    http://www.php.net/manual/en/mongocursor.valid.php MongoCursor::valid
	 *
	 * @return  boolean
	 */
	public function valid()
	{
		return $this->_cursor->valid();
	}

	/**
	 * Returns the cursor to the beginning of the result set
	 *
	 * @since   0.3.0
	 *
	 * See [Iterator::rewind]
	 *
	 * @link    http://www.php.net/manual/en/mongocursor.rewind.php MongoCursor::rewind
	 *
	 * @uses    Profiler::start
	 * @uses    Profiler::stop
	 */
	public function rewind()
	{
		try
		{
			if ($this->getDB()->profiling)
			{
				$this->_benchmark = Profiler::start("Mango::{$this->_db}", $this->shellQuery());
			}

			$this->getCursor()->rewind();

			if ($this->_benchmark)
			{
				// Stop the benchmark
				Profiler::stop($this->_benchmark);

				// Clean benchmark token
				$this->_benchmark = NULL;
			}
		}
		catch(MongoCursorException $e)
		{
			throw new MongoCursorException("{$e->getMessage()}: {$this->shellQuery()}", $e->getCode());
		}
		catch(MongoException $e)
		{
			throw new MongoException("{$e->getMessage()}: {$this->shellQuery()}", $e->getCode());
		}
	}

	/**
	 * Advances the cursor to the next result
	 *
	 * @since   0.3.0
	 *
	 * See [Iterator::next]
	 *
	 * @link    http://www.php.net/manual/en/mongocursor.next.php MongoCursor::next
	 */
	public function next()
	{
		$this->_cursor->next();
	}

	/**
	 * Returns the current result's _id
	 *
	 * @since   0.3.0
	 *
	 * See [Iterator::key]
	 *
	 * @link    http://www.php.net/manual/en/mongocursor.key.php MongoCursor::key
	 * @return  string
	 */
	public function key()
	{
		return $this->_cursor->key();
	}

	/**
	 * Returns the current element
	 *
	 * @since   0.3.0
	 *
	 * See [Iterator::current]
	 *
	 * @link    http://www.php.net/manual/en/mongocursor.current.php MongoCursor::current
	 *
	 * @return  array
	 */
	public function current()
	{
		return $this->_cursor->current();
	}

	/**
	 * Count the results from the query
	 *
	 * + Count the results from the current query: pass FALSE for "all" results (disregard limit/skip)
	 * + Count results of a separate query: pass an array or JSON string of query parameters
	 *
	 * @since   0.3.0
	 *
	 * See [Countable::count]
	 *
	 * @link    http://www.php.net/manual/en/mongocursor.count.php MongoCursor::count
	 *
	 * @param   boolean|array|string  $query
	 *
	 * @return  integer
	 *
	 * @throws  Exception
	 *
	 * @uses    JSON::encodeMongo
	 * @uses    JSON::decode
	 * @uses    Profiler::start
	 * @uses    Profiler::stop
	 */
	public function count($query = TRUE)
	{
		if (is_bool($query))
		{
			// Profile count operation for cursor
			if ($this->getDB()->profiling)
			{
				$this->_benchmark = Profiler::start("Mango::{$this->_db}", $this->shellQuery() . ".count(" . JSON::encodeMongo($query) .")");
			}

			$this->_cursor OR $this->load(TRUE);

			$count = $this->_cursor->count($query);
		}
		else
		{
			if (is_string($query) AND $query[0] == "{")
			{
				$query = JSON::decode($query, TRUE);
				if (is_null($query))
				{
					throw new Exception('Unable to parse query from JSON string');
				}
			}

			$query_trans = array();

			foreach ($query as $field => $value)
			{
				$query_trans[$field] = $value;
			}

			$query = $query_trans;

			// Profile count operation for collection
			if ($this->getDB()->profiling)
			{
				$this->_benchmark = Profiler::start("Mango::{$this->_db}", "db.{$this->_name}.count(" . ($query ? JSON::encodeMongo($query) : '') .")");
			}

			$count = $this->getCollection()->count($query);
		}

		// End profiling count
		if ($this->_benchmark)
		{
			// Stop the benchmark
			Profiler::stop($this->_benchmark);

			// Clean benchmark token
			$this->_benchmark = NULL;
		}

		return $count;
	}
}