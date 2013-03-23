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
	 * @todo remove this practically with large permissions breaks maximum input limit
	 */
	public function action_list()
	{
		$this->title = __('Permissions');

		$view = View::factory('admin/permission/list')
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

	/**
	 * Shows list of permissions per role
	 */
	public function action_role()
	{  	
                $id = $this->request->param('id', 1);
		$role = ORM::factory('role', $id);
		$errors = array();
		
		if( !$role->loaded() ) throw new HTTP_Exception_404('Attempt to access non-existent role.');
		
		if( isset($_POST['permissions']) AND $this->valid_post('role') )
		{
			$per_insert = DB::insert('permissions', array('rid', 'permission', 'module'));   
			
			foreach($_POST['role'] as $key => $val)
			{
				if( isset($val['name']))
				{
					//Message::success( Debug::vars($val) );
					$per_insert->values(array($role->id, $val['name'], $val['module']));
				}
			}

			try
			{
				DB::delete('permissions')->where('rid', '=', $role->id)->execute();
				$per_insert->execute();
				
				Message::success(__('Permissions: saved successful!'));
				$this->request->redirect(Route::get('admin/permission')->uri(array('action' => 'role', 'id' => $role->id)));
			}
			catch(Exception $e)
			{
				Message::error(__('Permissions: saved failed!'));
				$errors = array($e->getMessage());
			}
		}
		
		$role_perms = DB::select()->from('permissions')->as_object()->execute();
		$this->title    = __(':role Permissions', array(":role" => $role->name));
		
		$view   = View::factory('admin/permission/role')
                                        ->set('permissions', ACL::all())
                                        ->bind('errors', $errors)
                                        ->bind('perms', $role_perms)
                                        ->bind('role', $role)
					->bind('id', $id);

		$this->response->body($view);
	}
}
