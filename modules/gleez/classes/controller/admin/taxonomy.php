<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Taxonomy extends Controller_Admin {

	public function before()
	{
		ACL::Required('administer terms');
		parent::before();
	}

	public function action_list()
	{
		$view = View::factory('admin/taxonomy/list')->bind('pagination', $pagination)->bind('terms', $terms);
		$this->title = __('Vocabulary');
	
		$terms  = ORM::factory('term')->where('lft', '=', 1);
		$total  = $terms->reset(FALSE)->count_all();	

		if ($total == 0)
		{
			Kohana::$log->add(Log::INFO, 'No terms found');
			$this->response->body( View::factory('admin/taxonomy/none') );
			return;
		}
	
		$pagination = Pagination::factory(array(
				'current_page'   => array('source'=>'route', 'key'=>'page'),
				'total_items' => $total,
				'items_per_page' => 25,
				));
		
		$terms  = $terms->limit($pagination->items_per_page)->offset($pagination->offset)->find_all();
	
		$this->response->body($view);
	}
	
	public function action_add()
	{
		$this->title = __('Add Vocab');
		$view = View::factory('admin/taxonomy/form')->bind('post', $post)->bind('errors', $errors);
		$post = ORM::factory('term');
	
		if ($this->valid_post('vocab'))
		{
			$post->values($_POST);
			try
			{
				$post->make_root();
				
				Message::success(__('Vocab: %name saved successful!', array('%name' => $post->name)));
				
				// Redirect to listing
				if ( ! $this->_internal)
					$this->request->redirect( Route::get('admin/taxonomy')->uri() );
				
			}
			catch (ORM_Validation_Exception $e)
			{
				$errors = $e->errors();
			} 
		}
	
		$this->response->body($view);
	}

	public function action_edit()
	{
		$id = (int) $this->request->param('id', 0);
		$post = ORM::factory('term', $id);
	
		if ( ! $post->loaded())
		{
			Message::error(__('Vocab: doesn\'t exists!'));
			Kohana::$log->add(Log::ERROR, 'Attempt to access non-existent Vocab');
			
			if ( ! $this->_internal)
				$this->request->redirect( Route::get('admin/taxonomy')->uri() );
		}
	
		$this->title = __( 'Edit Vocab: :name', array(':name' => $post->name) );
		$view = View::factory('admin/taxonomy/form')->bind('post', $post)->bind('errors', $errors);
	
		if ($this->valid_post('vocab'))
		{
			$post->values($_POST);
			try
			{
				$post->save();
				
				Message::success(__('Vocab: %name saved successful!', array('%name' => $post->name)));
				
				// Redirect to listing
				if ( ! $this->_internal)
					$this->request->redirect( Route::get('admin/taxonomy')->uri() );
				
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
		$term = ORM::factory('term', $id);
	
		if ( ! $term->loaded())
		{
			Message::error(__('Taxonomy: doesn\'t exists!'));
			Kohana::$log->add(Log::ERROR, 'Attempt to access non-existent taxonomy');
			
			if ( ! $this->_internal)
				$this->request->redirect(Route::get('admin/taxonomy')->uri( array('action' => 'list') ));
		}

		$this->title = __('Delete Taxonomy :title', array(':title' => $term->name ));
		
		$view = View::factory('form/confirm')
				->set('action', Route::url('admin/taxonomy', array('action' => 'delete', 'id' => $term->id) ))
				->set('title', $term->name);
	
		// If deletion is not desired, redirect to list
                if ( isset($_POST['no']) AND $this->valid_post() )
                        $this->request->redirect(Route::get('admin/taxonomy')->uri());
	
		// If deletion is confirmed
                if ( isset($_POST['yes']) AND $this->valid_post() )
                {
			try
			{
				$term->delete();
				Message::success(__('Taxonomy: :name deleted successful!', array(':name' => $term->name)));
			
				if ( ! $this->_internal)
					$this->request->redirect(Route::get('admin/taxonomy')->uri( array('action' => 'list') ));
			}
			catch (Exception $e)
			{
				Kohana::$log->add(Log::ERROR, 'Error occured deleting taxonomy id: :id, :message',
							array(':id' => $term->id, ':message' => $e->getMessage()));
				Message::error('An error occured deleting taxonomy, :term.',array(':term' => $term->name));
			
				if ( ! $this->_internal)
					$this->request->redirect(Route::get('admin/taxonomy')->uri( array('action' => 'list') ));
			}
		}
	
		$this->response->body($view);
	}
	
}