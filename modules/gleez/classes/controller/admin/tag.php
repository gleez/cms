<?php
/**
 * Admin Tag Controller
 *
 * @package    Gleez\Controller\Admin
 * @author     Gleez Team
 * @version    1.0.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Admin_Tag extends Controller_Admin {

	/**
	 * The before() method is called before controller action
	 *
	 * @uses  ACL::required
	 */
	public function before()
	{
		ACL::required('administer tags');

		parent::before();
	}

	/**
	 * List tags
	 *
	 * @uses  Request::is_datatables
	 * @uses  ORM::dataTables
	 * @uses  Text::plain
	 * @uses  HTML::icon
	 * @uses  Route::url
	 * @uses  Route::get
	 * @uses  Assets::popup
	 */
	public function action_list()
	{
		Assets::popup();

		$is_datatables = Request::is_datatables();

		if ($is_datatables)
		{
			$tags = ORM::factory('tag');
			$this->_datatables = $tags->dataTables(array('name', 'id', 'type'));

			foreach ($this->_datatables->result() as $tag)
			{
				$this->_datatables->add_row(
					array(
						Text::plain($tag->name),
						HTML::anchor($tag->url, $tag->url),
						Text::plain($tag->type),

						HTML::icon($tag->edit_url, 'icon-edit', array('class'=>'action-edit', 'title'=> __('Edit Tag'))).'&nbsp;'.
						HTML::icon($tag->delete_url, 'icon-trash', array('class'=>'action-delete', 'title'=> __('Delete Tag'), 'data-toggle' => 'popup', 'data-table' => '#admin-list-tags'))
					)
				);
			}
		}

		$this->title = __('Tags');
		$url         = Route::url('admin/tag', array('action' => 'list'), TRUE);

		$view = View::factory('admin/tag/list')
				->bind('datatables',   $this->_datatables)
				->set('is_datatables', $is_datatables)
				->set('url',           $url);

		$this->response->body($view);
	}

	/**
	 * Add new tag
	 *
	 * @uses  Message::success
	 * @uses  Route::url
	 * @uses  Route::get
	 * @uses  Request::redirect
	 */
	public function action_add()
	{
		$this->title = __('Add New Tag');
		$post = ORM::factory('tag');
		$action = Route::get('admin/tag')->uri(array('action' => 'add'));

		if ($this->valid_post('tag'))
		{
			$post->values($_POST);
			try
			{
				$post->save();
				Message::success(__('Tag %name saved successful!', array('%name' => $post->name)));
				$this->request->redirect(Route::get('admin/tag')->uri(), 200);
			}
			catch (ORM_Validation_Exception $e)
			{
				$this->_errors = $e->errors('models', TRUE);
			}
		}

		$view = View::factory('admin/tag/form')
				->set('post',   $post)
				->set('action', $action)
				->set('errors', $this->_errors)
				->set('path', 	FALSE);
		
		$this->response->body($view);
	}

	/**
	 * Edit tag
	 *
	 * @uses  Message::success
	 * @uses  Message::error
	 * @uses  Route::url
	 * @uses  Route::get
	 * @uses  Request::redirect
	 * @uses  Log::add
	 */
	public function action_edit()
	{
		$id = (int) $this->request->param('id', 0);
		$post = ORM::factory('tag', $id);

		if ( ! $post->loaded())
		{
			Log::error('Attempt to access non-existent tag.');
			Message::error(__('Tag doesn\'t exists!'));

			$this->request->redirect(Route::get('admin/tag')->uri(), 404);
		}

		$this->title = __('Edit Tag %name', array('%name' => $post->name));

		if ($this->valid_post('tag'))
		{
			$post->values($_POST);
			try
			{
				$post->save();

				Log::info('Tag :name saved successful.', array(':name' => $post->name));
				Message::success(__('Tag %name saved successful!', array('%name' => $post->name)));

				$this->request->redirect(Route::get('admin/tag')->uri(), 200);
			}
			catch (ORM_Validation_Exception $e)
			{
				$this->_errors = $e->errors('models', TRUE);
			}
		}

		$view = View::factory('admin/tag/form')
					->set('post',    $post)
					->set('action',  $post->edit_url)
					->set('errors',  $this->_errors)
					->set('path', 	 $post->url);
		
		$this->response->body($view);
	}

	/**
	 * Delete tag
	 *
	 * @uses  Message::error
	 * @uses  Message::success
	 * @uses  Route::url
	 * @uses  Route::get
	 * @uses  Request::redirect
	 */
	public function action_delete()
	{
		$id = (int) $this->request->param('id', 0);
		$tag = ORM::factory('tag', $id);

		if ( ! $tag->loaded())
		{
			Log::error('Attempt to access non-existent tag.');
			Message::error(__('Tag doesn\'t exists!'));

			$this->request->redirect(Route::get('admin/tag')->uri(), 404);
		}

		$this->title = __('Delete Tag %title', array('%title' => $tag->name));

		$view = View::factory('form/confirm')
				->set('action', $tag->delete_url)
				->set('title',  $tag->name);

		// If deletion is not desired, redirect to list
		if (isset($_POST['no']) AND $this->valid_post())
		{
			$this->request->redirect(Route::get('admin/tag')->uri());
		}

		// If deletion is confirmed
		if (isset($_POST['yes']) AND $this->valid_post())
		{
			try
			{
				$tag->delete();
				Message::success(__('Tag %name deleted successful!', array('%name' => $tag->name)));
				$this->request->redirect(Route::get('admin/tag')->uri(), 200);
			}
			catch (Exception $e)
			{
				Log::error('Error occurred deleting tag id: :id, :msg',
					array(':id' => $tag->id, ':msg' => $e->getMessage())
				);
				Message::error('An error occurred deleting tag %tag',array('%tag' => $tag->name));
				$this->_errors = array(__('An error occurred deleting tag %tag',array('%tag' => $tag->name)));

				$this->request->redirect(Route::get('admin/tag')->uri(), 503);
			}
		}

		$this->response->body($view);
	}

}
