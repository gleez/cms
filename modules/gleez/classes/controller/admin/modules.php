<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Modules extends Controller_Admin {
	
        public function before()
        {
                ACL::Required('administer site');
                parent::before();
        }
        
        public function action_index()
        {
		//clear any cache for sure
		Gleez::cache('load_modules', '');
		Module::load_modules(TRUE);
	
                $this->title  	= __('Modules');
                $this->response->body( View::factory('admin/module')->set('available', Module::available()) );
        }
        
        public function action_confirm()
        {
                if ( ! $this->valid_post('modules') )
                        throw new HTTP_Exception_403('Unauthorised access attempt to action');
                
                $messages     = array( "error" => array(), "warn" => array());
                $desired_list = array();
                foreach (Module::available() as $module_name => $info)
                {
                        if ($info->locked)
                        {
                                continue;
                        }
                        
                        if ($desired = ARR::get($_POST, $module_name) == 1)
                        {
                                $desired_list[] = $module_name;
                        }
			
                        if ($info->active AND !$desired AND Module::is_active($module_name))
                        {
                                $messages = array_merge($messages, Module::can_deactivate($module_name));
                        }
                        else if (!$info->active AND $desired AND !Module::is_active($module_name))
                        {
                                $messages = array_merge($messages, Module::can_activate($module_name));
                        }
                }
	
		//clear any cache for sure
		Gleez::cache_delete();
	
                if (empty($messages["error"]) AND empty($messages["warn"]))
                {
                        $this->_do_save();
                        $result["reload"] = 1;
                        $this->request->redirect(Route::get('admin/module')->uri());
                }
                else
                {
                        $v                        = new View("admin_modules_confirm.html");
                        $v->messages              = $messages;
                        $v->modules               = $desired_list;
                        $result["dialog"]         = (string) $v;
                        $result["allow_continue"] = empty($messages["error"]);
                }
                //print json_encode($result);
        }
        
        private function _do_save()
        {
                $changes             = new stdClass();
                $changes->activate   = array();
                $changes->deactivate = array();
                $activated_names     = array();
                $deactivated_names   = array();
                foreach ( Module::available() as $module_name => $info )
                {
                        if ($info->locked)
                        {
                                continue;
                        }
                        
                        try
                        {
                                $desired = ARR::get($_POST, $module_name) == 1;
                                if ($info->active AND !$desired AND Module::is_active($module_name))
                                {
                                        Module::deactivate($module_name);
                                        $changes->deactivate[] = $module_name;
                                        $deactivated_names[]   = __($info->name);
                                }
                                else if (!$info->active AND $desired AND !Module::is_active($module_name))
                                {
                                        if ( Module::is_installed($module_name) )
                                        {
                                                Module::upgrade($module_name);
                                        }
                                        else
                                        {
                                                Module::install($module_name);
                                        }
                                        
                                        Module::activate($module_name);
                                        $changes->activate[] = $module_name;
                                        $activated_names[]   = __($info->name);
                                }
                        }
                        catch (Exception $e)
                        {
                                Kohana::$log->add(LOG::ERROR, Kohana::exception_text($e));
                        }
                }
                
                Module::event('module_change', $changes);
                
                // @todo this type of collation is questionable from an i18n perspective
                if ($activated_names)
                {
                        Message::success(__("Activated: %names", array(
                                '%names' => join(", ", $activated_names)
                        )));
                }
		
                if ($deactivated_names)
                {
                        Message::success(__("Deactivated: %names", array(
                                '%names' => join(", ", $deactivated_names)
                        )));
                }
        
		//clear any cache for sure
		Gleez::cache_delete();
        }
	
}