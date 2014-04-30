<?php
/**
 * Setting the Routes
 *
 * @package    Codebench\Routing
 * @author     Gleez Team
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */

/** Routing setup */
if (!Route::cache())
{

	// Catch-all route for Codebench classes to run
	Route::set('codebench', 'codebench(/<class>)')
	->defaults(array(
		'controller' => 'codebench',
		'action'     => 'index',
		'class'      => NULL
	));
}
