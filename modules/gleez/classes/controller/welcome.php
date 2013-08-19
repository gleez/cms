<?php
/**
 * Welcome Controller
 *
 * @package    Gleez\Controller
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Welcome extends Template {

	/**
	 * The before() method is called before controller action.
	 */
	public function before()
	{
		// The action_index() is default
		if ($this->request->action() == 'index')
		{
			$this->request->action('welcome');
		}

		parent::before();
	}

	/**
	 * Prepare welcome page
	 */
	public function action_welcome()
	{
		// If Gleez CMS don't installed
		if ( ! Gleez::$installed)
		{
			// Send to the installer with server status
			$this->request->redirect(Route::get('install')->uri(array('action' => 'index')), 200);
		}

		$this->title = __('Welcome!');
		$content = View::factory('welcome');

		$this->response->body($content);
	}

}
