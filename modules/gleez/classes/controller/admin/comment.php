<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Admin Comment Controller
 *
 * @package   Gleez\Admin\Controller
 * @author    Sandeep Sangamreddi - Gleez
 * @copyright (c) 2011-2013 Gleez Technologies
 * @license   http://gleezcms.org/license
 */
class Controller_Admin_Comment extends Controller_Admin {

        public function before()
	{
		ACL::Required('administer comment');
		parent::before();
	}

        public function action_list()
        {
                $posts   = ORM::factory('comment')->where('status', '=', 'publish');
                $total   = $posts->reset(FALSE)->count_all();

		$this->title    = __('Comments');


		if ($total == 0)
		{
			Kohana::$log->add(Log::INFO, 'No comments found');
			$this->response->body( View::factory('comment/none') );
			return;
		}

		$pagination = Pagination::factory(array(
				'current_page'   => array('source'=>'route', 'key'=>'page'),
				'total_items'    => $total,
				'items_per_page' => 30,
				));

		$posts->limit($pagination->items_per_page)->offset($pagination->offset);

		// and apply sorting
		if (Arr::get($_GET, 'sort') AND array_key_exists($_GET['sort'], $posts->list_columns())) {
			$order = (Arr::get($_GET, 'order', 'asc') == 'asc') ? 'asc' : 'desc';
			$posts->order_by(Arr::get($_GET, 'sort'), $order);
		}
		else
		{
			$posts->order_by('created', 'DESC');
		}

		$bulk_actions = Comment::bulk_actions(TRUE);
		if(isset($bulk_actions['publish'])) unset($bulk_actions['publish']);

                $view           = View::factory('admin/comment/list')
						->set('bulk_actions', $bulk_actions)
						->set('destination', $this->desti)
						->bind('pagination', $pagination)
						->set('posts',      $posts->find_all() );

                $this->response->body($view);
        }

        public function action_view()
        {
		$id = (int) $this->request->param('id', 0);
		$comment  = ORM::factory('comment', $id)->access('view');

                if( !$comment->loaded() )
		{
			Message::error( __('Comment: doesn\'t exists!') );
			Kohana::$log->add(Log::ERROR, 'Attempt to access non-existent comment');

			if ( ! $this->_internal)
				$this->request->redirect(Route::get('admin/comment')->uri(array('action' => 'list')));
		}

		$this->title = __('Comment :id', array('id' => $comment->id) );
		$view = View::factory('comment/view')->set('comment', $comment);

		$this->response->body($view);
	}

	public function action_pending()
        {
                $posts   = ORM::factory('comment')->where('status', '=', 'draft');
                $total   = $posts->reset(FALSE)->count_all();

		$this->title = __('Pending Comments');

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

		// and apply sorting
		if (Arr::get($_GET, 'sort') AND array_key_exists($_GET['sort'], $posts->list_columns())) {
			$order = (Arr::get($_GET, 'order', 'asc') == 'asc') ? 'asc' : 'desc';
			$posts->order_by(Arr::get($_GET, 'sort'), $order);
		}
		else
		{
			$posts->order_by('created', 'DESC');
		}

		$bulk_actions = Comment::bulk_actions(TRUE);
		if(isset($bulk_actions['unpublish'])) unset($bulk_actions['unpublish']);

                $view           = View::factory('admin/comment/list')
						->set('bulk_actions', $bulk_actions)
						->set('destination', $this->desti)
						->bind('pagination', $pagination)
						->set('posts',      $posts->find_all() );

		$this->response->body($view);
        }

	public function action_spam()
        {
                $posts   = ORM::factory('comment')->where('status', '=', 'spam');
                $total   = $posts->reset(FALSE)->count_all();

		$this->title    = __('Spam Comments');

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

		// and apply sorting
		if (Arr::get($_GET, 'sort') AND array_key_exists($_GET['sort'], $posts->list_columns())) {
			$order = (Arr::get($_GET, 'order', 'asc') == 'asc') ? 'asc' : 'desc';
			$posts->order_by(Arr::get($_GET, 'sort'), $order);
		}
		else
		{
			$posts->order_by('created', 'DESC');
		}

		$bulk_actions = Comment::bulk_actions(TRUE);
		if(isset($bulk_actions['spam'])) unset($bulk_actions['spam']);

                $view           = View::factory('admin/comment/list')
						->set('bulk_actions', $bulk_actions)
						->set('destination', $this->desti)
						->bind('pagination', $pagination)
						->set('posts',      $posts->find_all() );

		$this->response->body($view);
        }

	public function action_process()
	{
		$route = Route::get('admin/comment')->uri(array('action' => 'list'));
		$redirect = empty($this->redirect) ? $route : $this->redirect ;
		$post = $this->request->post();

		// If deletion is not desired, redirect to list
                if ( isset($post['no']) AND $this->valid_post() )  $this->request->redirect( $redirect );

		// If deletion is confirmed
                if ( isset($post['yes']) AND $this->valid_post() )
                {
			$comments = array_filter($post['items']);

			ORM::factory('comment')->where('id', 'IN', $comments)->delete_all();
			Module::event('comment_bulk_delete', $comments);

			Message::success(__('The delete has been performed!'));
			if ( ! $this->_internal) $this->request->redirect( $redirect );
		}

		if( $this->valid_post('comment-bulk-actions') )
		{
			if ( !isset($post['comments']) OR ( !is_array($post['comments']) OR !count(array_filter($post['comments'])) ) )
			{
				$view->errors = array( __('No items selected.') );
				if ( ! $this->_internal)  $this->request->redirect( $redirect );
			}

			try
			{
				if($post['operation'] == 'delete')
				{
					$comments = array_filter($post['comments']); // Filter out unchecked comments
					$this->title = __('Delete Comments');

					$items = DB::select('id', 'title')->from('comments')
						->where('id', 'IN', $comments)->execute()->as_array('id', 'title');

					$view = View::factory('form/confirm_multi')->set('action', '')->set('items', $items );

					$this->response->body( $view );
					return;
				}

				$this->_bulk_update($post);

				Message::success(__('The update has been performed!'));
				if ( ! $this->_internal)  $this->request->redirect( $redirect );
			}
			catch( Exception $e)
			{
				Message::error(__('The update has not been performed!'));
			}
		}

	}

	private function _bulk_update($post)
	{
		$comments = array_filter($post['comments']); // Filter out unchecked comments
		$operations = Comment::bulk_actions(FALSE);
		$operation  = $operations[$post['operation']];

		if ( $operation['callback'] )
		{
			list($func, $params) = Arr::callback($operation['callback']);
			if (isset($operation['arguments']))
			{
				$args = array_merge(array($comments), $operation['arguments']);
			}
			else
			{
				$args = array($comments);
			}

			//excetue the bulk operation
			call_user_func_array($func, $args);
		}
	}

	public function after()
	{
		$this->_tabs =  array(
			array('link' => Route::url('admin/comment', array('action' =>'list')), 'text' => __('Approved')),
			array('link' => Route::url('admin/comment', array('action' =>'pending')), 'text' => __('Pending')),
                        array('link' => Route::url('admin/comment', array('action' =>'spam')), 'text' => __('Spam')),
                );

		parent::after();
	}

}
