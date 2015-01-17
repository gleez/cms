<?php
/**
 * Gleez CMS (http://gleezcms.org)
 *
 * @link      https://github.com/gleez/cms Canonical source repository
 * @copyright Copyright (c) 2011-2015 Gleez Technologies
 * @license   http://gleezcms.org/license Gleez CMS License
 */

require_once  SRCPATH.'/Gleez/Loader/Autoloader.php';

// autoloader
$loader = new Gleez\Loader\Autoloader(array(
    Gleez\Loader\Autoloader::LOAD_NS => array(
        'Gleez' => 'Gleez',
    )
));

$loader->register(true);
