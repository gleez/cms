<?php
/**
 * Page Controller
 *
 * @package    Gleez\Controller
 * @author     Gleez Team
 * @version    1.0.2
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Page extends Template {

	/**
	 * The before() method is called before controller action
	 *
	 * @uses  Request::param
	 * @uses  Request::action
	 * @uses  ACL::required
	 */
	public function before()
	{
		$id = $this->request->param('id', FALSE);

		if ($id AND $this->request->action() == 'index')
		{
			$this->request->action('view');
		}

		if ( ! $id AND $this->request->action() == 'index')
		{
			$this->request->action('list');
		}

		ACL::required('access content');

		parent::before();
	}

	/**
	 * The after() method is called after controller action
	 *
	 * @uses  Request::action
	 * @uses  Assets::editor
	 */
	public function after()
	{
		if ($this->request->action() == 'add' OR $this->request->action() == 'edit')
		{
			// Add RichText Support
			Assets::editor('.textarea', I18n::$lang);

			// Flag to disable left/right sidebars
			$this->_sidebars = FALSE;
		}

		parent::after();
	}

	/**
	 * List of pages
	 *
	 * @uses  ACL::check
	 * @uses  Config::load
	 * @uses  Config_Group::get
	 * @uses  URL::canonical
	 * @uses  Route::get
	 * @uses  Route::uri
	 * @uses  Meta::links
	 */
	public function action_list()
	{
		$posts = ORM::factory('page');

		if ( ! ACL::check('administer content'))
		{
			$posts->where('status', '=', 'publish');
		}

		/**
		 * Bug in ORM to repeat the `where()` methods after using `count_all()`
		 * @link http://forum.kohanaframework.org/discussion/7736 Solved
		 */
		$total = $posts->reset(FALSE)->count_all();

		if ($total == 0)
		{
			Log::info('No posts found.');
			$this->response->body( View::factory('page/none') );
			return;
		}

		$config = Config::load('page');

		$this->title = __('Pages');

		$view = View::factory('page/list')
					->set('teaser',      TRUE)
					->set('config',      $config)
					->bind('rss_link',   $rss_link)
					->bind('pagination', $pagination)
					->bind('posts',      $posts);

		$url        = Route::get('page')->uri();
		$rss_link   = Route::get('rss')->uri(array('controller' => 'page'));
		$pagination = Pagination::factory(array(
			'current_page'   => array('source'=>'cms', 'key'=>'page'),
			'total_items'    => $total,
			'items_per_page' => $config->get('items_per_page', 15),
			'uri'            => $url,
		));

		$posts = $posts->order_by('sticky', 'DESC')
					->order_by('created', 'DESC')
					->limit($pagination->items_per_page)
					->offset($pagination->offset)
					->find_all();

		$this->response->body($view);

		// Set the canonical and shortlink for search engines
		if ($this->auto_render)
		{
			Meta::links(URL::canonical($url, $pagination), array('rel' => 'canonical'));
			Meta::links(Route::url('page', array(), TRUE), array('rel' => 'shortlink'));
			Meta::links(URL::site('rss/page', TRUE), array(
				'rel'   => 'alternate',
				'type'  => 'application/rss+xml',
				'title' => Template::getSiteName() . ' : ' . __('Pages'),
			));
		}
	}

	/**
	 * Page view
	 *
	 * @throws  HTTP_Exception_403
	 *
	 * @uses    ACL::post
	 * @uses    ACL::check
	 * @uses    Post::dcache
	 * @uses    Auth::logged_in
	 * @uses    Meta::links
	 * @uses    URL::canonical
	 * @uses    Route::get
	 * @uses    Route::uri
	 * @uses    User::providers
	 * @uses    Comment::form
	 */
	public function action_view()
	{
		$id = (int) $this->request->param('id', 0);
		$config = Config::load('page');

		$post = Post::dcache($id, 'page', $config);

		if ( ! ACL::post('view', $post))
		{
			// If the post was not loaded, we return access denied.
			throw HTTP_Exception::factory(403, 'Access denied!');
		}

		if (ACL::post('edit', $post))
		{
			$this->_tabs[] = array('link' => $post->url, 'text' => __('View'));
			$this->_tabs[] = array('link' => $post->edit_url, 'text' => __('Edit'));
		}

		if (ACL::post('delete', $post))
		{
			$this->_tabs[] = array('link' => $post->delete_url, 'text' => __('Delete'));
		}

		if (($post->comment > Comment::COMMENT_HIDDEN) AND ACL::check('access comment'))
		{
			// Determine pagination offset
			$p = ((int) $this->request->param('page', 0)) ? '/p'.$this->request->param('page', 0) : FALSE;

			// Handle comment listing
			$comments = Request::factory('comments/page/public/'.$id.$p)->execute()->body();
		}

		if ($post->comment == Comment::COMMENT_OPEN AND ACL::check('post comment'))
		{
			if ($this->_auth->logged_in() OR ($config->comment_anonymous AND ! $this->_auth->logged_in()))
			{
				// Handle comment posting
				$comment_form = Comment::form($this, $post);
			}
		}

		// show site and other provider login buttons
		if ($post->comment == Comment::COMMENT_OPEN AND $config->use_provider_buttons)
		{
			$provider_buttons = User::providers();
		}

		$this->title = $post->title;

		$view = View::factory('page/post')
				->set('title',             $this->title)
				->set('page',              $post->content)
				->bind('comments',         $comments)
				->bind('comment_form',     $comment_form)
				->bind('provider_buttons', $provider_buttons);

		$this->response->body($view);

		// Set the canonical and shortlink for search engines
		if ($this->auto_render)
		{
			Meta::links(URL::canonical($post->url), array('rel' => 'canonical'));
			Meta::links($post->rawurl, array('rel' => 'shortlink'));
		}
	}

	/**
	 * Page creating
	 *
	 * @uses  ACL::required
	 * @uses  Config::load
	 * @uses  Config_Group::get
	 * @uses  Request::query
	 * @uses  Route::get
	 * @uses  Route::uri
	 * @uses  URL::query
	 * @uses  ORM::select_list
	 * @uses  Log::add
	 * @uses  Message::success
	 */
	public function action_add()
	{
		ACL::required('create page');

		$this->title = __('Add Page');
		$config = Config::load('page');

		// Set form destination
		$destination = ( ! is_null($this->request->query('destination'))) ? array('destination' => $this->request->query('destination')) : array();
		// Set form action
		$action = Route::get('page')->uri(array('action' => 'add')).URL::query($destination);

		$view = View::factory('page/form')
					->set('destination', $destination)
					->set('action',      $action)
					->set('config',      $config)
					->set('created',     FALSE)
					->set('author',      FALSE)
					->set('path',        FALSE)
					->set('tags',        isset($_POST['ftags']) ? $_POST['ftags'] : FALSE)
					->bind('errors',     $this->_errors)
					->bind('terms',      $terms)
					->bind('post',       $post);


		$post = ORM::factory('page');
		$post->status = $config->get('default_status', 'draft');

		if($config->get('use_category', FALSE))
		{
			$terms = ORM::factory('term', array('type' => 'page', 'lvl' => 1))->select_list('id', 'name', '--');
		}

		if($config->get('use_captcha', FALSE))
		{
			$captcha = Captcha::instance();
			$view->set('captcha', $captcha);
		}

		if ($this->valid_post('page'))
		{
			try
			{
				$post->values($_POST)->save();

				Log::info('Page :title created.', array(':title' => $post->title));
				Message::success(__('Page %title created', array('%title' => $post->title)));

				$this->request->redirect($post->url);
			}
			catch (ORM_Validation_Exception $e)
			{
				// @todo Add messages
				$this->_errors = $e->errors('models', TRUE);
			}
		}

		$this->response->body($view);
	}

	/**
	 * Page edit
	 *
	 * @throws HTTP_Exception_403
	 *
	 * @uses    ACL::post
	 * @uses    Config::load
	 * @uses    Request::query
	 * @uses    Request::redirect
	 * @uses    Route::get
	 * @uses    Route::uri
	 * @uses    URL::query
	 * @uses    Tags::implode
	 * @uses    Path::load
	 * @uses    Message::success
	 * @uses    Log::add
	 */
	public function action_edit()
	{
		$id = (int) $this->request->param('id', 0);
		$post = ORM::factory('page', $id);

		if ( ! ACL::post('edit', $post))
		{
			// If the post was not loaded, we return access denied.
			throw HTTP_Exception::factory(403, 'Access denied!');
		}

		$this->title = $post->title;
		$config = Config::load('page');

		// Set form destination
		$destination = ( ! is_null($this->request->query('destination'))) ? array('destination' => $this->request->query('destination')) : array();
		// Set form action
		$action = Route::get('page')->uri(array('id' => $id, 'action' => 'edit')).URL::query($destination);

		$view = View::factory('page/form')
				->set('destination',  $destination)
				->set('action',       $action)
				->set('config',       $config)
				->set('path',         FALSE)
				->set('created',      Date::formatted_time($post->created, 'Y-m-d H:i:s O'))
				->set('author',       $post->user->name)
				->set('tags',         Tags::implode($post->tags_form))
				->bind('errors',      $this->_errors)
				->bind('terms',       $terms)
				->bind('post',        $post);

		if ($config->get('use_captcha', FALSE))
		{
			$captcha = Captcha::instance();
			$view->set('captcha', $captcha);
		}

		if ($path = Path::load($post->rawurl))
		{
			$view->set('path', $path['alias']);
		}

		if ($config->get('use_category', FALSE))
		{
			$terms = ORM::factory('term', array('type' => 'page', 'lvl' => 1))
					->select_list('id', 'name', '--');
		}

		if($this->valid_post('page'))
		{
			try
			{
				$post->values($_POST)->save();

				Log::info('Page: :title updated.', array(':title' => $post->title));
				Message::success(__('Page %title updated', array('%title' => $post->title)));

				$this->request->redirect(empty($destination) ? $post->url : $this->request->query('destination'));
			}
			catch (ORM_Validation_Exception $e)
			{
				$this->_errors = $e->errors('models', TRUE);
			}
		}

		$this->_tabs =  array(
			array('link' => $post->url, 'text' => __('View')),
			array('link' => $post->edit_url, 'text' => __('Edit')),
		);

		if (ACL::post('delete', $post))
		{
			$this->_tabs[] =  array('link' => $post->delete_url, 'text' => __('Delete'));
		}

		$this->response->body($view);
	}

	/**
	 * Delete page
	 *
	 * @throws  HTTP_Exception_403
	 *
	 * @uses    ACL::post
	 * @uses    Request::query
	 * @uses    Request::redirect
	 * @uses    Route::get
	 * @uses    Route::uri
	 * @uses    URL::query
	 * @uses    Post::delete
	 * @uses    Message::success
	 * @uses    Message::error
	 * @uses    Log::add
	 */
	public function action_delete()
	{
		$id = (int) $this->request->param('id', 0);
		$post = ORM::factory('page', $id);

		if ( ! ACL::post('delete', $post))
		{
			// If the post was not loaded, we return access denied.
			throw HTTP_Exception::factory(403, 'Access denied!');
		}

		$this->title = __('Delete :title', array(':title' => $post->title));

		$destination = ($this->request->query('destination') !== NULL) ?
					array('destination' => $this->request->query('destination')) : array();

		$view = View::factory('form/confirm')
					->set('action', $post->delete_url.URL::query($destination))
					->set('title',  $post->title);

		// If deletion is not desired, redirect to post
		if (isset($_POST['no']) AND $this->valid_post())
		{
			$this->request->redirect($post->url);
		}

		// If deletion is confirmed
		if ( isset($_POST['yes']) AND $this->valid_post() )
		{
			try
			{
				$title = $post->title;
				$post->delete();

				Log::info('Page: :title deleted.', array(':title' => $title));
				Message::success(__('Page: :title deleted successful!', array(':title' => $title)));
			}
			catch (Exception $e)
			{
				Log::error('Error occurred deleting page id: :id, :msg',
					array(':id' => $post->id, ':msg' => $e->getMessage())
				);
				Message::error(__('An error occurred deleting page %post',array('%post' => $post->title)));
			}

			$redirect = empty($destination) ? Route::get('page')->uri() : $this->request->query('destination');

			$this->request->redirect($redirect);
		}

		$this->response->body($view);
	}

	/**
	 * Category selector
	 *
	 * @throws  HTTP_Exception_403
	 *
	 * @uses    Config::load
	 * @uses    Config::get
	 * @uses    Log::add
	 * @uses    ACL::check
	 * @uses    Route::get
	 * @uses    Route::uri
	 * @uses    Route::url
	 * @uses    Meta::links
	 * @uses    URL::canonical
	 */
	public function action_term()
	{
		$config = Config::load('page');

		if ( ! $config->use_category)
		{
			throw HTTP_Exception::factory(403, 'Attempt access to access disabled feature');
		}

		$id    = (int) $this->request->param('id', 0);
		$array = array('id' => $id, 'type' => 'page');
		$term  = ORM::factory('term', $array )->where('lvl', '!=', 1);

		if ( ! $term->loaded())
		{
			throw HTTP_Exception::factory(404, 'Term ":term" Not Found', array(':term'=>$id));
		}

		$this->title = __(':term', array(':term' => $term->name));
		$view = View::factory('page/list')
					->set('teaser',      TRUE)
					->set('config',      $config)
					->bind('rss_link',   $rss_link)
					->bind('pagination', $pagination)
					->bind('posts',      $posts);

		$posts = $term->posts;

		if ( ! ACL::check('administer terms') AND ! ACL::check('administer content'))
		{
			$posts->where('status', '=', 'publish');
		}

		$total = $posts->reset(FALSE)->count_all();

		if ($total == 0)
		{
			Log::info('No topics found.');
			$this->response->body(View::factory('forum/none'));
			return;
		}
		$rss_link   = Route::get('rss')->uri(array('controller' => 'page', 'action' => 'term', 'id' => $term->id));
		$pagination = Pagination::factory(array(
			'current_page'   => array('source'=>'cms', 'key'=>'page'),
			'total_items'    => $total,
			'items_per_page' => $config->get('items_per_page', 15),
			'uri'            => $term->url,
		));

		$posts = $posts->order_by('sticky', 'DESC')
						->order_by('created', 'DESC')
						->limit($pagination->items_per_page)
						->offset($pagination->offset)
						->find_all();

		$this->response->body($view);

		// Set the canonical and shortlink for search engines
		if ($this->auto_render)
		{
			Meta::links(URL::canonical($term->url, $pagination), array('rel' => 'canonical'));
			Meta::links(Route::url('page', array('action' => 'term', 'id' => $term->id), TRUE ), array(
				'rel' => 'shortlink'
			));
			Meta::links(Route::url('rss', array('controller' => 'page', 'action' => 'term', 'id' => $term->id)), array(
				'rel'   => 'alternate',
				'type'  => 'application/rss+xml',
				'title' => Template::getSiteName() . ' : ' . $term->name,
			));
		}
	}

	/**
	 * Tags view
	 *
	 * @throw  HTTP_Exception_404
	 *
	 * @uses   Config::load
	 * @uses   Config::get
	 * @uses   Log::add
	 * @uses   Text::ucfirst
	 * @uses   Route::get
	 * @uses   Route::uri
	 * @uses   Route::url
	 * @uses   Meta::links
	 * @uses   URL::canonical
	 */
	public function action_tag()
	{
		$config = Config::load('page');
		$id = (int) $this->request->param('id', 0);
		$tag = ORM::factory('tag', array('id' => $id, 'type' => 'page') );

		if ( ! $tag->loaded())
		{
			throw HTTP_Exception::factory(404, 'Tag ":tag" Not Found', array(':tag'=>$id));
		}

		$this->title = __(':title', array(':title' => Text::ucfirst($tag->name)));
		$view        = View::factory('page/list')
					->set('teaser',      TRUE)
					->set('config',      $config)
					->bind('rss_link',   $rss_link)
					->bind('pagination', $pagination)
					->bind('posts',      $posts);

		$posts = $tag->posts;

		if ( ! ACL::check('administer tags') AND ! ACL::check('administer content'))
		{
			$posts->where('status', '=', 'publish');
		}

		$total = $posts->reset(FALSE)->count_all();

		if ($total == 0)
		{
			Log::info('No posts found.');
			$this->response->body(View::factory('page/none'));
			return;
		}

		$rss_link   = Route::get('rss')->uri(array('controller' => 'page', 'action' => 'tag', 'id' => $tag->id));
		$pagination = Pagination::factory(array(
			'current_page'   => array('source'=>'cms', 'key'=>'page'),
			'total_items'    => $total,
			'items_per_page' => $config->get('items_per_page', 15),
			'uri'            => $tag->url,
		));

		$posts = $posts->order_by('created', 'DESC')
						->limit($pagination->items_per_page)
						->offset($pagination->offset)
						->find_all();

		$this->response->body($view);

		// Set the canonical and shortlink for search engines
		if ($this->auto_render)
		{
			Meta::links(URL::canonical($tag->url, $pagination), array('rel' => 'canonical'));
			Meta::links(Route::url('page', array('action' => 'tag', 'id' => $tag->id), TRUE ), array(
				'rel' => 'shortlink'
			));
			Meta::links(Route::url('rss', array('controller' => 'page', 'action' => 'tag', 'id' => $tag->id)), array(
				'rel'   => 'alternate',
				'type'  => 'application/rss+xml',
				'title' => Template::getSiteName() . ' : ' . $tag->name,
			));
		}
	}

}
