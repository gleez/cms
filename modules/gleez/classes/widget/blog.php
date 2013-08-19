<?php
/**
 * Blog Widget class
 *
 * @package    Gleez\Widget
 * @author     Sergey Yakovlev - Gleez
 * @copyright  (c) 2011-2012 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Widget_Blog extends Widget {

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
			case 'announce':
				return $this->recent_announce_blogs();
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

	/**
	 * Receive the latest blog in the format Picture + Title
	 *
	 * @return  string
	 *
	 * @uses    Request::current
	 * @uses    Request::action
	 * @uses    Cache::get
	 * @uses    Cache::set
	 */
	public function recent_announce_blogs()
	{
		$action = Request::current()->action();

		// Don't show the widget on edit or delete actions
		if ($action == 'edit' OR $action == 'delete')
		{
			return FALSE;
		}

		$cache = Cache::instance('widgets');
		$view  = View::factory('widgets/blog/announce')->bind('items', $items);

		if ( ! $items = $cache->get('recent_announce_blogs'))
		{
			$blogs = ORM::factory('blog')->order_by('created', 'DESC')->limit(10)->find_all();

			$items = array();
			foreach($blogs as $blog)
			{
				$items[$blog->id]['id']    = $blog->id;
				$items[$blog->id]['title'] = $blog->title;
				$items[$blog->id]['url']   = $blog->url;

				$image = is_null($blog->image)
					? '<div class="empty-photo"><i class="icon-camera-retro icon-2x"></i></div>'
					: HTML::resize($blog->image, array('alt' => $blog->title, 'height' => 140, 'width' => 180, 'type' => 'resize', 'itemprop' => 'image'));

				$items[$blog->id]['image'] = $image;
			}

			// Set the cache
			$cache->set('recent_announce_blogs', $items, DATE::HOUR);
		}

		return $view->render();
	}
}