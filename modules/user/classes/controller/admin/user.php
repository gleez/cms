<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Admin User Controller
 *
 * @package   Gleez\User\Admin\Controller
 * @author    Sandeep Sangamreddi - Gleez
 * @copyright (c) 2011-2013 Gleez Technologies
 * @license   http://gleezcms.org/license
 */
class Controller_Admin_User extends Controller_Admin {

	public function before()
	{
		if($this->request->action() == 'index' )
		{
			//$this->request->action('list');
		}
		ACL::Required('administer users');
		parent::before();
	}

	public function action_index()
	{
		$view = 'test';
		$this->response->body($view);
	}

	public function action_list()
	{
		//$this->debug = TRUE;
		$this->title    = __('Users');
		$view           = View::factory('admin/user/list')
						->bind('pagination', $pagination)
						->bind('users', $users);

		$user       = ORM::factory('user');
		$total      = $user->count_all();

		if ($total == 0)
		{
			Kohana::$log->add(Log::INFO, 'No users found');
			$this->response->body( View::factory('admin/user/none') );
			return;
		}

		$pagination = Pagination::factory(array(
			'current_page'   => array('source'=>'route', 'key'=>'page'),
			'total_items' => $total,
			'items_per_page' => 35,
			));

		$users  = $user->order_by('created', 'DESC')->limit($pagination->items_per_page)
							->offset($pagination->offset)->find_all();

                $this->response->body($view);
	}

	public function action_add()
	{
		$this->title = __('Add User');
		$view = View::factory('admin/user/form')
						->bind('all_roles', $all_roles)
						->set('user_roles', array())
						->bind('errors', $errors)
						->bind('post', $post);

		$post = ORM::factory('user');
		$all_roles = ORM::factory('role')->where('id', '>', 1)->find_all()->as_array('name', 'description');

		if( $this->valid_post('user') )
		{
			try
			{
				#Affects the sanitized vars to the user object
				$post->values($_POST);

				#Create the User
				$post->save();

				#Add the login role to the user
				$login_role = new Model_Role(array('name' =>'login'));
				$post->add('roles',$login_role);

				Message::success(__("User: %name saved successful!", array('%name' => $post->name)));

				if ( ! $this->_internal)
					$this->request->redirect(Route::get('admin/user')->uri(array('action' => 'list')));

			}
                        catch (ORM_Validation_Exception $e)
			{
				$errors =  $e->errors('models');
			}
		}

		$this->response->body($view);
	}

	public function action_edit()
	{
		$id = (int) $this->request->param('id', 0);

		$post = ORM::factory('user', (int) $id);

		if(!$post->loaded() OR $id === 1)
		{
			Message::error( __('User: doesn\'t exists!') );
			Kohana::$log->add(Log::ERROR, 'Attempt to access non-existent user');

			if ( ! $this->_internal)
				$this->request->redirect(Route::get('admin/user')->uri(array('action' => 'list')));
		}

		$user_roles = $post->roles->find_all()->as_array('id', 'name');

		$all_roles = ORM::factory('role')
				->where('id', '>', 1)
				->find_all()
				->as_array('name', 'description');

		$this->title = __('Edit User %name', array('%name' => $post->nick));

		$view = View::factory('admin/user/form')
					->set('user_roles', $user_roles)
					->set('all_roles', $all_roles)
					->set('post', $post);

		if ( $this->valid_post('user') )
		{
			try
			{
				// password can be empty - it will be ignored in save.
				if ((empty($_POST['pass']) || (trim($_POST['pass']) == '')) )
				{
					unset($_POST['pass']);
				}

				$post->values($_POST);
				$post->save();

				//make sure to add an empty if none of the roles checked to avoid errros
				if(empty($_POST['roles']))
				{
					$_POST['roles'] = array();
				}

				// roles have to be added separately, and all users have to have the login role
				// you first have to remove the items, otherwise add() will try to add duplicates
				// could also use array_diff, but this is much simpler
				DB::delete('roles_users')->where('user_id', '=', $id)->execute();

				foreach(array_keys($_POST['roles']) as $role)
				{
					// add() executes the query immediately, and saves the data
					$post->add('roles', ORM::factory('role')->where('name', '=', $role)->find());
				}

				//always make sure login role is added if it's not there
				if(!in_array('login', array_keys($_POST['roles'])))
				{
					$post->add('roles', ORM::factory('role')->where('name', '=', 'login')->find());
				}

				Message::success(__("User: %name saved successful!", array('%name' => $post->name)));

				if ( ! $this->_internal)
					$this->request->redirect(Route::get('admin/user')->uri(array('action' => 'list')));

			}
			catch (ORM_Validation_Exception $e)
			{
				$view->errors = count($_POST) ? $e->errors() : array();
			}
		}

		$this->response->body($view);
	}

	public function action_delete()
	{
		$id = (int) $this->request->param('id', 0);

		$user = ORM::factory('user', $id);

		if ( ! $user->loaded())
		{
			Message::error(__("User: doesn't exists!"));
			Kohana::$log->add(Log::ERROR, 'Attempt to access non-existent user');

			if ( ! $this->_internal)
				$this->request->redirect(Route::get('admin/user')->uri( array('action' => 'list') ));
		}
		else if($user->id < 2)
		{
			Message::error(__('User: can\'t delete system user'));
			Kohana::$log->add(Log::ERROR, 'Attempt to delete system user');

			if ( ! $this->_internal)
				$this->request->redirect(Route::get('admin/user')->uri( array('action' => 'list') ));
		}

		$this->title = __('Delete :title', array(':title' => $user->name ));

		$view = View::factory('form/confirm')
				->set('action', Route::url('admin/user', array('action' => 'delete', 'id' => $user->id) ))
				->set('title', $user->name);

		// If deletion is not desired, redirect to list
                if ( isset($_POST['no']) AND $this->valid_post() )
                        $this->request->redirect(Route::get('admin/user')->uri());

		// If deletion is confirmed
                if ( isset($_POST['yes']) AND $this->valid_post() )
                {
			try
			{
				$user->delete();
				Message::success(__('User: :name deleted successful!', array(':name' => $user->name)));

				if ( ! $this->_internal)
					$this->request->redirect(Route::get('admin/user')->uri( array('action' => 'list') ));
			}
			catch (Exception $e)
			{
				Kohana::$log->add(Log::ERROR, 'Error occured deleting user id: :id, :message',
							array(':id' => $user->id, ':message' => $e->getMessage()));
				Message::error('An error occured deleting user, :user.',array(':user' => $user->name));

				if ( ! $this->_internal)
					$this->request->redirect(Route::get('admin/user')->uri( array('action' => 'list') ));
			}
		}

		$this->response->body($view);
	}

}
