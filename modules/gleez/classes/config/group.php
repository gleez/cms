<?php
/**
 * Config Group for the Gleez config system
 *
 * @package    Gleez\Configuration\Group
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Config_Group implements ArrayAccess {

	/**
	 * Reference the config items that created this group
	 * @var Config
	 */
	protected $_contents = array();

	/**
	 * The group this config is for
	 * Used when updating config items
	 * @var string
	 */
	protected $_group = '';

	/**
	 * Constructs the group object.  Config passes the config group
	 * and its config items to the object here.
	 *
	 * @param  array   $config  Group's config
	 * @param  string  $group   The group name
	 */
	public function __construct(array $config = array(), $group)
	{
		$this->_contents = $config;
		$this->_group 	 = $group;
	}

	/**
	 * Return the current group in serialized form
	 *
	 * Example:
	 * ~~~
	 * echo $config;
	 * ~~~
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return serialize($this->_contents);
	}

	/**
	 * This method is called when config is accessed via
	 * ~~~
	 * $config->var;
	 *
	 * // OR
	 *
	 * $config['var'];
	 * ~~~
	 *
	 * @param  string  $key  The key of the config item we're getting
	 *
	 * @return  mixed
	 */
	public function __get($key)
	{
		return $this->offsetGet($key);
	}

	/**
	 * Getter for contents
	 *
	 * @return array Array copy of the group's config
	 */
	public function as_array()
	{
		return $this->_contents;
	}

	/**
	 * Returns the config group's name
	 *
	 * @return string The group name
	 */
	public function group_name()
	{
		return $this->_group;
	}

	/**
	 * Get a variable from the configuration or return the default value.
	 *
	 * Example:
	 * ~~~
	 * $value = $config->get($key);
	 * ~~~
	 *
	 * @param   string  $key      Array key
	 * @param   mixed   $default  Default value [Optional]
	 *
	 * @return  mixed
	 */
	public function get($key, $default = NULL)
	{
		return $this->offsetExists($key) ? $this->offsetGet($key) : $default;
	}

	/**
	 * Alias to Config::set()
	 * Sets a value in the configuration array.
	 *
	 * Example:
	 * ~~~
	 * $config->set($key, $new_value);
	 * ~~~
	 *
	 * @param   string  $key    Array key
	 * @param   mixed   $value  Array value
	 *
	 * @return  Config_Group
	 */
	public function set($key, $value)
	{
		return $this->offsetSet($key, $value);
	}

	/**
	 * This method is called when config is accessed via
	 *
	 * Example:
	 * ~~~
	 * $config->var;
	 *
	 * // OR
	 *
	 * $config['var'];
	 * ~~~
	 *
	 * @param   string  $key  The key of the config item we're getting
	 *
	 * @return  mixed
	 */
	public function offsetGet($key)
	{
		if($this->offsetExists($key))
		{
			return $this->_contents[$key];
		}

		return FALSE;
	}

	/**
	 * Alias to Config::set()
	 *
	 * This method is called when config is changed via:
	 * ~~~
	 * $config->var = 'asd';
	 *
	 * // OR
	 *
	 * $config['var'] = 'asd';
	 * ~~~
	 *
	 * @param   string  $key    The key of the config item we're changing
	 * @param   mixed   $value  The new array value
	 *
	 * @return  Config_Group
	 */
	public function offsetSet($key, $value)
	{
		Config::set($this->_group, $key, $value);
		$this->_contents[$key] = $value;

		return $this;
	}

	/**
	 * Alias to Config::delete()
	 * Removes a given config item (key)
	 *
	 * @param string $key   The key of the config item we're removing
	 *
	 * @return  Config_Group
	 */
	public function offsetUnset($key)
	{
		if($this->offsetExists($key))
		{
			Config::delete($this->_group, $key);
			unset($this->_contents[$key]);
		}

		return $this;
	}

	/**
	 * Check if a given config item(key) exists
	 *
	 * @param   string  $key  The key of the config item we're checking
	 *
	 * @return  boolean
	 */
	public function offsetExists($key)
	{
		return isset($this->_contents[$key]);
	}

}