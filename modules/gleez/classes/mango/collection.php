<?php
/**
 * # Mango Collection
 *
 * This class can be used directly as a wrapper for MongoCollection/MongoCursor
 *
 * ## Usage
 *
 * ~~~
 * $collection = new Mango_Collection('users');
 *
 * // $users now is array of arrays
 * $users = collection->sortDesc('published')
 *                    ->limit(10)
 *                    ->toArray();
 * ~~~
 *
 * ## System Requirements
 *
 * - MongoDB 2.4 or higher
 * - PHP-extension MongoDB 1.4.0 or higher
 *
 * This class was adapted from
 * [colinmollenhour/mongodb-php-odm](https://github.com/colinmollenhour/mongodb-php-odm)
 *
 * @method     mixed          batchInsert(array $a, array $options = array())
 * @method     array          createDBRef(array $a)
 * @method     array          deleteIndex(mixed $keys)
 * @method     array          deleteIndexes()
 * @method     array          drop()
 * @method     boolean        ensureIndex(mixed $keys, array $options = array())
 * @method     array          getDBRef(array $ref)
 * @method     array          getIndexInfo()
 * @method     string         getName()
 * @method     array          getReadPreference()
 * @method     boolean        getSlaveOkay()
 * @method     array          group(mixed $keys, array $initial, MongoCode $reduce, array $options = array())
 * @method     boolean|array  insert(array $data, array $options = array())
 * @method     boolean|array  remove(array $criteria = array(), array $options = array())
 * @method     mixed          save(array $a, array $options = array())
 * @method     boolean        setReadPreference(string $read_preference, array $tags = array())
 * @method     boolean        setSlaveOkay(boolean $ok = TRUE)
 * @method     boolean|array  update(array $criteria, array $new_object, array $options = array())
 * @method     array          validate(boolean $scan_data = FALSE)
 *
 * @package    Gleez\Mango\Collection
 * @author     Gleez Team
 * @version    0.6.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 *
 * @link       https://github.com/colinmollenhour/mongodb-php-odm  MongoDB PHP ODM
 * @link       http://www.martinfowler.com/eaaCatalog/tableDataGateway.html  Table Data Gateway pattern
 * @link       http://www.martinfowler.com/eaaCatalog/rowDataGateway.html  Row Data Gateway pattern
 */
class Mango_Collection implements Iterator, Countable {

	/** @type integer ASC Sort mode - ascending */
	const ASC = 1;

	/** @type integer DESC Sort mode - descending */
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
	 * The class name or instance of the corresponding
	 * document model or NULL if direct mode
	 *
	 * @var mixed
	 */
	protected $_model;

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
	 * A cache of [Mango_Document] model instances for performance
	 * @var array
	 */
	protected static $_models = array();

