<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Comment extends Template {
	//public $debug = TRUE;
	public function before()
	{
		ACL::Required('access comment');
		parent::before();
	}

        public function action_view()
	{
                $id = (int) $this->request->param('id', 0);
 
                $comment  = ORM::factory('comment', $id)->access('view');
                if( !$comment->loaded() )
		{
			Message::error( __('Comment: doesn\'t exists!') );
			Kohana::$log->add(Log::ERROR, 'Attempt to access non-existent comment');
				
			if ( ! $this->_internal)
				$this->request->redirect(Route::get('comment')->uri(array('action' => 'list')));
		}
        
                $this->title = $comment->title;
		$view = View::factory('comment/view')
				->set('auth', Auth::instance())
                                ->set('comment', $comment);
		
		$this->response->body($view);
	}
	
        public function action_edit()
	{
                $id = (int) $this->request->param('id', 0);
 
                $comment  = ORM::factory('comment', $id)->access('edit');
                if( !$comment->loaded() )
		{
			Message::error( __('Comment: doesn\'t exists!') );
			Kohana::$log->add(Log::ERROR, 'Attempt to access non-existent comment');
				
			if ( ! $this->_internal)
				$this->request->redirect(Route::get('comment')->uri(array('action' => 'list')));
		}
        
                $this->title = __('Edit Comment');
                $view = View::factory('comment/form')
                                ->set('use_captcha', FALSE)
				->set('is_edit', TRUE)
				->set('auth', Auth::instance())
				->set('item', $comment)
                                ->set('action', Request::current()->uri())
                                ->set('destination', array('destination' => $this->redirect))
				->bind('errors', $errors)
                                ->bind('post', $comment);

                if ( $this->valid_post('comment') )
                {
                        $route = Route::get('comment')->uri(array('action' => 'list'));
                        $redirect = empty($this->redirect) ? $route : $this->redirect ;
        
			try
			{
				$comment->values($_POST)->save();
				Message::success( __('Comment has been updated.') );
				Kohana::$log->add(LOG::INFO, 'Comment: :title updated.', array(':title' => $comment->title) );
				
				if ( ! $this->_internal) $this->request->redirect( $redirect );
			}
			catch (ORM_Validation_Exception $e)
			{
				$errors =  $e->errors('models');
				Message::error(__('Please see the erros below!'));
			}
		}
        
                $this->response->body($view);
        }
        
        public function action_delete()
	{
		$id = (int) $this->request->param('id', 0);
		$comment = ORM::factory('comment', $id)->access('delete');
        
		$this->title = __('Delete Comment', array('%title' => $comment->title ));
                $destination = empty($this->redirect) ? array() : array('destination' => $this->redirect);
                $post = $this->request->post();
		$route = Route::get('comment')->uri(array('action' => 'view', 'id' => $comment->id));
	
		$view = View::factory('form/confirm')
				->set('action', Route::get('comment')
						->uri(array('action' => 'delete', 'id' => $comment->id)).URL::query($destination) )
				->set('title', $comment->title);
	
		// If deletion is not desired, redirect to post
                if ( isset($post['no']) AND $this->valid_post() )
                        $this->request->redirect( empty($this->redirect) ? $route : $this->redirect );
        
                // If deletion is confirmed
                if ( isset($post['yes']) AND $this->valid_post() )
                {
			$redirect = $comment->post->url;
			$title = $comment->title;
			
                        try
                        {
				$comment->delete();
				Message::success(__('Comment: :title deleted successful!', array(':title' => $title)));
				Kohana::$log->add(LOG::INFO, 'Comment: :title deleted.', array(':title' => $title) );
                        }
                        catch (Exception $e)
                        {
				Kohana::$log->add(LOG::ERROR, 'Error occured deleting comment id: :id, :message',
							array(':id' => $comment->id, ':message' => $e->getMessage()));
				Message::error('An error occured deleting comment, :post.',array(':post' => $title));
                        }
		
			$redirect = empty($destination) ? $redirect : $this->redirect;
			if ( ! $this->_internal) $this->request->redirect( $redirect );
                }
	
		$this->response->body($view);
	}
        
}