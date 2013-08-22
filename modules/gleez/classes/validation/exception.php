<?php
/**
 * @package    Gleez\Exceptions
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Validation_Exception extends Gleez_Exception {

	/**
	 * Validation instance
	 * @var object
	 */
	public $array;

	/**
	 * Validation constructor
	 *
	 * @param  Validation  $array    Validation object
	 * @param  string      $message  Error message [Optional]
	 * @param  array       $values   Translation variables [Optional]
	 * @param  integer     $code     The exception code [Optional]
	 * @param  Exception   $previous Previous exception [Optional]
	 */
	public function __construct(Validation $array, $message = 'Failed to validate array', array $values = NULL, $code = 0, Exception $previous = NULL)
	{
		$this->array = $array;

		parent::__construct($message, $values, $code, $previous);
	}
}
