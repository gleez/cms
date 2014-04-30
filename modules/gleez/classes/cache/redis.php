<?php
/**
 * [Cache](api/Cache) A Redis driver
 *
 * Provides a Redis based driver for the Gleez Cache library.
 *
 * ### Configuration example
 *
 * Below is an example of an _redis_ server configuration:
 * ~~~
 *     return array(
 *       'redis' => array(
 *               'driver' => 'redis',
 *               'default_expire' => 3600,
 *               'host' => 'localhost',
 *               'port' => 6379,
 *       ),
 *     )
 * ~~~
 *
 * In cases where only one cache group is required, if the group is named `default` there is
 * no need to pass the group name when instantiating a cache instance.
 *
 * #### General cache group configuration settings
 *
 * Below are the settings available to all types of cache driver.
 *
 * Name           | Required | Description
 * -------------- | -------- | ---------------------------------------------------------------
 * driver         | __YES__  | (_string_) The driver type to use
 *
 * ### System requirements
 *
 * * Redis PHP extension
 *
 * @package    Gleez\Cache\Base
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 * @link 	   https://github.com/nicolasff/phpredis
 */
class Cache_Redis extends Cache {

	const CACHE_TYPE = 'user';

	/**
	 * Check for existence of the PhpRedis extension
	 *
	 * [!!] This method cannot be invoked externally
	 *
	 * The driver must be instantiated using the `Cache::instance()` method.
	 *
	 * @param  array  $config  configuration
	 *
	 * @throws Cache_Exception
	 */
	protected function __construct(array $config)
	{
		// Check that the PhpRedis extension is loaded.
		if ( ! extension_loaded('redis'))
		{
			throw new Cache_Exception('You must have PhpRedis installed and enabled to use.');
		}

		// Define a default settings array.
		$default_settings = array(
			'host' => 'localhost',
			'port' => 6379
		);

		// Merge the default settings with the user-defined settings.
		$this->config(Arr::merge($default_settings, $config));

		// Create a new Redis instance and start a connection using the settings provided.
		$this->_redis = new Redis;
		$this->_redis->connect($this->config('host'), $this->config('port'));

		parent::__construct($config);
	}

	/**
	 * Retrieve a cached value entry by id
	 *
	 * Return the stored variable or array of variables on success
	 * or $default FALSE on failure
	 *
	 * Examples:
	 * ~~~
	 * // Retrieve cache entry from redis group
	 * $data = Cache::instance('redis')->get('foo');
	 *
	 * // Retrieve cache entry from redis group and return 'bar' if miss
	 * $data = Cache::instance('redis')->get('foo', 'bar');
	 * ~~~
	 *
	 * @param   string  $id       ID of cache to entry
	 * @param   string  $default  Default value to return if cache miss [Optional]
	 *
	 * @return  mixed
	 *
	 * @uses    System::sanitize_id
	 * @uses    Log::add
	 */
	public function get($id, $default = NULL)
	{
		//  Try to fetch a stored variable from the cache
		try
		{
			// Return the cache
			return $this->_redis->get(System::sanitize_id($this->config('prefix').$id));
		}
		catch (Exception $e)
		{
			// Cache is corrupt or not exists, let return happen normally
			Log::error('An error occurred retrieving corrupt or not exists cache name: [:name]',
				array(':name' => System::sanitize_id($this->config('prefix').$id))
			);
		}

		// Cache not found, return default value
		return $default;
	}

