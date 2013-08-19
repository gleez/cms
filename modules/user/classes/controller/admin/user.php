<?php
/**
 * Admin User Controller
 *
 * @package   Gleez\User\Admin\Controller
 * @author    Gleez Team
 * @version   1.0.1
 * @copyright (c) 2011-2013 Gleez Technologies
 * @license   http://gleezcms.org/license
 */
class Controller_Admin_User extends Controller_Admin {

	/**
	 * The before() method is called before controller action
	 *
	 * @uses  ACL::required
	 */
	public function before()
	{
		ACL::required('administer users');

		parent::before();
	}

	/**
	 * Displays a list of all users
	 *
	 * @uses  Request::is_datatables
	 * @uses  ORM::dataTables
	 * @uses  Text::plain
	 * @uses  User::roles
	 * @uses  HTML::anchor
	 * @uses  Route::url
	 * @uses  Assets::popup
	 */
	public function action_list()
	{
		$is_datatables = Request::is_datatables();

		if ($is_datatables)
		{
			$users = ORM::factory('user');

			// @todo fix dummy id column for roles to match the column order
			$this->_datatables = $users->dataTables(array('name', 'mail', 'created', 'login', 'id', 'status'));

			foreach ($this->_datatables->result() as $user)
			{
				$this->_datatables->add_row(
					array(
						HTML::anchor($user->url, Text::plain($user->nick)),
						Text::auto_link($user->mail),
						Date::formatted_time($user->created, 'M d, Y'),
						($user->login > 0) ? Date::formatted_time($user->login, 'M d, Y') : __('Never'),
						User::roles($user),
						$user->status == 1 ? '<span class="status-active"><i class="icon-ok-sign"></i></span>' : '<span class="status-blocked"><i class="icon-ban-circle"></i></span>',
						HTML::icon(Route::get('admin/user')->uri(array('action' => 'edit', 'id' => $user->id)), 'icon-edit',  array('class'=>'action-edit', 'title'=> __('Edit User'))) . '&nbsp;' .
						HTML::icon(Route::get('admin/permission')->uri(array('action' => 'user', 'id' => $user->id)), 'icon-key',  array('class'=>'', 'title'=> __('Edit Permission'))) . '&nbsp;' .
						HTML::icon($user->delete_url, 'icon-trash', array('class'=>'action-delete', 'title'=> __('Delete User'), 'data-toggle' => 'popup', 'data-table' => '#admin-list-users'))
					)
				);
			}
		}

		Assets::popup();

		$this->title = __('Users');
		$url         = Route::url('admin/user', array('action' => 'list'), TRUE);

		$view = View::factory('admin/user/list')
				->bind('datatables',   $this->_datatables)
				->set('is_datatables', $is_datatables)
				->set('url',           $url);

		$this->response->body($view);
	}

	/**
	 * Add new user
	 */
	public function action_add()
	{
		$this->title = __('Add User');
		$view = View::factory('admin/user/form')
						->bind('all_roles', $all_roles)
						->set('user_roles', array())
						->bind('errors',    $this->_errors)
						->bind('post',      $post);

		$post = ORM::factory('user');
		$all_roles = ORM::factory('role')
					->where('id', '>', 1)
					->find_all()
					->as_array('name', 'description');

		if ($this->valid_post('user'))
		{
			try
			{
				// Affects the sanitized vars to the user object
				$post->values($_POST);

				// Create the User
				$post->save();

				// Add the login role to the user
				$login_role = new Model_Role(array('name' =>'login'));
				$post->add('roles',$login_role);

				Message::success(__("User %name saved successful!", array('%name' => $post->name)));

				$this->request->redirect(Route::get('admin/user')->uri(array('action' => 'list')), 200);
			}
			catch (ORM_Validation_Exception $e)
			{
				$this->_errors = $e->errors('models', TRUE);
			}
		}

		$this->response->body($view);
	}

