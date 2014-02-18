<?php
/**
 * Theme helper for adding content to views
 *
 * This is the API for handling themes.
 * Code taken from Gallery3.
 *
 * @package    Gleez\Theme
 * @author     Gleez Team
 * @version    1.1.0
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 *
 * @todo       This class does not do any permission checking
 */
class Theme {

	/**
	 * Active theme name
	 * @var string
	 */
	public static $active = 'cerber';

	/**
	 * Admin?
	 * @var boolean
	 */
	public static $is_admin = FALSE;

	/**
	 * Available themes?
	 * @var array
	 */
	public static $themes = array();

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
		$config       = Config::load('site');
		self::$themes = self::available(FALSE);

		//set admin theme based on path info
		$path = ltrim(Request::detect_uri(), '/');
		Theme::$is_admin = ( $path == "admin" || !strncmp($path, "admin/", 6) );

		if (Theme::$is_admin)
		{
			// Load the admin theme
			Theme::$active  = $config->get('admin_theme', 'cerber');
		}
		else
		{
			// Load the site theme
			Theme::$active  = $config->get('theme', 'cerber');
		}
	
		//Set mobile theme, if enabled and mobile request
		if(Request::is_mobile() AND $config->get('mobile_theme', FALSE))
		{
			// Load the mobile theme
			Theme::$active = $config->get('mobile_theme', 'cerber');
		}
	
		// Admins can override the site theme, temporarily. This lets us preview themes.
		if (User::is_admin() AND isset($_GET['theme']) AND $override = Text::plain( $_GET['theme']) )
		{
			Theme::$active = $override;
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

		// Check if the active theme is not loaded already
		if( ! empty(Theme::$active) AND ! in_array(Theme::$active, array_keys($modules)))
		{
			// Make sure the theme is available
			if( $theme = self::getTheme() )
			{
				//set absolute theme path and load the request theme as kohana module
				Kohana::modules(array('theme' => $theme->path) + $modules);
			}
			else
			{
				Log::error('Missing site theme: :theme', array(':theme' => Theme::$active) );
			}
		}
	
		unset($modules);
	}

	/**
	 * Gets info about theme
	 *
	 * @param   boolean|string  $name  Theme name [Optional]
	 * @return  \Object  	 An object containing information about theme
	 */
	public static function getTheme($name = false)
	{
		if(empty($name)) $name = Theme::$active;

		// Make sure the theme is available
		if( in_array($name, array_keys(self::$themes) ) )
		{
			// Get the active theme object
			return self::$themes[$name];
		}

		return false;
	}

	/**
	 * Gets info about theme
	 *
	 * @param   string       $file   Theme info file
	 * @return  \Object  	 An object containing information about theme
	 */
	public static function get_info($file)
	{
		$theme              = (object) parse_ini_file($file, true);
		$theme->name        = basename(dirname($file));
		$theme->path        = dirname($file);
		$theme->title       = __($theme->title);
		$theme->description = __($theme->description);

		// Add i18n support
		if (isset($theme->regions) AND ! empty($theme->regions))
		{
			foreach ($theme->regions as $name => $title)
			{
				$theme->regions[$name] = __($title);
			}
		}

		return $theme;
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
		$paths 	= (array) Config::get('site.theme_dirs', array(THEMEPATH) );
		$cache  = Cache::instance('themes');

		if ( ! $themes = $cache->get('themes', false))
		{
			// Make sure THEMEPATH is set else add last
			if(!in_array(THEMEPATH, $paths))
			{
				array_push($paths, THEMEPATH);
			}

			// Iterate over each config path
			foreach ($paths AS $key => $path)
			{
				foreach (glob($path . "*/theme.info") as $file)
				{
					$name          = basename(dirname($file));
					$themes[$name] = Theme::get_info($file);
				}
			}
		}

		// set the cache for performance in production
		if (Kohana::$environment === Kohana::PRODUCTION)
		{
			$cache->set('themes', $themes, DATE::DAY);
		}

		if($title === TRUE)
		{
			foreach ($themes as $name => $theme)
			{
				$themes[$name] = $theme->title;
			}
		}

		return $themes;
	}
	
	public static function route_list()
	{
		return implode("|", array_keys( self::available()) );
	}

}
