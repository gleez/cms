<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Setting the Routes
 *
 * @package    Gleez
 * @category   User
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
if (! Route::cache())
{
	Route::set('user', 'user(/<action>)(/<id>)(/<token>)', array(
		'action'     => 'edit|login|logout|view|register|confirm|password|profile|photo',
		'id'         => '\d+'
	))
	->defaults(array(
		'controller' => 'user',
		'action'     => 'view',
		'token'      => NULL,
	));

	Route::set('user/oauth', 'oauth/<controller>(/<action>)')
	->defaults(array(
		'directory'  => 'oauth',
		'action'     => 'index',
	));

	Route::set('user/reset', 'user/reset(/<action>)(/<id>)(/<token>)(/<time>)', array(
		'action'     => 'password|confirm_password',
		'id'         => '\d+',
		'time'       => '\d+'
	))
	->defaults(array(
		'controller' => 'user',
		'action'     => 'confirm_password',
		'token'      => NULL,
		'time'       => NULL,
	));

	Route::set('admin/permission', 'admin/permissions(/<action>)')
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'permission',
		'action'     => 'list'
	));

	Route::set('admin/role', 'admin/roles(/<action>(/<id>))(/p<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'action'     => 'list|add|edit|delete'
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'role',
		'action'     => 'list'
	));

	Route::set('admin/user', 'admin/users(/<action>(/<id>))(/p<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'action'     => 'list|add|edit|delete'
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'user',
		'action'     => 'list',
	));

	Route::cache(Kohana::$environment === Kohana::PRODUCTION);
}


/**
 * Setting the Permiossions
 *
 * Definition of user privileges by default if the ACL is present in the system.
 * Note: Parameter `restrict access` indicates that these privileges have serious
 * implications for safety.
 *
 * @uses ACL Used to define the privileges
 */
if (class_exists('ACL'))
{
	ACL::set('user', array(
		'administer permissions' => array(
			'title'           => __('Administer permissions'),
			'restrict access' => TRUE,
			'description'     => __('Managing user authority'),
		),
		'administer users' => array(
			'title'           => __('Administer users'),
			'restrict access' => TRUE,
			'description'     => __('User management'),
		),
		'access profiles' => array(
			'title'           => __('Access profiles'),
			'restrict access' => FALSE,
			'description'     => __('Access to all profiles'),
		),
		'edit profile' => array(
			'title'           => __('Edit profile'),
			'restrict access' => FALSE,
			'description'     => __('The ability to change profile'),
		),
		'change own username' => array(
			'title'           => __('Change own username'),
			'restrict access' => TRUE,
			'description'     => __('The ability to change own username'),
		)
	));
}
