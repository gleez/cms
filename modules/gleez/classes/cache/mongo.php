<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ### System requirements
 * 
 *  Kohana 3.2.x
 *  PHP 5.3 or greater
 *  Mongodb PHP extension
 * 
 * @package    Gleez
 * @category   Cache
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Cache_Mongo extends Cache implements Cache_Arithmetic {
        
        // Mongo connection;
	protected $_db;

        // Mongo collection;
	protected $_collection = 'cache';
        
        /**
	 * Check for existence of the mongo extension This method cannot be invoked externally. The driver must
	 * be instantiated using the `Cache::instance()` method.
	 *
	 * @param  array     configuration
	 * @throws Cache_Exception
	 */
	protected function __construct(array $config)
	{
		if ( ! extension_loaded('mongo'))
		{
			throw new Cache_Exception('Mongodb PHP extention not loaded');
		}

                $this->_db = MangoDB::instance();
        
                if( $config['collection'] ) $this->_collection = $config['collection'];
        
		parent::__construct($config);
	}

	/**
	 * Retrieve a cached value entry by id.
	 * 
	 *     // Retrieve cache entry from apc group
	 *     $data = Cache::instance('mongo')->get('foo');
	 * 
	 *     // Retrieve cache entry from apc group and return 'bar' if miss
	 *     $data = Cache::instance('mongo')->get('foo', 'bar');
	 *
	 * @param   string   id of cache to entry
	 * @param   string   default value to return if cache miss
	 * @return  mixed
	 * @throws  Cache_Exception
	 */
	public function get($id, $default = NULL)
	{
		if(Kohana::$environment == Kohana::DEVELOPMENT) return false;
	
                // Get the value from Mongodb
                $result = $this->_db->find_one($this->_collection, array('_id' => (string)$this->_sanitize_id($id))); 
     
                if (!$result || !isset($result['cache']))
                {
                	return FALSE;
         	}

                $result = (object)$result;

		// If the cache has expired
		if ($result->expiration != 0 AND $result->expiration <= time())
		{
			// Delete it and return default value
			$this->delete($id);
			return $default;
		}
                else
                {
                	// Return the valid cache data
			$data = unserialize($result->cache);
                }
                
		// Return the value
		return $data;
	}

	/**
	 * Set a value to cache with id and lifetime
	 * 
	 *     $data = 'bar';
	 * 
	 *     // Set 'bar' to 'foo' in memcache group for 10 minutes
	 *     if (Cache::instance('mongo')->set('foo', $data, 600))
	 *     {
	 *          // Cache was set successfully
	 *          return
	 *     }
	 *
	 * @param   string   id of cache entry
	 * @param   mixed    data to set to cache
	 * @param   integer  lifetime in seconds, maximum value 2592000
	 * @return  boolean
	 */
	public function set($id, $data, $lifetime = 3600)
	{
		if(Kohana::$environment == Kohana::DEVELOPMENT) return false;
	
		// Setup lifetime
		$lifetime = (0 === $lifetime) ? 0 : $lifetime + time();
		
                $scalar = is_scalar($data);
                
                $entry = array(
                                '_id' => (string)$this->_sanitize_id($id),
                                'cid' => (string)$this->_sanitize_id($id),
                                'cache' => $scalar ? $data : serialize($data),
                                'expiration' => $lifetime,
                        ); 
                
                // Delete if exists - Besure to avoid conflict
		$this->delete($id);
                
		// Set the data to mongodb
		return  $this->_db->save($this->_collection, $entry);
	}

	/**
	 * Delete a cache entry based on id
	 * 
	 *     // Delete the 'foo' cache entry immediately
	 *     Cache::instance('mongo')->delete('foo');
	 * 
	 *     // Delete the 'bar' cache entry after 30 seconds
	 *     Cache::instance('mongo')->delete('bar', 30);
	 *
	 * @param   string   id of entry to delete
	 * @param   integer  timeout of entry, if zero item is deleted immediately, otherwise
	 *                      the item will delete after the specified value in seconds
	 * @return  boolean
	 */
	public function delete($id, $timeout = 0)
	{
		// Delete the id
                return $this->_db->remove( $this->_collection, array('_id' => $this->_sanitize_id($id)) );
	}

	/**
	 * Delete all cache entries.
	 * 
	 * Beware of using this method when
	 * using shared memory cache systems, as it will wipe every
	 * entry within the system for all clients.
	 * 
	 *     // Delete all cache entries in the default group
	 *     Cache::instance('mongo')->delete_all();
	 *
	 * @return  boolean
	 */
	public function delete_all()
	{
		return $this->_db->drop_collection($this->_collection);
	}

	/**
	 * Increments a given value by the step value supplied.
	 * Useful for shared counters and other persistent integer based
	 * tracking.
	 *
	 * @param   string    id of cache entry to increment
	 * @param   int       step value to increment by
	 * @return  integer
	 * @return  boolean
	 */
	public function increment($id, $step = 1)
	{
		//return $this->_memcache->increment($id, $step);
	}

	/**
	 * Decrements a given value by the step value supplied.
	 * Useful for shared counters and other persistent integer based
	 * tracking.
	 *
	 * @param   string    id of cache entry to decrement
	 * @param   int       step value to decrement by
	 * @return  integer
	 * @return  boolean
	 */
	public function decrement($id, $step = 1)
	{
		//return $this->_memcache->decrement($id, $step);
	}
}