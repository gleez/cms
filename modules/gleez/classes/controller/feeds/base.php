<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Base Feed Controller
 *
 * @package    Gleez\Feed\Controller
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Feeds_Base extends Controller_Feeds_Template {

	/**
	 * Get list of promoted posts
	 *
	 * @uses  DB::select
	 * @uses  Text::markup
	 * @uses  URL::site
	 * @uses  Cache::set
	 */
	public function action_list()
	{
		if ($this->_items === NULL OR empty($this->_items))
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
				$item['id']          = $post->id;
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

			$this->_cache->set($this->_cache_key, $this->_items, DATE::HOUR); // 1 Hour
			$this->_items = $items;
		}

		if (isset($this->_items[0]))
		{
			$this->_info['pubDate'] = $this->_items[0]['pubDate'];
		}
	}

	public function action_view()
	{
		if ($this->_items === NULL OR empty($this->_items))
		{
			$config = Kohana::$config->load('page');
			$id = (int) $this->request->param('id', 0);

			$post = ORM::factory('post')
				->where('id', '=', $id)
				->where('status', '=', 'publish')
				->find();

			if ( ! $post->loaded())
			{
				Kohana::$log->add(LOG::ERROR, 'Attempt to access non-existent post feed.');
				throw new HTTP_Exception_404('Attempt to access non-existent post feed.');
			}

			$item = array();
			$item['id']          = $post->id;
			$item['title']       = $post->title;
			$item['link']        = URL::site($post->url, TRUE);
			if ($config->get('use_submitted', FALSE))
			{
				$item['author']  = $post->user->nick;
			}
			$item['description'] = $post->teaser;
			$item['pubDate']     = $post->pubdate;

			$this->_cache->set($this->_cache_key, $item, DATE::HOUR); // 1 Hour
			$this->_items = $item;
		}
		if (isset($this->_items[0]))
		{
			$this->_info['pubDate'] = $this->_items[0]['pubDate'];
		}
	}
}