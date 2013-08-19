<?php
/**
 * Admin Role Controller
 *
 * @package   Gleez\User\Admin\Controller
 * @author    Gleez Team
 * @version   1.0.1
 * @copyright (c) 2011-2013 Gleez Technologies
 * @license   http://gleezcms.org/license
 */
class Controller_Admin_Role extends Controller_Admin {

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
	 * List user roles
	 *
	 * @uses  Request::is_datatables
	 * @uses  ORM::dataTables
	 */
	public function action_list()
	{
		$is_datatables = Request::is_datatables();

		if ($is_datatables)
		{
			$roles = ORM::factory('role');
			$this->_datatables = $roles->dataTables(array('name', 'description', 'special'));

			foreach ($this->_datatables->result() as $role)
			{
				$this->_datatables->add_row(
					array(
						Text::plain($role->name),
						Text::plain($role->description),
						$role->special ? '<i class="icon-ok-sign"></i>' : '<i class="icon-ban-circle"></i>',

						$role->special
							? HTML::icon($role->perm_url, 'icon-lock', array('class'=>'icon-large', 'title'=> __('Edit Permissions')))
							: HTML::icon($role->edit_url, 'icon-edit', array('class'=>'icon-large', 'title'=> __('Edit Role'))) . '&nbsp;' .
							  HTML::icon($role->delete_url, 'icon-trash', array('class'=>'icon-large', 'title'=> __('Delete Role'))) . '&nbsp;' .
							  HTML::icon($role->perm_url, 'icon-lock', array('class'=>'icon-large', 'title'=> __('Edit Permissions')))
					)
				);
			}
		}

		$this->title = __('Roles');
		$add_url = Route::get('admin/role')->uri(array('action' =>'add'));
		$url = Route::url('admin/role', array('action' => 'list'), TRUE);

		$view = View::factory('admin/role/list')
				->bind('datatables',   $this->_datatables)
				->set('is_datatables', $is_datatables)
				->set('add_url',       $add_url)
				->set('url',           $url);


		$this->response->body($view);
	}

	/**
	 * Add new role
	 *
	 * @uses  Message::success
	 * @uses  Log:add
	 * @uses  Route::get
	 * @uses  Route::uri
	 */
	public function action_add()
	{
		$action = Route::get('admin/role')->uri(array('action' => 'add'));

		$view = View::factory('admin/role/form')
					->set('action',  $action)
					->bind('post',   $post)
					->bind('errors', $this->_errors);

		$this->title = __('Add Role');
		$post = ORM::factory('role');

		if ($this->valid_post('role'))
		{
			$post->values($_POST);
			try
			{
				$post->save();
				Message::success(__('Role %name saved successful!', array('%name' => $post->name)));

				$this->request->redirect(Route::get('admin/role')->uri(), 200);
			}
			catch (ORM_Validation_Exception $e)
			{
				$this->_errors = $e->errors('models', TRUE);
			}
		}

		$this->response->body($view);
	}

	/**
	 * Add new role
	 *
	 * @uses  Message::success
	 * @uses  Message::error
	 * @uses  Log:add
	 * @uses  Route::get
	 * @uses  Route::uri
	 */
	public function action_edit()
	{
		$id = (int) $this->request->param('id', 0);

		$post = ORM::factory('role', $id);

		if(!$post->loaded())
		{
			Message::error(__("Role doesn't exists!"));
			Log::error('Attempt to access non-existent role.');

			$this->request->redirect(Route::get('admin/role')->uri());
		}

		$this->title = __('Edit role %name', array('%name' => $post->name));
		$action = Route::get('admin/role')->uri(array('id' => $post->id, 'action' => 'edit'));

		$view = View::factory('admin/role/form')
					->set('action', $action)
					->set('errors', $this->_errors)
					->bind('post',  $post);

		if ( $this->valid_post('role') )
		{
			$post->values($_POST);

			try
			{
				$post->save();

				Message::success(__('Role %name updated successful!', array('%name' => $post->name)));

				$this->request->redirect(Route::get('admin/role')->uri(), 200);
			}
			catch (ORM_Validation_Exception $e)
			{
				$this->_errors = $e->errors('models', TRUE);
			}
		}

		$this->response->body($view);
	}

	public function action_delete()
	{
		$id = (int) $this->request->param('id', 0);

		$role = ORM::factory('role', $id);

		if ( ! $role->loaded())
		{
			Message::error(__('Role: doesn\'t exists!'));
			Log::error('Attempt to access non-existent role.');
			$this->request->redirect(Route::get('admin/role')->uri());
		}

		$this->title = __('Delete :title', array(':title' => $role->name ));

		$view = View::factory('form/confirm')
						->set('action', Route::url('admin/role', array('action' => 'delete', 'id' => $role->id)))
						->set('title', $role->name);

		// If deletion is not desired, redirect to list
		if (isset($_POST['no']) AND $this->valid_post())
		{
			$this->request->redirect(Route::get('admin/role')->uri());
		}

		// If deletion is confirmed
		if (isset($_POST['yes']) AND $this->valid_post())
		{
			try
			{
				$role->delete(); //delete the role
				Message::success(__('Role: :name deleted successful!', array(':name' => $role->name)));

				$this->request->redirect(Route::get('admin/role')->uri());
			}
			catch (Exception $e)
			{
				Log::error('Error occured deleting role id: :id, :message',
					array(':id' => $role->id, ':message' => $e->getMessage())
				);
				Message::error('An error occured deleting blog, :post.',array(':post' => $post->title));

				$this->request->redirect(Route::get('admin/role')->uri());
			}
		}

		$this->response->body($view);
	}
}
