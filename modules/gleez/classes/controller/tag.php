<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Tag extends Template {

	public function before()
	{
		// Internal request only!
		if ($this->request->is_initial())
		{
			throw new HTTP_Exception_404('Accessing an internal request <small>:type</small> externally', array(
                                        ':type' => $this->request->uri(),
                                ));
		}
		
		ACL::Required('access content');
		parent::before();
	}
	
	public function action_list()
	{
        }
        
        public function action_view()
	{
                $id = (int) $this->request->param('id', 0);
                $tag = ORM::factory('tag', $id);
        
                if( ! $tag->loaded() )
		{
			Kohana::$log->add(LOG::ERROR, 'Attempt to access non-existent tag');
			throw new HTTP_Exception_404( __('Tag ":tag" Not Found'), array(':tag'=>$id));
		}
        
        	$this->title    = __(':title', array(':title' => Text::ucfirst($tag->name) ) );
		$view           = View::factory('tag/view')
						->set('teaser', TRUE)
						->bind('pagination', $pagination)
						->bind('posts', $posts);
        
                $posts = $tag->posts;
                
                if(!ACL::check('administer tags') AND !ACL::check('administer content'))
		{
			$posts->where('status', '=', 'publish');
		}
                
                $total      = $posts->reset(FALSE)->count_all();

		if ($total == 0)
		{
			Kohana::$log->add(Log::INFO, 'No posts found');
			$this->response->body( View::factory('page/none') );
			return;
		}
        
                $pagination = Pagination::factory(array(
				'current_page'   => array('source'=>'cms', 'key'=>'page'),
				'total_items' 	 => $total,
				'items_per_page' => 15,
				'uri'		 => $tag->url,
				));
		
		$posts  = $posts->order_by('created', 'DESC')->limit($pagination->items_per_page)
						->offset($pagination->offset)->find_all();
                
                $this->response->body($view);
		
		//Set the canocial and shortlink for search engines
		if ( $this->auto_render === TRUE )
		{
			Meta::links( URL::canonical($tag->url, $pagination), array('rel' => 'canonical'));
			Meta::links( Route::url('tag', array('action' => 'view', 'id' => $tag->id) ), array('rel' => 'shortlink'));
		}
        }
        
}