<?php
/**
 * Gleez CMS (http://gleezcms.org)
 *
 * @link      https://github.com/gleez/cms Canonical source repository
 * @copyright Copyright (c) 2011-2015 Gleez Technologies
 * @license   http://gleezcms.org/license Gleez CMS License
 */

// turn on all errors
error_reporting(E_ALL);

/**
 * @const DOCROOT The site document root (not public root)
 */
define('DOCROOT', realpath(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR);

if (!is_file(DOCROOT . '/vendor/autoload.php')) {
	throw new RuntimeException(sprintf(
		'Composer autoloader not found. Try to run "composer install" at :%s',
		DOCROOT
	));
}

require_once DOCROOT . '/vendor/autoload.php';

// autoloader
$loader = new Gleez\Loader\Autoloader(array(
    Gleez\Loader\Autoloader::LOAD_NS => array(
        'Gleez\Tests' => __DIR__ . 'src/Gleez',
    )
));

$loader->register(true);
