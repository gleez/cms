<?php defined('SYSPATH') OR die('No direct script access allowed.');
/**
 * Model base class
 *
 * [!!] Note: All models should extend this class.
 *
 * @package    Gleez\Models
 * @author     Gleez Team
 * @version    1.1.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
abstract class Model {

	/**
	 * Create a new model instance
	 *
	 * Example:
	 * ~~~
	 * // Attempts to create the Model_Post object
	 * $model = Model::factory('post');
	 *
	 * // Attempts to create the Model_User object
	 * $model = Model::factory('User');
	 *
	 * // Attempts to create the Model_Collection_Document object
	 * $model = Model::factory('collection_document');
	 *
	 * // Attempts to create the Document object
	 * $model = Model::factory('\Document');
	 * ~~~
	 *
	 * @param   string  $name  Model name
	 * @return  Model
	 */
	public static function factory($name)
	{
		if (FALSE !== strpos($name, '\\'))
		{
			$class = $name;
		}
		else
		{
			// Add the model prefix
			$class = 'Model_' . implode('_', array_map('ucfirst', explode('_', $name)));
		}

		return new $class;
	}
}
