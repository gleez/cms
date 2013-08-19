<?php
/**
 * [Cache](api/Cache) Mango driver
 *
 * ### System requirements
 *
 * - MondoDB 2.4 or higher
 * - PHP-extension MongoDB 1.4.0 or higher
 *
 * @package    Gleez\Cache\Base
 * @author     Gleez Team
 * @version    1.2.0
 * @copyright  (c) 2012-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Cache_Mango extends Cache implements Cache_Tagging {

	/**
	 * Mango_Collection instance
	 * @var Mango_Collection
	 */
	private $collection;

	/**
	 * Constructs the Mango cache driver
	 *
	 * The Mango cache driver must be instantiated using the [Cache::instance] method.
	 *
	 * [!!] Note: This method cannot be invoked externally.
	 *
	 * @param   array  $config  Config for Mango cache driver
	 *
	 * @uses    Arr::merge
	 * @uses    Mango::$default
	 * @uses    Mango::instance
	 * @uses    Mango::__get
	 * @uses    Cache::config
	 * @uses    Cache::DEFAULT_EXPIRE
	 */
	public function __construct(array $config)
	{
		// Set default config
		$default = array(
			'driver'         => 'mango',
			'group'          => Mango::$default,
			'collection'     => 'cache',
			'default_expire' => Cache::DEFAULT_EXPIRE,
		);

		// Merge config
		$this->config(Arr::merge($default, $config));

		// Create default prefix
		$this->_config['prefix'] = isset($config['prefix']) ? $config['prefix'].self::SEPARATOR : NULL;

		// Get collection name
		$collection  = $this->config('collection');

		// Get Mango_Collection instance
		$this->collection = Mango::instance($this->config('group'))->{$collection};
	}

	/**
	 * Retrieve a cached value entry by id
	 *
	 * Examples:
	 * ~~~
	 * // Retrieve cache entry from file group
	 * $data = Cache::instance('mango')->get('foo');
	 *
	 * // Retrieve cache entry from file group and return 'bar' if miss
	 * $data = Cache::instance('mango')->get('foo', 'bar');
	 * ~~~
	 *
	 * @param   string  $id       ID of cache entry
	 * @param   mixed   $default  Default value to return if cache miss [Optional]
	 *
	 * @return  mixed
	 *
	 * @uses    Mango_Collection::findOne
	 */
	public function get($id, $default = NULL)
	{
		$id = System::sanitize_id($this->config('prefix').$id);

		// Load the cache based on id
		$result = $this->collection->findOne(array('id' => $id));

		if (is_null($result))
		{
			return $default;
		}

		// If the cache has expired
		if ($result['lifetime'] != 0 AND $result['lifetime'] <= time())
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
			$data = unserialize($result['data']);

			// Turn notices back on
			error_reporting($ER);

			// Return the resulting data
			return $data;
		}

	}

	/**
	 * Set a value to cache with id and lifetime
	 *
	 * Examples:
	 * ~~~
	 * $data = 'bar';
	 *
	 * // Set 'bar' to 'foo' in mango group, using default expiry
	 * Cache::instance('mango')->set('foo', $data);
	 *
	 * // Set 'bar' to 'foo' in mango group for 30 seconds
	 * Cache::instance('mango')->set('foo', $data, 30);
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
	 * ~~~
	 * // Delete 'foo' entry from the mango group
	 * Cache::instance('mango')->delete('foo');
	 * ~~~
	 *
	 * @param   string  $id  ID to remove from cache
	 *
	 * @return  boolean
	 *
	 * @throws  MongoException
	 *
	 * @uses    Mango_Collection::findOne
	 * @uses    Mango_Collection::safeRemove
	 */
	public function delete($id)
	{
		$id = System::sanitize_id($this->config('prefix').$id);
		$data = $this->collection->findOne(array('id' => $id));

		if ( ! is_null($data))
		{
			$status = $this->collection->safeRemove(
				array('id'      => $id),  // Event ID
				array('justOne' => TRUE)  // Remove at most one record
			);

			return (is_bool($status) ? $status : (is_array($status) AND $status['ok'] == 1));
		}

		return FALSE;
	}

	/**
	 * Delete a cache entry based on regex pattern
	 *
	 * Example:
	 * ~~~
	 * // Delete 'foo:**' entries from the mango group
	 * Cache::instance('mango')->delete_pattern('foo:**:bar');
	 * ~~~
	 *
	 * @param   string  $pattern  The cache key pattern
	 * @return  boolean
	 */
	public function delete_pattern($pattern)
	{
		// Not implemented yet
	}

	/**
	 * Delete all cache entries
	 *
	 * @param   integer  $mode  The clean mode [Optional]
	 *
	 * @return  boolean
	 *
	 * @throws  Cache_Exception
	 */
	public function delete_all($mode = Cache::ALL)
	{
		if ($mode == Cache::ALL)
		{
			try
			{
				$response = $this->collection->safeRemove();

				return $response;
			}
			catch (MongoException $e)
			{
				throw new Cache_Exception('An error occurred when dropping the cache: :msg',
					array(':msg' => $e->getMessage())
				);
			}
		}
		elseif ($mode == Cache::OLD)
		{
			try
			{
				$response = $this->collection->safeRemove(array(
					'lifetime' => array('$lte' => time())
				));

				return (bool)$response;
			}
			catch (Exception $e)
			{
				throw new Cache_Exception('An error occurred when dropping the cache: :msg',
					array(':msg' => $e->getMessage())
				);
			}
		}
		else
		{
			return FALSE;
		}

	}

	/**
	 * Tests whether an id exists or not
	 *
	 * @param   string  $id ID
	 *
	 * @return  boolean
	 *
	 * @uses    Mango_Collection::findOne
	 */
	public function exists($id)
	{
		return (bool)$this->collection->findOne(array('id' => $id));
	}

	/**
	 * Set a value based on an id. Optionally add tags
	 *
	 * @param   string   $id        ID of cache entry
	 * @param   mixed    $data      Data to set to cache
	 * @param   integer  $lifetime  Lifetime in seconds [Optional]
	 * @param   array    $tags      Tags [Optional]
	 *
	 * @return  array    Returns an array containing the status of the insertion if the "w" option is set
	 * @return  boolean  Otherwise, returns TRUE if the inserted array is not empty
	 *
	 * @throws  Cache_Exception
	 *
	 * @uses    Mango_Collection::safeUpdate
	 */
	public function set_with_tags($id, $data, $lifetime = NULL, array $tags = NULL)
	{
		// Prepare ID
		$id = System::sanitize_id($this->config('prefix').$id);

		// Prepare data
		$data = serialize($data);

		// Setup lifetime
		if ($lifetime === NULL)
		{
			$lifetime = (0 === Arr::get($this->_config, 'default_expire', NULL)) ? 0 : (Arr::get($this->_config, 'default_expire', Cache::DEFAULT_EXPIRE) + time());
		}
		else
		{
			$lifetime = (0 === $lifetime) ? 0 : ($lifetime + time());
		}

		$data = array(
			'id'       => $id,
			'data'     => $data,
			'lifetime' => $lifetime,
			'tags'     => $tags
		);

		try
		{
			// try to update/create document, throw exception on errors
			$status = $this->collection->safeUpdate(
				array('id'=> $id),      // Condition
				$data,                  // Data
				array('upsert' => TRUE) // If no document matches $criteria, a new document will be inserted
			);
		}
		catch(MongoException $e)
		{
			throw new Cache_Exception('An error occurred saving cache data: :err', array(':err' => $e->getMessage()));
		}

		return (bool)($status);
	}

	/**
	 * Delete cache entries based on a tag
	 *
	 * @param   string  $tag  tag
	 */
	public function delete_tag($tag)
	{
		// Not implemented yet
	}

	/**
	 * Find cache entries based on a tag
	 *
	 * @since   1.2.0
	 *
	 * @param   string  $tag  tag
	 *
	 * @return  array
	 *
	 * @throws  Cache_Exception
	 */
	public function find($tag)
	{
		try
		{
			$result = $this->collection->find(array('tags' => array('$regex' => $tag)))->toArray();

			if (empty($result))
			{
				return array();
			}
		}
		catch(MongoCursorException $e)
		{
			throw new Cache_Exception('There was a problem querying the MongoDB cache. :error', array(':error' => $e->getMessage()));
		}

		$retval = array();

		foreach ($result as $item)
		{
			// Disable notices for unserializing
			$ER = error_reporting(~E_NOTICE);

			$retval[$item['id']] = unserialize($item['data']);

			// Turn notices back on
			error_reporting($ER);
		}

		return $retval;
	}
}