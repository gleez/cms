<?php defined('SYSPATH') OR die('No direct script access.');

// -- Environment setup --------------------------------------------------------

// Load the core core classes
require GLZPATH.'classes/kohana'.EXT;
require GLZPATH.'classes/gleez'.EXT;

/**
 * Set the default time zone.
 *
 * @link  http://kohanaframework.org/guide/using.configuration
 * @link  http://php.net/timezones
 */
date_default_timezone_set('UTC');

/**
 * Set the default locale.
 *
 * @link http://kohanaframework.org/guide/using.configuration
 * @link http://www.php.net/manual/function.setlocale
 */
setlocale(LC_ALL, 'en_US.utf-8');

/**
 * Enable the Kohana auto-loader.
 *
 * @link  http://kohanaframework.org/guide/using.autoloading
 * @link  http://php.net/spl_autoload_register
 */
spl_autoload_register(array('Kohana', 'auto_load'));

/**
 * Enable the Kohana auto-loader for unserialization.
 *
 * @link  http://php.net/spl_autoload_call
 * @link  http://php.net/manual/var.configuration.php#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

// -- Configuration and initialization -----------------------------------------

/**
 * Set Kohana::$environment if a 'GLEEZ_ENV' environment variable has been supplied.
 *
 * [!!] Note: If you supply an invalid environment name, a PHP warning will be thrown
 * saying "Couldn't find constant Kohana::<INVALID_ENV_NAME>"
 *
 * @todo In the future Kohana::$environment should be moved to Gleez Core as Gleez::$environment
 */
if (isset($_SERVER['GLEEZ_ENV']))
{
	Kohana::$environment = constant('Kohana::'.strtoupper($_SERVER['GLEEZ_ENV']));
}

/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   NULL
 * - string   index_file  name of your index file, usually "index.php"       index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - boolean  errors      enable or disable error handling                   TRUE
 * - boolean  profile     enable or disable internal profiling               TRUE
 * - boolean  caching     enable or disable internal caching                 FALSE
 * - boolean  autolocale  enable or disable autodetect locale                TRUE
 */
Kohana::init(array(
	'base_url'   => '/',
	'index_file' => FALSE,
	'caching'    => Kohana::$environment === Kohana::PRODUCTION,
	'profile'    => Kohana::$environment !== Kohana::PRODUCTION,
));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
Kohana::$config->attach(new Config_File);

/**
 * Enable modules.
 *
 * Modules are referenced by a relative or absolute path.
 */
Kohana::modules(array(
	'user'        => MODPATH.'user',       // User and group Administration
	'database'    => MODPATH.'database',   // Database access
	'image'       => MODPATH.'image',      // Image manipulation
	'captcha'     => MODPATH.'captcha',    // Captcha implementation
	//'unittest'    => MODPATH.'unittest',   // Unit testing
	//'codebench'   => MODPATH.'codebench',  // Benchmarking tool
	'userguide'   => MODPATH.'userguide',  // User guide and API documentation
	//'mango'       => MODPATH.'mango',      // Gleez Mango
));

/**
 * ### Attach the file write to logging
 *
 * Multiple writers are supported.
 *
 * ### Usage:
 *
 * Using file log writer:<br>
 * <code>
 *   Kohana::$log->attach(new Log_File(APPPATH.'logs'));
 * </code>
 *
 * Using STDERR log writer:<br>
 * <code>
 *   Kohana::$log->attach(new Log_StdErr());
 * </code>
 *
 * Using STDOUT log writer:<br>
 * <code>
 *   Kohana::$log->attach(new Log_StdOut());
 * </code>
 *
 * Using Syslog log writer:<br>
 * <code>
 *   Kohana::$log->attach(new Log_Syslog());
 * </code>
 *
 * Using Gleez Mango Log:<br>
 * <code>
 *   Kohana::$log->attach(new Log_Mango());
 * </code>
 *
 * @todo Add guide about this to the userguide and wiki
 */
Kohana::$log->attach(new Log_File(APPPATH.'logs'));

/**
 * Default path for uploads directory.
 * Path are referenced by a relative or absolute path.
 */
Upload::$default_directory = APPPATH.'uploads';

/**
 * Set the routes
 *
 * Each route must have a minimum of a name,
 * a URI and a set of defaults for the URI.
 *
 * Example:<br>
 * <code>
 *	Route::set('frontend/page', 'page(/<action>)')
 *		->defaults(array(
 *			'controller' => 'page',
 *			'action' => 'view',
 *	));
 * </code>
 *
 * @uses  Path::lookup
 * @uses  Route::cache
 * @uses  Route::set
 */
if ( ! Route::cache())
{
	Route::set('default', '(<controller>(/<action>(/<id>)))')
		->filter( 'Path::lookup' )
		->defaults(array(
			'controller' => 'welcome',
			'action'     => 'index',
		));

	// Cache the routes in production
	Route::cache(Kohana::$environment === Kohana::PRODUCTION);
}
