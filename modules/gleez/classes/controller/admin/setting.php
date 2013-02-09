<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Setting extends Controller_Admin {
        
        public function before()
        {
                ACL::Required('administer site');
                parent::before();
        }

        public function action_index()
        {
                $this->title = __('General Settings');
                $config = Kohana::$config->load('site');
	
                if(isset($config['maintenance_mode']) AND $config['maintenance_mode'] == 1)
                {
                        Message::success(__('Site running in maintenance mode!'));
                }
	
                $view = View::factory('admin/settings')
                                ->set('date_time_formats', Date::date_time_formats(1))
                                ->set('date_formats', Date::date_formats(1))
                                ->set('time_formats', Date::time_formats(1))
                                ->set('date_weekdays', Date::weeekdays())
                                ->set('timezones', Date::timezones())
                                ->bind('title', $this->title)
                                ->set('post', $config);
	
                if ($this->valid_post('settings'))
                {
                        unset($_POST['settings'], $_POST['_token'], $_POST['_action']);
		
                        foreach($_POST as $key => $value)
                        {
                                $config->set($key, $value);
				
				if($key == 'front_page' )
				{
					$this->_set_front_page($value);
				}
                        }
	    
                        Message::success(__('Site configuration updated!'));
                        $this->request->redirect(Route::get('admin/setting')->uri());
                }
        
                $this->response->body($view);
        }


	/**
	 * Sets Front page route
	 *
	 * @return void
	 */
	private function _set_front_page($source)
	{
		//Delete previous alias if any
		Path::delete( array( 'alias' => '<front>' ) );
	
		// create and save alias
		$values = array();
		$values['source'] = $source;
		$values['alias']  = '<front>' ;
		
		return Path::save($values);
	}
}