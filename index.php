<?php

/**
 * The directory in which your site application specific resources are located.
 * The application directory must contain the bootstrap.php file.
 * This automatically detects multi-site configuration found in 'sites' directory
 *
 * @link  http://kohanaframework.org/guide/about.install#application
 */
$application = 'application';

/**
 * The directory in which your modules are located.
 *
 * @link  http://kohanaframework.org/guide/about.install#modules
 */
$modules = 'modules';

/**
 * The directory in which the Gleez resources are located. The Gleez system
 * directory must contain the classes/kohana.php file.
 *
 * @link  http://kohanaframework.org/guide/about.install#system
 */
$gleez = 'modules/gleez';

/**
 * The directory in which the Kohana resources are located. The system
 * directory must contain the classes/kohana.php file.
 *
 * @link  http://kohanaframework.org/guide/about.install#system
 */
$system = 'system';

/**
 * The directory in which the Gleez themes directory are located. This directory should contain all the themes,
 * and the resources you included in your layout of the application.
 *
 * This path can be absolute or relative to this file.
 */
$themes = 'themes';

/**
 * The default extension of resource files. If you change this, all resources
 * must be renamed to use the new extension.
 *
 * @link  http://kohanaframework.org/guide/about.install#ext
 */
define('EXT', '.php');

/**
 * For convenience, shorten the name of the DIRECTORY_SEPARATOR constant
 */
define('DS', DIRECTORY_SEPARATOR);

/**
 * Set the PHP error reporting level. If you set this in php.ini, you remove this.
 * @link  http://php.net/error_reporting
 *
 * When developing your application, it is highly recommended to enable notices
 * and strict warnings. Enable them by using: E_ALL | E_STRICT
 *
 * In a production environment, it is safe to ignore notices and strict warnings.
 * Disable them by using: E_ALL ^ E_NOTICE
 *
 * When using a legacy application with PHP >= 5.3, it is recommended to disable
 * deprecated notices. Disable with: E_ALL & ~E_DEPRECATED
 */
error_reporting(E_ALL | E_STRICT);

/**
 * End of standard configuration! Changing any of the code below should only be
 * attempted by those with a working knowledge of Kohana internals.
 *
 * @link  http://kohanaframework.org/guide/using.configuration
 */

// Set the full path to the docroot
define('DOCROOT', realpath(dirname(__FILE__)).DS);

// Make the application relative to the docroot, for symlink'd index.php
if ( ! is_dir($application) AND is_dir(DOCROOT.$application))
	$application = DOCROOT.$application;

// Make the modules relative to the docroot, for symlink'd index.php
if ( ! is_dir($modules) AND is_dir(DOCROOT.$modules))
	$modules = DOCROOT.$modules;

// Make the gleez relative to the docroot, for symlink'd index.php
if ( ! is_dir($gleez) AND is_dir(DOCROOT.$gleez))
	$gleez = DOCROOT.$gleez;

// Make the system relative to the docroot, for symlink'd index.php
if ( ! is_dir($system) AND is_dir(DOCROOT.$system))
	$system = DOCROOT.$system;

// Make the themes relative to the docroot
if ( ! is_dir($themes) AND is_dir(DOCROOT.$themes))
	$themes = DOCROOT.$themes;

// Define the absolute paths for configured directories
define('APPPATH', realpath($application).DS);
define('MODPATH', realpath($modules).DS);
define('GLZPATH', realpath($gleez).DS);
define('SYSPATH', realpath($system).DS);
define('THEMEPATH', realpath($themes).DS);

// Clean up the configuration vars
unset($application, $modules, $system, $themes);

/**
 * Define the start time of the application, used for profiling.
 */
if ( ! defined('GLEEZ_START_TIME'))
{
	define('GLEEZ_START_TIME', microtime(TRUE));
}

/**
 * Define the memory usage at the start of the application, used for profiling.
 */
if ( ! defined('GLEEZ_START_MEMORY'))
{
	define('GLEEZ_START_MEMORY', memory_get_usage());
}

// Bootstrap the application
require APPPATH.'bootstrap'.EXT;

if (PHP_SAPI == 'cli') // Try and load minion
{
    class_exists('Minion_Task') OR die('Please enable the Minion module for CLI support.');
    set_exception_handler(array('Minion_Exception', 'handler'));

    Minion_Task::factory(Minion_CLI::options())->execute();
}
else
{
	/**
 	 * Execute the main request. A source of the URI can be passed, eg: $_SERVER['PATH_INFO'].
	 * If no source is specified, the URI will be automatically detected.
	 */
	echo Request::factory()
		->execute()
		->send_headers()
		->body();
}