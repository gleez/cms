<?php
/**
 * Admin Dashboard Controller
 *
 * @package    Gleez\Controller\Admin
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Admin_Dashboard extends Controller_Admin {

	public function action_index()
	{
		$this->title = __('Administer');

		$view = View::factory('admin/dashboard')
			->set('widgets', Widgets::instance()->render('dashboard'));

		$this->response->body($view);
	}

}
