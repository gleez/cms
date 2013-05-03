<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Gleez Blog Controller
 *
 * @package    Gleez\Controller
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Blog extends Template {

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

		ACL::required('access blog');

		parent::before();
	}

	/**
	 * The after() method is called after controller action
	 */
	public function after()
	{
		if ($this->request->action() == 'add' OR $this->request->action() == 'edit')
		{
			// Add RichText Support
			Assets::editor('.textarea', I18n::$lang);
		}

		parent::after();
	}

	/**
	 * List of blogs
	 *
	 * @uses  ACL::check
	 */
	public function action_list()
	{
		$posts = ORM::factory('blog');

		if ( ! ACL::check('administer blog'))
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
			Kohana::$log->add(Log::INFO, 'No blogs found');
			$this->response->body(View::factory('blog/none'));
			return;
		}

		$config = Kohana::$config->load('blog');

		$this->title = __('Blogs');
		$feed = Route::get('rss')->uri(array('controller' => 'blog'));

		$view = View::factory('blog/list')
			->set('teaser',      TRUE)
			->set('config',      $config)
			->set('feed',        $feed)
			->bind('pagination', $pagination)
			->bind('posts',      $posts);

		$url = Route::get('blog')->uri();
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
			Meta::links(Route::url('blog', array(), TRUE), array('rel' => 'shortlink'));
			Meta::links(URL::site('rss/blog', TRUE), array(
				'rel'   => 'alternate',
				'type'  => 'application/rss+xml',
				'title' => $this->_config->get('site_name', 'Gleez CMS (RSS 2.0)') . ' : ' . __('Blogs'),
			));
		}
	}
}
