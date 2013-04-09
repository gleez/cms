<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Admin Tag Controller
 *
 * @package    Gleez\Admin\Controller
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Controller_Admin_Tag extends Controller_Admin {

	/**
	 * The before() method is called before controller action.
	 */
	public function before()
	{
		ACL::required('administer tags');

		parent::before();
	}

	/**
	 * List tags
	 */
	public function action_list()
	{
		$is_datatables = Request::is_datatables();

		if ($is_datatables)
		{
			$tags       = ORM::factory('tag');
			$this->_datatables = $tags->dataTables(array('name', 'id', 'type'));
			
			foreach ($this->_datatables->result() as $tag)
			{
				$this->_datatables->add_row(
					array(
						Text::plain($tag->name),
						HTML::anchor($tag->url, $tag->url),
						Text::plain($tag->type),

						HTML::anchor(Route::get('admin/tag')->uri(array('action' => 'edit', 'id' => $tag->id)), '<i class="icon-edit"></i>', array('class'=>'action-edit', 'title'=> __('Edit Tag'))) .
						HTML::anchor(Route::get('admin/tag')->uri(array('action' => 'delete', 'id' => $tag->id)), '<i class="icon-trash"></i>', array('class'=>'action-delete', 'title'=> __('Delete Tag')))
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

	public function action_add()
	{
		$post = ORM::factory('tag');
		$action = Route::get('admin/tag')->uri(array('action' => 'add'));

		if( $this->valid_post('tag') )
		{
			try
			{
				$post->values($_POST)->save();

				Message::success(__('Tag: %name saved successful!', array('%name' => $post->name)));

				if ( ! $this->_internal)
					$this->request->redirect(Route::get('admin/tag')->uri(array('action' => 'list')));

			}
                        catch (ORM_Validation_Exception $e)
			{
				$this->_errors =  $e->errors('models');
			}
		}

		$view = View::factory('admin/tag/form')
				->set('post',   $post)
				->set('action', $action)
				->set('errors', $this->_errors)
				->set('path', 	FALSE);
		
		$this->response->body($view);
	}

	public function action_edit()
	{
		$id = (int) $this->request->param('id', 0);
		$post = ORM::factory('tag', (int) $id);

		if( !$post->loaded() )
		{
			Message::error( __('Tag: doesn\'t exists!') );
			Kohana::$log->add(Log::ERROR, 'Attempt to access non-existent tag');

			if ( ! $this->_internal)
				$this->request->redirect(Route::get('admin/tag')->uri(array('action' => 'list')));
		}


		$this->title = __('Edit Tag %name', array('%name' => $post->name));
		$action = Route::get('admin/tag')->uri(array('action' => 'edit', $id => $id));

		if ( $this->valid_post('tag') )
		{
			try
			{
				$post->values($_POST)->save();

				Message::success(__('Tag: %name saved successful!', array('%name' => $post->name)));

				if ( ! $this->_internal)
					$this->request->redirect(Route::get('admin/tag')->uri(array('action' => 'list')));

			}
			catch (ORM_Validation_Exception $e)
			{
				$this->_errors = $e->errors();
			}
		}

		$view = View::factory('admin/tag/form')
					->set('post',    $post)
					->set('action',  $action)
					->set('errors',  $this->_errors)
					->set('path', 	 $post->url);
		
		$this->response->body($view);
	}

	public function action_delete()
	{
		$id = (int) $this->request->param('id', 0);
		$tag = ORM::factory('tag', $id);

		if ( ! $tag->loaded())
		{
			Message::error(__('Tag: doesn\'t exists!'));
			Kohana::$log->add(Log::ERROR, 'Attempt to access non-existent tag');

			if ( ! $this->_internal)
				$this->request->redirect(Route::get('admin/tag')->uri( array('action' => 'list') ));
		}

		$this->title = __('Delete Tag :title', array(':title' => $tag->name ));

		$view = View::factory('form/confirm')
				->set('action', Route::url('admin/tag', array('action' => 'delete', 'id' => $tag->id) ))
				->set('title', $tag->name);

		// If deletion is not desired, redirect to list
                if ( isset($_POST['no']) AND $this->valid_post() )
                        $this->request->redirect(Route::get('admin/tag')->uri());

		// If deletion is confirmed
                if ( isset($_POST['yes']) AND $this->valid_post() )
                {
			try
			{
				$tag->delete();
				Message::success(__('Tag: :name deleted successful!', array(':name' => $tag->name)));

				if ( ! $this->_internal)
					$this->request->redirect(Route::get('admin/tag')->uri( array('action' => 'list') ));
			}
			catch (Exception $e)
			{
				Kohana::$log->add(Log::ERROR, 'Error occured deleting tag id: :id, :message',
							array(':id' => $tag->id, ':message' => $e->getMessage()));
				Message::error('An error occured deleting tag, :tag.',array(':tag' => $tag->name));

				if ( ! $this->_internal)
					$this->request->redirect(Route::get('admin/tag')->uri( array('action' => 'list') ));
			}
		}

		$this->response->body($view);
	}

}
