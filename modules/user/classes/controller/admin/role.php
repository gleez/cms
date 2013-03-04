<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Admin Role Controller
 *
 * @package   Gleez\User\Admin\Controller
 * @author    Sandeep Sangamreddi - Gleez
 * @copyright (c) 2011-2013 Gleez Technologies
 * @license   http://gleezcms.org/license
 */
class Controller_Admin_Role extends Controller_Admin {


	public function before()
	{
		ACL::Required('administer users');

		parent::before();
	}

	public function action_list()
	{
		$this->title = __('Roles');
		$view = View::factory('admin/role/list')
				->bind('pagination', $pagination)
				->bind('roles', $roles);

		$role       = ORM::factory('role');
		$total      = $role->count_all();

		if ($total == 0)
		{
			Kohana::$log->add(Kohana::INFO, 'No roles found');
			$this->request->response = View::factory('admin/role/none');
			return;
		}

		$pagination = Pagination::factory(array(
			'current_page'   => array('source'=>'route', 'key'=>'page'),
			'total_items' => $total,
			'items_per_page' => 5,
			));

		$roles  = $role->order_by('name', 'ASC')->limit($pagination->items_per_page)
						->offset($pagination->offset)->find_all();

                $this->response->body($view);
	}

	public function action_add()
	{
		$view = View::factory('admin/role/form')
					->set('errors', array() )
					->bind('post', $post);

		$this->title = __('Add Role');
		$post = ORM::factory('role')->values($_POST);

		if( $this->valid_post('role') )
		{
			try
			{
				$post->save();
				Message::success(__('Role: :name saved successful!', array(':name' => $post->name)));

				if ( ! $this->_internal)
					$this->request->redirect(Route::get('admin/role')->uri());

			}
			catch (ORM_Validation_Exception $e)
			{
				$view->errors = $e->errors('permissions');
			}
		}

                $this->response->body($view);
	}

	public function action_edit()
	{
		$id = (int) $this->request->param('id', 0);

		$post = ORM::factory('role', $id);

		if(!$post->loaded())
		{
			Message::error(__("Role: doesn't exists!"));
			Kohana::$log->add(Log::ERROR, 'Attempt to access non-existent role');

			if ( ! $this->_internal)
				$this->request->redirect(Route::get('admin/role')->uri());
		}

		$this->title = __('Edit role :name', array(':name' => $post->name));

		$view = View::factory('admin/role/form')
					->set('errors', array())
					->bind('post', $post);

		if ( $this->valid_post('role') )
		{
			$post->values($_POST);

			try
			{
				$post->save();

				Message::success(__('Role: :name saved successful!', array(':name' => $post->name)));

				if ( ! $this->_internal)
					$this->request->redirect(Route::get('admin/role')->uri());

			}
			catch (ORM_Validation_Exception $e)
			{
				$view->errors = $e->errors();
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
			Kohana::$log->add(Log::ERROR, 'Attempt to access non-existent role');
			$this->request->redirect(Route::get('admin/role')->uri());
		}

		$this->title = __('Delete :title', array(':title' => $role->name ));

		$view = View::factory('form/confirm')
						->set('action', Route::url('admin/role',
								array('action' => 'delete', 'id' => $role->id)
								))
						->set('title', $role->name);

		// If deletion is not desired, redirect to list
                if ( isset($_POST['no']) AND $this->valid_post() )
                        $this->request->redirect(Route::get('admin/role')->uri());

                // If deletion is confirmed
                if ( isset($_POST['yes']) AND $this->valid_post() )
                {
                        try
                        {
                               	$role->delete(); //delete the role
				Message::success(__('Role: :name deleted successful!', array(':name' => $role->name)));

				if ( ! $this->_internal)
					$this->request->redirect(Route::get('admin/role')->uri());
                        }
                        catch (Exception $e)
                        {
				Kohana::$log->add(Log::ERROR, 'Error occured deleting role id: :id, :message',
							array(':id' => $role->id, ':message' => $e->getMessage()));
				Message::error('An error occured deleting blog, :post.',array(':post' => $post->title));

				if ( ! $this->_internal)
					$this->request->redirect(Route::get('admin/role')->uri());
                        }
                }

                $this->response->body($view);
	}

}
