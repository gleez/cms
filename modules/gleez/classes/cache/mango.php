<?php defined('SYSPATH') OR die('No direct script access allowed.');
/**
 * [Cache](api/Cache) Mango driver
 *
 * ### System requirements
 *
 * - PHP 5.3 or higher
 * - Gleez CMS 0.9.26 or higher
 * - MondoDB 2.4 or higher
 * - PHP-extension MongoDB 1.4.0 or higher
 *
 * @package    Gleez\Cache\Base
 * @author     Sergey Yakovlev - Gleez
 * @version    1.0.0
 * @copyright  (c) 2012-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Cache_Mango extends Cache implements Cache_Tagging {

	/**
	 * Mango_Collection instance
	 * @var Mango_Collection
	 */
	protected $_collection;

	/**
	 * The default configuration for the MongoDB
	 * @var array
	 */
	protected $_default_config = array();

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
		$this->_default_config = array(
			'driver'         => 'mango',
			'default_expire' => Cache::DEFAULT_EXPIRE,
			'mango_config'   => Mango::$default,
			'collection'     => 'Cache',
		);

		// Merge config
		$this->config(Arr::merge($this->_default_config, $config));

		// Create default prefix
		$this->_config['prefix'] = isset($config['prefix']) ? $config['prefix'].self::SEPARATOR : NULL;

		// Get collection name
		$collection  = $this->config('collection');

		// Get Mango_Collection instance
		$this->_collection = Mango::instance($this->config('mango_config'))->{$collection};
	}

	/**
	 * Retrieve a cached value entry by id
	 *
	 * Examples:
	 * ~~~
	 * // Retrieve cache entry from file group
	 * $data = Cache::instance('file')->get('foo');
	 *
	 * // Retrieve cache entry from file group and return 'bar' if miss
	 * $data = Cache::instance('file')->get('foo', 'bar');
	 * ~~~
	 *
	 * @param   string  $id       ID of cache to entry
	 * @param   mixed   $default  Default value to return if cache miss [Optional]
	 *
	 * @return  mixed
	 *
	 * @uses    Mango_Collection::findOne
	 */
	public function get($id, $default = NULL)
	{
		// Load the cache based on id
		$result = $this->_collection->findOne(array('id' => $this->prepareID($id)));

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
			// Return the valid cache data
			$data = unserialize($result['data']);

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
	 * // Delete 'foo' entry from the file group
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
		$data = $this->_collection->findOne(array('id' => $this->prepareID($id)));

		if ( ! is_null($data))
		{
			$status = $this->_collection->safeRemove(
				array('id'      => $this->prepareID($id)), // Event ID
				array('justOne' => TRUE)                   // Remove at most one record
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
	 * // Delete 'foo:**' entries from the mango cache
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
				$response = $this->_collection->safeDrop();

				return $response;
			}
			catch (Exception $e)
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
				$response = $this->_collection->safeRemove(array('lifetime' => array('$lte' => time())));

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
	 * Prepare ID of cache entry
	 *
	 * @param  string  $id  ID of cache entry
	 *
	 * @return string
	 *
	 * @uses   System::sanitize_id
	 */
	public function prepareID($id)
	{
		return System::sanitize_id($this->config('prefix').$id);
	}

	/**
	 * Prepare data of cache entry
	 *
	 * @param   mixed  $data  Data to set to cache
	 * @return  string
	 */
	public function prepareData($data)
	{
		return serialize($data);
	}

	/**
	 * Prepare cache lifetime
	 *
	 * Uses [Cache::DEFAULT_EXPIRE] if the `default_expire`
	 * option is missing or empty
	 *
	 * @param   integer  $lifetime  Cache lifetime
	 *
	 * @uses    Arr::get
	 *
	 * @return  integer
	 */
	public function prepareLifeTime($lifetime = NULL)
	{
		$default_expire = Arr::get($this->_config, 'default_expire', NULL);

		// Setup lifetime
		if (is_null($lifetime))
		{
			if ($default_expire === 0)
			{
				$lifetime = 0;
			}
			elseif (is_null($default_expire))
			{
				$lifetime = Cache::DEFAULT_EXPIRE + time();
			}
			else
			{
				$lifetime = (int)$default_expire + time();
			}
		}
		else
		{
			$lifetime = (0 === $lifetime) ? 0 : ((int)$lifetime + time());
		}

		return $lifetime;
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
		return (bool)$this->_collection->findOne(array('id' => $id));
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
	 * @uses    Mango_Collection::safeUpdate
	 * @uses    Mango_Collection::insert
	 */
	public function set_with_tags($id, $data, $lifetime = NULL, array $tags = NULL)
	{
		// Prepare ID
		$id = $this->prepareID($id);

		// Prepare data
		$data = $this->prepareData($data);

		// Prepare cache lifetime
		$lifetime = $this->prepareLifeTime($lifetime);

		if ($this->exists($id))
		{
			$newdata = array(
				'data'     => $data,
				'lifetime' => $lifetime,
				'tags'     => $tags
			);

			$status = $this->_collection->safeUpdate(array('id'=> $id), $newdata);
		}
		else
		{
			$status = $this->_collection->insert(array(
					'id'       => $id,
					'data'     => $data,
					'lifetime' => $lifetime,
					'tags'     => $tags
				),
				array('w' => TRUE)
			);
		}

		return (is_bool($status) ? $status : (is_array($status) AND $status['ok'] == 1));
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
	 * @param   string  $tag  tag
	 *
	 * @return  array
	 */
	public function find($tag)
	{
		// Not implemented yet
	}
}