<?php
/**
 * Gleez CMS (http://gleezcms.org)
 *
 * @link https://github.com/cleez/cms Canonical source repository
 * @copyright Copyright (c) 2011-2014 Gleez Technologies
 * @license http://gleezcms.org/license Gleez CMS License
 */

namespace Gleez\Mango;

use Profiler;
use JSON;

/**
 * Gleez Mongo Collection
 *
 * This class can be used directly as a wrapper for MongoCollection/MongoCursor
 *
 * Usage:
 * ~~~
 * $collection = new \Gleez\Mango\Collection('users');
 *
 * // $users now is array of arrays
 * $users = $collection->sortDesc('published')
 *                     ->limit(10)
 *                     ->toArray();
 * ~~~
 *
 * System Requirements
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
 * @method     array          group(mixed $keys, array $initial, \MongoCode $reduce, array $options = array())
 * @method     boolean|array  insert(array $data, array $options = array())
 * @method     boolean|array  remove(array $criteria = array(), array $options = array())
 * @method     mixed          save(array $a, array $options = array())
 * @method     boolean        setReadPreference(string $read_preference, array $tags = array())
 * @method     boolean        setSlaveOkay(boolean $ok = true)
 * @method     boolean|array  update(array $criteria, array $new_object, array $options = array())
 * @method     array          validate(boolean $scan_data = false)
 *
 * @package    Gleez\Mango
 * @author     Gleez Team
 * @version    1.0.0-gleez-1.1
 *
 * @link       https://github.com/colinmollenhour/mongodb-php-odm  MongoDB PHP ODM
 * @link       http://www.martinfowler.com/eaaCatalog/tableDataGateway.html  Table Data Gateway pattern
 * @link       http://www.martinfowler.com/eaaCatalog/rowDataGateway.html  Row Data Gateway pattern
 */
class Collection implements \Iterator, \Countable {

	/**
	 * Sort mode - ascending
	 * @type integer
	 */
	const ASC = 1;

	/**
	 * Sort mode - descending
	 * @type integer
	 */
	const DESC = -1;

	/**
	 * The name of the collection within the database or the gridFS prefix if gridFS is true
	 * @var string
	 */
	protected $name;

	/**
	 * The database configuration name
	 * Passed to [\Gleez\Mango\Client::instance]
	 * @var string
	 */
	protected $db;

	/**
	 * Whether or not this collection is a gridFS collection
	 * @var boolean
	 */
	protected $gridFS = false;

	/**
	 * The class name or instance of the corresponding
	 * document model or null if direct mode
	 *
	 * @var mixed
	 */
	protected $model;

	/**
	 * Benchmark token
	 * @var string
	 */
	protected $benchmark = null;

	/**
	 * The cursor instance in use while iterating a collection
	 * @var \MongoCursor
	 */
	protected $cursor;

	/**
	 * The current query options
	 * @var array
	 */
	protected $options = array();

	/**
	 * The current query fields (a hash of 'field' => 1)
	 * @var array
	 */
	protected $fields = array();

	/**
	 * The current query criteria (with field names translated)
	 * @var array
	 */
	protected $query = array();

	/**
	 * A cache of \MongoCollection instances for performance
	 * @var array
	 */
	protected static $collections = array();

	/**
	 * A cache of [\Gleez\Mango\Document] model instances for performance
	 * @var array
	 */
	protected static $models = array();

	/**
	 * Class constructor
	 *
	 * Instantiate a new collection object, can be used for querying, updating, etc..
	 *
	 * Example:
	 * ~~~
	 * $posts = new \Gleez\Mango\Collection('posts');
	 * ~~~
	 *
	 * @param   string       $name    The collection name [Optional]
	 * @param   string       $db      The database configuration name [Optional]
	 * @param   boolean      $gridFS  Is the collection a gridFS instance? [Optional]
	 * @param   bool|string  $model   Class name of template model for new documents [Optional]
	 */
	public function __construct($name = null, $db = null, $gridFS = false, $model = false)
	{
		if (!empty($name)) {
			$this->name   = $name;
			$this->db     = $db ?: Client::$default;
			$this->gridFS = $gridFS;
		}

		$this->model = $model ?: null;
	}

	/**
	 * Return the collection name
	 *
	 * @return string
	 */
	public function  __toString()
	{
		return $this->name;
	}

