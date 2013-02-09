<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Admin Tag Controller
 *
 * @package   Gleez\Admin\Controller
 * @author    Sandeep Sangamreddi - Gleez
 * @copyright (c) 2011-2013 Gleez Technologies
 * @license   http://gleezcms.org/license
 */
class Controller_Admin_Tag extends Controller_Admin {

	public function before()
	{
		ACL::Required('administer tags');
		parent::before();
	}

	public function action_list()
	{
		$this->title    = __('Tags');
		$view           = View::factory('admin/tag/list')
						->bind('pagination', $pagination)
						->bind('tags', $tags);

		$tag       = ORM::factory('tag');
		$total      = $tag->count_all();

		if ($total == 0)
		{
			Kohana::$log->add(Log::INFO, 'No tags found');
			$this->response->body( View::factory('admin/tag/none') );
			return;
		}

		$pagination = Pagination::factory(array(
			'current_page'   => array('source'=>'route', 'key'=>'page'),
			'total_items' => $total,
			'items_per_page' => 25,
			));

		$tags  = $tag->order_by('name', 'ASC')->limit($pagination->items_per_page)
							->offset($pagination->offset)->find_all();

                $this->response->body($view);
	}

	public function action_add()
	{
		$this->title = __('Add Tag');
		$view = View::factory('admin/tag/form')->bind('errors', $errors)->bind('post', $post);

		$post = ORM::factory('tag');

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
				$errors =  $e->errors('models');
			}
		}

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
		$view = View::factory('admin/tag/form')->bind('errors', $errors)->bind('post', $post);

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
				$errors = $e->errors();
			}
		}

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