	/**
	 * Class constructor
	 *
	 * Instantiate a new collection object, can be used for querying, updating, etc..
	 *
	 * Example:
	 * ~~~
	 * $posts = new Mango_Collection('posts');
	 * ~~~
	 *
	 * @param   string          $name    The collection name [Optional]
	 * @param   string          $db      The database configuration name [Optional]
	 * @param   boolean         $gridFS  Is the collection a gridFS instance? [Optional]
	 * @param   boolean|string  $model   Class name of template model for new documents [Optional]
	 */
	public function __construct($name = NULL, $db = NULL, $gridFS = FALSE, $model = FALSE)
	{
		if ( ! is_null($name))
		{
			if (is_null($db))
			{
				$db = Mango::$default;
			}

			$this->_name   = $name;
			$this->_db     = $db;
			$this->_gridFS = $gridFS;
		}

		if ($model)
		{
			$this->_model = $model;
		}
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
	 * @param   array   $arguments  Enumerated array containing the parameters passed to the $name
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
		return FALSE;
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
	 * Merge two distinct arrays __recursively__
	 *
	 * MongoDB implementation.
	 * Return an array of values resulted from merging the arguments together
	 *
	 * [!!] This method doesn't change the datatypes of the values in the arrays
	 *
	 * @todo    May be should be merged with Arr Helper
	 *
	 * @since   0.4.0
	 *
	 * @param   array  $array1  Initial array to merge
	 * @param   array  $array2  Array to recursively merge
	 *
	 * @return  array
	 */
	protected static function _mergeDistinctArrays(array $array1, array $array2)
	{
		if ( ! count($array1))
		{
			return $array2;
		}

		foreach ($array2 as $key => $value)
		{
			if (is_array($value) AND isset($array1[$key]) AND is_array($array1[$key]))
			{
				// Intersect $in queries
				if ($key == '$in')
				{
					$array1[$key] = array_intersect($array1[$key], $value);
				}
				// Union $nin and $all queries
				elseif ($key == '$nin' OR $key == '$all')
				{
					$array1[$key] = array_unique(array_splice($array1[$key], count($array1[$key]), 0, $value));
				}
				// Recursively merge all other queries/values
				else
				{
					$array1 [$key] = self::_mergeDistinctArrays($array1 [$key], $value);
				}
			}
			else
			{
				$array1[$key] = $value;
			}
		}

		return $array1;
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
	 * Is the query executed yet?
	 *
	 * @since   0.4.0
	 *
	 * @return  boolean
	 */
	public function is_loaded()
	{
		return isset($this->_cursor);
	}

	/**
	 * Is capped collection?
	 *
	 * [!!] Use [Capped Collection](http://docs.mongodb.org/manual/core/capped-collections/)
	 *      to support high-bandwidth inserts
	 *
	 * @since   0.4.0
	 *
	 * @return  boolean
	 */
	public function is_caped()
	{
		$stats = $this->getStats();

		return ( ! empty($stats['capped']));
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
	 * Set some criteria for the query
	 *
	 * Unlike [MongoCollection::find], this can be called multiple times and the
	 * query parameters will be merged together.
	 *
	 * The possible values for `$query`:
	 *
	 * + `$query` is an array
	 * + `$query` is a field name and `$value` is the value to search for
	 * + `$query` is a JSON string that will be interpreted as the query criteria
	 *
	 * @since   0.4.0
	 *
	 * @param   mixed  $query  An array of parameters or a key [Optional]
	 * @param   mixed  $value  If $query is a key, this is the value [Optional]
	 *
	 * @return  Mango_Collection
	 *
	 * @throws  MongoCursorException
	 * @throws  Mango_Exception
	 *
	 * @uses    JSON::decode
	 */
	public function find($query = array(), $value = NULL)
	{
		if ($this->_cursor)
		{
			throw new MongoCursorException('The cursor has already been instantiated');
		}

		if ( ! is_array($query))
		{
			if ($query[0] == "{")
			{
				$query = JSON::decode($query, TRUE);
			}
			else
			{
				$query = array($query => $value);
			}
		}

		// Translate field aliases
		$query_fields = array();

		foreach ($query as $field => $value)
		{
			// Special purpose condition
			if ($field[0] == '$')
			{
				// $or and $where and possibly other special values
				if ($field == '$or' AND ! is_int(key($value)))
				{
					if ( ! isset($this->_query['$or']))
					{
						$this->_query['$or'] = array();
					}
					$this->_query['$or'][] = $value;
				}
				elseif ($field == '$where')
				{
					$this->_query['$where'] = $value;
				}
				else
				{
					$query_fields[$field] = $value;
				}
			}
			// Simple key = value condition
			else
			{
				$query_fields[$this->getFieldName($field)] = $value;
			}
		}

		$this->_query = self::_mergeDistinctArrays($this->_query, $query_fields);

		return $this;
	}

	/**
	 * Queries this collection, returning a single element
	 *
	 * Wrapper for [MongoCollection::findOne] which adds field name translations
	 * and allows query to be a single `_id`.
	 *
	 * Return record matching query or NULL
	 *
	 * @param   mixed  $query   An _id, a JSON encoded query or an array by which to search [Optional]
	 * @param   array  $fields  Fields of the results to return [Optional]
	 *
	 * @return  mixed
	 *
	 * @uses    JSON::decode
	 */
	public function findOne($query = array(), $fields = array())
	{
		// String query is either JSON encoded or an _id
		if ( ! is_array($query))
		{
			if ($query[0] == "{")
			{
				$query = JSON::decode($query, TRUE);
			}
			else
			{
				$query = array('_id' => $query);
			}
		}

		// Translate field aliases
		$query_trans = array();
		foreach ($query as $field => $value)
		{
			$query_trans[$this->getFieldName($field)] = $value;
		}

		$fields_trans = array();
		if ($fields AND is_int(key($fields)))
		{
			$fields = array_fill_keys($fields, 1);
		}
		foreach ($fields as $field => $value)
		{
			$fields_trans[$this->getFieldName($field)] = $value;
		}

		return $this->__call('findOne', array($query_trans, $fields_trans));
	}

	/**
	 * Simple findAndModify helper
	 *
	 * @since   0.4.0
	 *
	 * @param   array  $command  The query to send
	 *
	 * @return  mixed
	 *
	 * @uses    Mango::findAndModify
	 */
	public function findAndModify(array $command)
	{
		return $this->getDB()->findAndModify($this->_name, $command);
	}

	/**
	 * Perform an update, throw exception on errors
	 *
	 * Same usage as [MongoCollection::update] except it throws an exception on error.
	 *
	 * Return values depend on type of update:
	 *
	 * + __multiple__:        return number of documents updated on success
	 * + __upsert__:          return upserted id if upsert resulted in new document
	 * + __updatedExisting__: flag for all other cases
	 *
	 * @since   0.4.2
	 *
	 * @param   array  $criteria    Description of the objects to update
	 * @param   array  $new_object  The object with which to update the matching records
	 * @param   array  $options     Associative array of the form array("optionname" => <boolean>, ...) [Optional]
	 *
	 * @return  integer|boolean|MongoId
	 *
	 * @throws  MongoException
	 *
	 * @uses    Arr::merge
	 */
	public function safeUpdate(array $criteria, array $new_object, $options = array())
	{
		$options = Arr::merge(
			array(
				'w'        => 1,      // The write will be acknowledged by the server
				'upsert'   => FALSE,  // If no document matches $criteria, a new document will be inserted
				'multiple' => FALSE   // All documents matching $criteria will be updated?
			),
			$options
		);

		$result = $this->update($criteria, $new_object, $options);

		// A write will not be followed up with a getLastError call,
		// and therefore not checked ("fire and forget")
		if ($options['w'] == 0)
		{
			// boolean
			return $result;
		}

		// According to the driver docs an exception should have already been
		// thrown if there was an error, but just in case
		if ( ! $result['ok'])
		{
			throw new MongoException($result['err']);
		}

		// Return the number of documents updated for multiple updates or
		// the updatedExisting flag for single updates
		if ($options['multiple'])
		{
			// integer
			return $result['n'];
		}
		// Return the upserted id if a document was upserted with a new _id
		elseif ($options['upsert'] AND ! $result['updatedExisting'] AND isset($result['upserted']))
		{
			// MongoId
			return $result['upserted'];
		}
		// Return the updatedExisting flag for single, non-upsert updates
		else
		{
			// boolean
			return $result['updatedExisting'];
		}
	}

	/**
	 * Remove, throw exception on errors
	 *
	 * Same usage as [MongoCollection::remove] except it throws an exception on error.
	 *
	 * Returns number of documents removed if "safe",
	 * otherwise just if the operation was successfully sent.
	 *
	 * [!!] Note: You cannot use this method with a capped collection.
	 *
	 * @since   0.4.2
	 *
	 * @param   array  $criteria  Description of records to remove [Optional]
	 * @param   array  $options   Options for remove [Optional]
	 *
	 * @return  array|bool
	 *
	 * @throws  MongoException
	 *
	 * @uses    Arr::merge
	 */
	public function safeRemove(array $criteria = array(), array $options = array())
	{
		$options = Arr::merge(
			array(
				'w'       => 1,     // The write will be acknowledged by the server
				'justOne' => FALSE, // To limit the deletion to just one document, set to true
			),
			$options
		);

		$result = $this->remove($criteria, $options);

		// A write will not be followed up with a getLastError call,
		// and therefore not checked ("fire and forget")
		if ($options['w'] == 0)
		{
			/** @var $result boolean */
			return $result;
		}

		// According to the driver docs an exception should have already been
		// thrown if there was an error, but just in case
		if ( ! $result['ok'])
		{
			throw new MongoException($result['err']);
		}

		// Return the number of documents removed
		return $result['n'];
	}

	/**
	 * Drop collection, throw exception on errors
	 *
	 * @since   0.4.4
	 *
	 * @return  boolean
	 *
	 * @throws  Mango_Exception
	 */
	public function safeDrop()
	{
		$result = $this->drop();

		if ( ! $result['ok'])
		{
			throw new Mango_Exception($result['errmsg']);
		}

		return TRUE;
	}

	/**
	 * Retrieve a list of distinct values for the given key across a collection
	 *
	 * Same usage as [MongoCollection::distinct] except it throws an exception on error.
	 *
	 * @since   0.4.0
	 *
	 * @link    http://www.php.net/manual/en/mongocollection.distinct.php MongoCollection::distinct
	 *
	 * @param   string  $key    The key to use
	 * @param   array   $query  An optional query parameters [Optional]
	 *
	 * @return  array
	 *
	 * @throws  MongoException
	 *
	 * @uses    Mango::safeCommand
	 */
	public function distinct($key, array $query = array())
	{
		return $this->getDB()->safeCommand(array(
			'distinct' => $this->_name,
			'key'      => (string)$key,
			'query'    => $query
		));
	}

	/**
	 * Perform an aggregation using the aggregation framework
	 *
	 * Same usage as [MongoCollection::aggregate] except it throws an exception on error.
	 *
	 * [!!] This method accepts either a variable amount of pipeline operators,
	 *      or a single array of operators constituting the pipeline.
	 *
	 * Example:
	 * ~~~
	 * // Return all states with a population greater than 10 million:
	 * $results = $collection->aggregate(
	 *     array(
	 *         '$group' => array(
	 *             '_id' => '$state',
	 *             'totalPop' => array('$sum' => '$pop')
	 *         )
	 *         '$match' => array(
	 *             'totalPop' => array('$gte' => 10*1000*1000)
	 *         )
	 *     )
	 * );
	 * ~~~
	 *
	 * @since   0.4.0
	 *
	 * @link    http://www.php.net/manual/en/mongocollection.aggregate.php MongoCollection::aggregate
	 *
	 * @param   array  $pipeline  An array of pipeline operators
	 *
	 * @return  array
	 *
	 * @throws  MongoException
	 *
	 * @uses    Mango::safeCommand
	 */
	public function aggregate(array $pipeline)
	{
		return $this->getDB()->safeCommand(array(
			'aggregate' => $this->_name,
			'pipeline'  => $pipeline,
		));
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
	 * @since   0.4.0
	 *
	 * @param   boolean  $objects  Pass FALSE to get raw data
	 *
	 * @return  array
	 */
	public function toArray($objects = TRUE)
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
	 * Get an instance of the corresponding document model
	 *
	 * @since   0.6.0
	 *
	 * @return  Mango_Document
	 */
	public function getModel()
	{
		if ( ! isset(self::$_models[$this->_model]))
		{
			$model = $this->_model;
			self::$_models[$this->_model] = new $model;
		}

		return self::$_models[$this->_model];
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
	 * Translate a field name according to aliases defined in the model if they exist
	 *
	 * @since   0.4.0  getFieldName($name)  Introduced
	 * @since   0.6.0  getFieldName($name)  Used $this->_model
	 *
	 * @param   string  $name  Field name
	 *
	 * @return  string
	 *
	 * @uses    Mango_Document::getFieldName
	 */
	public function getFieldName($name)
	{
		if ( ! $this->_model)
		{
			return $name;
		}

		return $this->getModel()->getFieldName($name);
	}

	/**
	 * Get a variety of storage statistics for a given collection
	 *
	 * [!!] The `collStats` command returns a variety of storage
	 *      statistics for a given collection.
	 *
	 * Example:
	 * ~~~
	 * $output = $collection->getStats();
	 * ~~~
	 *
	 * @since   0.4.0
	 *
	 * @param   mixed  $scale  Argument to scale the output [Optional]
	 * @return  array
	 */
	public function getStats($scale = 1024)
	{
		return $this->getDB()->safeCommand(array(
			'collStats' => $this->_name,
			'scale'     => $scale
		));
	}

	/**
	 * Implement [MongoCursor::getNext] so that the return value
	 * is a [Mango_Document] instead of array
	 *
	 * @since   0.6.0
	 *
	 * @return array|Mango_Document
	 */
	public function getNext()
	{
		if($this->getDB()->profiling AND ( ! $this->_cursor OR ! $this->is_iterating()))
		{
			$this->getCursor();

			// Start the benchmark
			$this->_benchmark = Profiler::start("Mongo_Database::{$this->_db}", $this->shellQuery());

			$this->getCursor()->next();

			// Stop the benchmark
			Profiler::stop($this->_benchmark);

			// Clean benchmark token
			$this->_benchmark = NULL;
		}
		else
		{
			$this->getCursor()->next();
		}

		return $this->current();
	}

	/**
	 * Implement [MongoCursor::hasNext] to ensure that the cursor is loaded
	 *
	 * @since   0.6.0
	 *
	 * @return  boolean
	 */
	public function hasNext()
	{
		return $this->getCursor()->hasNext();
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
			throw new MongoCursorException(__('The cursor has already started iterating'));
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
	 * Unset a cursor option to be set before executing the query
	 *
	 * @since   0.4.2
	 *
	 * @param   string  $name  Option name
	 *
	 * @return  Mango_Collection
	 *
	 * @throws  MongoCursorException
	 */
	public function unsetOption($name)
	{
		if ($this->is_iterating())
		{
			throw new MongoCursorException(__('The cursor has already started iterating'));
		}

		unset($this->_options[$name]);

		return $this;
	}

	/**
	 * See if a cursor has an option to be set before executing the query
	 *
	 * Example:
	 * ~~~
	 * // Set option
	 * $collection->limit(50)->skip(100);
	 *
	 * // Check option
	 * if ($collection->hasOption('limit'))
	 * {
	 *     // some actions...
	 * }
	 * ~~~
	 *
	 * @since   0.4.4
	 *
	 * @param   string  $name  Option name
	 *
	 * @return  boolean
	 */
	public function hasOption($name)
	{
		if (is_string($name))
		{
			return array_key_exists($name, $this->_options);
		}

		return FALSE;
	}

	/**
	 * Gives the database a hint about the query
	 *
	 * If a string is passed, it should correspond to an index name.
	 * If an array or object is passed, it should correspond to the
	 * specification used to create the index
	 *
	 * @link    http://www.php.net/manual/en/mongocursor.hint.php
	 *
	 * @since   0.4.0
	 *
	 * @param   mixed  $index  Index to use for the query
	 *
	 * @return  Mango_Collection
	 */
	public function hint($index)
	{
		return $this->setOption('hint', $index);
	}

	/**
	 * Sets whether this cursor will timeout
	 *
	 * Ordinarily, a cursor "dies" on the database server after a certain length of time
	 * (approximately 10 minutes), to prevent inactive cursors from hogging resources.
	 *
	 * @link    http://php.net/manual/en/mongocursor.immortal.php
	 *
	 * @since   0.5.0
	 *
	 * @param   boolean|integer  $liveForever  If the cursor should be immortal [Optional]
	 *
	 * @return  Mango_Collection
	 */
	public function immortal($liveForever = TRUE)
	{
		return $this->setOption('immortal', (bool)$liveForever);
	}

	/**
	 * Limits the number of results returned
	 *
	 * @link    http://www.php.net/manual/en/mongocursor.limit.php
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
	 * @link    http://www.php.net/manual/en/mongocursor.skip.php
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
	 * Sets whether this query can be done on a slave
	 *
	 * @link    http://www.php.net/manual/en/mongocursor.slaveokay.php
	 *
	 * @since   0.5.0
	 *
	 * @param   boolean|integer  $okay  If it is okay to query the secondary [Optional]
	 *
	 * @return  Mango_Collection
	 */
	public function slaveOkay($okay = TRUE)
	{
		return $this->setOption('slaveOkay', (bool)$okay);
	}

	/**
	 * Use snapshot mode for the query
	 *
	 * Snapshot mode assures no duplicates are returned, or objects missed,
	 * which were present at both the start and end of the query's execution
	 * (if an object is new during the query, or deleted during the query,
	 * it may or may not be returned, even with snapshot mode).
	 *
	 * @link    http://www.php.net/manual/en/mongocursor.snapshot.php
	 *
	 * @since   0.5.0
	 *
	 * @return  Mango_Collection
	 */
	public function snapshot()
	{
		return$this->setOption('snapshot', NULL);
	}

	/**
	 * Create Tailable Cursor
	 *
	 * Sets whether this cursor will be left open after fetching the last results.
	 *
	 * By default, MongoDB will automatically close a cursor when the client has
	 * exhausted all results in the cursor. However, for capped collections you
	 * may use a Tailable Cursor that remains open after the client exhausts the
	 * results in the initial cursor.
	 *
	 * @since   0.4.5
	 *
	 * @param   boolean  $tail  If TRUE will be sets tailable option
	 *
	 * @return  Mango_Collection
	 */
	public function tailable($tail = TRUE)
	{
		return $this->setOption('tailable', $tail);
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
			throw new MongoCursorException(__('The cursor has already started iterating'));
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
		foreach ($fields as $field => $dir)
		{
			if (is_string($dir))
			{
				if (strtolower($dir) == 'asc' || $dir == '1')
				{
					$dir = self::ASC;
				}
				else
				{
					$dir = self::DESC;
				}
			}

			$this->_options['sort'][$this->getFieldName($field)] = $dir;
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
		return $this->sort($field, self::ASC);
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
	 * @uses    Mango::$profiling
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
				$this->_benchmark = Profiler::start("Mango_Collection::{$this->_db}", $this->shellQuery() . ".count(" . JSON::encodeMongo($query) .")");
			}

			$this->_cursor OR $this->load(TRUE);

			$count = $this->_cursor->count($query);
		}
		else
		{
			if (is_string($query) AND $query[0] == "{")
			{
				$query = JSON::decode($query, TRUE);
			}

			$query_trans = array();

			foreach ($query as $field => $value)
			{
				$query_trans[$this->getFieldName($field)] = $value;
			}

			$query = $query_trans;

			// Profile count operation for collection
			if ($this->getDB()->profiling)
			{
				$this->_benchmark = Profiler::start("Mango_Collection::{$this->_db}", "db.{$this->_name}.count(" . ($query ? JSON::encodeMongo($query) : '') .")");
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