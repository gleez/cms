Subdomain Module for Kohana 3.2.x
===================================

This module implements ability to catch subdomains on class "Request" and set routes specifically by sub-domain.

## How to use:
### Requeriment
**Set base_url using absolute value in your bootstrap. Ex:**

	Kohana::init(array(
		'base_url'   => 'http://your_domain.com/',
	));


### Use
**catch actual sub-domain**

`echo Request::$subdomain;`
	
**Set defaults subdomains to routes. Ex:**

	// Default value is array(Route::SUBDOMAIN_EMPTY, 'www'); Route::SUBDOMAIN_EMPTY = if not having subdomain
	Routes::$default_subdomains = array('','www');
	
**Set Route to default subdomain. Ex:**

	// if use address "your-domain.com" or "www.your-domain.com", this route is valid.
	Route::set('subdomain', '(<controller>(/<action>(/<id>)))')
		->defaults(array(
			'controller' => 'welcome',
			'action'     => 'index',
		));	

**Set Route by specifically subdomain. Ex:**

	// if use address "test.your-domain.com", this route is valid.
	Route::set('subdomain', '(<controller>(/<action>(/<id>)))')
        ->subdomains(array('test'))
		->defaults(array(
			'controller' => 'test',
			'action'     => 'index',
		));
		
**Set Route for all subdomains (wildcard). Ex:**

	// you can use any sub-domain to execute this route.
	Route::set('subdomain', '(<controller>(/<action>(/<id>)))')
        ->subdomains(array(Route::SUBDOMAIN_WILDCARD))
		->defaults(array(
			'controller' => 'test',
			'action'     => 'wildcard',
		));


### Complete bootstrap example

	<?php defined('SYSPATH') or die('No direct script access.');

	// -- Environment setup --------------------------------------------------------

	// Load the core Kohana class
	require SYSPATH.'classes/kohana/core'.EXT;

	if (is_file(APPPATH.'classes/kohana'.EXT))
	{
		// Application extends the core
		require APPPATH.'classes/kohana'.EXT;
	}
	else
	{
		// Load empty core extension
		require SYSPATH.'classes/kohana'.EXT;
	}

	/**
	 * Set the default time zone.
	 *
	 * @see  http://kohanaframework.org/guide/using.configuration
	 * @see  http://php.net/timezones
	 */
	date_default_timezone_set('America/Chicago');

	/**
	 * Set the default locale.
	 *
	 * @see  http://kohanaframework.org/guide/using.configuration
	 * @see  http://php.net/setlocale
	 */
	setlocale(LC_ALL, 'en_US.utf-8');

	/**
	 * Enable the Kohana auto-loader.
	 *
	 * @see  http://kohanaframework.org/guide/using.autoloading
	 * @see  http://php.net/spl_autoload_register
	 */
	spl_autoload_register(array('Kohana', 'auto_load'));

	/**
	 * Enable the Kohana auto-loader for unserialization.
	 *
	 * @see  http://php.net/spl_autoload_call
	 * @see  http://php.net/manual/var.configuration.php#unserialize-callback-func
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
		'base_url'   => 'http://your-domain/app/',
	));

	/**
	 * Attach the file write to logging. Multiple writers are supported.
	 */
	Kohana::$log->attach(new Log_File(APPPATH.'logs'));

	/**
	 * Attach a file reader to config. Multiple readers are supported.
	 */
	Kohana::$config->attach(new Config_File);

	/**
	 * Enable modules. Modules are referenced by a relative or absolute path.
	 */
	Kohana::modules(array(
		// 'auth'       => MODPATH.'auth',       // Basic authentication
		// 'cache'      => MODPATH.'cache',      // Caching with multiple backends
		// 'codebench'  => MODPATH.'codebench',  // Benchmarking tool
		// 'database'   => MODPATH.'database',   // Database access
		// 'image'      => MODPATH.'image',      // Image manipulation
		// 'orm'        => MODPATH.'orm',        // Object Relationship Mapping
		// 'unittest'   => MODPATH.'unittest',   // Unit testing
		// 'userguide'  => MODPATH.'userguide',  // User guide and API documentation
			 'subdomain'  => MODPATH.'subdomain'
		));

	/**
	 * Set the routes. Each route must have a minimum of a name, a URI and a set of
	 * defaults for the URI.
	 */
	 
	Route::$default_subdomains = array(Route::SUBDOMAIN_EMPTY, 'www');

	Route::set('default', '(<controller>(/<action>(/<id>)))')
		->defaults(array(
			'controller' => 'welcome',
			'action'     => 'index',
		));
	 
	Route::set('subdomain1', '(<controller>(/<action>(/<id>)))')
        ->subdomains(array('test','test2'))
		->defaults(array(
			'controller' => 'test',
			'action'     => 'index',
		));
		
	Route::set('subdomain2', '(<controller>(/<action>(/<id>)))')
        ->subdomains(array(Route::SUBDOMAIN_WILDCARD))
		->defaults(array(
			'controller' => 'test',
			'action'     => 'wildcard',
		));	

Sugestions? jean@webmais.net.br

[]'s :)
