<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Array and variable validation
 *
 * @package    Gleez\Security
 * @version    1.0
 * @author     Sergey Yakovlev - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
abstract class Gleez_Validation extends Kohana_Validation {

	/**
	 * Creates a new Validation instance
	 *
	 * @param   array $array  Array to use for validation
	 * @return  Validation|static
	 */
	public static function factory(array $array)
	{
		return new static($array);
	}

	/**
	 * Class constructor
	 *
	 * @param  array  $array  Array to validate
	 */
	public function __construct(array $array)
	{
		if ($this->_fields())
		{
			$array = Arr::extract($array, $this->_fields());
		}

		parent::__construct($array);

		// Add labels
		$this->labels($this->_labels());

		// Add rules
		foreach ($this->_rules() as $field => $rules)
		{
			$this->rules($field, $rules);
		}
	}

	/**
	 * Sets fields
	 *
	 * @return array
	 */
	protected function _fields()
	{
		return array();
	}

	/**
	 * Sets rules
	 *
	 * @return array
	 */
	protected function _rules()
	{
		return array();
	}

	/**
	 * Sets the label names for fields
	 *
	 * @return array
	 */
	protected function _labels()
	{
		return array();
	}
}