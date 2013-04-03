<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Admin Page Controller
 *
 * @package    Gleez\Admin\Controller
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Admin_Page extends Controller_Admin {

	/**
	 * The before() method is called before controller action
	 *
	 * @uses  ACL::required
	 */
	public function before()
	{
		ACL::required('administer page');

		parent::before();
	}

	/**
	 * The after() method is called after controller action
	 *
	 * @uses  Route::url
	 */
	public function after()
	{
		// Tabs
		$this->_tabs =  array(
			array('link' => Route::url('admin/page', array('action' =>'index')), 'text' => __('Statistics')),
			array('link' => Route::url('admin/page', array('action' =>'list')), 'text' => __('List')),
			array('link' => Route::url('admin/page', array('action' =>'settings')),'text' => __('Settings')),
		);

		parent::after();
	}

	/**
	 * Page management dashboard
	 *
	 * Displays Page statistics
	 */
	public function action_index()
	{
		$this->title = __('Page Statistics');

		$view = View::factory('admin/page/stats')
				->bind('stats', $stats);

		$categories = ORM::factory('term')->where('type', '=', 'page')->find_all();
		$tags       = ORM::factory('tag')->where('type', '=', 'page')->find_all();
		$articles   = ORM::factory('page')->where('type', '=', 'page')->find_all();
		$comments   = ORM::factory('comment')->where('type', '=', 'page')->find_all();

		$stats = array();
		$stats['categories']['total'] = count($categories);
		$stats['tags']['total']       = count($tags);
		$stats['articles']['total']   = count($articles);
		$stats['comments']['total']   = count($comments);

		$this->response->body($view);
	}

	/**
	 * Setting the display of pages
	 *
	 * @uses  Arr::merge
	 * @uses  Config::load
	 * @uses  Message::success
	 */
	public function action_settings()
	{
		$this->title = __('Page Settings');

		$post     = Kohana::$config->load('page');
		$action   = Route::url('admin/page', array('action' =>'settings'));
		$vocabs   = array(__('none'));

		$view = View::factory('admin/page/settings')
					->set('vocabs',  $vocabs)
					->set('post',    $post)
					->set('action',  $action);

		$vocabs = Arr::merge($vocabs, ORM::factory('term')->where('lft', '=', 1)->find_all()->as_array('id', 'name'));

		if ($this->valid_post('page_settings'))
		{
			unset($_POST['page_settings'], $_POST['_token'], $_POST['_action']);

			$cats = $post->get('category', array());

			foreach ($_POST as $key => $value)
			{
				if ($key == 'category')
				{
					$terms = array_diff($cats, $value);
					if ($terms)
					{
						DB::delete('posts_terms')
							->where('parent_id', 'IN', array_values($terms))
							->execute();
					}
				}
				$post->set($key, $value);
			}

			Message::success(__('Page Settings updated!'));

			if ( ! $this->_internal)
			{
				$this->request->redirect(Route::url('admin/page', array('action' =>'settings')), 200);
			}
		}

		$this->response->body($view);
	}

	/**
	 * Displays list of pages
	 */
	public function action_list()
	{
		$this->title = __('Page List');

		$posts = ORM::factory('page')
				->where('type', '=', 'page');

		$total = $posts->reset(FALSE)->count_all();

		if ($total == 0)
		{
			Kohana::$log->add(Log::INFO, 'No posts found');
			$this->response->body(View::factory('admin/page/none'));
			return;
		}

		$pagination = Pagination::factory(array(
			'current_page'   => array('source'=>'route', 'key'=>'page'),
			'total_items'    => $total,
			'items_per_page' => 30,
		));

		$posts->limit($pagination->items_per_page)->offset($pagination->offset);

		// and apply sorting
		if (Arr::get($_GET, 'sort') AND array_key_exists($_GET['sort'], $posts->list_columns()))
		{
			$order = (Arr::get($_GET, 'order', 'asc') == 'asc') ? 'asc' : 'desc';
			$posts->order_by(Arr::get($_GET, 'sort'), $order);
		}
		else
		{
			$posts->order_by('updated', 'DESC');
		}

		$view = View::factory('admin/page/list')
				->bind('pagination', $pagination)
				->set('destination', array('destination' => $this->request->uri()))
				->set('actions',     Post::bulk_actions(TRUE, 'page'))
				->set('params',      array('action' => 'list'))
				->set('posts',       $posts->find_all());

		$dest = ($this->request->query('destination') !== NULL) ?
					array('destination' => $this->request->query('destination')) : array();
		$route = Route::get('admin/page')->uri(array('action' => 'list'));
		$redirect = empty($dest) ? $route : $this->request->query('destination') ;
		$post = $this->request->post();

		// If deletion is not desired, redirect to list
		if (isset($post['no']) AND $this->valid_post())
		{
			$this->request->redirect($redirect);
		}

		// If deletion is confirmed
		if (isset($post['yes']) AND $this->valid_post())
		{
			$pages = array_filter($post['items']);

			Post::bulk_delete($pages, 'page');

			Message::success(__('The delete has been performed!'));

			if ( ! $this->_internal)
			{
				$this->request->redirect($redirect);
			}
		}

		if ($this->valid_post('page-bulk-actions'))
		{
			if ( ! isset($post['posts']) OR ( ! is_array($post['posts']) OR ! count(array_filter($post['posts']))))
			{
				$view->errors = array(__('No items selected.'));
				if ( ! $this->_internal)
				{
					$this->request->redirect($this->request->uri());
				}
			}

			try
			{
				if ($post['operation'] == 'delete')
				{
					$pages = array_filter($post['posts']); // Filter out unchecked posts
					$this->title = __('Delete Pages');

					$items = DB::select('id', 'title')->from('posts')
								->where('id', 'IN', $pages)->execute()->as_array('id', 'title');

					$view = View::factory('form/confirm_multi')->set('action', '')->set('items', $items );

					$this->response->body($view);
					return;
				}

				$this->_bulk_update($post);

				Message::success(__('The update has been performed!'));
				if ( ! $this->_internal)
				{
					$this->request->redirect($this->request->uri());
				}
			}
			catch( Exception $e)
			{
				Message::error(__('The update has not been performed!'));
			}
		}

		$this->response->body($view);
	}

	/**
	 * Bulk updates
	 *
	 * @param  array  $post
	 */
	private function _bulk_update($post)
	{
		$operations = Post::bulk_actions(FALSE);
		$operation  = $operations[$post['operation']];
		$pages = array_filter($post['posts']); // Filter out unchecked pages

		if ($operation['callback'])
		{
			list($func, $params) = Arr::callback($operation['callback']);
			if (isset($operation['arguments']))
			{
				$args = array_merge(array($pages), $operation['arguments']);
			}
			else
			{
				$args = array($pages);
			}

			// set model name
			$args['type'] = 'page';

			// excetue the bulk operation
			call_user_func_array($func, $args);
		}
	}

}
