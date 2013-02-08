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
 * @package   Gleez
 * @category  Theme
 * @author    Sandeep Sangamreddi - Gleez
 * @copyright (c) 2012 Gleez Technologies
 * @license   http://gleezcms.org/license
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

                if (self::$is_admin)
                {
                        // Load the admin theme
                        self::$admin_theme_name = $config->get('admin_theme', self::$admin_theme_name);
                        $array                  = array(
                                self::$admin_theme_name => THEMEPATH . self::$admin_theme_name
                        );
                }
                else
                {
                        // Load the site theme
                        self::$site_theme_name = $config->get('theme', self::$site_theme_name);
                        $array                 = array(
                                self::$site_theme_name => THEMEPATH . self::$site_theme_name
                        );
                }

                // Merging array of modules
                Kohana::modules(Arr::merge($array, Kohana::modules()));

                // Clean up
                unset($array);
        }

        /**
         * Gets info abou theme
         *
         * @param   string        $theme_name   Theme name
         * @return  \ArrayObject  An array containinig information about theme
         */
        public static function get_info($theme_name)
        {
                $info_file               = THEMEPATH . $theme_name . DIRECTORY_SEPARATOR . Theme::INFO_FILE;
                $theme_info              = new ArrayObject(parse_ini_file($info_file, true), ArrayObject::ARRAY_AS_PROPS);
                $theme_info->title       = __($theme_info->title);
                $theme_info->description = __($theme_info->description);

                return $theme_info;
        }

        /**
         * Gets list of avaliable themes
         *
         * @return  array  Avaliable themes aray
         */
        public static function avaliable()
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
                                $themes[$theme_name] = $theme->name;
                        }
                }

                return $themes;
        }

}
