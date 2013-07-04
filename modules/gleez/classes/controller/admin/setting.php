<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Admin Setting Controller
 *
 * @package    Gleez\Controller\Admin
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Admin_Setting extends Controller_Admin {

	/**
	 * General Settings
	 *
	 * @uses  Config::load
	 * @uses  Message::success
	 * @uses  Route::get
	 * @uses  Route::uri
	 * @uses  Date::date_time_formats
	 * @uses  Date::date_formats
	 * @uses  Date::time_formats
	 * @uses  Date::weeekdays
	 * @uses  Date::timezones
	 * @uses  Template::valid_post
	 * @uses  Request::redirect
	 */
	public function action_index()
	{
		$this->title = __('Settings');
		$config = Kohana::$config->load('site');

		if (isset($config['maintenance_mode']) AND $config['maintenance_mode'] == 1)
		{
			Message::success(__('Site running in maintenance mode!'));
		}

		$action = Route::get('admin/setting')->uri();
		$view = View::factory('admin/settings')
			->set('date_time_formats',  Date::date_time_formats(1))
			->set('date_formats',       Date::date_formats(1))
			->set('time_formats',       Date::time_formats(1))
			->set('date_weekdays',      Date::weeekdays())
			->set('timezones',          Date::timezones())
			->bind('title',             $this->title)
			->set('action',             $action)
			->set('post',               $config);

		if ($this->valid_post('settings'))
		{
			unset($_POST['settings'], $_POST['_token'], $_POST['_action']);

			foreach($_POST as $key => $value)
			{
				$config->set($key, $value);

				if($key == 'front_page' )
				{
					$this->_set_front_page($value);
				}
			}

			Message::success(__('Site configuration updated!'));

			$this->request->redirect(Route::get('admin/setting')->uri());
		}

		$this->response->body($view);
	}

	/**
	 * Sets Front page route
	 *
	 * @param   string  $source  Path for alias
	 * @return  ORM Model_Path
	 *
	 * @uses    Path::delete
	 * @uses    Path::save
	 */
	private function _set_front_page($source)
	{
		// Delete previous alias if any
		Path::delete( array( 'alias' => '<front>' ) );

		// Create and save alias
		$values = array();
		$values['source'] = $source;
		$values['alias']  = '<front>' ;

		return Path::save($values);
	}
}
