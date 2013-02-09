<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Post extends Template {

	//public $debug = TRUE;
	
	public function before()
	{
		$id = $this->request->param('id', FALSE);
		$action = $this->request->action();

		if( $id AND $action === 'index' )
		{
			$this->request->action('view');
		}

		if( !$id AND $action === 'index' )
		{
			$this->request->action('list');
		}
	
		ACL::Required('access content');
		parent::before();
	}
	
	public function action_index()
	{
		$this->title = 'Hello Post!';
        
                $post = ORM::factory('post', 2);
                //$post->title = 'Hello Welcome';
                $post->body = 'Welcome to GleezCMS - Content Management System!';
        
                //$post->save();
                $content = Debug::vars( $post );
	
		$this->response->body($content);
	}

	public function action_view()
	{
		$id = (int) $this->request->param('id', 0);
		$post = ORM::factory('post', (int) $id);

		if( !$post->loaded() )
		{
			Message::error( __('Post: doesn\'t exists!') );
			Kohana::$log->add(Log::ERROR, 'Attempt to access non-existent post');
				
			if ( ! $this->_internal)
				$this->request->redirect(Route::get('post')->uri(array('action' => 'list')));
		}
	
		$this->title = $post->title;
		$view           = View::factory('post/post')
					->set('teaser', FALSE)
					->set('page_title', TRUE)
					->bind('post', $post);
	
		$this->response->body($view);
	
		if ( $this->auto_render === TRUE )
			Meta::links( URL::canonical( $post->url ), array('rel' => 'canonical'));
	}
	
	public function action_list()
	{
		//$this->debug = TRUE;
		$this->title    = __('Posts');
		$view           = View::factory('post/list')
						->set('teaser', TRUE)
						->bind('pagination', $pagination)
						->bind('posts', $posts);
		
		$posts       = ORM::factory('post');

		if( !ACL::check('administer content') )
		{
			$posts->where('status', '=', 'publish');
		}
	
		/** Bug in ORM to repeat the where() methods after using count_all()
		 *  @http://forum.kohanaframework.org/discussion/3956
		 *  @http://forum.kohanaframework.org/discussion/8234
		 *  @http://forum.kohanaframework.org/discussion/7736 -- solved
		 */
		$total      = $posts->reset(FALSE)->count_all();

		if ($total == 0)
		{
			Kohana::$log->add(Log::INFO, 'No posts found');
			$this->response->body( View::factory('post/none') );
			return;
		}
	
		$pagination = Pagination::factory(array(
				'current_page'   => array('source'=>'route', 'key'=>'page'),
				'total_items' => $total,
				'items_per_page' => 15,
				));
	
		$posts  = $posts->order_by('created', 'DESC')->limit($pagination->items_per_page)
							->offset($pagination->offset)->find_all();
                
                $this->response->body($view);
	
		if ( $this->auto_render === TRUE )
			Meta::links( URL::canonical( $this->request ), array('rel' => 'canonical'));
	}
        
} // End Post