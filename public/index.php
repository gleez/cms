<?php
/**
 * Gleez CMS (http://gleezcms.org)
 *
 * @link      https://github.com/gleez/cms Canonical source repository
 * @copyright Copyright (c) 2011-2015 Gleez Technologies
 * @license   http://gleezcms.org/license Gleez CMS License
 */

/**
 * @const DOCROOT The site document root (not public root)
 */
define('DOCROOT', realpath(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR);

/**
 * Bootstrap the application
 */
require DOCROOT . 'app/Application.php';

$application = new \Gleez\Application;
