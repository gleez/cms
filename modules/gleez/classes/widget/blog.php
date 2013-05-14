<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Blog Widget class
 *
 * @package    Gleez\Widget
 * @author     Sergey Yakovlev - Gleez
 * @copyright  (c) 2011-2012 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Widget_blog extends Widget {	public function info(){}
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
	 */
	public function recent_blogs()
	{
		$action = Request::current()->action();
		$cache  = Cache::instance('blogs');
		$view   = View::factory('widgets/blog/list')->bind('items', $items);

		// Don't show the widget on edit or delete actions
		if ($action == 'edit' OR $action == 'delete')
		{
			return FALSE;
		}

		$items = $cache->get('recent_blogs', array());

		if (empty($items))
		{
			$blogs = ORM::factory('blog');

			/**
			 * Bug in ORM to repeat the `where()` methods after using `count_all()`
			 * @link http://forum.kohanaframework.org/discussion/7736 Solved
			 */
			$total = $blogs->reset(FALSE)->count_all();

			if ($total == 0)
			{
				Kohana::$log->add(Log::INFO, 'No blogs found');
				$this->response->body(View::factory('blog/none'));
				return;
			}

			if ( ! ACL::check('administer blog'))
			{
				$blogs->where('status', '=', 'publish');
			}

			$blogs->limit(10)->find_all();


			$items = array();
			foreach($blogs as $blog)
			{
				$items[$blog->id]['id'] = $blog->id;
				$items[$blog->id]['title'] = $blog->title;
				$items[$blog->id]['url'] = $blog->url;
				$items[$blog->id]['date'] = $blog->updated ? $blog->updated : $blog->created;

			}

			// set the cache
			$cache->set('recent_blogs', $items, DATE::HOUR);
			echo Debug::vars($cache);exit;
		}

		$view->render();
	}
}