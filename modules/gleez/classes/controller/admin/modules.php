<?php
/**
 * Admin Modules Controller
 *
 * @package    Gleez\Controller\Admin
 * @author     Gleez Team
 * @version    1.0.3
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Admin_Modules extends Controller_Admin {

	/**
	 * Module list
	 *
	 * @uses  Cache::delete
	 * @uses  Module::load_modules
	 * @uses  Module::available
	 * @uses  Route::uri
	 * @uses  Route::get
	 */
	public function action_list()
	{
		// Clear any cache for sure
		// Note: Gleez Caching only available in production
		Cache::instance()->delete('load_modules');

		// Load modules
		Module::load_modules(TRUE);

		$this->title = __('Modules');
		$action      = Route::get('admin/module')->uri(array('action' => 'confirm'));

		$view = View::factory('admin/module/list')
			->set('available', Module::available())
			->set('action',    $action);

		$this->response->body($view);
	}

	/**
	 * Confirm action
	 *
	 * @throws  HTTP_Exception_403
	 *
	 * @uses    Arr::get
	 * @uses    Module::available
	 * @uses    Module::is_active
	 * @uses    Module::can_deactivate
	 * @uses    Module::is_active
	 * @uses    Module::can_activate
	 * @uses    Cache::delete_all
	 * @uses    Route::uri
	 * @uses    Route::get
	 * @uses    Request::redirect
	 */
	public function action_confirm()
	{
		if ( ! $this->valid_post('modules'))
		{
			throw HTTP_Exception::factory(403, 'Unauthorized attempt to access action.');
		}

		$messages = array("error" => array(), "warn" => array());
		$desired_list = array();

		foreach (Module::available() as $module_name => $info)
		{
			if ($info->locked)
			{
				continue;
			}

			if ($desired = Arr::get($_POST, $module_name) == 1)
			{
				$desired_list[] = $module_name;
			}

			if ($info->active AND ! $desired AND Module::is_active($module_name))
			{
				$messages = Arr::merge($messages, Module::can_deactivate($module_name));
			}
			else if (!$info->active AND $desired AND ! Module::is_active($module_name))
			{
				$messages = Arr::merge($messages, Module::can_activate($module_name));
			}
		}

		// Clear any cache for sure
		Cache::instance()->delete_all();

		if (empty($messages["error"]) AND empty($messages["warn"]))
		{
			$this->_do_save();
			$result["reload"] = 1;

			$this->request->redirect(Route::get('admin/module')->uri(), 200);
		}
		else
		{
			$v = new View('admin_modules_confirm.html');
			$v->messages = $messages;
			$v->modules = $desired_list;
			$result["dialog"] = (string) $v;
			$result["allow_continue"] = empty($messages["error"]);
		}
	}

	/**
	 * Do save
	 *
	 * @uses  Arr::get
	 * @uses  Module::available
	 * @uses  Module::is_active
	 * @uses  Module::deactivate
	 * @uses  Module::is_installed
	 * @uses  Module::upgrade
	 * @uses  Module::install
	 * @uses  Module::activate
	 * @uses  Module::event
	 * @uses  Cache::delete_all
	 * @uses  Log::add
	 * @uses  Gleez_Exception::text
	 */
	private function _do_save()
	{
		$changes = new stdClass();
 		$changes->activate = array();
		$changes->deactivate = array();
		$activated_names = array();
		$deactivated_names = array();

		foreach (Module::available() as $module_name => $info)
		{
			if ($info->locked)
			{
				continue;
			}

			try
			{
				$desired = Arr::get($_POST, $module_name) == 1;

				if ($info->active AND ! $desired AND Module::is_active($module_name))
				{
					Module::deactivate($module_name);
					$changes->deactivate[] = $module_name;
					$deactivated_names[] = __($info->name);
				}
				elseif ( ! $info->active AND $desired AND ! Module::is_active($module_name))
				{
					if (Module::is_installed($module_name))
					{
						Module::upgrade($module_name);
					}
					else
					{
						Module::install($module_name);
					}

					Module::activate($module_name);

					$changes->activate[] = $module_name;
					$activated_names[] = __($info->name);
				}
			}
			catch (Exception $e)
			{
				Log::error(Gleez_Exception::text($e));
			}
		}

		Module::event('module_change', $changes);

		// @todo This type of collation is questionable from an i18n perspective
		if ($activated_names)
		{
			Message::success(__('Activated: %names', array('%names' => join(", ", $activated_names))));
		}
		if ($deactivated_names)
		{
			Message::success(__('Deactivated: %names', array('%names' => join(", ", $deactivated_names))));
		}

		// Clear any cache for sure
		Cache::instance()->delete_all();
	}

}
