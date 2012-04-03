<?php defined('SYSPATH') or die('No direct script access.');
/*
 * @package    Gleez
 * @category   User
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Model_Identity extends ORM
{
	protected $_belongs_to = array(
                        'user' => array('foreign_key' => 'user_id')
	);
	
	/**
	 * Validates that this identity is unique
	 * @param string URL to validate
	 * @return bool True if the URL is unique, false otherwise.
	 */
	public static function unique_identity($identity)
	{
		return (bool) DB::select(array(DB::expr('COUNT(provider)'), 'total'))
			->from('identities')
			->where('provider', '=', $identity)
			->execute()
			->get('total');
	}
	
	public function login()
	{

	}
}
