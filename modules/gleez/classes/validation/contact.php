<?php
/**
 * Contact Validation Class
 *
 * @package    Gleez\Security
 * @version    1.0.1
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Validation_Contact extends Validation {

	/** Default subject length */
	const SUBJECT_LEN = 80;

	/** Default body length */
	const BODY_LEN = 600;

	/**
	 * Creates a new Validation instance.
	 *
	 * @param   array   $array  array to use for validation
	 * @return  Validation
	 */
	public static function factory(array $array)
	{
		return new Validation_Contact($array);
	}
	
	/**
	 * Sets the fields for Contact form
	 *
	 * @return array
	 */
	protected function _fields()
	{
		return array('name', 'email', 'subject', 'category', 'body');
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
			'name' => array(
				array('not_empty'),
				array('min_length', array(':value', 4)),
				array('max_length', array(':value', 60)),
			),
			'email' => array(
				array('not_empty'),
				array('email'),
				array('email_domain'),
				array('min_length', array(':value', 5)),
				array('max_length', array(':value', 254)),
			),
			'subject' => array(
				array('not_empty'),
				array('max_length', array(':value', Config::get('contact.subject_length', self::SUBJECT_LEN))),
			),
			'category' => array(
				array('not_empty'),
			),
			'body' => array(
				array('not_empty'),
				array('max_length', array(':value', Config::get('contact.body_length', self::BODY_LEN))),
			),
		);
	}

	/**
	 * Sets the labels for Contact form
	 *
	 * @return array
	 */
	protected function _labels()
	{
		return array(
			'name'     => __('Your Name'),
			'email'    => __('E-Mail'),
			'subject'  => __('Subject'),
			'category' => __('Category'),
			'body'     => __('Body')
		);
	}
}