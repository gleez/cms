<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Comment Controller
 *
 * @package    Gleez\Controller
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Comment extends Template {

	protected $_route;

	public function before()
	{
		ACL::required('access comment');
		$this->_route = Route::get('comment')->uri(array('action' => 'list'));

		parent::before();
	}

	public function action_view()
	{
		$id = (int) $this->request->param('id', 0);
		$comment  = ORM::factory('comment', $id)->access();

		if ( ! $comment->loaded())
		{
			Message::error(__('Comment doesn\'t exists!'));
			Kohana::$log->add(Log::ERROR, 'Attempt to access non-existent comment');

			if ( ! $this->_internal)
			{
				$this->request->redirect($this->_route, 404);
			}
		}

		$this->title = $comment->title;
		$view = View::factory('comment/view')
					->set('auth',    Auth::instance())
					->set('comment', $comment);

		$this->response->body($view);
	}

	public function action_edit()
	{
		$id = (int) $this->request->param('id', 0);
		$comment  = ORM::factory('comment', $id)->access('edit');

		if ( ! $comment->loaded())
		{
			Message::error(__('Comment doesn\'t exists!'));
			Kohana::$log->add(Log::ERROR, 'Attempt to access non-existent comment');

			if ( ! $this->_internal)
			{
				$this->request->redirect($this->_route, 404);
			}
		}

		$this->title = __('Edit Comment');
		$view = View::factory('comment/form')
					->set('use_captcha',  FALSE)
					->set('is_edit',      TRUE)
					->set('auth',         Auth::instance())
					->set('item',         $comment)
					->set('action',       Request::current()->uri())
					->set('destination',  array('destination' => $this->redirect))
					->bind('errors',      $this->_errors)
					->bind('post',        $comment);

		if ($this->valid_post('comment'))
		{
			$redirect = empty($this->redirect) ? $this->_route : $this->redirect ;

			try
			{
				$comment->values($_POST)->save();
				Message::success(__('Comment has been updated.'));
				Kohana::$log->add(LOG::INFO, 'Comment: :title updated.', array(':title' => $comment->title));

				if ( ! $this->_internal)
				{
					$this->request->redirect($redirect, 200);
				}
			}
			catch (ORM_Validation_Exception $e)
			{
				$this->_errors = $e->errors('models', TRUE);
				Message::error(__('Please see the errors below!'));
			}
		}

		$this->response->body($view);
	}

	public function action_delete()
	{
		$id          = (int) $this->request->param('id', 0);
		$comment     = ORM::factory('comment', $id)->access('delete');
		$this->title = __('Are you absolutely sure?');
		$destination = empty($this->redirect) ? array() : array('destination' => $this->redirect);
		$post        = $this->request->post();
		$route       = Route::get('comment')->uri(array('action' => 'view', 'id' => $comment->id));

		$view = View::factory('form/confirm')
				->set('action', Route::get('comment')->uri(array('action' => 'delete', 'id' => $comment->id)).URL::query($destination))
				->set('title', $comment->title);

		// If deletion is not desired, redirect to post
		if (isset($post['no']) AND $this->valid_post())
		{
			$this->request->redirect(empty($this->redirect) ? $route : $this->redirect);
		}

		// If deletion is confirmed
		if (isset($post['yes']) AND $this->valid_post())
		{
			$redirect = $comment->post->url;
			$title = $comment->title;

			try
			{
				$comment->delete();
				Message::success(__('Comment %title deleted successful!', array('%title' => $title)));
				Kohana::$log->add(LOG::INFO, 'Comment: :title deleted.', array(':title' => $title));
			}
			catch (Exception $e)
			{
				Kohana::$log->add(LOG::ERROR, 'Error occurred deleting comment id: :id, :message',
					array(':id' => $comment->id, ':message' => $e->getMessage()));
				Message::error('An error occurred deleting comment %post.',array('%post' => $title));
			}

			$redirect = empty($destination) ? $redirect : $this->redirect;

			if ( ! $this->_internal)
			{
				$this->request->redirect($redirect);
			}
		}

		$this->response->body($view);
	}

}