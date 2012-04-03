<?php defined('SYSPATH') or die('404 Not Found.');

class Controller_Admin_Permission extends Controller_Admin {
	
	public function action_list()
	{  	
		$this->title    = __('Permissions');
                
		$view           = View::factory('admin/permission')
                                        ->set('permissions', ACL::all())
                                        ->bind('errors', $errors)
                                        ->bind('perms', $role_perms)
                                        ->bind('roles', $roles);
        
	  	$roles = ORM::factory('role')->order_by('name', 'ASC')->find_all();
		$role_perms = DB::select()->from('permissions')->as_object()->execute(); 

		$errors = array();
		$this->response->body($view);
	
		if( $this->valid_post('roles') )
		{
			$per_insert = DB::insert('permissions', array('rid', 'permission', 'module'));   

			foreach ($_POST['roles'] as $id => $role) 
			{
				foreach($role as $key => $val)
				{
					if( isset($val['name']))
					{
						//Message::success( Kohana::debug($val) );
						$per_insert->values(array($val['id'], $val['name'], $val['module']));
					}
				}
			}
		
			try
			{
				DB::delete('permissions')->execute();
				$per_insert->execute();
				
				Message::success(__('Permissions: saved successful!'));
				$this->request->redirect(Route::get('admin/permission')->uri());
			}
			catch(Validate_Exception $e)
			{
				$errors = $e->array->errors('permissions');
			}
		}
	}


}