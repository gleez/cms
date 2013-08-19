<?php
/**
 * Admin Comment Controller
 *
 * @package    Gleez\Controller\Admin
 * @author     Gleez Team
 * @version    1.1.2
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Admin_Comment extends Controller_Admin {

	/**
	 * The before() method is called before controller action
	 *
	 * @uses  ACL::required
	 */
	public function before()
	{
		ACL::required('administer comment');

		$this->_destination = '?destination='.Route::get('admin/comment')->uri(array('action' => $this->request->action()));
		$this->_form_action = Route::get('admin/comment')->uri(array('action' => 'process')).$this->_destination;

		parent::before();
	}

	/**
	 * The after() method is called after controller action
	 *
	 * @uses  Route::get
	 * @uses  Route::uri
	 */
	public function after()
	{
		$this->_tabs =  array(
			array('link' => Route::get('admin/comment')->uri(array('action' =>'list')), 'text' => __('Approved')),
			array('link' => Route::get('admin/comment')->uri(array('action' =>'pending')), 'text' => __('Pending')),
			array('link' => Route::get('admin/comment')->uri(array('action' =>'spam')), 'text' => __('Spam')),
		);

		parent::after();
	}

	/**
	 * List comments
	 *
	 * @uses  Assets::popup
	 */
	public function action_list()
	{
		Assets::popup();

		$this->_prepare_list(ORM::factory('comment')->where('status', '=', 'publish'));

		$this->title = __('Comments');

		$view = View::factory('admin/comment/list')
				->bind('datatables',   $this->_datatables)
				->set('is_datatables', Request::is_datatables())
				->set('bulk_actions',  Comment::bulk_actions(TRUE))
				->set('action',        $this->_form_action)
				->set('url',           Route::url('admin/comment', array('action' => 'list'), TRUE));

		$this->response->body($view);
	}

	/**
	 * View comments
	 */
	public function action_view()
	{
		$id = (int) $this->request->param('id', 0);
		$comment = ORM::factory('comment', $id)->access();

		if( ! $comment->loaded())
		{
			Log::error('Attempt to access non-existent comment.');
			Message::error( __('Comment doesn\'t exists!') );

			// Redirect to listing
			$this->request->redirect(Route::get('admin/comment')->uri());
		}

		$this->title = __('Comment :name', array(':name' => Text::limit_chars($comment->title, 40)));
		$view = View::factory('comment/view')->set('comment', $comment);

		$this->response->body($view);
	}

	/**
	 * Pending comments
	 *
	 * @uses  Assets::popup
	 */
	public function action_pending()
	{
		Assets::popup();

		$this->_prepare_list(ORM::factory('comment')->where('status', '=', 'draft'));

		$this->title = __('Pending Comments');

		$view = View::factory('admin/comment/list')
			->bind('datatables',   $this->_datatables)
			->set('is_datatables', Request::is_datatables())
			->set('bulk_actions',  Comment::bulk_actions(TRUE))
			->set('action',        $this->_form_action)
			->set('url',           Route::url('admin/comment', array('action' => 'pending'), TRUE));

		$this->response->body($view);
	}

	/**
	 * Spam Comments
	 *
	 * @uses  Assets::popup
	 */
	public function action_spam()
	{
		Assets::popup();

		$this->_prepare_list(ORM::factory('comment')->where('status', '=', 'spam'));

		$this->title = __('Spam Comments');

		$view = View::factory('admin/comment/list')
			->bind('datatables',   $this->_datatables)
			->set('is_datatables', Request::is_datatables())
			->set('bulk_actions',  Comment::bulk_actions(TRUE))
			->set('action',        $this->_form_action)
			->set('url',           Route::url('admin/comment', array('action' => 'pending'), TRUE));

		$this->response->body($view);
	}

	/**
	 * Process actions
	 */
	public function action_process()
	{
		$route = Route::get('admin/comment')->uri(array('action' => 'list'));
		$redirect = empty($this->redirect) ? $route : $this->redirect ;
		$post = $this->request->post();

		// If deletion is not desired, redirect to list
		if (isset($post['no']) AND $this->valid_post())
		{
			$this->request->redirect($redirect);
		}

		// If deletion is confirmed
		if (isset($post['yes']) AND $this->valid_post())
		{
			$comments = array_filter($post['items']);

			ORM::factory('comment')->where('id', 'IN', $comments)->delete_all();
			Module::event('comment_bulk_delete', $comments);

			Message::success(__('The delete has been performed!'));

			$this->request->redirect($redirect);
		}

		if ($this->valid_post('comment-bulk-actions'))
		{
			if ( ! isset($post['comments']) OR ( ! is_array($post['comments']) OR ! count(array_filter($post['comments']))))
			{
				$this->_errors = array(__('No items selected.'));

				$this->request->redirect($redirect);
			}

			try
			{
				if($post['operation'] == 'delete')
				{
					// Filter out unchecked comments
					$comments = array_filter($post['comments']);
					$this->title = __('Delete Comments');

					$items = DB::select('id', 'title')->from('comments')
							->where('id', 'IN', $comments)
							->execute()
							->as_array('id', 'title');

					$view = View::factory('form/confirm_multi')
								->set('action', '')
								->set('items', $items);

					$this->response->body($view);

					return;
				}

				$this->_bulk_update($post);

				Message::success(__('The update has been performed!'));

				$this->request->redirect($redirect);
			}
			catch( Exception $e)
			{
				Message::error(__('The update has not been performed!'));
			}
		}

	}

	/**
	 * Bulk update
	 *
	 * Executes the bulk operation
	 *
	 * @param  array $post  Array of comments
	 * @uses   Comment::bulk_actions
	 * @uses   Arr::callback
	 * @uses   Arr::merge
	 */
	private function _bulk_update($post)
	{
		// Filter out unchecked comments
		$comments = array_filter($post['comments']);
		$operations = Comment::bulk_actions(FALSE);
		$operation = $operations[$post['operation']];

		if ($operation['callback'])
		{
			list($func, $params) = Arr::callback($operation['callback']);
			if (isset($operation['arguments']))
			{
				$args = Arr::merge(array($comments), $operation['arguments']);
			}
			else
			{
				$args = array($comments);
			}

			// Execute the bulk operation
			call_user_func_array($func, $args);
		}
	}

	/**
	 * Prepare DataTables list
	 *
	 * @param  ORM  $posts Posts
	 *
	 * @uses   Request::is_datatables
	 * @uses   ORM::dataTables
	 * @uses   Datatables::result
	 * @uses   Datatables::add_row
	 * @uses   HTML::anchor
	 * @uses   Form::checkbox
	 * @uses   Text::limit_words
	 * @uses   Date::formatted_time
	 * @uses   HTML::icon
	 * @uses   I18n::__
	 * @uses   Route::get
	 * @uses   Route::uri
	 */
	private function _prepare_list(ORM $posts)
	{
		if (Request::is_datatables())
		{
			$this->_datatables = $posts->dataTables(array('id', 'title', 'author', 'guest_name', 'created'));

			foreach ($this->_datatables->result() as $post)
			{
				if ($post->author == 1 AND ! is_null($post->guest_name))
				{
					$author = HTML::anchor($post->guest_url, $post->guest_name, array()) . __(' (not verified)');
				}
				else
				{
					$author = HTML::anchor(Route::get('user')->uri(array('action' => 'profile', 'id' => $post->author)), $post->user->nick, array());
				}

				$this->_datatables->add_row(array(
						Form::checkbox('comments['.$post->id.']', $post->id, isset($_POST['comments'][$post->id]) ),
						HTML::anchor($post->url, Text::limit_chars($post->title,40), array('class'=>'action-view','title' => Text::limit_chars($post->rawbody, 120))),
						$author,
						HTML::anchor($post->post->url, $post->post->title, array('class'=>'action-view')),
						Date::formatted_time($post->created),
						HTML::icon($post->edit_url.$this->_destination, 'icon-edit', array('class'=>'action-edit', 'title'=> __('Edit'))),
						HTML::icon($post->delete_url.$this->_destination, 'icon-trash', array('class'=>'action-delete', 'title'=> __('Delete'), 'data-toggle' => 'popup', 'data-table' => '#admin-list-comments'))
				));
			}
		}
	}
}
