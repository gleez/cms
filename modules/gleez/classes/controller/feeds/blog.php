<?php
/**
 * Blog Feed Controller
 *
 * @package    Gleez\Controller\Feed
 * @author     Sandeep Sangamreddi - Gleez
 * @author     Sergey Yakovlev - Gleez
 * @version    1.1.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Feeds_Blog extends Controller_Feeds_Base {

	/**
	 * The before() method is called before controller action
	 *
	 * Setting the type for tags, categories, etc.
	 */
	public function before()
	{
		parent::before();

		$this->_type = 'blog';
	}

	/**
	 * Get list of pages
	 *
	 * @uses  Config::load
	 * @uses  Config_Group::get
	 * @uses  URL::site
	 * @uses  Cache::set
	 */
	public function action_list()
	{
		if (empty($this->_items))
		{
			$config = Kohana::$config->load('blog');

			// Cache is Empty so Re-Cache
			$blogs = ORM::factory('blog')
				->where('status', '=', 'publish')
				->order_by('pubdate', 'DESC')
				->limit($this->_limit)
				->offset($this->_offset)
				->find_all();

			$items = array();
			foreach($blogs as $blog)
			{
				$item = array();
				$item['guid']        = $blog->id;
				$item['title']       = $blog->title;
				$item['link']        = URL::site($blog->url, TRUE);
				if ($config->get('use_submitted', FALSE))
				{
					$item['author']  = $blog->user->nick;
				}
				$item['description'] = $blog->teaser;
				$item['pubDate']     = $blog->pubdate;

				$items[] = $item;
			}

			$this->_cache->set($this->_cache_key, $items, $this->_ttl);
			$this->_items = $items;
		}

		if (isset($this->_items[0]))
		{
			$this->_info['title']   = __('Pages - Recent updates');
			$this->_info['link']    = Route::url('rss', array('controller' => 'blog'), TRUE);
			$this->_info['pubDate'] = $this->_items[0]['pubDate'];
		}
	}

	/**
	 * Get a list of pages with a specific term
	 *
	 * @since  1.1.0
	 *
	 * @uses  Controller_Feed_Base::_term
	 */
	public function action_term()
	{
		parent::_term();
	}

	/**
	 * Get a list of blogs with a specific tag
	 *
	 * @since  1.1.0
	 *
	 * @uses  Controller_Feed_Base::_tag
	 */
	public function action_tag()
	{
		parent::_tag();
	}
}
