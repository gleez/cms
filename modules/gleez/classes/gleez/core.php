<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Gleez Core class
 *
 * @package   Gleez
 * @category  Core
 * @version   0.9.8.2
 * @author    Sandeep Sangamreddi - Gleez
 * @copyright (c) 2013 Gleez Technologies
 * @license   http://gleezcms.org/license
 */
class Gleez_Core {

	/** @var string Release version */
	const VERSION = '0.9.8.2';

	/** @var string Release codename */
	const CODENAME = 'Turdus obscurus';

	/** @var boolean Installed? */
	public static $installed = FALSE;

	/** @var string Theme name */
	public static $theme = 'fluid';

	/** @var string Application language */
	public static $locale = '';

	/** @var boolean Has [Gleez::init] been called? */
	protected static $_ginit = FALSE;

	/**
	 * Runs the Gleez environment
	 */
	public static function ready()
	{
		if (self::$_ginit)
		{
			// Do not allow execution twice
			return;
		}

		// Gleez is now initialized
		self::$_ginit = TRUE;

		// Set default cookie salt
		Cookie::$salt = Kohana::$config->load('cookie.salt');

		// Set default cookie lifetime
		Cookie::$expiration = Kohana::$config->load('cookie.lifetime');

    // Check database config file exist or not
    Gleez::$installed = file_exists(APPPATH.'config/database.php');

    if (Gleez::$installed) {
      // Database config reader and writer
      Kohana::$config->attach(new Config_Database);
    }

		// I18n settins
		self::_set_locale();

		if (Kohana::$environment !== Kohana::DEVELOPMENT)
		{
			Kohana_Exception::$error_view = 'errors/stack';
		}

		// Disable the kohana powred headers
		Kohana::$expose = FALSE;

		/**
		 * If database.php doesn't exist, then we assume that the Gleez is not
		 * properly installed and send to the installer.
		 */
		if (!Gleez::$installed)
		{
			Session::$default = 'cookie';
			Kohana_Exception::$error_view = 'kohana/error';

			// Static file serving (CSS, JS, images)
			Route::set('install/media', 'media(/<file>)', array(
				'file' => '.+'
			))
			->defaults(array(
				'controller' => 'install',
				'action'     => 'media',
				'file'       => NULL,
				'directory'  => 'install'
			));

			Route::set('install', '(install(/<action>))', array(
				'action' => 'index|systemcheck|database|install|finalize'
			))
			->defaults(array(
				'controller' => 'install',
				'directory'  => 'install'
			));

			return;
		}

		// Set the default session type
		Session::$default = Kohana::$config->load('site.session_type');

		// Initialize Gleez modules
		Module::load_modules(FALSE);

		// Load the active theme(s)
		Theme::load_themes();
	}

	/**
	 * APC cache. Provides an opcode based cache.
	 *
	 * @param   string   $name      name of the cache
	 * @param   mixed    $data      data to cache [Optional]
	 * @param   integer  $lifetime  number of seconds the cache is valid for [Optional]
	 * @return  mixed    for getting
	 * @return  boolean  for setting
	 */
	public static function cache($name, $data = NULL, $lifetime = 3600)
	{
		// Enable cache only in production environment
		if (Kohana::$environment !== Kohana::PRODUCTION)
		{
			Kohana::$log->add(LOG::DEBUG, 'Gleez Caching only available in production');
			return FALSE;
		}

		// Check for existence of the APC extension
		if ( ! extension_loaded('apc'))
		{
			Kohana::$log->add(LOG::INFO, 'PHP APC extension is not available');
			return FALSE;
		}

		if (isset($_SERVER['HTTP_HOST']))
		{
			$name .= $_SERVER['HTTP_HOST'];
		}

		if (is_null($data))
		{
			try
			{
				// Return the cache
				return apc_fetch(self::_sanitize_id($name));
			}
			catch (Exception $e)
			{
				// Cache is corrupt, let return happen normally
				Kohana::$log->add(LOG::ERROR, "Cache name: `:name` is corrupt", array(
					':name' => $name
				));
			}

			// Cache not found
			return FALSE;
		}
		else
		{
			try
			{
				return apc_store(self::_sanitize_id($name), $data, $lifetime);
			}
			catch (Exception $e)
			{
				// Failed to write cache
				return FALSE;
			}
		}
	}

