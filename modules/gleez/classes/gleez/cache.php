<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Gleez Core Cache Class
 *
 * @package    Gleez\Base
 * @author	   Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
abstract class Gleez_Cache extends Kohana_Cache {
        
	/**
	 * Creates a singleton of a Cache group. If no group is supplied
	 * the __default__ cache group is used.
	 * 
	 *     // Create an instance of the default group
	 *     $default_group = Cache::instance();
	 * 
	 *     // Create an instance of a group
	 *     $foo_group = Cache::instance('foo');
	 * 
	 *     // Access an instantiated group directly
	 *     $foo_group = Cache::$instances['default'];
	 *
	 * @param   string   the name of the cache group to use [Optional]
	 * @return  Kohana_Cache
	 * @throws  Cache_Exception
	 */
	public static function instance($group = NULL)
	{
		// If there is no group supplied
		if ($group === NULL)
		{
			// Use the default setting
			$group = Cache::$default;
		}

		if (isset(Cache::$instances[$group]))
		{
			// Return the current group if initiated already
			return Cache::$instances[$group];
		}

		$config = Kohana::$config->load('cache');
		$collection = $group;

		if ( ! $config->offsetExists($group))
		{
			// Use the default setting by gleez
			$group = Cache::$default;
                
                	if (isset(Cache::$instances[$group]))
                        {
                                // Return the current group if initiated already
                                return Cache::$instances[$group];
                        }
                
                        if ( ! $config->offsetExists($group))
                        {
                                throw new Cache_Exception(
                                        'Failed to load Kohana Cache group: :group',
                                        array(':group' => $group)
                                );
                        }
		}

		$config = $config->get($group);
		$config['collection'] = 'cache_'.$collection;

		// Create a new cache type instance
		$cache_class = 'Cache_'.ucfirst($config['driver']);
		Cache::$instances[$group] = new $cache_class($config);

		// Return the instance
		return Cache::$instances[$group];
	}

}