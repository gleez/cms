<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Theme helper for adding content to views.
 *
 *
 * This is the API for handling themes.
 *
 * Note: by design, this class does not do any permission checking.
 *
 * Code taken from Gallery3
 *
 * @package	Gleez
 * @category	Theme
 * @author	Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2012 Gleez Technologies
 * @license	http://gleezcms.org/license
 */
class Gleez_Theme {
        
        /**
	 * @var  string  site theme name
	 */
	public static $site_theme_name = 'anytime';

        /**
	 * @var  string  admin theme name
	 */
	public static $admin_theme_name = 'anytime';
        
	/**
	 * @var  boolean  admin?
	 */
	public static $is_admin = FALSE;
        
        /**
         * Load the active theme. This is called at bootstrap time.
         * We will only ever have one theme active for any given request.
         */
        public static function load_themes()
        {
                $modules                = Kohana::modules();
                $config                 = Kohana::$config->load('site');
                self::$site_theme_name  = $config->get('theme', 'anytime');
                
                if (self::$is_admin)
                {
                        // Load the admin theme
                        self::$admin_theme_name = $config->get('admin_theme', 'anytime');
                        $array  = array( self::$admin_theme_name => THEMEPATH . self::$admin_theme_name );

                        /*
                        // If the site theme has an admin subdir, load that as a module so that
                        // themes can provide their own code.
                        if (file_exists(THEMEPATH . self::$site_theme_name . '/admin'))
                        {
                                $array = array( self::$site_theme_name => self::$site_theme_name . '/admin' );
                        }
                       */
                }
                else
                {
                        $array = array( self::$site_theme_name => THEMEPATH . self::$site_theme_name );
                }
        
                Kohana::modules($array + $modules);
        
                // Clean up
                unset($modules, $array);
        }
        
        public static function get_info($theme_name)
        {
                //$theme_name              = preg_replace("/[^\w]/", "", $theme_name);
                $file                    = THEMEPATH . "$theme_name/theme.info";
                $theme_info              = new ArrayObject(parse_ini_file($file), ArrayObject::ARRAY_AS_PROPS);
                $theme_info->description = __($theme_info->description);
                $theme_info->name        = __($theme_info->name);
                
                return $theme_info;
        }
        
        public static function avaliable()
        {
                $themes = array();
                
                foreach (scandir(THEMEPATH) as $theme_name)
                {
                        if (file_exists(THEMEPATH . "$theme_name/theme.info"))
                        {
                                if ($theme_name[0] == ".")
                                {
                                        continue;
                                }
                                $theme = Theme::get_info($theme_name);
                                $themes[$theme_name] = $theme->name;
                        }
                }
                
                return $themes;
        }
        
}