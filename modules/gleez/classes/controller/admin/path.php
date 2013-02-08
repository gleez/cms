<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Path extends Controller_Admin {
	//public $debug = TRUE;

	public function before()
	{
		ACL::Required('administer paths');
		parent::before();
	}
	
	public function action_list()
	{
		$this->title    = __('Path Aliases');
		$view           = View::factory('admin/path/list')
						->bind('pagination', $pagination)
						->bind('paths', $paths);
		
		$path       = ORM::factory('path');
		$total      = $path->count_all();

		if ($total == 0)
		{
			Kohana::$log->add(Log::INFO, 'No paths found');
			$this->response->body( View::factory('admin/path/none') );
			return;
		}
	
		$pagination = Pagination::factory(array(
			'current_page'   => array('source'=>'route', 'key'=>'page'),
			'total_items' => $total,
			'items_per_page' => 25,
			));
		
		$paths  = $path->order_by('source', 'ASC')->limit($pagination->items_per_page)
							  ->offset($pagination->offset)->find_all();
                
                $this->response->body($view);
	}

	public function action_add()
	{
		$this->title = __('Add Alias');
		$view = View::factory('admin/path/form')
                                ->bind('errors', $errors)
                                ->bind('post', $post)
                                ->set('url', URL::site(null, TRUE));

		$post = ORM::factory('path');

		if( $this->valid_post('path') )
		{
			try
			{	
				$post->values($_POST)->save();
				
				Message::success(__('Alias: %name saved successful!', array('%name' => $post->source)));
				
				if ( ! $this->_internal)
					$this->request->redirect(Route::get('admin/path')->uri(array('action' => 'list')));
				
			}
                        catch (ORM_Validation_Exception $e)
			{
				$errors =  $e->errors('models');
			}
		}

		$this->response->body($view);
	}

	public function action_edit()
	{
		$id = (int) $this->request->param('id', 0);
		
		$post = ORM::factory('path', (int) $id);
		
		if( !$post->loaded() )
		{
			Message::error( __('Alias: doesn\'t exists!') );
			Kohana::$log->add(Log::ERROR, 'Attempt to access non-existent alias');
				
			if ( ! $this->_internal)
				$this->request->redirect(Route::get('admin/path')->uri(array('action' => 'list')));
		}

	
		$this->title = __('Edit Alias %name', array('%name' => $post->source));
		$view = View::factory('admin/path/form')->bind('errors', $errors)
                                ->bind('post', $post)
                                ->set('url', URL::site(null, TRUE));
	
		if ( $this->valid_post('path') )
		{
			try
			{
				$post->values($_POST)->save();
	
				Message::success(__('Alias: %name saved successful!', array('%name' => $post->source)));
				
				if ( ! $this->_internal)
					$this->request->redirect(Route::get('admin/path')->uri(array('action' => 'list')));
				
			}
			catch (ORM_Validation_Exception $e)
			{
				$errors = $e->errors();
			} 
		}
		
		$this->response->body($view);
	}

	public function action_delete()
	{
		$id = (int) $this->request->param('id', 0);
		
		$path = ORM::factory('path', $id);
		
		if ( ! $path->loaded())
		{
			Message::error(__('Alias: doesn\'t exists!'));
			Kohana::$log->add(Log::ERROR, 'Attempt to access non-existent alias');
			
			if ( ! $this->_internal)
				$this->request->redirect(Route::get('admin/path')->uri( array('action' => 'list') ));
		}

		$this->title = __('Delete Alias :title', array(':title' => $path->source ));
		
		$view = View::factory('form/confirm')
				->set('action', Route::url('admin/path', array('action' => 'delete', 'id' => $path->id) ))
				->set('title', $path->alias);
	
		// If deletion is not desired, redirect to list
                if ( isset($_POST['no']) AND $this->valid_post() )
                        $this->request->redirect(Route::get('admin/path')->uri());
	
		// If deletion is confirmed
                if ( isset($_POST['yes']) AND $this->valid_post() )
                {
			try
			{
				$path->delete();
				Message::success(__('Alias: :name deleted successful!', array(':name' => $path->alias)));
			
				if ( ! $this->_internal)
					$this->request->redirect(Route::get('admin/path')->uri( array('action' => 'list') ));
			}
			catch (Exception $e)
			{
				Kohana::$log->add(Log::ERROR, 'Error occured deleting alias id: :id, :message',
							array(':id' => $path->id, ':message' => $e->getMessage()));
				Message::error('An error occured deleting alias, :path.',array(':path' => $path->alias));
			
				if ( ! $this->_internal)
					$this->request->redirect(Route::get('admin/path')->uri( array('action' => 'list') ));
			}
		}
	
		$this->response->body($view);
	}

}
