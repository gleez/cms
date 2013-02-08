<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Format extends Controller_Admin {

        public function action_index()
        {
                $config = Kohana::$config->load('inputfilter');
                $fallback_format = (int) $config->get('default_format', 1);
                $rl = ORM::factory('role')->find_all();
                
                $formats = array();
                foreach ($config->formats as $id => $format)
                {
                        $formats[$id]['#is_fallback'] = ($id == $fallback_format);
                        $formats[$id]['name'] = HTML::chars($format['name']);
                        $formats[$id]['weight'] = HTML::chars($format['weight']);
                        
                        if ($formats[$id]['#is_fallback'])
                        {
                              $roles_markup = __('All roles may use this format');
                        }
                        else
                        {
                                $roles = $format['roles'];
                                
                                $roles_markup = $roles ? implode(', ', $roles) : __('No roles may use this format');
                        }
                        $formats[$id]['roles'] = $roles_markup;
                }
        
                $this->title   = __('Text formats');
                $view = View::factory('admin/format/list')->set('formats', $formats);
                
                $this->response->body($view);
        
                if ( ! $this->_internal)
		{
                        Assets::tabledrag('text-format-order', 'order', 'sibling', 'text-format-order-weight');
		}
        }

        public function action_configure()
        {
                $id = (int) $this->request->param('id');
                $config = Kohana::$config->load('inputfilter');
        
                if(!array_key_exists($id, $config->formats))
		{
			Message::error(__('Text Format doesn\'t exists!'));
			Kohana::$log->add(LOG::ERROR, 'Attempt to access non-existent format id :id', array(':id' => $id));
			
			if ( ! $this->_internal)
				$this->request->redirect( Route::get('admin/format')->uri() );
		}
	
                $fallback_format = (int) $config->default_format;
                $formats = $config->formats;
                $formats[$id]['id'] = $id;
        
                $all_roles = ORM::factory('role')->find_all()->as_array('id', 'name');
                
                $filters = InputFilter::filters();
                $enabled_filters = $formats[$id]['filters'];//Message::error( Kohana::debug($formats[$id]['filters']) );
                
                $this->title     = __('Configure :name', array(':name' => $formats[$id]['name']));
                $view            = View::factory('admin/format/form')
                                                        ->set('roles', $all_roles)
                                                        ->set('filters', $filters)
                                                        ->set('enabled_filters', $enabled_filters)
                                                        ->set('format', $formats[$id]);
                
                if ($this->valid_post('filter'))
		{
			//Message::debug( Debug::vars($formats[1]) );
			unset($_POST['filter'], $_POST['_token'], $_POST['_action']);
			//Message::debug( Debug::vars($_POST) );
                        Message::info( __('Not implemented yet!') );
                }
        
                $this->response->body($view);
        
                if ( ! $this->_internal)
		{
                        Assets::tabledrag('filter-order', 'order', 'sibling', 'filter-order-weight', NULL, NULL, TRUE);
		}
        }
}