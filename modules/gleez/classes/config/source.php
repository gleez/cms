<?php
/**
 * Base Config source Interface
 *
 * @package    Gleez\Configuration
 * @author     Sandeep Sangamreddi - Gleez
 * @version    1.0.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
interface Config_Source
{
	/**
	 * Tries to load the specified configuration group
	 *
	 * Returns FALSE if group does not exist or an array if it does
	 *
	 * @param  string  $group  Configuration group
	 *
	 * @return boolean|array
	 */
    public function load($group);

	/**
	 * Writes the passed config for $group
	 *
	 * @param   string      $group  The config group
	 * @param   string      $key    The config key to write to
	 * @param   array       $config The configuration to write
	 *
	 * @return  boolean
	 */
    public function write($group, $key, $config);

	/**
	 * Delete the config key from $group
	 *
	 * @param   string   $group  The config group
	 * @param   string   $key    The config key to delete
	 *
	 * @return  boolean
	 */
    public function delete($group, $key);
}