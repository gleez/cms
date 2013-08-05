<?php defined('SYSPATH') OR die('No direct script access allowed.');
/**
 * Model base class
 *
 * [!!] Note: All models should extend this class.
 *
 * @package    Gleez\Models
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License Agreement
 */
abstract class Model {

	/**
	 * Create a new model instance
	 *
	 * Example:
	 * ~~~
	 * $model = Model::factory($name);
	 * ~~~
	 *
	 * @param   string  $name  Model name
	 * @return  Model
	 */
	public static function factory($name)
	{
		// Add the model prefix
		$class = 'Model_'.$name;

		return new $class;
	}
}
