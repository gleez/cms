<?php
/**
 * Base Feed Controller
 *
 * @package    Gleez\Controller\Feed
 * @author     Gleez Team
 * @version    1.1.2
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Feeds_Base extends Controller_Feeds_Template {

	/**
	 * Get list of promoted posts
	 *
	 * @uses  DB::select
	 * @uses  URL::site
	 * @uses  Cache::set
	 * @uses  Config::load
	 * @uses  Config_Group::get
	 */
	public function action_list()
	{
		if (empty($this->_items))
		{
			$config = Kohana::$config->load('page');

			// Cache is Empty so Re-Cache
			$posts = ORM::factory('post')
				->where('status', '=', 'publish')
				->where('promote', '=', 1)
				->order_by('pubdate', 'DESC')
				->limit($this->_limit)
				->offset($this->_offset)
				->find_all();

			$items = array();
			foreach($posts as $post)
			{
				$item = array();
				$item['guid']        = $post->id;
				$item['title']       = $post->title;
				$item['link']        = URL::site($post->url, TRUE);
				if ($config->get('use_submitted', FALSE))
				{
					$item['author']  = $post->user->nick;
				}
				$item['description'] = $post->teaser;
				$item['pubDate']     = $post->pubdate;

				$items[] = $item;
			}

			$this->_cache->set($this->_cache_key, $items, $this->_ttl);
			$this->_items = $items;
		}

		if (isset($this->_items[0]))
		{
			$this->_info['pubDate'] = $this->_items[0]['pubDate'];
		}
	}

	/**
	 * Get a list of posts (pages|blogs|etc.) with a specific tag
	 *
	 * @since  1.1.0
	 *
	 * @throws  HTTP_Exception_404
	 *
	 * @uses    Config::load
	 * @uses    Config_Group::get
	 * @uses    Log::add
	 * @uses    URL::site
	 * @uses    Cache::set
	 */
	protected function _tag()
	{
		if (empty($this->_items))
		{
			$config = Config::load($this->_type);

			$id  = $this->request->param('id', 0);
			$tag = ORM::factory('tag', array('id' => $id, 'type' => $this->_type));

			if ( ! $tag->loaded())
			{
				throw HTTP_Exception::factory(404, 'Tag ":tag" Not Found', array(':tag' => $id));
			}

			$posts = $tag->posts
					->where('status', '=', 'publish')
					->order_by('pubdate', 'DESC')
					->limit($this->_limit)
					->offset($this->_offset)
					->find_all();

			$items = array();

			foreach($posts as $post)
			{
				$item = array();
				$item['guid']        = $post->id;
				$item['title']       = $post->title;
				$item['link']        = URL::site($post->url, TRUE);
				if ($config->get('use_submitted', FALSE))
				{
					$item['author']  = $post->user->nick;
				}
				$item['description'] = $post->teaser;
				$item['pubDate']     = $post->pubdate;

				$items[] = $item;
			}

			$items['title'] = $tag->name;
			$this->_items   = $items;

			$this->_cache->set($this->_cache_key, $this->_items, $this->_ttl);
		}

		if (isset($this->_items[0]))
		{
			$this->_info['title']   = __(':tag - Recent updates', array(':tag' => ucfirst($this->_items['title'])));
			$this->_info['link']    = Route::url('rss', array('controller' => $this->_type, 'action' => 'tag', 'id' => (int) $this->request->param('id')), TRUE);
			$this->_info['pubDate'] = $this->_items[0]['pubDate'];
		}
	}

	/**
	 * Get a list of posts (pages|blogs|etc.) with a specific term
	 *
	 * @since   1.1.0
	 *
	 * @throws  HTTP_Exception_404
	 *
	 * @uses    Config::load
	 * @uses    Config_Group::get
	 * @uses    Cache::set
	 * @uses    Log::add
	 * @uses    URL::site
	 */
	protected function _term()
	{
		if (empty($this->_items))
		{
			$config = Config::load($this->_type);

			$id   = $this->request->param('id', 0);
			$term = ORM::factory('term')
					->where('id', '=', $id)
					->where('type', '=', $this->_type)
					->where('lvl', '!=', 1)
					->find();

			if ( ! $term->loaded())
			{
				throw HTTP_Exception::factory(404, 'Term ":term" Not Found', array(':term' => $id));
			}

			$posts = $term->posts
					->where('status', '=', 'publish')
					->order_by('pubdate', 'DESC')
					->limit($this->_limit)
					->offset($this->_offset)
					->find_all();

			$items = array();

			foreach($posts as $post)
			{
				$item = array();
				$item['guid']        = $post->id;
				$item['title']       = $post->title;
				$item['link']        = URL::site($post->url, TRUE);
				if ($config->get('use_submitted', FALSE))
				{
					$item['author']  = $post->user->nick;
				}
				$item['description'] = $post->teaser;
				$item['pubDate']     = $post->pubdate;

				$items[] = $item;
			}

			$items['title'] = $term->name;
			$this->_items   = $items;

			$this->_cache->set($this->_cache_key, $this->_items, $this->_ttl);
		}

		if (isset($this->_items[0]))
		{
			$this->_info['title']   = __(':term - Recent updates', array(':term' => ucfirst($this->_items['title'])));
			$this->_info['link']    = Route::url('rss', array('controller' => $this->_type, 'action' => 'term', 'id' => (int) $this->request->param('id')), TRUE);
			$this->_info['pubDate'] = $this->_items[0]['pubDate'];
		}
	}

}