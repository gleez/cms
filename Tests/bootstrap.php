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

require_once  dirname(realpath(dirname(__FILE__))).'/src/Gleez/Loader/Autoloader.php';

// autoloader
$loader = new Gleez\Loader\Autoloader(array(
    Gleez\Loader\Autoloader::LOAD_NS => array(
        'Gleez\Tests' => __DIR__ . 'src/Gleez',
    )
));

$loader->register(true);