	/**
	 * Magic method override
	 *
	 * Passes on method calls to either the \MongoCursor or the \MongoCollection
	 *
	 * @param   string  $name       Name of the method being called
	 * @param   array   $arguments  Enumerated array containing the parameters passed to the $name
	 *
	 * @return  mixed
	 *
	 * @throws  \Gleez\Mango\Exception
	 *
	 * @uses    \Profiler::start
	 * @uses    \Profiler::stop
	 * @uses    \JSON::encode
	 */
	public function __call($name, $arguments)
	{
		if ($this->cursor && method_exists($this->cursor, $name))
			return call_user_func_array(array($this->cursor, $name), $arguments);

		if (method_exists($this->getCollection(), $name)) {
			if ($this->getClientInstance()->profiling) {
				$json_args = array();
				foreach($arguments as $arg)
					$json_args[] = JSON::encode($arg);

				$this->benchmark = Profiler::start(__CLASS__."::{$this->db}", "db.{$this->name}.{$name}(" . implode(', ', $json_args) . ")");
			}

			$retval = call_user_func_array(array($this->getCollection(), $name), $arguments);

			if ($this->benchmark) {
				// Stop the benchmark
				Profiler::stop($this->benchmark);

				// Clean benchmark token
				$this->benchmark = null;
			}

			return $retval;
		} else
			throw new Exception('Method :method not found', array(':method' => get_class($this->getCollection())."::{$name}"));
	}

