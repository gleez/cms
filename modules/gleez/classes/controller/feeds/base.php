<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Base Feed Controller
 *
 * @package    Gleez\Controller\Feed
 * @author     Sandeep Sangamreddi - Gleez
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

}