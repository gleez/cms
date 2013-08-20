<?php
/**
 * [Cache](api/Cache) Wincache driver
 *
 * Provides an opcode based driver for the Gleez Cache library.
 *
 * ### Configuration example
 *
 * Below is an example of an _wincache_ server configuration.
 *
 *     return array(
 *          'wincache' => array(             // Driver group
 *                  'driver'  => 'wincache', // using wincache driver
 *           ),
 *     )
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
 * *  Windows XP SP3 with IIS 5.1 and » FastCGI Extension
 * *  Windows Server 2003 with IIS 6.0 and » FastCGI Extension
 * *  Windows Vista SP1 with IIS 7.0 and FastCGI Module
 * *  Windows Server 2008 with IIS 7.0 and FastCGI Module
 * *  Windows 7 with IIS 7.5 and FastCGI Module
 * *  Windows Server 2008 R2 with IIS 7.5 and FastCGI Module
 * *  PHP 5.2.X, Non-thread-safe build
 * *  PHP 5.3 X86, Non-thread-safe VC9 build
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
class Cache_Wincache extends Cache {

	/**
	 * Check for existence of the wincache extension
	 *
	 * [!!] This method cannot be invoked externally
	 *
	 * The driver must be instantiated using the `Cache::instance()` method.
	 *
	 * @param  array  $config  configuration
	 * @throws Cache_Exception
	 */
	protected function __construct(array $config)
	{
		if ( ! extension_loaded('wincache'))
		{
			throw new Cache_Exception('PHP wincache extension is not available.');
		}

		parent::__construct($config);
	}

	/**
	 * Retrieve a cached value entry by id.
	 *
	 * Examples:
	 * ~~~
	 * // Retrieve cache entry from wincache group
	 * $data = Cache::instance('apc')->get('foo');
	 *
	 * // Retrieve cache entry from wincache group and return 'bar' if miss
	 * $data = Cache::instance('apc')->get('foo', 'bar');
	 * ~~~
	 *
	 * @param   string  $id       ID of cache to entry
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
		$data = wincache_ucache_get(System::sanitize_id($this->config('prefix').$id), $success);

		return $success ? $data : $default;
	}

	/**
	 * Set a value to cache with id and lifetime
	 *
	 * Example:
	 * ~~~
	 * $data = 'bar';
	 *
	 * // Set 'bar' to 'foo' in wincache group, using default expiry
	 * Cache::instance('wincache')->set('foo', $data);
	 *
	 * // Set 'bar' to 'foo' in wincache group for 30 seconds
	 * Cache::instance('wincache')->set('foo', $data, 30);
	 * ~~~
	 *
	 * @param   string   $id        ID of cache entry
	 * @param   mixed    $data      The data to cache
	 * @param   integer  $lifetime  Lifetime [Optional]
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

		return wincache_ucache_set(System::sanitize_id($this->config('prefix').$id), $data, $lifetime);
	}

	/**
	 * Delete a cache entry based on id
	 *
	 * Example:
	 * ~~~
	 * // Delete 'foo' entry from the wincache group
	 * Cache::instance('wincache')->delete('foo');
	 * ~~~
	 *
	 * @param   string  $id  ID of cache entry
	 *
	 * @return  boolean
	 *
	 * @uses    System::sanitize_id
	 */
	public function delete($id)
	{
		return wincache_ucache_delete(System::sanitize_id($this->config('prefix').$id));
	}

	/**
	 * Delete a cache entry based on regex pattern
	 *
	 * Example:
	 * ~~~
	 * // Delete 'foo:**' entries from the wincache cache
	 * Cache::instance('wincache')->delete_pattern('foo:**:bar');
	 * ~~~
	 *
	 * @param   string  $pattern The cache key pattern
	 * @return  boolean
	 *
	 * @throws  Cache_Exception
	 */
	public function delete_pattern($pattern)
	{
		throw new Cache_Exception('Not implemented yet!');
	}

	/**
	 * Delete all cache entries.
	 *
	 * Beware of using this method when using shared memory cache systems,
	 * as it will wipe every entry within the system for all clients.
	 *
	 * Example:
	 * ~~~
	 * // Delete all cache entries in the wincache group
	 * Cache::instance('wincache')->delete_all();
	 * ~~~
	 *
	 * @param   integer  $mode  The clean mode [Optional]
	 *
	 * @return  boolean
	 */
	public function delete_all($mode = Cache::ALL)
	{
		return wincache_ucache_clear();
	}
}
