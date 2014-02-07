<?php

class Controller_Client extends Template {

	public function action_list()
	{ 
		if ( Request::is_datatables() )
		{
			if ( ! ACL::check('access oaclient2'))
			{
				throw new HTTP_Exception_404('You have no permission to access oauth2 clients.');
			}
			
			$posts = ORM::factory('oaclient');
			
			if ( ! User::is_admin())
			{
				$user = Auth::instance()->get_user();
				$posts->where('user_id', '=', $user->id);
			}
			
			$this->_datatables = $posts->dataTables( array('title', 'client_id', 'user_id', 'created') );
		
			foreach ($this->_datatables->result() as $oaclient)
			{
			    
				$this->_datatables->add_row(array
				(
				    HTML::anchor($oaclient->url ,Text::plain($oaclient->title)),
				    $oaclient->client_id,
				    $oaclient->user->nick,
				    System::date('M d, Y',$oaclient->created),
				    HTML::icon($oaclient->edit_url, 'fa-edit', array('class'=>'action-edit', 'data-toggle' => 'popup1', 'title'=> __('Edit'))) . '&nbsp;' .
				    HTML::icon($oaclient->delete_url, 'fa-trash-o', array('class'=>'action-delete', 'data-toggle' => 'popup', 'title'=> __('Delete')))
				));
			}
		}

		$this->title = __('Oauth Clients');

		$view = View::factory('client/list')
				->bind('datatables', $this->_datatables)
				->set('url', Route::url('oauth2/client', array('action' => 'list'), TRUE))
				->set('show', TRUE);
		
		$this->response->body($view);
	}
	    
	public function action_Register()
	{
		if ( ! ACL::check('administer oauth2'))
		{
			throw new HTTP_Exception_404('You have no permission to add oauth2 clients.');
		}
		
		$this->title = __('Oaclient Registration');
		$grant_types = Config::get('oauth2.grant_types');
		$view        = View::factory('client/form')->set('grant_types', $grant_types)->bind('oaclient', $oaclient)->bind('errors', $this->_errors);
		
		$oaclient = ORM::factory('oaclient');
		
		if ( isset($_POST['cancel']) AND $this->valid_post() )
		{
		    $this->request->redirect(Route::get('oauth2/client')->uri(array('action' => 'list')));
		}
		
		if ($this->valid_post('save'))
		{
		    $oaclient->values($this->request->post());
		    
		    try
		    {
			    if (isset($_POST['grant_types']) && ! empty($_POST['grant_types']))
			    {
					$grant_types_selected = implode(" ", $_POST['grant_types']);
					$oaclient->grant_types = $grant_types_selected;
			    }
			    
			    if (isset($_FILES) AND isset($_FILES['logo']))
			    {
				    $filename = uniqid().preg_replace('/\s+/u', '_', $_FILES['logo']['name']);
			    
				    if( $file = Upload::save($_FILES['logo'], $filename, APPPATH.'/media/logos') )
				    {
					    $oaclient->logo = $filename;
				    }
			    }
			
				$oaclient->save();
				Message::success( __('Client registered :title ', array(':title' => $oaclient->title)) );
				$this->request->redirect(Route::get('oauth2/client')->uri(array('action' => 'list')));
		    }
		    catch(ORM_Validation_Exception $e)
		    {
				$this->_errors = $e->errors('models');
		    }
		}
		
		$this->response->body($view);
	}

