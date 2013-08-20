<?php
/**
 * [Cache](api/Cache) Sqlite driver
 *
 * ### System requirements
 *
 * * SQLite3
 * * PDO
 *
 * @package    Gleez\Cache\Base
 * @author     Kohana Team
 * @author     Sandeep Sangamreddi - Gleez
 * @version    1.0.1
 * @copyright  (c) 2009-2012 Kohana Team
 * @copyright  (c) 2012-2013 Gleez Technologies
 * @license    http://kohanaphp.com/license
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Cache_Sqlite extends Cache implements Cache_Tagging {

	/**
	 * Database resource
	 * @var PDO
	 */
	protected $_db;

	/**
	 * Sets up the PDO SQLite table and initialises the PDO connection
	 *
	 * @param  array  $config  configuration
	 *
	 * @throws  Cache_Exception
	 *
	 * @uses  Arr::get
	 */
	protected function __construct(array $config)
	{
		parent::__construct($config);

		$database = Arr::get($this->_config, 'database', NULL);

		if ($database === NULL)
		{
			throw new Cache_Exception('Database path not available in Gleez Cache configuration');
		}

		// Load new Sqlite DB
		$this->_db = new PDO('sqlite:'.$database);

		// Test for existing DB
		$result = $this->_db->query("SELECT * FROM sqlite_master WHERE name = 'caches' AND type = 'table'")->fetchAll();

		// If there is no table, create a new one
		if (0 == count($result))
		{
			$database_schema = Arr::get($this->_config, 'schema', NULL);

			if ($database_schema === NULL)
			{
				throw new Cache_Exception('Database schema not found in Gleez Cache configuration');
			}

			try
			{
				// Create the caches table
				$this->_db->query(Arr::get($this->_config, 'schema', NULL));
				$this->_db->query(Arr::get($this->_config, 'index', NULL));
			}
			catch (PDOException $e)
			{
				throw new Cache_Exception('Failed to create new SQLite caches table with the following error : :error', array(':error' => $e->getMessage()));
			}
		}

		// Registers a User Defined Function for use in SQL statements
		$this->_db->sqliteCreateFunction('regexp', array($this, 'removePatternRegexpCallback'), 2);
	}

	/**
	 * Retrieve a value based on an id
	 *
	 * Examples:
	 * ~~~
	 * // Retrieve cache entry from sqlite group
	 * $data = Cache::instance('sqlite')->get('foo');
	 *
	 * // Retrieve cache entry from sqlite group and return 'bar' if miss
	 * $data = Cache::instance('sqlite')->get('foo', 'bar');
	 * ~~~
	 *
	 * @param   string  $id       ID of cache entry
	 * @param   string  $default  Default value to return if cache miss [Optional]
	 *
	 * @return  mixed
	 *
	 * @throws  Cache_Exception
	 *
	 * @uses    System::sanitize_id
	 */
	public function get($id, $default = NULL)
	{
		// Prepare statement
		$statement = $this->_db->prepare('SELECT id, expiration, cache FROM caches WHERE id = :id LIMIT 0, 1');

		// Try and load the cache based on id
		try
		{
			$statement->execute(array(':id' => System::sanitize_id($id)));
		}
		catch (PDOException $e)
		{
			throw new Cache_Exception('There was a problem querying the local SQLite3 cache. :error', array(':error' => $e->getMessage()));
		}

		if ( ! $result = $statement->fetch(PDO::FETCH_OBJ))
		{
			return $default;
		}

		// If the cache has expired
		if ($result->expiration != 0 and $result->expiration <= time())
		{
			// Delete it and return default value
			$this->delete($id);
			return $default;
		}
		// Otherwise return cached object
		else
		{
			// Disable notices for unserializing
			$ER = error_reporting(~E_NOTICE);

			// Return the valid cache data
			$data = unserialize($result->cache);

			// Turn notices back on
			error_reporting($ER);

			// Return the resulting data
			return $data;
		}
	}

	/**
	 * Set a value based on an id
	 *
	 * Optionally add tags.
	 *
	 * Examples:
	 * ~~~
	 * $data = 'bar';
	 *
	 * // Set 'bar' to 'foo' in sqlite group, using default expiry
	 * Cache::instance('sqlite')->set('foo', $data);
	 *
	 * // Set 'bar' to 'foo' in sqlite group for 30 seconds
	 * Cache::instance('sqlite')->set('foo', $data, 30);
	 * ~~~
	 *
	 * @param   string   $id        ID of cache entry
	 * @param   mixed    $data      The data to cache
	 * @param   integer  $lifetime  Lifetime [Optional]
	 *
	 * @return  boolean
	 */
	public function set($id, $data, $lifetime = NULL)
	{
		return (bool) $this->set_with_tags($id, $data, $lifetime);
	}

	/**
	 * Delete a cache entry based on id
	 *
	 * Example:
	 * ~~~
	 * // Delete 'foo' entry from the sqlite group
	 * Cache::instance('sqlite')->delete('foo');
	 * ~~~
	 *
	 * @param   string  $id  ID of cache entry
	 *
	 * @return  boolean
	 *
	 * @throws  Cache_Exception
	 *
	 * @uses    System::sanitize_id
	 */
	public function delete($id)
	{
		// Prepare statement
		$statement = $this->_db->prepare('DELETE FROM caches WHERE id = :id');

		// Remove the entry
		try
		{
			$statement->execute(array(':id' => System::sanitize_id($id)));
		}
		catch (PDOException $e)
		{
			throw new Cache_Exception('There was a problem querying the local SQLite3 cache. :error', array(':error' => $e->getMessage()));
		}

		return (bool) $statement->rowCount();
	}

	/**
	 * Delete a cache entry based on regex pattern
	 *
	 * Example:
	 * ~~~
	 * // Delete 'foo:**' entries from the sqlite cache
	 * Cache::instance('sqlite')->delete_pattern('foo:**:bar');
	 * ~~~
	 *
	 * @param   string  $pattern The cache key pattern
	 *
	 * @return  boolean
	 *
	 * @throws  Cache_Exception
	 *
	 * @uses    System::sanitize_id
	 */
	public function delete_pattern($pattern)
	{
		$regexp = $this->_regexp_pattern($this->config('prefix').$pattern);

		// Prepare statement
		$statement = $this->_db->prepare('DELETE FROM caches WHERE REGEXP(:id, id)');

		// Remove the entry
		try
		{
			$statement->execute(array(':id' => System::sanitize_id($regexp)));
		}
		catch (PDOException $e)
		{
			throw new Cache_Exception('There was a problem querying the local SQLite3 cache. :error', array(':error' => $e->getMessage()));
		}

		return (bool) $statement->rowCount();
	}

	/**
	 * Delete all cache entries
	 *
	 * @param   integer  $mode  The clean mode [Optional]
	 *
	 * @return  boolean
	 *
	 * @throws  Cache_Exception
	 *
	 * @todo    Handle $mode variable
	 */
	public function delete_all($mode = Cache::ALL)
	{
		// Prepare statement
		$statement = $this->_db->prepare('DELETE FROM caches');

		// Remove the entry
		try
		{
			$statement->execute();
		}
		catch (PDOException $e)
		{
			throw new Cache_Exception('There was a problem querying the local SQLite3 cache. :error', array(':error' => $e->getMessage()));
		}

		return (bool) $statement->rowCount();
	}

	/**
	 * Set a value based on an id
	 *
	 * Optionally add tags.
	 *
	 * @param   string   $id        ID of cache entry
	 * @param   mixed    $data      Data to set to cache
	 * @param   integer  $lifetime  Lifetime in seconds [Optional]
	 * @param   array    $tags      Tags [Optional]
	 *
	 * @return  boolean
	 *
	 * @throws  Cache_Exception
	 *
	 * @uses    System::sanitize_id
	 */
	public function set_with_tags($id, $data, $lifetime = NULL, array $tags = NULL)
	{
		// Serialize the data
		$data = serialize($data);

		// Normalise tags
		$tags = (NULL === $tags) ? NULL : ('<'.implode('>,<', $tags).'>');

		// Setup lifetime
		if ($lifetime === NULL)
		{
			$lifetime = (0 === Arr::get($this->_config, 'default_expire', NULL)) ? 0 : (Arr::get($this->_config, 'default_expire', Cache::DEFAULT_EXPIRE) + time());
		}
		else
		{
			$lifetime = (0 === $lifetime) ? 0 : ($lifetime + time());
		}

		// Prepare statement
		// $this->exists() may throw Cache_Exception, no need to catch/rethrow
		$statement = $this->exists($id) ? $this->_db->prepare('UPDATE caches SET expiration = :expiration, cache = :cache, tags = :tags WHERE id = :id') : $this->_db->prepare('INSERT INTO caches (id, cache, expiration, tags) VALUES (:id, :cache, :expiration, :tags)');

		// Try to insert
		try
		{
			$statement->execute(array(':id' => System::sanitize_id($this->config('prefix').$id), ':cache' => $data, ':expiration' => $lifetime, ':tags' => $tags));
		}
		catch (PDOException $e)
		{
			throw new Cache_Exception('There was a problem querying the local SQLite3 cache. :error', array(':error' => $e->getMessage()));
		}

		return (bool) $statement->rowCount();
	}

	/**
	 * Delete cache entries based on a tag
	 *
	 * @param   string  $tag  Tag of cache entry
	 *
	 * @return  boolean
	 *
	 * @throws  Cache_Exception
	 */
	public function delete_tag($tag)
	{
		// Prepare the statement
		$statement = $this->_db->prepare('DELETE FROM caches WHERE tags LIKE :tag');

		// Try to delete
		try
		{
			$statement->execute(array(':tag' => "%<{$tag}>%"));
		}
		catch (PDOException $e)
		{
			throw new Cache_Exception('There was a problem querying the local SQLite3 cache. :error', array(':error' => $e->getMessage()));
		}

		return (bool) $statement->rowCount();
	}

	/**
	 * Find cache entries based on a tag
	 *
	 * @param   string  $tag  Tag of cache entry
	 *
	 * @return  array
	 *
	 * @throws  Cache_Exception
	 */
	public function find($tag)
	{
		// Prepare the statement
		$statement = $this->_db->prepare('SELECT id, cache FROM caches WHERE tags LIKE :tag');

		// Try to find
		try
		{
			if ( ! $statement->execute(array(':tag' => "%<{$tag}>%")))
			{
				return array();
			}
		}
		catch (PDOException $e)
		{
			throw new Cache_Exception('There was a problem querying the local SQLite3 cache. :error', array(':error' => $e->getMessage()));
		}

		$result = array();

		while ($row = $statement->fetchObject())
		{
			// Disable notices for unserializing
			$ER = error_reporting(~E_NOTICE);

			$result[$row->id] = unserialize($row->cache);

			// Turn notices back on
			error_reporting($ER);
		}

		return $result;
	}

	/**
	 * Garbage collection method that cleans any expired
	 * cache entries from the cache.
	 *
	 * @throws  Cache_Exception
	 */
	public function garbage_collect()
	{
		// Create the sequel statement
		$statement = $this->_db->prepare('DELETE FROM caches WHERE expiration < :expiration');

		try
		{
			$statement->execute(array(':expiration' => time()));
		}
		catch (PDOException $e)
		{
			throw new Cache_Exception('There was a problem querying the local SQLite3 cache. :error', array(':error' => $e->getMessage()));
		}
	}

	/**
	 * Tests whether an id exists or not
	 *
	 * @param   string  $id  ID of cache entry
	 *
	 * @return  boolean
	 *
	 * @throws  Cache_Exception
	 *
	 * @uses    System::sanitize_id
	 */
	protected function exists($id)
	{
		$statement = $this->_db->prepare('SELECT id FROM caches WHERE id = :id');
		try
		{
			$statement->execute(array(':id' => System::sanitize_id($this->config('prefix').$id)));
		}
		catch (PDOException $e)
		{
			throw new Cache_Exception('There was a problem querying the local SQLite3 cache. :error', array(':error' => $e->getMessage()));
		}

		return (bool) $statement->fetchAll();
	}


	/**
	 * Callback used when deleting keys from cache.
	 */
	public function removePatternRegexpCallback($regexp, $key)
	{
		return preg_match($regexp, $key);
	}
}