	/**
	 * Edit user
	 */
	public function action_edit()
	{
		$id = (int) $this->request->param('id', 0);

		$post = ORM::factory('user', (int) $id);

		if ( ! $post->loaded() OR $id === 1)
		{
			Message::error(__("User doesn't exists!"));
			Log::error('Attempt to access non-existent user');

			$this->request->redirect(Route::get('admin/user')->uri(array('action' => 'list')), 404);
		}

		$user_roles = $post->roles->find_all()->as_array('id', 'name');

		$all_roles = ORM::factory('role')
					->where('id', '>', 1)
					->find_all()
					->as_array('name', 'description');

		$this->title = __('Edit User %name', array('%name' => $post->nick));

		$view = View::factory('admin/user/form')
					->set('user_roles', $user_roles)
					->set('all_roles',  $all_roles)
					->set('post',       $post)
					->bind('errors',    $this->_errors);

		if ($this->valid_post('user'))
		{
			try
			{
				// password can be empty - it will be ignored in save.
				if ((empty($_POST['pass']) || (trim($_POST['pass']) == '')))
				{
					unset($_POST['pass']);
				}

				$post->values($_POST);
				$post->save();

				// Make sure to add an empty if none of the roles checked to avoid errros
				if (empty($_POST['roles']))
				{
					$_POST['roles'] = array();
				}

				// Roles have to be added separately, and all users have to have the login role
				// you first have to remove the items, otherwise add() will try to add duplicates
				// could also use array_diff, but this is much simpler
				DB::delete('roles_users')->where('user_id', '=', $id)->execute();

				foreach(array_keys($_POST['roles']) as $role)
				{
					// add() executes the query immediately, and saves the data
					$post->add('roles', ORM::factory('role')->where('name', '=', $role)->find());
				}

				// Always make sure login role is added if it's not there
				if ( ! in_array('login', array_keys($_POST['roles'])))
				{
					$post->add('roles', ORM::factory('role')->where('name', '=', 'login')->find());
				}

				Message::success(__("User %name saved successful!", array('%name' => $post->name)));

				$this->request->redirect(Route::get('admin/user')->uri());
			}
			catch (ORM_Validation_Exception $e)
			{
				$this->_errors = $e->errors('models', TRUE);
			}
		}

		$this->response->body($view);
	}

	/**
	 * Delete user
	 */
	public function action_delete()
	{
		$id = (int) $this->request->param('id', 0);

		$user = ORM::factory('user', $id);

		if ( ! $user->loaded())
		{
			Message::error(__("User doesn't exists!"));
			Log::error('Attempt to access non-existent user.');

			$this->request->redirect(Route::get('admin/user')->uri());
		}
		// If it is an external request and id < 3
		elseif ($user->id < 3)
		{
			Message::error(__("You can't delete system users!"));
			Log::error('Attempt to delete system user.');

			$this->request->redirect(Route::get('admin/user')->uri());
		}

		$this->title = __('Delete :title', array(':title' => $user->name));

		$view = View::factory('form/confirm')
				->set('action',$user->delete_url)
				->set('title', $user->name);

		// If deletion is not desired, redirect to list
		if (isset($_POST['no']) AND $this->valid_post())
		{
			$this->request->redirect(Route::get('admin/user')->uri());
		}

		// If deletion is confirmed
		if (isset($_POST['yes']) AND $this->valid_post())
		{
			try
			{
				$user->delete();
				Message::success(__('User %name deleted successful!', array('%name' => $user->name)));

				$this->request->redirect(Route::get('admin/user')->uri());
			}
			catch (Exception $e)
			{
				Log::error('Error occurred deleting user id: :id, :message',
					array(':id' => $user->id,':message' => $e->getMessage())
				);
				$this->_errors = array(__('An error occurred deleting user %user: :message',
					array(
						'%user'    => $user->name,
						':message' => $e->getMessage()
					)
				));
				$this->request->redirect(Route::get('admin/user')->uri());
			}
		}

		$this->response->body($view);
	}

}
