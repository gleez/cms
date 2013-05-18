<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Blog Widget class
 *
 * @package    Gleez\Widget
 * @author     Sergey Yakovlev - Gleez
 * @copyright  (c) 2011-2012 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Widget_blog extends Widget {

	public function info(){}
	public function form(){}
	public function save(array $post){}
	public function delete(array $post){}

	public function render()
	{
		switch($this->name)
		{
			case 'recent':
				return $this->recent_blogs();
			break;
			default:
				return;
		}
	}

	/**
	 * Get recent blogs
	 *
	 * @return  string
	 *
	 * @uses    Request::current
	 * @uses    Request::action
	 * @uses    Cache::get
	 * @uses    Cache::set
	 */
	public function recent_blogs()
	{
		$action = Request::current()->action();

		// Don't show the widget on edit or delete actions
		if ($action == 'edit' OR $action == 'delete')
		{
			return FALSE;
		}

		$cache = Cache::instance('widgets');
		$view  = View::factory('widgets/blog/list')->bind('items', $items);

		if ( ! $items = $cache->get('recent_blogs'))
		{
			$blogs = ORM::factory('blog')->order_by('created', 'DESC')->limit(10)->find_all();

			$items = array();
			foreach($blogs as $blog)
			{
				$items[$blog->id]['id']       = $blog->id;
				$items[$blog->id]['title']    = $blog->title;
				$items[$blog->id]['url']      = $blog->url;
				$items[$blog->id]['user']     = $blog->user->name;
				$items[$blog->id]['user_url'] = $blog->user->url;
				$items[$blog->id]['date']     = $blog->updated ? $blog->updated : $blog->created;

			}

			// Set the cache
			$cache->set('recent_blogs', $items, DATE::HOUR);
		}

		return $view->render();
	}
}