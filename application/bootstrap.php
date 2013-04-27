<?php defined('SYSPATH') OR die('No direct script access.');

// -- Environment setup --------------------------------------------------------

// Load the core Kohana class
require SYSPATH.'classes/kohana/core'.EXT;

if (is_file(MODPATH.'gleez/classes/kohana'.EXT))
{
	// Application extends the core
	require MODPATH.'gleez/classes/kohana'.EXT;
	require MODPATH.'gleez/classes/kohana/config'.EXT;
	require MODPATH.'gleez/classes/gleez/core'.EXT;
}
else
{
	// Load empty core extension
	require SYSPATH.'classes/kohana'.EXT;
}

/**
 * Set the default time zone.
 *
 * @link  http://kohanaframework.org/guide/using.configuration
 * @link  http://php.net/timezones
 */
date_default_timezone_set('Asia/Kolkata');

/**
 * Set the default locale.
 *
 * @link  http://kohanaframework.org/guide/using.configuration
 * @link  http://php.net/setlocale
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
 * Set the default language
 */
I18n::lang('en-us');

/**
 * Set Kohana::$environment if a 'KOHANA_ENV' environment variable has been supplied.
 *
 * Note: If you supply an invalid environment name, a PHP warning will be thrown
 * saying "Couldn't find constant Kohana::<INVALID_ENV_NAME>"
 */
if (isset($_SERVER['KOHANA_ENV']))
{
	Kohana::$environment = constant('Kohana::'.strtoupper($_SERVER['KOHANA_ENV']));
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
 */
Kohana::init(array(
	'base_url'   => str_replace(basename($_SERVER['SCRIPT_NAME']),'',$_SERVER['SCRIPT_NAME']),
	'index_file' => FALSE,
	'caching'    => Kohana::$environment === Kohana::PRODUCTION,
	'profile'    => Kohana::$environment !== Kohana::PRODUCTION,
));


/**
 * Attach the file write to logging. Multiple writers are supported.
 */
//Kohana::$log->attach(new Log_File(APPPATH.'logs'));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
Kohana::$config->attach(new Config_File);

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
Kohana::modules(array(
	 'gleez'       => MODPATH.'gleez',      // Gleez Core Module
	 'user'        => MODPATH.'user',       // User and group Administration
	 'database'    => MODPATH.'database',   // Database access
	 'image'       => MODPATH.'image',      // Image manipulation
	 'captcha'     => MODPATH.'captcha',    // Captcha implementation
	// 'unittest'   => MODPATH.'unittest',   // Unit testing
	// 'codebench'  => MODPATH.'codebench',  // Benchmarking tool
	 'userguide'   => MODPATH.'userguide',  // User guide and API documentation
	//'mango'       => MODPATH.'mango',      // Mango Reader
	));

/**
 * Attach the file write to logging. Multiple writers are supported.
 */
Kohana::$log->attach(new Gleez_Log_File(APPPATH.'logs'));
// For Log_Mango() Mango Reader is required
// Kohana::$log->attach(new Log_Mango());

//default path for uploads directory
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