	/**
	 * Cloned objects have uninitialized cursors
	 *
	 * @since   0.3.0
	 */
	public function __clone()
	{
		$this->reset(true);
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
	protected static function mergeDistinctArrays(array $array1, array $array2)
	{
		if (!count($array1))
			return $array2;

		foreach ($array2 as $key => $value)
		{
			if (is_array($value) && isset($array1[$key]) && is_array($array1[$key])) {
				// Intersect $in queries
				if ($key == '$in')
					$array1[$key] = array_intersect($array1[$key], $value);
				// Union $nin and $all queries
				else if ($key == '$nin' || $key == '$all')
					$array1[$key] = array_unique(array_splice($array1[$key], count($array1[$key]), 0, $value));
				// Recursively merge all other queries/values
				else
					$array1 [$key] = static::mergeDistinctArrays($array1 [$key], $value);
			} else
				$array1[$key] = $value;
		}

		return $array1;
	}

	/**
	 * Is the query iterating yet?
	 *
	 * @link    http://www.php.net/manual/en/mongocursor.info.php \MongoCursor::info
	 *
	 * @since   0.3.0
	 *
	 * @return  boolean
	 */
	public function is_iterating()
	{
		if (empty($this->cursor))
			return false;

		/** @var $info array */
		$info = $this->cursor->info();

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
		return isset($this->cursor);
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

		return (!empty($stats['capped']));
	}

	/**
	 * Return a string representation of the full query (in Mongo shell syntax)
	 *
	 * @return  string
	 *
	 * @since   0.3.0
	 *
	 * @uses    \JSON::encodeMongo
	 */
	public function shellQuery()
	{
		$query = array();

		if ($this->query)
			$query[] = JSON::encodeMongo($this->query);
		else
			$query[] = '{}';

		if ($this->fields)
			$query[] = JSON::encodeMongo($this->fields);

		/** @var $query string */
		$query = "db.{$this->name}.find(" . implode(',', $query) . ")";

		foreach ($this->options as $key => $value)
			$query .= ".$key(" . JSON::encodeMongo($value) . ")";

		return $query;
	}

	/**
	 * Instantiates a cursor, after this is called the query cannot be modified
	 *
	 * [!!] This is automatically called when the iterator initializes (rewind).
	 *
	 * @since   0.3.0
	 *
	 * @return  \Gleez\Mango\Collection
	 *
	 * @throws  \Gleez\Mango\Exception
	 */
	public function load()
	{
		// Execute the query, add query to any thrown exceptions
		try {
			$this->cursor = $this->getCollection()->find($this->query, $this->fields);
		} catch(\Exception $e) {
			throw new Exception("{$e->getMessage()}: {$this->shellQuery()}", $e->getCode());
		}

		// Add cursor options
		foreach ($this->options as $key => $value) {
			if(is_null($value))
				$this->cursor->$key();
			else
				$this->cursor->$key($value);
		}

		return $this;
	}

	/**
	 * Set some criteria for the query
	 *
	 * Unlike [\MongoCollection::find], this can be called multiple times and the
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
	 * @return  \Gleez\Mango\Collection
	 *
	 * @throws  \Gleez\Mango\Exception
	 *
	 * @uses    \JSON::decode
	 */
	public function find($query = array(), $value = null)
	{
		if ($this->cursor)
			throw new Exception('The cursor has already been instantiated');

		if (!is_array($query)) {
			if ($query[0] == "{")
				$query = JSON::decode($query, true);
			else
				$query = array($query => $value);
		}

		// Translate field aliases
		$queryFields = array();

		foreach ($query as $field => $value) {
			// Special purpose condition
			if ($field[0] == '$') {
				// $or and $where and possibly other special values
				if ($field == '$or' && ! is_int(key($value))) {
					if ( ! isset($this->query['$or']))
						$this->query['$or'] = array();
					$this->query['$or'][] = $value;
				} else if ($field == '$where')
					$this->query['$where'] = $value;
				else
					$queryFields[$field] = $value;
			} else // Simple key = value condition
				$queryFields[$this->getFieldName($field)] = $value;
		}

		$this->query = static::mergeDistinctArrays($this->query, $queryFields);

		return $this;
	}

	/**
	 * Queries this collection, returning a single element
	 *
	 * Wrapper for [\MongoCollection::findOne] which adds field name translations
	 * and allows query to be a single `_id`.
	 *
	 * Return record matching query or null
	 *
	 * @param   mixed  $query   An _id, a JSON encoded query or an array by which to search [Optional]
	 * @param   array  $fields  Fields of the results to return [Optional]
	 *
	 * @return  mixed
	 *
	 * @uses    \JSON::decode
	 */
	public function findOne($query = array(), $fields = array())
	{
		// String query is either JSON encoded or an _id
		if (!is_array($query)) {
			if ($query[0] == "{")
				$query = JSON::decode($query, true);
			else
				$query = array('_id' => $query);
		}

		// Translate field aliases
		$query_trans = array();
		foreach ($query as $field => $value)
			$query_trans[$this->getFieldName($field)] = $value;

		$fields_trans = array();
		if ($fields && is_int(key($fields)))
			$fields = array_fill_keys($fields, 1);

		foreach ($fields as $field => $value)
			$fields_trans[$this->getFieldName($field)] = $value;

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
	 * @uses    \Gleez\Mango\Client::findAndModify
	 */
	public function findAndModify(array $command)
	{
		return $this->getClientInstance()->findAndModify($this->name, $command);
	}

	/**
	 * Perform an update, throw exception on errors
	 *
	 * Same usage as [\MongoCollection::update] except it throws an exception on error.
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
	 * @return  integer|boolean|\MongoId
	 *
	 * @throws  \Gleez\Mango\Exception
	 */
	public function safeUpdate(array $criteria, array $new_object, $options = array())
	{
		$options = array_merge(
			array(
				'w'        => 1,      // The write will be acknowledged by the server
				'upsert'   => false,  // If no document matches $criteria, a new document will be inserted
				'multiple' => false   // All documents matching $criteria will be updated?
			),
			$options
		);

		$result = $this->update($criteria, $new_object, $options);

		// A write will not be followed up with a getLastError call,
		// and therefore not checked ("fire and forget")
		if ($options['w'] == 0)
			// boolean
			return $result;

		// According to the driver docs an exception should have already been
		// thrown if there was an error, but just in case
		if ( ! $result['ok'])
			throw new Exception($result['err']);

		// Return the number of documents updated for multiple updates or
		// the updatedExisting flag for single updates
		if ($options['multiple'])
			// integer
			return $result['n'];
		// Return the upserted id if a document was upserted with a new _id
		else if ($options['upsert'] && ! $result['updatedExisting'] && isset($result['upserted']))
			// MongoId
			return $result['upserted'];
		// Return the updatedExisting flag for single, non-upsert updates
		else
			// boolean
			return $result['updatedExisting'];
	}

	/**
	 * Remove, throw exception on errors
	 *
	 * Same usage as [\MongoCollection::remove] except it throws an exception on error.
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
	 * @throws  \Gleez\Mango\Exception
	 */
	public function safeRemove(array $criteria = array(), array $options = array())
	{
		$options = array_merge(
			array(
				'w'       => 1,     // The write will be acknowledged by the server
				'justOne' => false, // To limit the deletion to just one document, set to true
			),
			$options
		);

		$result = $this->remove($criteria, $options);

		// A write will not be followed up with a getLastError call,
		// and therefore not checked ("fire and forget")
		if ($options['w'] == 0)
			/** @var $result boolean */
			return $result;

		// According to the driver docs an exception should have already been
		// thrown if there was an error, but just in case
		if (!$result['ok'])
			throw new Exception($result['err']);

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
	 * @throws  \Gleez\Mango\Exception
	 */
	public function safeDrop()
	{
		$result = $this->drop();

		if (!$result['ok'])
			throw new Exception($result['errmsg']);

		return true;
	}

	/**
	 * Retrieve a list of distinct values for the given key across a collection
	 *
	 * Same usage as [\MongoCollection::distinct] except it throws an exception on error.
	 *
	 * @since   0.4.0
	 *
	 * @link    http://www.php.net/manual/en/mongocollection.distinct.php \MongoCollection::distinct
	 *
	 * @param   string  $key    The key to use
	 * @param   array   $query  An optional query parameters [Optional]
	 *
	 * @return  array
	 *
	 * @throws  \Gleez\Mango\Exception
	 *
	 * @uses    \Gleez\Mango\Client::safeCommand
	 */
	public function distinct($key, array $query = array())
	{
		return $this->getClientInstance()->safeCommand(array(
			'distinct' => $this->name,
			'key'      => (string)$key,
			'query'    => $query
		));
	}

	/**
	 * Perform an aggregation using the aggregation framework
	 *
	 * Same usage as [\MongoCollection::aggregate] except it throws an exception on error.
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
	 * @link    http://www.php.net/manual/en/mongocollection.aggregate.php \MongoCollection::aggregate
	 *
	 * @param   array  $pipeline  An array of pipeline operators
	 *
	 * @return  array
	 *
	 * @throws  \Gleez\Mango\Exception
	 *
	 * @uses    \Gleez\Mango\Client::safeCommand
	 */
	public function aggregate(array $pipeline)
	{
		return $this->getClientInstance()->safeCommand(array(
			'aggregate' => $this->name,
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
	 * @return  \Gleez\Mango\Collection
	 */
	public function reset($cursor_only = false)
	{
		if (!$cursor_only)
			$this->query = $this->fields = $this->options = array();

		$this->cursor = null;

		return $this;
	}

	/**
	 * Returns the current query results as an array
	 *
	 * @since   0.4.0
	 *
	 * @param   boolean  $objects  Pass false to get raw data
	 *
	 * @return  array
	 */
	public function toArray($objects = true)
	{
		$array = array();

		// Iterate using wrapper
		if ($objects)
			foreach ($this as $key => $value)
				$array[$key] = $value;
		// Iterate bypassing wrapper
		else {
			$this->rewind();

			foreach ($this->cursor as $key => $value)
				$array[$key] = $value;
		}

		return $array;
	}

	/**
	 * Get the corresponding \MongoCollection instance
	 *
	 * @return  \MongoCollection
	 *
	 * @uses    \Gleez\Mango\Client::db
	 */
	public function getCollection()
	{
		$name = "{$this->db}.{$this->name}.{$this->gridFS}";

		if (!isset(static::$collections[$name])) {
			$method = ($this->gridFS ? 'getGridFS' : 'selectCollection');
			static::$collections[$name] = $this->getClientInstance()->getDb()->$method($this->name);
		}

		return static::$collections[$name];
	}

	/**
	 * Get an instance of the corresponding document model
	 *
	 * @since   0.6.0
	 *
	 * @return  \Gleez\Mango\Document
	 */
	public function getModel()
	{
		if (!isset(static::$models[$this->model])) {
			$model = $this->model;
			static::$models[$this->model] = new $model;
		}

		return static::$models[$this->model];
	}

	/**
	 * Get the Mango instance used for this collection
	 *
	 * @since   0.3.0  Initial Mango_Collection::getDB method
	 * @since   0.7.0  Renamed to Mango_Collection::getMangoInstance
	 * @since   1.0.0  Renamed to \Gleez\Mango\Collection::getClientInstance
	 *
	 * @return  \Gleez\Mango\Client
	 *
	 * @uses    \Gleez\Mango\Client::instance
	 */
	public function getClientInstance()
	{
		return Client::instance($this->db);
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
			return $this->query;

		if ($name == 'fields')
			return $this->fields;

		return isset($this->options[$name]) ? $this->options[$name] : null;
	}

	/**
	 * Access the \MongoCursor instance directly, triggers a load if there is none
	 *
	 * @since   0.3.0
	 *
	 * @return  \MongoCursor
	 */
	public function getCursor()
	{
		$this->cursor || $this->load();

		return $this->cursor;
	}

	/**
	 * Translate a field name according to aliases defined in the model if they exist
	 *
	 * @since   0.4.0  getFieldName($name)  Introduced
	 * @since   0.6.0  getFieldName($name)  Used $this->model
	 *
	 * @param   string  $name  Field name
	 *
	 * @return  string
	 *
	 * @uses    \Gleez\Mango\Document::getFieldName
	 */
	public function getFieldName($name)
	{
		return $this->model ? $this->getModel()->getFieldName($name) : $name;
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
		return $this->getClientInstance()->safeCommand(array(
			'collStats' => $this->name,
			'scale'     => $scale
		));
	}

	/**
	 * Implement [MongoCursor::getNext] so that the return value
	 * is a [Mango_Document] instead of array
	 *
	 * @since   0.6.0
	 *
	 * @return array|\Gleez\Mango\Document
	 *
	 * @uses   \Profiler::start
	 * @uses   \Profiler::stop
	 */
	public function getNext()
	{
		if($this->getClientInstance()->profiling && ( ! $this->cursor || ! $this->is_iterating())) {
			$this->getCursor();

			// Start the benchmark
			$this->benchmark = Profiler::start(get_class($this->getClientInstance())."::{$this->db}", $this->shellQuery());

			$this->getCursor()->next();

			// Stop the benchmark
			Profiler::stop($this->benchmark);

			// Clean benchmark token
			$this->benchmark = null;
		} else
			$this->getCursor()->next();

		return $this->current();
	}

	/**
	 * Implement [\MongoCursor::hasNext] to ensure that the cursor is loaded
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
	 * @param   string  $name   Option Name
	 * @param   mixed   $value  Option value
	 *
	 * @return  \Gleez\Mango\Collection
	 *
	 * @throws  \Gleez\Mango\Exception
	 */
	public function setOption($name, $value)
	{
		if ($name != 'batchSize' && $name != 'timeout' && $this->is_iterating())
			throw new Exception('The cursor has already started iterating');

		if ($name == 'query')
			$this->query = $value;
		else if ($name == 'fields')
			$this->fields = $value;
		else {
			if ($this->cursor) {
				if (is_null($value))
					$this->cursor->$name();
				else
					$this->cursor->$name($value);
			}

			$this->options[$name] = $value;
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
	 * @return  \Gleez\Mango\Collection
	 *
	 * @throws  \Gleez\Mango\Exception
	 */
	public function unsetOption($name)
	{
		if ($this->is_iterating())
			throw new Exception('The cursor has already started iterating');

		unset($this->options[$name]);

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
			return array_key_exists($name, $this->options);

		return false;
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
	 * @return  \Gleez\Mango\Collection
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
	 * @return  \Gleez\Mango\Collection
	 */
	public function immortal($liveForever = true)
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
	 * @return  \Gleez\Mango\Collection
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
	 * @return  \Gleez\Mango\Collection
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
	 * @return  \Gleez\Mango\Collection
	 */
	public function slaveOkay($okay = true)
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
	 * @return  \Gleez\Mango\Collection
	 */
	public function snapshot()
	{
		return$this->setOption('snapshot', null);
	}

	/**
	 * Create Tailable Cursor
	 *
	 * Sets whether this cursor will be left open after fetching the last results.
	 *
	 * By default, \MongoDB will automatically close a cursor when the client has
	 * exhausted all results in the cursor. However, for capped collections you
	 * may use a Tailable Cursor that remains open after the client exhausts the
	 * results in the initial cursor.
	 *
	 * @since   0.4.5
	 *
	 * @param   boolean  $tail  If true will be sets tailable option
	 *
	 * @return  \Gleez\Mango\Collection
	 */
	public function tailable($tail = true)
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
	 * @return  \Gleez\Mango\Collection
	 *
	 * @throws  \Gleez\Mango\Exception
	 */
	public function sort($fields, $dir = self::ASC)
	{
		if ($this->cursor)
			throw new Exception('The cursor has already started iterating');

		if (!isset($this->options['sort']))
			// Clear current sort option
			$this->options['sort'] = array();

		if (!is_array($fields))
			$fields = array($fields => $dir);

		// Translate field aliases
		foreach ($fields as $field => $dir) {
			if (is_string($dir)) {
				if (strtolower($dir) == 'asc' || $dir == '1')
					$dir = static::ASC;
				else
					$dir = static::DESC;
			}

			$this->options['sort'][$this->getFieldName($field)] = $dir;
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
	 * @return  \Gleez\Mango\Collection
	 */
	public function sortAsc($field)
	{
		return $this->sort($field, static::ASC);
	}

	/**
	 * Sorts the results descending by the given field
	 *
	 * @since   0.3.0
	 *
	 * @param   string  $field  The field name to sort by
	 *
	 * @return  \Gleez\Mango\Collection
	 */
	public function sortDesc($field)
	{
		return $this->sort($field, static::DESC);
	}

	/**
	 * Checks if the cursor is reading a valid result
	 *
	 * @since   0.3.0
	 *
	 * See [\Iterator::valid]
	 *
	 * @link    http://www.php.net/manual/en/mongocursor.valid.php \MongoCursor::valid
	 *
	 * @return  boolean
	 */
	public function valid()
	{
		return $this->cursor->valid();
	}

	/**
	 * Returns the cursor to the beginning of the result set
	 *
	 * @since   0.3.0
	 *
	 * See [\Iterator::rewind]
	 *
	 * @link    http://www.php.net/manual/en/mongocursor.rewind.php \MongoCursor::rewind
	 *
	 * @throws  \Gleez\Mango\Exception
	 *
	 * @uses    \Profiler::start
	 * @uses    \Profiler::stop
	 * @uses    \Gleez\Mango\Client::$profiling
	 */
	public function rewind()
	{
		try {
			if ($this->getClientInstance()->profiling)
				$this->benchmark = Profiler::start(get_class($this->getClientInstance())."::{$this->db}", $this->shellQuery());

			$this->getCursor()->rewind();

			if ($this->benchmark) {
				// Stop the benchmark
				Profiler::stop($this->benchmark);

				// Clean benchmark token
				$this->benchmark = null;
			}
		} catch(\Exception $e) {
			throw new Exception("{$e->getMessage()}: {$this->shellQuery()}", $e->getCode());
		}
	}

	/**
	 * Advances the cursor to the next result
	 *
	 * @since   0.3.0
	 *
	 * See [\Iterator::next]
	 *
	 * @link    http://www.php.net/manual/en/mongocursor.next.php \MongoCursor::next
	 */
	public function next()
	{
		$this->cursor->next();
	}

	/**
	 * Returns the current result's _id
	 *
	 * @since   0.3.0
	 *
	 * See [\Iterator::key]
	 *
	 * @link    http://www.php.net/manual/en/mongocursor.key.php \MongoCursor::key
	 * @return  string
	 */
	public function key()
	{
		return $this->cursor->key();
	}

	/**
	 * Returns the current element
	 *
	 * @since   0.3.0
	 *
	 * See [\Iterator::current]
	 *
	 * @link    http://www.php.net/manual/en/mongocursor.current.php \MongoCursor::current
	 *
	 * @return  array
	 */
	public function current()
	{
		return $this->cursor->current();
	}

	/**
	 * Count the results from the query
	 *
	 * + Count the results from the current query: pass false for "all" results (disregard limit/skip)
	 * + Count results of a separate query: pass an array or JSON string of query parameters
	 *
	 * @since   0.3.0
	 *
	 * See [\Countable::count]
	 *
	 * @link    http://www.php.net/manual/en/mongocursor.count.php \MongoCursor::count
	 *
	 * @param   boolean|array|string  $query
	 *
	 * @return  integer
	 *
	 * @throws  \Exception
	 *
	 * @uses    \JSON::encodeMongo
	 * @uses    \JSON::decode
	 * @uses    \Profiler::start
	 * @uses    \Profiler::stop
	 */
	public function count($query = true)
	{
		if (is_bool($query)) {
			// Profile count operation for cursor
			if ($this->getClientInstance()->profiling)
				$this->benchmark = Profiler::start(__CLASS__."::{$this->db}", $this->shellQuery() . ".count(" . JSON::encodeMongo($query) .")");

			$this->cursor || $this->load();
			$count = $this->cursor->count($query);
		} else {
			if (is_string($query) && $query[0] == "{")
				$query = JSON::decode($query, true);

			$query_trans = array();

			foreach ($query as $field => $value)
				$query_trans[$this->getFieldName($field)] = $value;

			$query = $query_trans;

			// Profile count operation for collection
			if ($this->getClientInstance()->profiling)
				$this->benchmark = Profiler::start(__CLASS__."::{$this->db}", "db.{$this->name}.count(" . ($query ? JSON::encodeMongo($query) : '') .")");

			$count = $this->getCollection()->count($query);
		}

		// End profiling count
		if ($this->benchmark) {
			// Stop the benchmark
			Profiler::stop($this->benchmark);

			// Clean benchmark token
			$this->benchmark = null;
		}

		return $count;
	}
}
