<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Page Feed Controller
 *
 * @package    Gleez\Feed\Controller
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Feeds_Page extends Controller_Feeds_Base {

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
		if ($this->_items === NULL OR empty($this->_items))
		{
			$config = Kohana::$config->load('page');
			// Cache is Empty so Re-Cache
			$pages = ORM::factory('page')
					->where('status', '=', 'publish')
					->order_by('pubdate', 'DESC')
					->limit($this->_limit)
					->offset($this->_offset)
					->find_all();

			$items = array();
			foreach($pages as $page)
			{
				$item = array();
				$item['id']          = $page->id;
				$item['title']       = $page->title;
				$item['link']        = URL::site($page->url, TRUE);
				if ($config->get('use_submitted', FALSE))
				{
					$item['author']  = $page->user->nick;
				}
				$item['description'] = $page->teaser;
				$item['pubDate']     = $page->pubdate;

				$items[] = $item;
			}

			$this->_cache->set($this->_cache_key, $items, DATE::HOUR); // 1 Hour
			$this->_items = $items;
		}

		if (isset($this->_items[0]))
		{
			$this->_info['title']   = __('Pages - Recent updates');
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
		if ($this->_items === NULL OR empty($this->_items))
		{
			$config = Kohana::$config->load('page');
			// Cache is Empty so Re-Cache
			$id = (int) $this->request->param('id', 0);
			$term = ORM::factory('term')
					->where('id', '=', $id)
					->where('type', '=', 'page')
					->where('lvl', '!=', 1)
					->find();

			if ( ! $term->loaded())
			{
				Kohana::$log->add(LOG::ERROR, 'Attempt to access non-existent page term feed');
				throw new HTTP_Exception_404(__('Term ":term" Not Found'), array(':term' => $id));
			}

			$posts = $term->posts
					->where('status', '=', 'publish')
					->order_by('pubdate', 'DESC')
					->limit($this->_limit)
					->offset($this->_offset)
					->find_all();

			$items = array();
			foreach($posts as $page)
			{
				$item = array();
				$item['id']          = $page->id;
				$item['title']       = $page->title;
				$item['link']        = URL::site($page->url, TRUE);
				if ($config->get('use_submitted', FALSE))
				{
					$item['author']  = $page->user->nick;
				}
				$item['description'] = $page->teaser;
				$item['pubDate']     = $page->pubdate;

				$items[] = $item;
			}

			$items['title'] = $term->name;
			$this->_cache->set($this->_cache_key, $items, Date::HOUR); // 1 Hour
			$this->_items = $items;
		}

		if (isset($this->_items[0]))
		{
			$this->_info['title'] = __(':term - Recent updates', array(':term' => ucfirst($this->_items['title'])));
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
		if ($this->_items === NULL OR empty($this->_items))
		{
			$config = Kohana::$config->load('page');
			// Cache is Empty so Re-Cache
			$id = (int) $this->request->param('id', 0);
			$tag = ORM::factory('tag', array('id' => $id, 'type' => 'page'));

			if ( ! $tag->loaded())
			{
				Kohana::$log->add(LOG::ERROR, 'Attempt to access non-existent page tag feed');
				throw new HTTP_Exception_404(__('Tag ":tag" Not Found'), array(':tag' => $id));
			}

			$posts = $tag->posts
					->where('status', '=', 'publish')
					->order_by('pubdate', 'DESC')
					->limit($this->_limit)
					->offset($this->_offset)
					->find_all();

			$items = array();
			foreach($posts as $page)
			{
				$item = array();
				$item['id']          = $page->id;
				$item['title']       = $page->title;
				$item['link']        = URL::site($page->url, TRUE);
				if ($config->get('use_submitted', FALSE))
				{
					$item['author']  = $page->user->nick;
				}
				$item['description'] = $page->teaser;
				$item['pubDate']     = $page->pubdate;

				$items[] = $item;
			}

			$items['title'] = $tag->name;
			$this->_cache->set($this->_cache_key, $items, Date::HOUR); // 1 Hour
			$this->_items = $items;
		}

		if( isset($this->_items[0]))
		{
			$this->_info['title']   = __(':tag - Recent updates', array(':tag' => ucfirst($this->_items['title'])));
			$this->_info['pubDate'] = $this->_items[0]['pubDate'];
		}
	}
}