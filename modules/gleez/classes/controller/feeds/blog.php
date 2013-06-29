<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Blog Feed Controller
 *
 * @package    Gleez\Controller\Feed
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Feeds_Blog extends Controller_Feeds_Template {

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
				$item['id']          = $blog->id;
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
	 * @throws  HTTP_Exception_404
	 * @uses    Config::load
	 * @uses    Config_Group::get
	 * @uses    Cache::set
	 * @uses    Log::add
	 * @uses    URL::site
	 */
	public function action_term()
	{
		if (empty($this->_items))
		{
			$config = Kohana::$config->load('blog');
			// Cache is Empty so Re-Cache
			$id = (int) $this->request->param('id', 0);
			$term = ORM::factory('term')
				->where('id', '=', $id)
				->where('type', '=', 'blog')
				->where('lvl', '!=', 1)
				->find();

			if ( ! $term->loaded())
			{
				Kohana::$log->add(LOG::ERROR, 'Attempt to access non-existent blog term feed');
				throw new HTTP_Exception_404('Term ":term" Not Found', array(':term' => $id));
			}

			$posts = $term->posts
				->where('status', '=', 'publish')
				->order_by('pubdate', 'DESC')
				->limit($this->_limit)
				->offset($this->_offset)
				->find_all();

			$items = array();
			foreach($posts as $blog)
			{
				$item = array();
				$item['id']          = $blog->id;
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

			$items['title'] = $term->name;
			$this->_cache->set($this->_cache_key, $items, $this->_ttl);
			$this->_items = $items;
		}

		if (isset($this->_items[0]))
		{
			$this->_info['title'] = __(':term - Recent updates', array(':term' => ucfirst($this->_items['title'])));
			$this->_info['link']    = Route::url('rss', array('controller' => 'blog', 'action' => 'term', 'id' => (int) $this->request->param('id')), TRUE);
			$this->_info['pubDate'] = $this->_items[0]['pubDate'];
		}
	}

	/**
	 * Get a list of pages with a specific tag
	 *
	 * @throws  HTTP_Exception_404
	 * @uses    Config::load
	 * @uses    Config_Group::get
	 * @uses    Log::add
	 * @uses    URL::site
	 * @uses    Cache::set
	 */
	public function action_tag()
	{
		if (empty($this->_items))
		{
			$config = Kohana::$config->load('blog');
			// Cache is Empty so Re-Cache
			$id = (int) $this->request->param('id', 0);
			$tag = ORM::factory('tag', array('id' => $id, 'type' => 'blog'));

			if ( ! $tag->loaded())
			{
				Kohana::$log->add(LOG::ERROR, 'Attempt to access non-existent blog tag feed');
				throw new HTTP_Exception_404('Tag ":tag" Not Found', array(':tag' => $id));
			}

			$posts = $tag->posts
				->where('status', '=', 'publish')
				->order_by('pubdate', 'DESC')
				->limit($this->_limit)
				->offset($this->_offset)
				->find_all();

			$items = array();
			foreach($posts as $blog)
			{
				$item = array();
				$item['id']          = $blog->id;
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

			$items['title'] = $tag->name;
			$this->_cache->set($this->_cache_key, $items, $this->_ttl);
			$this->_items = $items;
		}

		if( isset($this->_items[0]))
		{
			$this->_info['title']   = __(':tag - Recent updates', array(':tag' => ucfirst($this->_items['title'])));
			$this->_info['link']    = Route::url('rss', array('controller' => 'blog', 'action' => 'tag', 'id' => (int) $this->request->param('id')), TRUE);
			$this->_info['pubDate'] = $this->_items[0]['pubDate'];
		}
	}
}
