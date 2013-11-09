<?php
/**
 * Admin Permission Controller
 *
 * @package    Gleez\User\Admin\Controller
 * @author     Gleez Team
 * @version    1.0.2
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
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
			->bind('errors',     $this->_errors)
			->bind('perms',      $role_perms)
			->bind('roles',      $roles)
			->bind('count',      $total);

		$roles = ORM::factory('role')
			->order_by('name', 'ASC')
			->find_all();

		$total = $roles->count();

		$role_perms = DB::select()
			->from('permissions')
			->as_object()
			->execute();

		$this->response->body($view);
	}

	/**
	 * Shows list of permissions per role
	 *
	 * @throws HTTP_Exception_404
	 */
	public function action_role()
	{
		$id = $this->request->param('id', 1);
		$role = ORM::factory('role', $id);

		if ( ! $role->loaded())
		{
			throw HTTP_Exception::factory(404, 'Attempt to access non-existent role.');
		}

		if (isset($_POST['permissions']) AND $this->valid_post('role'))
		{
			$per_insert = DB::insert('permissions', array('rid', 'permission', 'module'));

			foreach ($_POST['role'] as $key => $val)
			{
				if (isset($val['name']))
				{
					$per_insert->values(array($role->id, $val['name'], $val['module']));
				}
			}

			try
			{
				DB::delete('permissions')->where('rid', '=', $role->id)->execute();
				$per_insert->execute();

				Message::success(__('Permissions saved successfully!'));

				// Redirect to listing
				$this->request->redirect(Route::get('admin/permission')->uri(array('action' => 'role', 'id' => $role->id)));
			}
			catch(ORM_Validation_Exception $e)
			{
				Message::error(__('Permissions save failed!'));
				$this->_errors = array('models', TRUE);
			}
		}

		$role_perms  = DB::select()->from('permissions')->as_object()->execute();
		$this->title = __(':role Permissions', array(':role' => $role->name));

		$view = View::factory('admin/permission/role')
			->set('permissions', ACL::all())
			->bind('errors',     $this->_errors)
			->bind('perms',      $role_perms)
			->bind('role',       $role)
			->bind('id',         $id);

		$this->response->body($view);
	}

	public function action_user()
	{
		$id   = (int) $this->request->param('id', 0);
		$post = ORM::factory('user', $id);

		if ( ! $post->loaded() OR $id === 1)
		{
			Message::error(__("User doesn't exists!"));
			Log::error('Attempt to access non-existent user.');

			$this->request->redirect(Route::get('admin/user')->uri(array('action' => 'list')), 404);
		}

		$this->title = __(':user Permissions', array(":user" => $post->name));
		$action      = Route::get('admin/permission')->uri(array('action' => 'user', 'id' => (isset($post->id) ? $post->id : 0)));

		$view = View::factory('admin/permission/user')
			->set('post',        $post)
			->set('oldperms',    $post->perms())
			->set('permissions', ACL::all())
			->set('action',      $action)
			->bind('errors',     $this->_errors);

		if ($this->valid_post('permissions'))
		{
			$perms = array_filter($_POST['perms']);
			$post->data = array('permissions' => $perms);

			try
			{
				$post->save();
				Message::success(__('Permissions: saved successful!'));

				$this->request->redirect(Route::get('admin/permission')->uri(array('action' => 'user', 'id' => $post->id)));
			}
			catch(ORM_Validation_Exception $e)
			{
				Message::error(__('Permissions save failed!'));

				$this->_errors = $e->errors('models', TRUE);
			}
			catch(Exception $e)
			{
				Message::error(__('Permissions save failed!'));

				$this->_errors = array($e->getMessage()); 
			}
		}

		$this->response->body($view);
	}
}
