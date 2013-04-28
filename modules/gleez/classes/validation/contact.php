<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Contact Validation Class
 *
 * @package    Gleez\Security
 * @version    1.0
 * @author     Sergey Yakovlev - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Validation_Contact extends Gleez_Validation {

	/** Default subject length */
	const SUBJECT_LEN = 80;

	/** Default body length */
	const BODY_LEN = 400;

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
	 * @return array
	 */
	protected function _rules()
	{
		$config = Kohana::$config->load('contact');

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
				array('max_length', array(':value', $config->get('subject_length', self::SUBJECT_LEN))),
			),
			'category' => array(
				array('not_empty'),
			),
			'body' => array(
				array('not_empty'),
				array('max_length', array(':value', $config->get('body_length', self::BODY_LEN))),
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