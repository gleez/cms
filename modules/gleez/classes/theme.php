<?php
/**
 * Theme helper for adding content to views
 *
 * This is the API for handling themes.
 * Code taken from Gallery3.
 *
 * @package    Gleez\Theme
 * @author     Gleez Team
 * @version    1.0.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 *
 * @todo       This class does not do any permission checking
 */
class Theme {

	/** @type string INFO_FILE Theme info filename */
	const INFO_FILE = 'theme.info';

	/**
	 * Active theme name
	 * @var string
	 */
	public static $active = 'fluid';
	
	/**
	 * Site theme name
	 * @var string
	 */
	public static $site_theme_name = 'fluid';

	/**
	 * Admin theme name
	 * @var string
	 */
	public static $admin_theme_name = 'fluid';

	/**
	 * Admin?
	 * @var boolean
	 */
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

		//set admin theme based on path info
		$path = ltrim(Request::detect_uri(), '/');
		Theme::$is_admin = ( $path == "admin" || !strncmp($path, "admin/", 6) );

		if (Theme::$is_admin)
		{
			// Load the admin theme
			Theme::$admin_theme_name = $config->get('admin_theme', Theme::$admin_theme_name);
			Theme::$active = Theme::$admin_theme_name;
		}
		else
		{
			// Load the site theme
			Theme::$site_theme_name = $config->get('theme', Theme::$site_theme_name);
			Theme::$active = Theme::$site_theme_name;
		}
	
		//Set mobile theme, if enabled and mobile request
		if(Request::is_mobile() AND $config->get('mobile_theme', FALSE))
		{
			// Load the mobile theme
			Theme::$site_theme_name = $config->get('mobile_theme', Theme::$site_theme_name);
			Theme::$active = Theme::$site_theme_name;
		}
	
		// Admins can override the site theme, temporarily. This lets us preview themes.
		if (User::is_admin() AND isset($_GET['theme']) AND $override = $_GET['theme'])
		{
			if (file_exists(THEMEPATH.$override))
			{
				Theme::$site_theme_name  = $override;
				Theme::$admin_theme_name = $override;
				Theme::$active 		 = $override;
			}
			else
			{
				Log::error('Missing override site theme: :theme', array(':theme' => $override));
			}
		}

		//Finally set the active theme
		Theme::set_theme();
	}

	/**
	 * Sets active theme if none supplied or uses the supplied one 
	 *
	 * @param  boolean|string  $theme  Theme name [Optional]
	 */
	public static function set_theme($theme = FALSE)
	{
		if( !empty($theme)) Theme::$active = $theme;

		$modules = Kohana::modules();
		if( ! empty(Theme::$active) AND ! in_array(Theme::$active, array_keys($modules)))
		{
			//set absolute theme path and load the request theme as kohana module
			Kohana::modules(array('theme' => THEMEPATH.Theme::$active) + $modules);
		}
	
		unset($modules);
	}
	
	/**
	 * Gets info about theme
	 *
	 * @param   string       $theme_name   Theme name
	 * @return  \Object  	 An array containing information about theme
	 */
	public static function get_info($theme_name)
	{
		$info_file               = THEMEPATH . $theme_name . DS . Theme::INFO_FILE;
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
			$info_file = THEMEPATH . $theme_name . DS . Theme::INFO_FILE;
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
	
	public static function route_list()
	{
		$themes = array();
		
		$cache = Cache::instance('themes');

		if ( ! $themes = $cache->get('themes_route', false))
		{
			foreach (scandir(THEMEPATH) as $theme_name)
			{
				$info_file = THEMEPATH . $theme_name . DS . Theme::INFO_FILE;
				
				// File can be exists and can be readable
				if (is_readable($info_file))
				{
					// Skip hidden files
					if ($theme_name[0] == ".")
					{
						continue;
					}
					$themes[$theme_name] = $theme_name;
				}
			}
		
			$cache->set('themes_route', $themes, DATE::DAY);
		}
		
		return implode("|", $themes);
	}

}
