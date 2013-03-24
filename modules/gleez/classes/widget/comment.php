<?php defined('SYSPATH') OR die('No direct access allowed.');

class Widget_Comment extends Widget {

	public function info()
	{
	}
	public function form()
	{
	}

	public function save( array $post )
	{
	}

	public function delete( array $post )
	{
	}
	
	public function render()
	{
		switch($this->name)
                {
                        case 'recent':
                                return $this->recent($this->widget);
                        break;
                        default:
                                return;
                }
	}

        public function recent($widget)
        {
                // Dont show the widget on edit or delete actions.
		if( Request::current()->action() == 'edit' OR Request::current()->action() == 'delete' )
			return false;

		$cache = Cache::instance('widgets');
	
		if( ! $comments = $cache->get('recent_comments') )
		{
			$blogs = ORM::factory('comment')
					->join('posts')
						->on('posts.id', '=', 'comment.post_id')
					->where('comment.status', '=', 'publish')
					->where('posts.status', '=', 'publish')
					->order_by('comment.created', 'DESC')
					->limit(10)
					->find_all();
	
			$comments = array();
			foreach($blogs as $blog)
			{
				$comments[$blog->id]['id'] = $blog->id;
				$comments[$blog->id]['type'] = $blog->type;
				$comments[$blog->id]['title'] = $blog->title;
				$comments[$blog->id]['post_url'] = $blog->post->url;
				//$comments[$blog->id] = (object)$blog->as_array();
			}

			//set the cache
			$cache->set('recent_comments', $comments, DATE::HOUR);
		}
	
		return View::factory('widgets/comment/list')->set('comments', $comments)->render();
        }

}