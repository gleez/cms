<?php defined('SYSPATH') OR die('No direct script access allowed.');
/**
 * Gleez Core class
 *
 * @package    Gleez
 * @author     Sandeep Sangamreddi - Gleez
 * @author     Sergey Yakovlev - Gleez
 * @version    0.10.2
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Gleez {

	/** Release version */
	const VERSION = '0.10.2';

	/** Release codename */
	const CODENAME = 'Turdus obscurus';

	/** Default message for maintenance mode */
	const MAINTENANCE_MESSAGE = 'This site is down for maintenance';

	/* Security check that is added to all generated PHP files */
	const FILE_SECURITY = '<?php defined(\'SYSPATH\') OR die(\'No direct script access allowed.\');';

	/**
	 * Gleez installed?
	 * @var boolean
	 */
	public static $installed = FALSE;

	/**
	 * Default theme name
	 * @var string
	 */
	public static $theme = 'fluid';

	/**
	 * Public [Gleez_Locale] instance
	 *
	 * @todo In the future, this object should be moved to Gleez Core
	 *
	 * @var Gleez_Locale
	 */
	public static $locale = NULL;
	
	/**
	 * Has [Gleez::ready] been called?
	 * @var boolean
	 */
	protected static $_ginit = FALSE;

	/**
	 * Runs the Gleez environment
	 *
	 * @uses  Gleez::_set_cookie
	 * @uses  Route::set
	 * @uses  Route::defaults
	 * @uses  Config::load
	 * @uses  Module::load_modules
	 * @uses  Theme::load_themes
	 */
	public static function ready()
	{
		if (self::$_ginit)
		{
			// Do not allow execution twice
			return;
		}

		// Gleez is now initialized?
		self::$_ginit = TRUE;

		// Link the Kohana locale to gleez for temporary, it's not singleton
		Gleez::$locale = Gleez_Locale::instance();

		// Set default cookie salt and lifetime
		self::_set_cookie();

		// Trying to get language from cookies
		if ($lang = Cookie::get(Gleez_Locale::$cookie))
		{
			I18n::$lang = $lang;
		}
		elseif (Kohana::$autolocale)
		{
			I18n::$lang = Gleez::$locale->get_language();
			// Trying to set language to cookies
			Cookie::set(Gleez_Locale::$cookie, I18n::$lang, Date::YEAR);
		}
		else
		{
			I18n::$lang = 'en-us';
		}

		// Check database config file exist or not
		Gleez::$installed = file_exists(APPPATH.'config/database.php');

		if (Gleez::$installed)
		{
			// Database config reader and writer
			Kohana::$config->attach(new Config_Database);
		}

		if (Kohana::$environment !== Kohana::DEVELOPMENT)
		{
			// @todo We need error handler with Gleez Views
			Gleez_Exception::$error_view = 'errors/stack';
		}

		// Turn off notices and strict errors in production
		if (Kohana::$environment === Kohana::PRODUCTION)
		{
			// Turn off notices and strict errors
			error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT);
		}
	
		// Disable the kohana powered headers
		Kohana::$expose = FALSE;
	
		/**
		 * If database.php doesn't exist, then we assume that the Gleez is not
		 * properly installed and send to the installer.
		 */
		if ( ! Gleez::$installed)
		{
			Session::$default = 'cookie';
			Gleez_Exception::$error_view = 'kohana/error';

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
	 * Delete all known cache's we set
	 *
	 * @uses  Cache::instance
	 * @uses  Cache::delete_all
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
		Cache::instance('roles')->delete_all();

		// For each cache instance
		foreach (Cache::$instances as $group => $instance)
		{
			/** @var $instance Cache */
			$instance->delete_all();
		}
	}

	/**
	 * List of route types
	 *
	 * Route name used for creating alias and term/tag routes
	 *
	 * @return  array  types
	 * @uses    Module::action
	 */
	public static function types()
	{
		$states = array(
			'blog'  => __('Blog'),
			'page'  => __('Page'),
			'user'  => __('User')
		);

		$values = Module::action('gleez_types', $states);

		return $values;
	}

	/**
	 * Check for maintenance_mode
	 *
	 * If Gleez is in maintenance mode, then force all non-admins to get routed
	 * to a "This site is down for maintenance" page.
	 *
	 * @throws  HTTP_Exception_503
	 *
	 * @uses    Request::initial
	 * @uses    Config::load
	 * @uses    Request::controller
	 * @uses    Request::action
	 * @uses    ACL::check
	 */
	public static function maintenance_mode()
	{
		$maintenance_mode = Kohana::$config->load('site.maintenance_mode', FALSE);
		$message          = Kohana::$config->load('site.offline_message', FALSE);
		$message          = (empty($message) OR ! $message) ? Gleez::MAINTENANCE_MESSAGE : $message;
		$request          = Request::initial();

		if ($maintenance_mode AND ($request instanceof Request) AND ($request->controller() != 'user' AND $request->action() != 'login') AND !ACL::check('administer site') AND $request->controller() != 'media')
		{
			Kohana::$log->add(LOG::INFO, 'Site running in Maintenance Mode');
			throw new HTTP_Exception_503(__($message));
		}
	}

	/**
	 * Check to see if an IP address has been blocked and deny access to blocked IP addresses
	 *
	 * @throws  HTTP_Exception_403
	 *
	 * @uses    Config::load
	 * @uses    Log::add
	 * @uses    Request::$client_ip
	 */
	public static function block_ips()
	{
		$blocked_ips = Kohana::$config->load('site.blocked_ips', NULL);
		$ip          = Request::$client_ip;

		if ( ! empty($blocked_ips) AND in_array($ip, preg_split("/[\s,]+/",$blocked_ips)))
		{
			Kohana::$log->add(LOG::INFO, 'Attempt to access with banned ip address: (:ip).', array(':ip' => $ip));
			throw new HTTP_Exception_403('Sorry, your ip address (:ip) has been banned.', array(':ip' => $ip));
		}
	}

	/**
	 * This function searches for the file that first matches the specified file
	 * name and returns its path.
	 *
	 * @param   string  $file The file name
	 * @return  string  The file path
	 * @throws  Gleez_Exception Indicates that the file does not exist
	 *
	 * @uses    Kohana::modules
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

		throw new Gleez_Exception('Unable to locate file `:file`. No file exists with the specified file name.', array(
			':file' => $file
		));
	}

	/**
	 * Gets current Gleez version
	 *
	 * @param   boolean  $with_v  If set, return the version number with the prefix `v` [Optional]
	 * @param   boolean  $full    If set, return the full version with `Gleez CMS` prefix [Optional]
	 * @return  string   The version of Gleez
	 */
	public static function getVersion($with_v = TRUE, $full = FALSE)
	{
		$version = $with_v ? 'v' . Gleez::VERSION : Gleez::VERSION;
		$version = $full ? 'Gleez CMS ' . $version : $version;

		return $version;
	}

	/**
	 * Set default cookie [salt](gleez/cookie/config#salt)
	 * and [lifetime](gleez/cookie/config#expiration)
	 *
	 * Also you can define a salt for the `Cookie` class in bootstrap.php:
	 * ~~~
	 * Cookie::$salt = [really-long-cookie-salt-here]
	 * ~~~
	 */
	protected static function _set_cookie()
	{
		/** @var Cookie::$salt string */
		Cookie::$salt = Kohana::$config->load('cookie.salt');

		/** @var Cookie::$expiration string */
		Cookie::$expiration = Kohana::$config->load('cookie.lifetime');
	}
}
