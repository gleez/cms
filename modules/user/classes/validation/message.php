<?php
/**
 * Message Validation Class
 *
 * @package    Gleez\Security
 * @version    1.0.0
 * @author     Gleez Team
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Validation_Message extends Validation {

	/**
	 * Creates a new Validation instance.
	 *
	 * @param   array   $array  array to use for validation
	 * @return  Validation
	 */
	public static function factory(array $array)
	{
		return new Validation_Message($array);
	}

	/**
	 * Sets the fields for Message form
	 *
	 * @return array
	 */
	protected function _fields()
	{
		return array('recipient', 'subject', 'body', 'format', 'draft');
	}

	/**
	 * Sets the rules for Contact form
	 *
	 * @return  array
	 *
	 * @uses    Config::get
	 */
	protected function _rules()
	{
		return array(
			'recipient' => array(
				array(array($this, 'notExists'), array(':value'))
			),
			'subject' => array(
				array('max_length', array(':value', 128)),
			)
		);
	}

	/**
	 * Sets the labels for Message form
	 *
	 * @return array
	 */
	protected function _labels()
	{
		return array(
			'recipient' => __('Recipient'),
			'subject'   => __('Subject'),
			'body'      => __('Body'),
			'format'    => __('Format'),
			'draft'     => __('Draft')
		);
	}

	/**
	 * Checks whether user exists with the specified name
	 *
	 * @param  string $recipient User name
	 * @return bool
	 */
	protected function notExists($recipient)
	{
		$result = ORM::factory('user')
				->where('name', '=', $recipient)
				->and_where('name', '!=', 'guest')
				->find();

		return $result->loaded();
	}
}
