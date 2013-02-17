<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * User Widget
 *
 * @package    User\Widget
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Widget_User extends Widget {

	public function info()
	{
	}
	public function form()
	{
	}

	public function save( array $post )
	{
	}

	public function delete( array $post )
	{
	}

	public function render()
	{
		switch($this->name)
                {
                        case 'login':
                                return $this->login();
                        break;
                        /*case 'online':
                                return $this->online();
                        break;
                        case 'latest':
                                return $this->latest();
                        break;*/
                        default:
                                return;
                }
	}

        public function login()
        {
                // If user already signed-in / dont show the widget on user controller.
		if( Auth::instance()->logged_in() !== FALSE OR Request::current()->controller() === 'user')
                        return;

		$config = Kohana::$config->load('auth');
                return View::factory('user/login')
				->set('errors', array())
				->set('use_username', $config->get('username'))
				->set('providers', array_filter($config->get('providers')) )
				->set('post', ORM::factory('user'));
        }

}