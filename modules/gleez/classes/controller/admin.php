<?php
/**
 * Admin Base Controller
 *
 * @package    Gleez\Controller\Admin
 * @author     Gleez Team
 * @version    1.1.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Admin extends Template {

	/**
	 * Currently destination
	 * @var string
	 */
	protected $_destination;

	/**
	 * Currently form action
	 * @var string
	 */
	protected $_form_action;

	/**
	 * Currently logged in user
	 * @var
	 */
	protected $_current_user;

	/**
	 * The before() method is called before controller action
	 *
	 * @uses  ACL::required
	 * @uses  Theme::$is_admin
	 */
	public function before()
	{
		// Inform tht we're in admin section for themers/developers
		Theme::$is_admin = TRUE;

		ACL::required('administer site');

		parent::before();
	}

	public function after()
	{
		parent::after();
	}

	public function index()
	{
		$this->response->body(__('Welcome to admin'));
	}
}
