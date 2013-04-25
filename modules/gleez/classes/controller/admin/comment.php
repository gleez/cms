<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Admin Comment Controller
 *
 * @package    Gleez\Admin\Controller
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Controller_Admin_Comment extends Controller_Admin {

	/**
	 * The before() method is called before controller action.
	 */
	public function before()
	{
		ACL::required('administer comment');

		parent::before();
	}

	/**
	 * The after() method is called after controller action.
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
	 */
	public function action_list()
	{
		$is_datatables = Request::is_datatables();
		$posts = ORM::factory('comment')->where('status', '=', 'publish');
		$redirect    = Route::get('admin/comment')->uri(array('action' => 'list'));
		$destination = '?destination='.$redirect;
		
		if ($is_datatables)
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
				
				$this->_datatables->add_row(
					array(
						Form::checkbox('comments['.$post->id.']', $post->id, isset($_POST['comments'][$post->id]) ),
						HTML::anchor($post->url, $post->title, array('class'=>'action-view','title' => Text::limit_words( $post->rawbody, 128, ' ...'))),
						$author,
						HTML::anchor($post->post->url, $post->post->title, array('class'=>'action-view')),
						date('M d, Y', $post->created),
						HTML::icon($post->edit_url.$destination, 'icon-edit', array('class'=>'action-edit', 'title'=> __('Edit'))),
						HTML::icon($post->delete_url.$destination, 'icon-trash', array('class'=>'action-delete', 'title'=> __('Delete')))
					)
				);
			}
		}

		$this->title = __('Comments');
		$url         = Route::url('admin/comment', array('action' => 'list'), TRUE);

		$bulk_actions = Comment::bulk_actions(TRUE);
		if(isset($bulk_actions['publish'])) unset($bulk_actions['publish']);
		
		$view = View::factory('admin/comment/list')
				->bind('datatables',   $this->_datatables)
				->set('is_datatables', $is_datatables)
				->set('bulk_actions', $bulk_actions)
				->set('destination',  $destination)
				->set('url',           $url);

		$this->response->body($view);
	}

	/**
	 * View comments
	 */
	public function action_view()
	{
		$id = (int) $this->request->param('id', 0);
		$comment = ORM::factory('comment', $id)->access('view');

		if( ! $comment->loaded())
		{
			Message::error( __('Comment: doesn\'t exists!') );
			Kohana::$log->add(Log::ERROR, 'Attempt to access non-existent comment');

			if ( ! $this->_internal)
			{
				$this->request->redirect(Route::get('admin/comment')->uri(array('action' => 'list')));
			}
		}

		$this->title = __('Comment :id', array('id' => $comment->id) );
		$view = View::factory('comment/view')->set('comment', $comment);

		$this->response->body($view);
	}

	/**
	 * Pending comments
	 */
	public function action_pending()
	{
		$posts = ORM::factory('comment')->where('status', '=', 'draft');
		$total = $posts->reset(FALSE)->count_all();

		$this->title = __('Pending Comments');

		if ($total == 0)
		{
			Kohana::$log->add(Log::INFO, 'No comments found');
			$this->response->body(View::factory('admin/comment/none'));

			return;
		}

		$pagination = Pagination::factory(array(
			'current_page'   => array('source'=>'route', 'key'=>'page'),
			'total_items'    => $total,
			'items_per_page' => 30,
		));

		$posts->limit($pagination->items_per_page)->offset($pagination->offset);

		// Apply sorting
		if (Arr::get($_GET, 'sort') AND array_key_exists($_GET['sort'], $posts->list_columns()))
		{
			$order = (Arr::get($_GET, 'order', 'asc') == 'asc') ? 'asc' : 'desc';
			$posts->order_by(Arr::get($_GET, 'sort'), $order);
		}
		else
		{
			$posts->order_by('created', 'DESC');
		}

		$bulk_actions = Comment::bulk_actions(TRUE);

		if(isset($bulk_actions['unpublish']))
		{
			unset($bulk_actions['unpublish']);
		}

		$view = View::factory('admin/comment/list')
				->set('bulk_actions', $bulk_actions)
				->set('destination',  $this->desti)
				->bind('pagination',  $pagination)
				->set('posts',        $posts->find_all());

		$this->response->body($view);
	}

	/**
	 * Spam Comments
	 */
	public function action_spam()
	{
		$posts = ORM::factory('comment')->where('status', '=', 'spam');
		$total = $posts->reset(FALSE)->count_all();

		$this->title = __('Spam Comments');

		if ($total == 0)
		{
			Kohana::$log->add(Log::INFO, 'No comments found');
			$this->response->body( View::factory('admin/comment/none') );
			return;
		}

		$pagination = Pagination::factory(array(
			'current_page'   => array('source'=>'route', 'key'=>'page'),
			'total_items'    => $total,
			'items_per_page' => 30,
		));

		$posts->limit($pagination->items_per_page)->offset($pagination->offset);

		// Apply sorting
		if (Arr::get($_GET, 'sort') AND array_key_exists($_GET['sort'], $posts->list_columns()))
		{
			$order = (Arr::get($_GET, 'order', 'asc') == 'asc') ? 'asc' : 'desc';
			$posts->order_by(Arr::get($_GET, 'sort'), $order);
		}
		else
		{
			$posts->order_by('created', 'DESC');
		}

		$bulk_actions = Comment::bulk_actions(TRUE);

		if(isset($bulk_actions['spam']))
		{
			unset($bulk_actions['spam']);
		}

		$view = View::factory('admin/comment/list')
				->set('bulk_actions', $bulk_actions)
				->set('destination',  $this->desti)
				->bind('pagination',  $pagination)
				->set('posts',        $posts->find_all());

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
			if ( ! $this->_internal)
			{
				$this->request->redirect($redirect);
			}
		}

		if($this->valid_post('comment-bulk-actions'))
		{
			if ( ! isset($post['comments']) OR ( ! is_array($post['comments']) OR ! count(array_filter($post['comments']))))
			{
				$view->errors = array(__('No items selected.'));

				if ( ! $this->_internal)
				{
					$this->request->redirect($redirect);
				}
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
				if ( ! $this->_internal)
				{
					$this->request->redirect($redirect);
				}
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
	 * Excetues the bulk operation
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

			// Excetue the bulk operation
			call_user_func_array($func, $args);
		}
	}
}
