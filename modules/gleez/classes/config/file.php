<?php
/**
 * Default file source for the Gleez config system
 *
 * @package    Gleez\Configuration\File
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Config_File implements Config_Source {

	/**
	 * The directory where config files are located
	 * @var string
	 */
	protected $_directory = '';

	/**
	 * Creates a new file reader using the given directory as a config source
	 *
	 * @param  string  $directory  Configuration directory to search {optional]
	 */
	public function __construct($directory = 'config')
	{
		// Set the configuration directory name
		$this->_directory = trim($directory, DS);
	}

	/**
	 * Load and merge all of the configuration files in this group.
	 *
	 * Example:
	 * ~~~
	 * $config->load($name);
	 * ~~~
	 *
	 * @param   string  $group  Configuration group name
	 * @return  $this   Current object
	 *
	 * @uses    Kohana::load
	 * @uses    Kohana::find_file
	 * @uses    Arr::merge
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
	 * @param   string  $group  The config group
	 * @param   string  $key    The config key to write to
	 * @param   array   $config The configuration to write
	 * @return  boolean
	 */
	public function write($group, $key, $config)
	{
		//always return true
		return TRUE;
	}

	/**
	 * Delete the config item from config
	 *
	 * @param   string  $group  The config group
	 * @param   string  $key    The config key to delete
	 * @return  boolean
	 */
	public function delete($group, $key)
	{
		// Always return true
		return TRUE;
	}

}
