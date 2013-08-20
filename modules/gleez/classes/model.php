<?php
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
	 *
	 * // Attempts to create the \Document\Collection object
	 * $model = Model::factory('\document\collection');
	 * ~~~
	 *
	 * @param   string  $name  Model name
	 *
	 * @return  Model
	 *
	 * @uses    Text::reduce_slashes
	 */
	public static function factory($name)
	{
		if (FALSE !== strpos($name, '\\'))
		{
			$class = implode('\\', array_map('ucfirst', explode('\\', Text::reduce_slashes($name))));;
		}
		else
		{
			// Add the model prefix
			$class = 'Model_' . implode('_', array_map('ucfirst', explode('_', $name)));
		}

		return new $class;
	}
}
