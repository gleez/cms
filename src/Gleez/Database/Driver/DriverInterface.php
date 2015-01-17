<?php
/**
 * Gleez CMS (http://gleezcms.org)
 *
 * @link https://github.com/gleez/cms Canonical source repository
 * @copyright Copyright (c) 2011-2015 Gleez Technologies
 * @license http://gleezcms.org/license Gleez CMS License
 */

namespace Gleez\Database\Driver;

/**
 * Database Driver interface
 *
 * @package Gleez\Database\Driver
 * @version 1.0.0
 * @author  Gleez Team
 */
interface DriverInterface
{
	/**
	 * Check environment
	 *
	 * @return bool
	 */
	public function checkEnvironment();
}
