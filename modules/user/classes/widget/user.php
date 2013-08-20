<?php
/**
 * User Widget class
 *
 * @package    User\Widget
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Widget_User extends Widget {

	public function info(){}
	public function form(){}
	public function save(array $post){}
	public function delete(array $post){}

	public function render()
	{
		switch($this->name)
		{
			case 'login':
				return $this->login();
			break;
			default:
				return;
		}
	}

	public function login()
	{
		$auth    = Auth::instance();
		$request = Request::current();

		// If user already signed-in / don't show the widget on user controller.
		if ($auth->logged_in() OR $request->controller() === 'user')
		{
			return;
		}

		Assets::css('user', 'media/css/user.css', array('weight' => 2));

		// Create form action
		$destination = isset($_GET['destination']) ? $_GET['destination'] : Request::initial()->uri();
		$params      = array('action' => 'login');
		$action      = Route::get('user')->uri($params).URL::query(array('destination' => $destination));

		return View::factory('widget/login')
				->set('register',     Config::get('auth.register'))
				->set('use_username', Config::get('auth.username'))
				->set('providers',    array_filter(Config::get('auth.providers')))
				->set('action',       $action)
				->set('post',         ORM::factory('user'))
				->render();
	}
}