<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Admin Permission Controller
 *
 * @package   Gleez\User\Admin\Controller
 * @author    Sandeep Sangamreddi - Gleez
 * @copyright (c) 2011-2013 Gleez Technologies
 * @license   http://gleezcms.org/license
 */
class Controller_Admin_Permission extends Controller_Admin {

	/**
	 * Shows list of permissions
	 */
	public function action_list()
	{
		$this->title = __('Permissions');

		$view = View::factory('admin/permission')
					->set('permissions', ACL::all())
					->bind('errors', $errors)
					->bind('perms', $role_perms)
					->bind('roles', $roles)
					->bind('count', $total);

		$roles = ORM::factory('role')
					->order_by('name', 'ASC')
					->find_all();

		$total = $roles->count();

		$role_perms = DB::select()
						->from('permissions')
						->as_object()
						->execute();

		$errors = array();
		$this->response->body($view);

		if($this->valid_post('roles'))
		{
			$per_insert = DB::insert('permissions', array('rid', 'permission', 'module'));

			foreach ($_POST['roles'] as $id => $role)
			{
				foreach($role as $key => $val)
				{
					if( isset($val['name']))
					{
						$per_insert->values(array($val['id'], $val['name'], $val['module']));
					}
				}
			}

			try
			{
				DB::delete('permissions')->execute();
				$per_insert->execute();

				Message::success(__('Permissions saved successfully!'));
				$this->request->redirect(Route::get('admin/permission')->uri());
			}
			catch(Validate_Exception $e)
			{
				$errors = $e->array->errors('permissions');
			}
		}
	}


}
