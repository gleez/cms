<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Dashboard extends Controller_Admin {

	public function action_index()
	{
		$this->title = __('Administer');
		$view = View::factory('admin/dashboard')->set('widgets', Widgets::instance()->render('dashboard'));
		$this->response->body($view);
	}

} // End Admin