	/**
	 * Delete all known cache's we set
	 */
	public static function cache_delete()
	{
		// Clear any cache for sure
		Cache::instance('modules')->delete_all();
		Cache::instance('menus')->delete_all();
		Cache::instance('widgets')->delete_all();
		Cache::instance('feeds')->delete_all();
		Cache::instance('page')->delete_all();
		Cache::instance('blog')->delete_all();

		// For each cache instance
		foreach (Cache::$instances as $group => $instance)
		{
			$instance->delete_all();
		}
	}

	/**
	 * Replaces troublesome characters with underscores.
	 *
	 *   // Sanitize a cache id
	 *   $id = $this->_sanitize_id($id);
	 *
	 * @param   string   $id  id of cache to sanitize
	 * @return  string
	 */
	protected static function _sanitize_id($id)
	{
		// Change slashes and spaces to underscores
		return str_replace(array(
			'/',
			'\\',
			' '
		), '_', $id);
	}

	/**
	 * List of route types (route name used for creating alias and term/tag routes)
	 *
	 *  @return array types
	 */
	public static function types()
	{
		$states = array(
			'post' => __('Post'),
			'page' => __('Page'),
			'blog' => __('Blog'),
			'forum' => __('Forum'),
			'book' => __('Book'),
			'user' => __('User')
		);

		$values = Module::action('gleez_types', $states);

		return $values;
	}

	/**
	 * If Gleez is in maintenance mode, then force all non-admins to get routed
	 * to a "This site is down for maintenance" page.
	 *
	 * @throws  HTTP_Exception_503
	 */
	public static function maintenance_mode()
	{
		$maintenance_mode = Kohana::$config->load('site.maintenance_mode', false);
		$request          = Request::initial();

		if ($maintenance_mode AND ($request instanceof Request) AND ($request->controller() != 'user' AND $request->action() != 'login') AND !ACL::check('administer site') AND $request->controller() != 'media')
		{
			Kohana::$log->add(LOG::INFO, 'Site running in Maintenance Mode');
			throw new HTTP_Exception_503('Site running in Maintenance Mode');
		}
	}

	/**
	 * This function searches for the file that first matches the specified file
	 * name and returns its path.
	 *
	 * @param   string  $file The file name
	 * @return  string  The file path
	 * @throws  Kohana_Exception Indicates that the file does not exist
	 */
	protected static function find_file_custom($file)
	{
		if (file_exists($file))
		{
			return $file;
		}

		$uri = THEMEPATH . $file;
		if (file_exists($uri))
		{
			return $uri;
		}

		$uri = APPPATH . $file;
		if (file_exists($uri))
		{
			return $uri;
		}

		$modules = Kohana::modules();
		foreach ($modules as $module)
		{
			$uri = $module . $file;
			if (file_exists($uri))
			{
				return $uri;
			}
		}

		$uri = SYSPATH . $file;
		if (file_exists($uri))
		{
			return $uri;
		}

		throw new Kohana_Exception('Unable to locate file `:file`. No file exists with the specified file name.', array(
			':file' => $file
		));
	}

	/**
	 * Check the supplied integer in given range
	 *
	 * @param   integer   $min
	 * @param   integer   $max
	 * @param   integer   $from_user supplied intiger
	 * @return  boolean
	 */
	public static function check_in_range($min, $max, $from_user)
	{
		// Convert to int
		$start = (int) $min;
		$end   = (int) $max;
		$user  = (int) $from_user;

		// Check that user data is between start & end
		return (($user > $start) AND ($user < $end));
	}

	/**
	 * I18n settins
	 */
	protected static function _set_locale()
	{
		// First check cookies
		$locale = Cookie::get('locale');

		// If cookies are empty read accept_language
		if (empty($locale) AND isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		{
			$locale = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
		}

		// If the config group `site` locale are missing or it empty
		if ( ! in_array($locale, Kohana::$config->load('site.installed_locales')))
		{
			// By default - english
			$locale = 'en';
		}

		// Setting lang
		I18n::$lang = self::$locale = $locale;

		Cookie::set('locale', $locale);
	}

}
