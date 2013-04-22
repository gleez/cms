<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Theme helper for adding content to views
 *
 * This is the API for handling themes.
 * Code taken from Gallery3.
 *
 * @package    Gleez\Theme
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 *
 * @todo       This class does not do any permission checking
 */
class Gleez_Theme {

        /** @var string The file contains information about theme */
        const INFO_FILE = 'theme.info';

        /** @var string Site theme name */
        public static $site_theme_name = 'fluid';

        /** @var string Admin theme name */
        public static $admin_theme_name = 'fluid';

        /** @var boolean Admin? */
        public static $is_admin = FALSE;

        /**
         * Load the active theme.
         *
         * This is called at bootstrap time.
         * We will only ever have one theme active for any given request.
         *
         * @uses Kohana::modules
         */
        public static function load_themes()
        {
                $config = Kohana::$config->load('site');
                $modules = Kohana::modules();

                //set admin theme based on path info
                $path = ltrim(Request::detect_uri(), '/');
                Theme::$is_admin = ( $path == "admin" || !strncmp($path, "admin/", 6) );

                if (self::$is_admin)
                {
                        // Load the admin theme
                        self::$admin_theme_name = $config->get('admin_theme', self::$admin_theme_name);
                        $theme = THEMEPATH . self::$admin_theme_name;
                }
                else
                {
                        // Load the site theme
                        self::$site_theme_name = $config->get('theme', self::$site_theme_name);
                        $theme = THEMEPATH . self::$site_theme_name;
                }
        
                // Admins can override the site theme, temporarily. This lets us preview themes.
                if (User::is_admin() AND isset($_GET['theme']) AND $override = $_GET['theme'])
                {
                        if (file_exists(THEMEPATH . $override))
                        {
                                self::$site_theme_name  = $override;
                                self::$admin_theme_name = $override;
                                $theme = THEMEPATH . self::$site_theme_name;
                        }
                        else
                        {
                                Kohana::$log->add(LOG::ERROR, 'Missing override site theme: :theme', array(':theme' => $override));
                        }
                }

                // set modules with active theme
                array_unshift($modules, $theme);
                
                Kohana::modules($modules);

                // Clean up
                unset($modules, $theme);
        }

        /**
         * Gets info about theme
         *
         * @param   string        $theme_name   Theme name
         * @return  \ArrayObject  An array containing information about theme
         */
        public static function get_info($theme_name)
        {
                $info_file               = THEMEPATH . $theme_name . DIRECTORY_SEPARATOR . Theme::INFO_FILE;
                $theme_info              = (object) parse_ini_file($info_file, true);
                $theme_info->title       = __($theme_info->title);
                $theme_info->description = __($theme_info->description);

                // Add i18n support
                if (isset($theme_info->regions) AND ! empty($theme_info->regions))
                {
                        foreach ($theme_info->regions as $name => $title)
                        {
                                $theme_info->regions[$name] = __($title);
                        }
                }

                return $theme_info;
        }

        /**
         * Gets list of available themes
         *
         * @param   boolean $title returns only title if its true or full object
         * @return  array  Available themes array
         */
        public static function available($title = TRUE)
        {
                $themes = array();

                foreach (scandir(THEMEPATH) as $theme_name)
                {
                        $info_file = THEMEPATH . $theme_name . DIRECTORY_SEPARATOR . Theme::INFO_FILE;
                        // File can be exists and can be readable
                        if (is_readable($info_file))
                        {
                                // Skip hidden files
                                if ($theme_name[0] == ".")
                                {
                                        continue;
                                }
                                
                                $theme               = Theme::get_info($theme_name);
                                $themes[$theme_name] = ($title === TRUE) ? $theme->title : $theme;
                        }
                }

                return $themes;
        }

        public static function set_admin_theme()
        {
                $config = Kohana::$config->load('site');
                $modules = Kohana::modules();

                // Load the theme info from config
                self::$admin_theme_name = $config->get('admin_theme', self::$admin_theme_name);
                self::$site_theme_name = $config->get('theme', self::$site_theme_name);

                //unset base site theme while in admin
                if (($key = array_search(THEMEPATH . self::$site_theme_name.DIRECTORY_SEPARATOR, $modules)) !== false)
                {
                        unset($modules[$key]);
                        $modules = array_values($modules); // reindex
                }

                // set modules with active theme
                array_unshift($modules, THEMEPATH . self::$admin_theme_name);
                
                Kohana::modules($modules);

                // Clean up
                unset($modules);
        }
}
