<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * This is the API for handling modules.
 *
 * @package   Gleez\Modules
 * @author    Sandeep Sangamreddi - Gleez
 * @copyright (c) 2011-2013 Gleez Technologies
 * @license   http://gleezcms.org/license  Gleez CMS License
 *
 * @todo      [!!] This class does not do any permission checking
 */
class Gleez_Module {

        public static $active = array();
        public static $modules = array();
        public static $available = array();

        /**
         * Set the version of the corresponding Module_Model
         * @param string  $module_name
         * @param integer $version
         */
        static function set_version($module_name, $version)
        {
                $module = Module::get($module_name);

                if (!$module->loaded())
                {
                        $module->name   = $module_name;
                        $module->active = $module_name == 'gleez'; // only gleez is active by default
                }

                $module->version = $version;
                $module->save();

                Kohana::$log->add(LOG::DEBUG, ':module_name : version is now :version', array(
                                                        ':module_name' => $module_name, ':version' => $version) );
        }

        /**
         * Load the corresponding Model_Module
         * @param string $module_name
         */
        static function get($module_name)
        {
                if ( empty(self::$modules[$module_name]) OR !(self::$modules[$module_name] instanceof ORM) )
                {
                        return ORM::factory('module')->where('name', '=', $module_name)->find();
                }
                return self::$modules[$module_name];
        }

        /**
         * Get the information about a module
         * @returns ArrayObject containing the module information from the module.
         * info file or false if not found
         */
        static function info($module_name)
        {
                $module_list = self::available();
                return isset($module_list->$module_name) ? $module_list->$module_name : false;
        }

        /**
         * Check to see if a module is installed
         * @param string $module_name
         */
        static function is_installed($module_name)
        {
                return array_key_exists($module_name, self::$modules);
        }

        /**
         * Check to see if a module is active
         *
         * @param string $module_name
         *
         * @return boolean
         */
        static function is_active($module_name)
        {
                return array_key_exists($module_name, self::$active);
        }

        /**
         * Return the list of available modules, including uninstalled modules.
         */
        static function available()
        {
                if (empty(self::$available))
                {
                        $upgrade = FALSE;
                        $modules = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);

                        foreach (glob(MODPATH . "*/module.info") as $file)
                        {
                                $module_name           = basename(dirname($file));
                                $modules->$module_name = new ArrayObject(
                                        parse_ini_file($file), ArrayObject::ARRAY_AS_PROPS);

                                $m =& $modules->$module_name;
                                $m->installed    = self::is_installed($module_name);
                                $m->active       = self::is_active($module_name);
                                $m->code_version = $m->version;
                                $m->version      = self::get_version($module_name);
                                $m->locked       = false;

                                if ($m->active AND $m->version != $m->code_version)
                                {
                                        $upgrade = TRUE;
                                }
                        }

                        if ($upgrade)
                        {
                                Message::warn(__('Some of your modules are out of date. :upgrade_url',
                                    array(':upgrade_url' => HTML::anchor(Route::get('admin/module')->uri(array('action' => 'upgrade')), __('Upgrade now!')))));
                        }

                        // Lock certain modules
                        $modules->user->locked  = true;
                        $modules->gleez->locked = true;

                        $modules->ksort();
                        self::$available = $modules;
                }

