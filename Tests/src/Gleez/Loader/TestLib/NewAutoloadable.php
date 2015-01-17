<?php
/**
 * Gleez CMS (http://gleezcms.org)
 *
 * @link      https://github.com/gleez/cms Canonical source repository
 * @copyright Copyright (c) 2011-2015 Gleez Technologies
 * @license   http://gleezcms.org/license Gleez CMS License
 */

namespace Gleez\Tests\Loader\TestLib;

use Gleez\Loader\Autoloadable as OriginalAutoloadable;

/**
 * Gleez Autoloader Test
 *
 * @package  Gleez\Loader\UnitTest
 * @author   Gleez Team
 * @version  1.0.2
 */
interface NewAutoloadable extends OriginalAutoloadable
{

    /**
     * Gets Autoloader config
     *
     * @return array()
     */
    public function getConfig();
}
