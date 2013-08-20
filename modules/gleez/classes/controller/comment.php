<?php
/**
 * Comment Controller
 *
 * @package    Gleez\Controller
 * @author     Gleez Team
 * @version    1.0.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Comment extends Template {

	/**
	 * The before() method is called before controller action
	 *
	 * @uses  ACL::required
	 */
	public function before()
	{
		ACL::required('access comment');

		parent::before();
	}

	public function action_view()
	{
		$id       = (int) $this->request->param('id', 0);
		$comment  = ORM::factory('comment', $id)->access();
		$route    =  Route::get('comment')->uri(array('action' => 'list'));

		if ( ! $comment->loaded())
		{
			Log::error('Attempt to access non-existent comment.');
			Message::error(__('Comment doesn\'t exists!'));

			$this->request->redirect($route, 404);
		}

		$this->title = $comment->title;
		$view = View::factory('comment/view')
					->set('auth',    Auth::instance())
					->set('comment', $comment);

		$this->response->body($view);
	}

	/**
	 * Edit comment
	 *
	 * @uses  Request::query
	 * @uses  Route::get
	 * @uses  Route::uri
	 * @uses  URL::query
	 * @uses  Message::success
	 * @uses  Log::add
	 */
	public function action_edit()
	{
		$id = (int) $this->request->param('id', 0);
		$comment  = ORM::factory('comment', $id)->access('edit');

		// Set form destination
		$destination = ( ! is_null($this->request->query('destination'))) ? array('destination' => $this->request->query('destination')) : array();
		// Set form action
		$action = Route::get('comment')->uri(array('id' => $id, 'action' => 'edit')).URL::query($destination);

		$this->title = __('Edit Comment');
		$view = View::factory('comment/form')
					->set('use_captcha',  FALSE)
					->set('is_edit',      TRUE)
					->set('auth',         Auth::instance())
					->set('item',         $comment)
					->set('action',       $action)
					->set('destination',  $destination)
					->bind('errors',      $this->_errors)
					->bind('post',        $comment);

		if ($this->valid_post('comment'))
		{
			try
			{
				/** @var $comment ORM */
				$comment->values($_POST)->save();

				Log::info('Comment: :title updated.', array(':title' => $comment->title));
				Message::success(__('Comment %title has been updated.', array('%title' => $comment->title)));

				$this->request->redirect(empty($destination) ? $comment->url : $this->request->query('destination'));
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

				Log::info('Comment: :title deleted.', array(':title' => $title));
				Message::success(__('Comment %title deleted successful!', array('%title' => $title)));
			}
			catch (Exception $e)
			{
				Log::error('Error occurred deleting comment id: :id, :msg',
					array(':id' => $comment->id, ':msg' => $e->getMessage())
				);
				Message::error('An error occurred deleting comment %post.',array('%post' => $title));

				$this->_errors = array('An error occurred deleting comment %post.',array('%post' => $title));
			}

			$redirect = empty($destination) ? $redirect : $this->redirect;

			$this->request->redirect($redirect);
		}

		$this->response->body($view);
	}

}