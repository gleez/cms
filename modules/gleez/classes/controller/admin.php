<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Admin Base Controller
 *
 * @package    Gleez\Controller\Admin
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Admin extends Template {

	// Currently logged in user
	protected $_current_user;

	public function before()
	{
		// Inform tht we're in admin section for themers/developers
		Theme::$is_admin = TRUE;

		if ( class_exists('ACL') )
		{
			ACL::required('administer site');
		}
		parent::before();
	}

	final public function action_skip()
	{
		// Do nothing
	}

	public function after()
	{
		parent::after();
	}

	public function index()
	{
		$this->response->body( __('Welcome to admin') );
	}
}
