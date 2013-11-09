<?php
/**
 * Wrapper for configuration arrays
 *
 * Multiple configuration sources can be attached to allow loading configuration
 * from files, database, etc.
 *
 * Configuration directives cascade across config sources in the same way that
 * files cascade across the filesystem.
 *
 * Directives from sources high in the sources list will override ones from those
 * below them.
 *
 * @package    Gleez\Configuration
 * @version    2.0.0
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Config {

	/**
	 * Config array
	 * @var array
	 */
	protected static $_items = array();

	/**
	 * Configuration sources
	 * @var array
	 */
	protected static $_sources = array();

	/**
	 * Config instance
	 * @var Config
	 */
	protected static $_instance;

	/**
	 * Singleton pattern
	 *
	 * @return  Config
	 */
	public static function instance()
	{
		if(is_null(self::$_instance))
		{
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Attach a configuration source
	 *
	 * By default, the source will be added as the first used source.
	 * However, if the source should be used only when all other sources fail,
	 * use `FALSE` for the second parameter.
	 *
	 * Example:
	 * ~~~
	 * // Try first
	 * $config->attach($source);
	 *
	 * // Try last
	 * $config->attach($source, FALSE);
	 * ~~~
	 *
	 * @param   Config_Source  $source  Instance
	 * @param   boolean        $first   Add the source as the first used object [Optional]
	 *
	 * @return  Config
	 */
	public function attach(Config_Source $source, $first = TRUE)
	{
		if ($first === TRUE)
		{
			// Place the log source at the top of the stack
			array_unshift(self::$_sources, $source);
		}
		else
		{
			// Place the source at the bottom of the stack
			self::$_sources[] = $source;
		}

		return self::$_instance;
	}

	/**
	 * Detach a configuration source
	 *
	 * Example:
	 * ~~~
	 * $config->detach($source);
	 * ~~~
	 *
	 * @param   Config_Source  $source  Instance
	 *
	 * @return  Config
	 */
	public function detach(Config_Source $source)
	{
		if (($key = array_search($source, self::$_sources)) !== FALSE)
		{
			// Remove the writer
			unset(self::$_sources[$key]);
		}

		return self::$_instance;
	}

	/**
	 * Load a configuration group
	 *
	 * Searches all the config sources, merging all the directives found
	 * into a single config group. Any changes made to the config in this
	 * group will be mirrored across all writable sources.
	 *
	 * Example:
	 * ~~~
	 * $array = Config::load($name);
	 * ~~~
	 *
	 * See [Config] for more info
	 *
	 * @param   string  $group  Configuration group name
	 *
	 * @return  object Config
	 *
	 * @throws  Gleez_Exception
	 */
	public static function load($group)
	{
		if( ! count(self::$_sources))
		{
			throw new Gleez_Exception('No configuration sources attached');
		}

		if (empty($group))
		{
			throw new Gleez_Exception('Need to specify a config group');
		}

		if ( ! is_string($group))
		{
			throw new Gleez_Exception('Config group must be a string');
		}

		if (strpos($group, '.') !== FALSE)
		{
			// Split the config group and path
			list ($group, $path) = explode('.', $group, 2);
		}

		if(isset(self::$_items[$group]))
		{
			if (isset($path))
			{
				return Arr::path(self::$_items[$group]->as_array(), $path, NULL, '.');
			}
			return self::$_items[$group];
		}

		$config = array();

		// We search from the "lowest" source and work our way up
		$sources = array_reverse(self::$_sources);

		foreach ($sources as $source)
		{
			if ($source instanceof Config_Source)
			{
				if ($source_config = $source->load($group))
				{
					$config = Arr::merge($config, $source_config);
				}
			}
		}

		if ( ! isset(self::$_items[$group]) )
		{
			self::$_items[$group] = array();
		}

		self::$_items[$group] = new Config_Group($config, $group);

		if (isset($path))
		{
			return Arr::path($config, $path, NULL, '.');
		}

		return self::$_items[$group];
	}

	/**
	 * Returns a (dot notated) config setting
	 *
	 * @param   string   $item      Name of the config item, can be dot notated
	 * @param   mixed    $default   The return value if the item isn't found [Optional]
	 *
	 * @return  mixed               The config setting or default if not found
	 */
	public static function get($item, $default = NULL)
	{
		if (strpos($item, '.') !== FALSE)
		{
			// Split the config group and path
			list ($group, $path) = explode('.', $item, 2);
		}
		else
		{
			$group = $item;
		}

		$config = self::load($group);

		//if empty or no config return default
		if(empty($config) OR ($config === FALSE))
		{
			return $default;
		}

		if (isset($path))
		{
			return Arr::path($config->as_array(), $path, $default, '.');
		}

		return self::$_items[$group];
	}

	/**
	 * To store changes made to configuration
	 *
	 * @param   string  $group  Group name
	 * @param   string  $key    Variable name
	 * @param   mixed   $value  The new value
	 *
	 * @return  boolean
	 */
	public static function set($group, $key, $value)
	{
		$status = TRUE;

		/** @var $source \Config_Source */
		foreach (self::$_sources as $source)
		{
			// Copy each value in the config
			$status = $source->write($group, $key, $value);
		}

		return $status;
	}

	/**
	 * Deletes a config item
	 *
	 * @param   string  Config group
	 * @param   string  Config key
	 *
	 * @return  boolean
	 */
	public static function delete($group, $key)
	{
		$status = TRUE;

		/** @var $source \Config_Source */
		foreach (self::$_sources as $source)
		{
			$status = $source->delete($group, $key);
		}

		return $status;
	}

	/**
	 * To store changes made to configuration
	 * Experimental
	 *
	 * @param string    $key    Path of variable eg: group.key or group.key.option1
	 * @param mixed     $value  The new value
	 * @return boolean
	 */
	public static function myset($key, $value)
	{
		if (strpos($key, '.') !== FALSE) return FALSE;

		// Split the config group, item and path
		list ($group, $item, $_path) = explode('.', $key, 3);
		$path = (isset($_path) AND !empty($_path)) ? $item.'.'.$_path : $item;

		// load and override config array
		$config = self::load($group);
		Arr::set_path($config, $path, $value, '.');

		$status = TRUE;
		foreach (self::$_sources as $source)
		{
			// Copy each value in the config
			/** @var $source \Config_Source */
			$status = $source->write($group, $item, $config[$item]);
		}

		return $status;
	}

}