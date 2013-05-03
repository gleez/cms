<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Default file source for the Gleez config system
 *
 * @package     Gleez/core
 * @category    Configuration
 * @author	Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2011 - 2013 Gleez Technologies
 * @license	http://gleezcms.org/license
 */
class Gleez_Config_File implements Config_Source {
	
	/**
	 * The directory where config files are located
	 * @var string
	 */
	protected $_directory = '';
	
	/**
	 * Creates a new file reader using the given directory as a config source
	 *
	 * @param string    $directory  Configuration directory to search
	 */
	public function __construct($directory = 'config')
	{
		// Set the configuration directory name
		$this->_directory = trim($directory, '/');
	}
	
	/**
	 * Load and merge all of the configuration files in this group.
	 *
	 *     $config->load($name);
	 *
	 * @param   string  $group  configuration group name
	 * @return  $this   current object
	 * @uses    Kohana::load
	 */
	public function load($group)
	{
		$config = array();

		if ($files = Kohana::find_file($this->_directory, $group, NULL, TRUE))
		{
			foreach ($files as $file)
			{
				// Merge each file to the configuration array
				$config = Arr::merge($config, Kohana::load($file));
			}
		}

		return $config;
	}
	
	/**
	 * Writes the passed config for $group
	 *
	 * @param string      $group  The config group
	 * @param string      $key    The config key to write to
	 * @param array       $config The configuration to write
	 * @return boolean
	 */
	public function write($group, $key, $config)
	{
		//always return true
		return TRUE;
	}
	
	/**
	 * Delete the config item from config
	 *
	 * @param string      $group  The config group
	 * @param string      $key    The config key to delete
	 * @return boolean
	 */
	public function delete($group, $key)
	{
		//always return true
		return TRUE;
	}
	
}