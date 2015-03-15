<?php
/**
 * Gleez CMS (http://gleezcms.org)
 *
 * @link      https://github.com/gleez/cms Canonical source repository
 * @copyright Copyright (c) 2011-2015 Gleez Technologies
 * @license   http://gleezcms.org/license Gleez CMS License
 */

namespace Gleez;

use RuntimeException;

class Application
{
	/**
	 * Application constructor
	 */
	public function __construct()
	{
		$this->init();
	}

	/**
	 * Runs the application performing all initializations
	 *
	 * @return Application
	 */
	protected function init()
	{
		$initializers = [
			'environment'
		];

		foreach($initializers as $service) {
			$function = 'init' . ucfirst($service);
			$this->$function();
		}

		return $this;
	}

	/**
	 * Initializes the Environment
	 * @throws \RuntimeException
	 */
	protected function initEnvironment()
	{
		if (!is_file(DOCROOT . '/vendor/autoload.php')) {
			throw new RuntimeException(sprintf(
				'Composer autoloader not found. Try to run "composer install" at :%s',
				DOCROOT
			));
		}

		// Include Composer autoloader
		require_once(DOCROOT . '/vendor/autoload.php');
	}
}
