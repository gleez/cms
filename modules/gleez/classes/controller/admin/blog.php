<?php
/**
 * Admin Blog Controller
 *
 * @package    Gleez\Controller\Admin
 * @author     Gleez Team
 * @version    1.0.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Admin_Blog extends Controller_Admin {

	/**
	 * The before() method is called before controller action
	 *
	 * @uses  ACL::required
	 */
	public function before()
	{
		ACL::required('administer blog');

		parent::before();
	}

	/**
	 * The after() method is called after controller action.
	 *
	 * @uses  Route::url
	 */
	public function after()
	{
		$this->_tabs =  array(
			array('link' => Route::url('admin/blog', array('action' =>'index')), 'text' => __('Statistics')),
			array('link' => Route::url('admin/blog', array('action' =>'list')), 'text' => __('List')),
			array('link' => Route::url('admin/blog', array('action' =>'settings')),'text' => __('Settings')),
		);

		parent::after();
	}

	/**
	 * Blog management dashboard, display Blog statistics
	 */
	public function action_index()
	{
		$this->title = __('Blog Statistics');

		$view = View::factory('admin/blog/stats')
				->bind('stats', $stats);

		$categories = ORM::factory('term')->where('type', '=', 'blog')->find_all();
		$tags       = ORM::factory('tag')->where('type', '=', 'blog')->find_all();
		$articles   = ORM::factory('blog')->where('type', '=', 'blog')->find_all();
		$comments   = ORM::factory('comment')->where('type', '=', 'blog')->find_all();

		$stats = array();
		$stats['categories']['total'] = count($categories);
		$stats['tags']['total']       = count($tags);
		$stats['articles']['total']   = count($articles);
		$stats['comments']['total']   = count($comments);

		$this->response->body($view);
	}

	/**
	 * Blog settings
	 *
	 * @uses  Config::load
	 * @uses  Config_Group::get
	 * @uses  Config_Group::set
	 * @uses  Template::valid_post
	 * @uses  Arr::merge
	 * @uses  Request::redirect
	 * @uses  Route::get
	 * @uses  Route::uri
	 */
	public function action_settings()
	{
		$this->title = __('Blog Settings');

		$config = Kohana::$config->load('blog');
		$action = Route::get('admin/blog')->uri(array('action' =>'settings'));
		$vocabs = array(__('none'));

		$use_captcha       = (isset($config['use_captcha']) AND $config['use_captcha'] == 1) ? TRUE : FALSE;
		$use_authors       = (isset($config['use_authors']) AND $config['use_authors'] == 1) ? TRUE : FALSE;
		$use_comment       = (isset($config['use_comment']) AND $config['use_comment'] == 1) ? TRUE : FALSE;
		$use_category      = (isset($config['use_category']) AND $config['use_category'] == 1) ? TRUE : FALSE;
		$use_excerpt       = (isset($config['use_excerpt']) AND $config['use_excerpt'] == 1) ? TRUE : FALSE;
		$use_tags          = (isset($config['use_tags']) AND $config['use_tags'] == 1) ? TRUE : FALSE;
		$use_submitted     = (isset($config['use_submitted']) AND $config['use_submitted'] == 1) ? TRUE : FALSE;
		$comment_anonymous = (isset($config['comment_anonymous']) AND $config['comment_anonymous'] == 1) ? TRUE : FALSE;
		$use_cache         = (isset($config['use_cache']) AND $config['use_cache'] == 1) ? TRUE : FALSE;
		$primary_image     = (isset($config['primary_image']) AND $config['primary_image'] == 1) ? TRUE : FALSE;
		$comment1          = (isset($config['comment']) && $config['comment'] == 0) ? TRUE : FALSE;
		$comment2          = (isset($config['comment']) && $config['comment'] == 1) ? TRUE : FALSE;
		$comment3          = (isset($config['comment']) && $config['comment'] == 2) ? TRUE : FALSE;
		$mode1             = (isset($config['comment_default_mode']) && $config['comment_default_mode'] == 1) ? TRUE : FALSE;
		$mode2             = (isset($config['comment_default_mode']) && $config['comment_default_mode'] == 2) ? TRUE : FALSE;
		$mode3             = (isset($config['comment_default_mode']) && $config['comment_default_mode'] == 3) ? TRUE : FALSE;
		$mode4             = (isset($config['comment_default_mode']) && $config['comment_default_mode'] == 4) ? TRUE : FALSE;

		$view   = View::factory('admin/blog/settings')
					->bind('vocabs',            $vocabs)
					->set('config',             $config)
					->set('action',             $action)
					->set('use_captcha',        $use_captcha)
					->set('use_authors',        $use_authors)
					->set('use_comment',        $use_comment)
					->set('use_category',       $use_category)
					->set('use_excerpt',        $use_excerpt)
					->set('use_tags',           $use_tags)
					->set('use_submitted',      $use_submitted)
					->set('comment_anonymous',  $comment_anonymous)
					->set('use_cache',          $use_cache)
					->set('primary_image',      $primary_image)
					->set('comment1',           $comment1)
					->set('comment2',           $comment2)
					->set('comment3',           $comment3)
					->set('mode1',              $mode1)
					->set('mode2',              $mode2)
					->set('mode3',              $mode3)
					->set('mode4',              $mode4);

		$vocabs = Arr::merge($vocabs, ORM::factory('term')->where('lft', '=', 1)->where('type', '=', 'blog')->find_all()->as_array('id', 'name'));

		if ($this->valid_post('blog_settings'))
		{
			unset($_POST['blog_settings'], $_POST['_token'], $_POST['_action']);

			$cats = $config->get('category', array());

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
				$config->set($key, $value);
			}

			Log::info('Blog Settings updated.');
			Message::success(__('Blog Settings updated!'));

			$this->request->redirect(Route::get('admin/blog')->uri(array('action' =>'settings')));
		}

		$this->response->body($view);
	}

	/**
	 * Blog list
	 *
	 * @uses  Route::url
	 * @uses  Route::get
	 * @uses  Route::uri
	 * @uses  Request::is_datatables
	 * @uses  Form::checkbox
	 * @uses  HTML::anchor
	 * @uses  HTML::label
	 * @uses  HTML::icon
	 * @uses  Post::bulk_actions
	 * @uses  Assets::popup
	 */
	public function action_list()
	{
		Assets::popup();

		$this->title = __('Blog List');

		$url         = Route::url('admin/blog', array('action' => 'list'), TRUE);
		$redirect    = Route::get('admin/blog')->uri(array('action' => 'list'));
		$action      = Route::get('admin/blog')->uri(array('action' => 'bulk'));
		$destination = '?destination='.$redirect;

		$is_datatables = Request::is_datatables();
		$blogs = ORM::factory('blog');

		if ($is_datatables)
		{
			$this->_datatables = $blogs->dataTables(array('id', 'title', 'author', 'status', 'updated'));

			foreach ($this->_datatables->result() as $blog)
			{
				$this->_datatables->add_row(
					array(
						Form::checkbox('blogs['.$blog->id.']', $blog->id, isset($_POST['blogs'][$blog->id])),
						HTML::anchor($blog->url, $blog->title),
						HTML::anchor($blog->user->url, $blog->user->nick),
						HTML::label(__($blog->status), $blog->status),
						Date::formatted_time($blog->updated, 'M d, Y'),
						HTML::icon($blog->edit_url.$destination, 'icon-edit', array('class'=>'action-edit', 'title'=> __('Edit Blog'))) . '&nbsp;' .
						HTML::icon($blog->delete_url.$destination, 'icon-trash', array('class'=>'action-delete', 'title'=> __('Delete Blog'), 'data-toggle' => 'popup', 'data-table' => '#admin-list-blogs'))
					)
				);
			}
		}

		$view = View::factory('admin/blog/list')
			->bind('datatables',   $this->_datatables)
			->set('is_datatables', $is_datatables)
			->set('action',        $action)
			->set('actions',       Post::bulk_actions(TRUE, 'blog'))
			->set('url',           $url);

		$this->response->body($view);
	}

	/**
	 * Perform bulk actions
	 *
	 * @uses  Route::get
	 * @uses  Route::uri
	 * @uses  Request::redirect
	 * @uses  Message::success
	 * @uses  Message::error
	 * @uses  Post::bulk_delete
	 * @uses  DB::select
	 */
	public function action_bulk()
	{
		$redirect = Route::get('admin/blog')->uri(array('action' => 'list'));

		$this->title = __('Bulk Actions');
		$post = $this->request->post();

		// If deletion is not desired, redirect to list
		if (isset($post['no']) AND $this->valid_post())
		{
			$this->request->redirect($redirect);
		}

		// If deletion is confirmed
		if (isset($post['yes']) AND $this->valid_post())
		{
			$blogs = array_filter($post['items']);

			Post::bulk_delete($blogs, 'blog');

			Message::success(__('The delete has been performed!'));

			$this->request->redirect($redirect);
		}

		if ($this->valid_post('blog-bulk-actions'))
		{
			if(isset($post['operation']) AND empty($post['operation']))
			{
				Message::error(__('No bulk operation selected.'));
				$this->request->redirect($redirect);
			}
			
			if ( ! isset($post['blogs']) OR ( ! is_array($post['blogs']) OR ! count(array_filter($post['blogs']))))
			{
				Message::error(__('No blogs selected.'));
				$this->request->redirect($redirect);
			}

			try
			{
				if ($post['operation'] == 'delete')
				{
					$blogs = array_filter($post['blogs']); // Filter out unchecked posts
					$this->title = __('Delete Blogs');

					$items = DB::select('id', 'title')->from('posts')
						->where('id', 'IN', $blogs)->execute()->as_array('id', 'title');

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
		//always redirect to list, if no action performed
		$this->request->redirect($redirect);
	}

	/**
	 * Bulk updates
	 *
	 * @param  array  $post
	 * @uses   Post::bulk_actions
	 * @uses   Arr::callback
	 */
	private function _bulk_update($post)
	{
		$operations = Post::bulk_actions(FALSE, 'blog');
		$operation  = $operations[$post['operation']];
		$blogs = array_filter($post['blogs']); // Filter out unchecked pages

		if ($operation['callback'])
		{
			list($func, $params) = Arr::callback($operation['callback']);
			if (isset($operation['arguments']))
			{
				$args = array_merge(array($blogs), $operation['arguments']);
			}
			else
			{
				$args = array($blogs);
			}

			// set model name
			$args['type'] = 'blog';

			// execute the bulk operation
			call_user_func_array($func, $args);
		}
	}
}