	/**
	 * Set a value to cache with id and lifetime
	 *
	 * Example:
	 * ~~~
	 * $data = 'bar';
	 *
	 * // Set 'bar' to 'foo' in redis group, using default expiry
	 * Cache::instance('redis')->set('foo', $data);
	 *
	 * // Set 'bar' to 'foo' in redis group for 30 seconds
	 * Cache::instance('redis')->set('foo', $data, 30);
	 * ~~~
	 *
	 * @param   string   $id        ID of cache entry
	 * @param   string   $data      Data to set to cache
	 * @param   integer  $lifetime  Lifetime in seconds [Optional]
	 *
	 * @return  boolean
	 *
	 * @uses    System::sanitize_id
	 */
	public function set($id, $data, $lifetime = NULL)
	{
		if ($lifetime === NULL)
		{
			$lifetime = Arr::get($this->_config, 'default_expire', Cache::DEFAULT_EXPIRE);
		}

		try
		{
			return $this->_redis->set(System::sanitize_id($this->config('prefix').$id), $data, $lifetime);
		}
		catch (Exception $e)
		{
			Log::error('An error occurred setting [:name] to cache.',
				array(':name' => System::sanitize_id($this->config('prefix').$id))
			);
		}

		// Failed to write cache
		return FALSE;
	}

	/**
	 * Delete a cache entry based on id
	 *
	 * Example:
	 * ~~~
	 * // Delete 'foo' entry from the redis group
	 * Cache::instance('redis')->delete('foo');
	 * ~~~
	 *
	 * @param   string  $id  ID to remove from cache
	 *
	 * @return  boolean
	 *
	 * @uses    System::sanitize_id
	 */
	public function delete($id)
	{
		return $this->_redis->del(System::sanitize_id($this->config('prefix').$id));
	}

	/**
	 * Delete a cache entry based on regex pattern
	 *
	 * Example:
	 * ~~~
	 * // Delete 'foo:**' entries from the redis cache
	 * Cache::instance('redis')->delete_pattern('foo:**:bar');
	 * ~~~
	 *
	 * @param   string  $pattern  The cache key pattern
	 *
	 * @return  boolean
	 */
	public function delete_pattern($pattern)
	{

	}

	/**
	 * Delete all cache entries
	 *
	 * Beware of using this method when using shared memory cache systems,
	 * as it will wipe every entry within the system for all clients.
	 *
	 * Example:
	 * ~~~
	 * // Delete all cache entries in the redis group
	 * Cache::instance('redis')->delete_all();
	 * ~~~
	 *
	 *
	 * @param   integer  $mode  The clean mode [Optional]
	 *
	 * @return  boolean
	 */
	public function delete_all($mode = Cache::ALL)
	{
		if (Cache::ALL === $mode)
		{
			return $this->_redis->flushAll();
		}
		else
		{
			// @todo
		}
	}

	/**
	 * Increments a given value by the step value supplied
	 *
	 * Useful for shared counters and other persistent integer based
	 * tracking.
	 *
	 * @param   string   $id    ID of cache entry to increment
	 * @param   integer  $step  Step value to increment by [Optional]
	 *
	 * @return  integer|boolean
	 *
	 * @uses    System::sanitize_id
	 */
	public function increment($id, $step = 1)
	{
		return $this->_redis->incrBy(System::sanitize_id($this->config('prefix').$id), $step);
	}

	/**
	 * Decrements a given value by the step value supplied
	 *
	 * Useful for shared counters and other persistent integer based
	 * tracking.
	 *
	 * @param   string   $id    ID of cache entry to decrement
	 * @param   integer  $step  Step value to decrement by [Optional]
	 *
	 * @return  integer|boolean
	 *
	 * @uses    System::sanitize_id
	 */
	public function decrement($id, $step = 1)
	{
		return $this->_redis->decrBy(System::sanitize_id($this->config('prefix').$id), $step);
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
		try
		{
			return $this->_redis->exists(System::sanitize_id($this->config('prefix').$id));
		}
		catch (Exception $e)
		{
			// Cache is corrupt or not exists, let return happen normally
			Log::error('An error occurred retrieving corrupt or not exists cache name: [:name]',
				array(':name' => System::sanitize_id($this->config('prefix').$id))
			);
		}

		// Cache not found, return default value
		return FALSE;
	}
}