	public function action_edit()
	{
		if ( ! ACL::check('edit oaclient2'))
		{
			throw new HTTP_Exception_404('You have no permission to edit oauth2 clients.');
		}
		
		$id       = (int) $this->request->param('id');
		$oaclient = ORM::factory('oaclient', $id);
		
		if( ! $oaclient->loaded() )
		{
			Message::error( __('Client: doesn\'t exists!') );
			Kohana::$log->add(Log::ERROR, 'Attempt to edit non-existent client');
				
			$this->request->redirect(Route::get('oauth2/client')->uri(array('action' => 'list')));
		}
		
		if ( isset($_POST['cancel']) AND $this->valid_post() )
		{
		    $this->request->redirect(Route::get('oauth2/client')->uri(array('action' => 'list')));
		}
		
		if ($this->valid_post('save'))
		{
		    $oaclient->values($this->request->post());
		    
		    try
		    {
			    //$grant_types_selected = 'authorization_code';
			    if (isset($_POST['grant_types']) && ! empty($_POST['grant_types']))
			    {
				$grant_types_selected = implode(" ", $_POST['grant_types']);
				$oaclient->grant_types = $grant_types_selected;
			    }
			    
			    if (isset($_FILES) AND isset($_FILES['logo']))
			    {
				    $filename = uniqid().preg_replace('/\s+/u', '_', $_FILES['logo']['name']);
			    
				    if( $file = Upload::save($_FILES['logo'], $filename, APPPATH.'/media/logos') )
				    {
					    $oaclient->logo = $filename;
				    }
			    }

				$oaclient->save();
				Message::success( __('Client :title updated successfully', array(':title' => $oaclient->title)) );
				$this->request->redirect(Route::get('oauth2/client')->uri(array('action' => 'list')));
		    }
		    catch(ORM_Validation_Exception $e)
		    {
				$this->_errors = $e->errors('models');
		    }
		}
		
		$grant_types    = Config::get('oauth2.grant_types');
		$this->title    = __('Edit oaclient');
		$this->subtitle = Text::plain($oaclient->title);
		$view           = View::factory('client/form')
							->set('grant_types', $grant_types)
							->bind('oaclient', $oaclient)
							->bind('errors', $this->_errors);
		
		$this->response->body($view);
	}

	public function action_view()
	{
		if ( ! ACL::check('access oaclient2'))
		{
			throw new HTTP_Exception_404('You have no permission to access oauth2 clients.');
		}
		
		$id       = (int) $this->request->param('id');
		$oaclient = ORM::factory('oaclient', $id);
		
		if( ! $oaclient->loaded() )
		{
			Message::error( __('Client: doesn\'t exists!') );
			Kohana::$log->add(Log::ERROR, 'Attempt to edit non-existent client');
				
			$this->request->redirect(Route::get('oauth2/client')->uri(array('action' => 'list')));
		}
		
		$this->title    = __('Client info');
		$this->response->body(View::factory('client/view')->set('oaclient', $oaclient));
	}

	public function action_delete()
	{
		if ( ! ACL::check('delete oaclient2'))
		{
			throw new HTTP_Exception_404('You have no permission to delete oauth2 clients.');
		}
		
		$id       = (int) $this->request->param('id');
		$redirect = empty($this->redirect) ? Route::get('oauth2/client')->uri(array('action' => 'list')) : $this->redirect;
		$oaclient  = ORM::factory('oaclient', $id);
		
		if ( ! $oaclient->loaded() )
		{
			Message::error( __('oaclient: doesn\'t exists!') );
			Kohana::$log->add(Log::ERROR, 'Attempt to delete non-existent oaclient');
				
			$this->request->redirect(Route::get('oauth2/client')->uri(array('action' => 'list')));
		}
		
		$clone_oaclient = clone $oaclient;
		
		if ( ! Access::oaclient('delete', $oaclient) )
		{
			// If the lead was not loaded, we return access denied.
			throw new HTTP_Exception_404('Attempt to non-existent oaclient.');
		}
		
		$this->title    = __('Delete oaclient');
		$this->subtitle = Text::plain($oaclient->client_id);
		$form = View::factory('form/confirm')->set('action', $oaclient->delete_url)->set('title', $oaclient->client_id);

		// If deletion is not desired, redirect to post
		if ( isset($_POST['no']) AND $this->valid_post() )
			$this->request->redirect( 'oauth2/client' );

		// If deletion is confirmed
		if ( isset($_POST['yes']) AND $this->valid_post() )
		{
			try
			{
				$oaclient->delete();

				Message::success( __('oaclient: :title deleted successfully', array(':title' => $clone_oaclient->client_id)) );
				$this->request->redirect($redirect);
			}
			catch(Exception $e)
			{
				Message::error( __('oaclient: :title unable to delete the record', array(':title' => $clone_oaclient->client_id)) );
				$this->request->redirect($redirect);
			}			
		}
		
		$this->response->body($form);		
	}
}