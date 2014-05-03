<?php

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

// Define the absolute paths for configured directories
define('APPPATH', realpath('../App'));
define('VENPATH', realpath('../Vendor'));

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

// Bootstrap the application
require APPPATH.'/Bootstrap'.EXT;