                return self::$available;
        }

        /**
         * Return a list of all the active modules in no particular order.
         */
        static function active()
        {
                return self::$active;
        }

        /**
         * Check that the module can be activated. (i.e. all the prerequistes exist)
         * @param string $module_name
         * @return array an array of warning or error messages to be displayed
         */
        static function can_activate($module_name)
        {
                Module::_add_to_path($module_name);
                $messages = array();

                $installer_class = ucfirst($module_name).'_Installer';
                if (is_callable( array($installer_class, "can_activate") ))
                {
                        $messages = call_user_func(array(
                                $installer_class,
                                "can_activate"
                        ));
                }

                // Remove it from the active path
                Module::_remove_from_path($module_name);
                return $messages;
        }

        /**
         * Allow modules to indicate the impact of deactivating the specifeid module
         * @param string $module_name
         * @return array an array of warning or error messages to be displayed
         */
        static function can_deactivate($module_name)
        {
                $data = (object) array( "module" => $module_name, "messages" => array() );
                Module::event("pre_deactivate", $data);

                return $data->messages;
        }

        /**
         * Install a module.  This will call <module>_installer::install(), which is responsible for
         * creating database tables, setting module variables and calling module::set_version().
         * Note that after installing, the module must be activated before it is available for use.
         * @param string $module_name
         */
        static function install($module_name)
        {
                Module::_add_to_path($module_name);

                $installer_class = ucfirst($module_name).'_Installer';
                if (is_callable( array($installer_class, "install") ))
                {
                        call_user_func_array(array(
                                $installer_class,
                                "install"
                        ), array());
                }
                else
                {
                        Module::set_version($module_name, 1);
                }

                // Set the weight of the new module, which controls the order in which the modules are
                // loaded. By default, new modules are installed at the end of the priority list.  Since the
                // id field is monotonically increasing, the easiest way to guarantee that is to set the weight
                // the same as the id.  We don't know that until we save it for the first time
                $module = ORM::factory('module')->where('name', '=', $module_name)->find();
                if ($module->loaded())
                {
                        $module->weight = $module->id;
                        $module->save();
                }

        	//clear any cache for sure
		Gleez::cache('load_modules', '');

                Module::load_modules(TRUE);

                // Now the module is installed but inactive, so don't leave it in the active path
                Module::_remove_from_path($module_name);

                Kohana::$log->add(LOG::INFO, 'Installed module :module_name', array(':module_name' => $module_name) );
        }

        private static function _add_to_path($module_name)
        {
                $kohana_modules = Kohana::modules();
                array_unshift($kohana_modules, MODPATH . $module_name);
                Kohana::modules($kohana_modules);

                // Rebuild the include path so the module installer can benefit from auto loading
                Kohana::include_paths(true);
        }

        private static function _remove_from_path($module_name)
        {
                $kohana_modules = Kohana::modules();
                if (($key = array_search(MODPATH . $module_name, $kohana_modules)) !== false)
                {
                        unset($kohana_modules[$key]);
                        $kohana_modules = array_values($kohana_modules); // reindex
                }
                Kohana::modules($kohana_modules);
                Kohana::include_paths(true);
        }

        /**
         * Upgrade a module.  This will call <module>_installer::upgrade(), which is responsible for
         * modifying database tables, changing module variables and calling module::set_version().
         * Note that after upgrading, the module must be activated before it is available for use.
         * @param string $module_name
         */
        static function upgrade($module_name)
        {
                $version_before  = module::get_version($module_name);
                $installer_class = ucfirst($module_name).'_Installer';
                if (is_callable( array($installer_class, "upgrade") ))
                {
                        call_user_func_array(array(
                                $installer_class,
                                "upgrade"
                        ), array(
                                $version_before
                        ));
                }
                else
                {
                        $available = Module::available();
                        if (isset($available->$module_name->code_version))
                        {
                                Module::set_version($module_name, $available->$module_name->code_version);
                        }
                        else
                        {
                                throw new Exception("@todo UNKNOWN_MODULE");
                        }
                }

                // Now the module is upgraded so deactivate it, but we can'it deactivate gleez or user

                if ( !in_array($module_name, array('gleez', 'user')) )
                {
                        self::deactivate($module_name);
                }

        	//clear any cache for sure
		Gleez::cache('load_modules', '');

                Module::load_modules(TRUE);

                $version_after = Module::get_version($module_name);
                if ($version_before != $version_after)
                {
                        Kohana::$log->add(Log::INFO, "Upgraded module $module_name from $version_before to $version_after");
                }
        }

        /**
         * Activate an installed module.  This will call <module>_installer::activate() which should take
         * any steps to make sure that the module is ready for use.  This will also activate any
         * existing graphics rules for this module.
         * @param string $module_name
         */
        static function activate($module_name)
        {
                Module::_add_to_path($module_name);
                $installer_class = ucfirst($module_name).'_Installer';

                if (is_callable( array($installer_class, "activate")  ))
                {
                        call_user_func_array(array(
                                $installer_class,
                                "activate"
                        ), array());
                }

                $module = self::get($module_name);

                if ($module->loaded())
                {
                        $module->active = true;
                        $module->save();
                }

        	//clear any cache for sure
		Gleez::cache('load_modules', '');

                Module::load_modules(TRUE);

                //Widget::activate($module_name);
                //Menu_Item::rebuild(TRUE);

                Kohana::$log->add(LOG::INFO, 'Activated module :module_name', array(':module_name' => $module_name) );
        }

        /**
         * Deactivate an installed module.  This will call <module>_installer::deactivate() which should
         * take any cleanup steps to make sure that the module isn't visible in any way.  Note that the
         * module remains available in Kohana's cascading file system until the end of the request!
         * @param string $module_name
         */
        static function deactivate($module_name)
        {
                $installer_class = ucfirst($module_name).'_Installer';
                if (is_callable( array($installer_class, "deactivate") ))
                {
                        call_user_func_array(array(
                                $installer_class,
                                "deactivate"
                        ), array());
                }

                $module = self::get($module_name);
                if ($module->loaded())
                {
                        $module->active = false;
                        $module->save();
                }

        	//clear any cache for sure
		Gleez::cache('load_modules', '');

                Module::load_modules(TRUE);

                Kohana::$log->add(LOG::INFO, 'Deactivated module :module_name', array(':module_name' => $module_name) );
        }

        /**
         * Uninstall a deactivated module.  This will call <module>_installer::uninstall() which should
         * take whatever steps necessary to make sure that all traces of a module are gone.
         * @param string $module_name
         */
        static function uninstall($module_name)
        {
                $installer_class = ucfirst($module_name).'_Installer';
                if (is_callable( array($installer_class, "uninstall") ))
                {
                        call_user_func(array(
                                $installer_class,
                                "uninstall"
                        ));
                }

                $module = self::get($module_name);
                if ($module->loaded())
                {
                        $module->delete();
                }

                Module::load_modules(TRUE);
        
                //remove widgets when the module is uninstalled
                Widget::uninstall($module_name);

                Kohana::$log->add(LOG::INFO, 'Uninstalled module :module_name', array(':module_name' => $module_name) );
        }

        /**
         * Load the active modules.  This is called at bootstrap time.
         *
         * @param bool $reset reset true to clear the cache
         */
        static function load_modules( $reset = TRUE )
        {
                self::$modules  = array();
                self::$active   = array();
                $kohana_modules = array();
                $cache = Cache::instance('modules');

                if( $reset === FALSE AND $data = $cache->get('load_modules') AND isset($data['kohana_modules']) )
                {
                        //db has to be inisiated @todo fix this bug
                        Database::instance(NULL);

                        //use data from cache
                        self::$modules  = $data['modules'];
                        self::$active   = $data['active'];
                        $kohana_modules = $data['kohana_modules'];

                        unset($data);
                        Kohana::$log->add(LOG::DEBUG, 'Modules Loaded FROM Cache');
                }
                else
                {
                        $modules = ORM::factory('module')->order_by('weight','ASC')->order_by('name','ASC')->find_all();

                        $_cache_modules = $_cache_active = array();
                        foreach ($modules as $module)
                        {
                                self::$modules[$module->name]  = $module;
                                $_cache_modules[$module->name] = $module->as_array();

                                if ( ! $module->active ) continue;

                                if ($module->name == 'gleez')
                                {
                                        $gleez = $module;
                                }
                                else
                                {
                                        self::$active[$module->name]   = $module;
                                        $_cache_active[$module->name]  = $module->as_array();
                                        
                                        //try to get module path from db if it set
                                        if(!empty($module->path) AND is_dir($module->path))
                                        {
                                                $kohana_modules[$module->name] = $module->path;
                                        }
                                        else
                                        {
                                                $kohana_modules[$module->name] = MODPATH . $module->name;
                                        }
                                }
                        }

                        // put gleez last in the module list to match core.modules
                        self::$active['gleez']  = $gleez;
                        $_cache_active['gleez'] = $gleez->as_array();

                        //set cache for performance
                        $data = array();
                        $data['modules'] = $_cache_modules;
                        $data['active']  = $_cache_active;
                        $data['kohana_modules'] = $kohana_modules;

                        $cache->set('load_modules', $data, Date::DAY);
                        unset($data, $_cache_modules, $_cache_active);
                        Kohana::$log->add(LOG::DEBUG, 'Modules Loaded from ORM');
                }

                Kohana::modules( array_merge($kohana_modules, Kohana::modules()) );
        }

        /**
         * Check to see if a module installed and active
         * @param string $module_name
         */
        public static function exists($module_name)
        {
                return self::is_active($module_name);
        }

        /**
         * Run a specific event on all active modules.
         * @param string $name the event name
         * @param mixed  $data data to pass to each event handler
         */
        public static function event($name, &$data = null)
        {
                $args = func_get_args();
                array_shift($args);
                $function = str_replace(".", "_", $name);

                if (method_exists('Gleez_Event', $function)) {
                        switch (count($args)) {
                                case 0:
                                        Gleez_Event::$function();
                                        break;
                                case 1:
                                        Gleez_Event::$function($args[0]);
                                        break;
                                case 2:
                                        Gleez_Event::$function($args[0], $args[1]);
                                        break;
                                case 3:
                                        Gleez_Event::$function($args[0], $args[1], $args[2]);
                                        break;
                                case 4: // Context menu events have 4 arguments so lets optimize them
                                        Gleez_Event::$function($args[0], $args[1], $args[2], $args[3]);
                                        break;
                                default:
                                        call_user_func_array(array( 'Gleez_Event', $function ), $args);
                        }
                }

                foreach (self::$active as $name => $module)
                {
                        if ($name == 'gleez') {
                                continue;
                        }
                        $class = "{$name}_Event";
                        if (is_callable( array($class, $function) ))
                        {
                                try
                                {
                                       call_user_func_array(array( $class, $function ), $args);
                                }
                                catch(Exception $e){}
                        }
                }

        }

        /**
	 * Call to execute a Module action
	 * @param string The name of the action to execute
	 * @param mixed The value to action.
	 */
	public static function action()
	{
		list( $action, $return ) = func_get_args();
                $function = str_replace(".", "_", $action);
                $filterargs = array_slice(func_get_args(), 2);

		foreach ( self::$active as $name => $module )
                {
                        $class = "{$name}_Action";
			$args = $filterargs;
			array_unshift( $args, $return );

                        if (is_callable( array($class, $function) ))
                        {
                                try
                                {
                                        $return = call_user_func_array(array( $class, $function ), $args);
                                }
                                catch(Exception $e){}
                        }
		}

		return $return;
        }

        /**
         * Return the version of the installed module.
         * @param string $module_name
         */
        static function get_version($module_name)
        {
                return self::get($module_name)->version;
        }

}
