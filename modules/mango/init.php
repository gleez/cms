<?php defined('SYSPATH') OR die('No direct script access.');
 /**
 * Setting the Routes
 *
 * @package    Gleez
 * @category   Routing
 * @author     Sergey Yakovlev
 * @copyright  (c) 2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */

/** Routing setup */
if (! Route::cache())
{
  Route::set('admin/log', 'admin/logs(/<action>)(/<id>)(/p<page>)', array(
      'id'      => '([A-Za-z0-9]+)',
      'page'    => '\d+',
      'action'  => 'list|view|delete',
    ))
    ->defaults(array(
      'directory'   => 'admin',
      'controller'  => 'log',
      'action'      => 'list',
  ));
}

/**
 * Define Module specific Permissions
 *
 * Definition of user privileges by default if the ACL is present in the system.
 * Note: Parameter `restrict access` indicates that these privileges have serious
 * implications for safety.
 *
 * @uses ACL Used to define the privileges
 */
if ( class_exists('ACL') && ! ACL::cache() )
{
  ACL::set('Mango Reader', array
  (
    'view logs' =>  array (
      'title'           => __('View logs'),
      'restrict access' => TRUE,
      'description'     => __('View all log events'),
    ),
    'delete logs' =>  array (
      'title'           => __('Cleanup logs'),
      'restrict access' => TRUE,
      'description'     => __('Deleting events from the log'),
    ),
  ));

  /** Cache the module specific permissions in production */
  ACL::cache(Kohana::$environment === Kohana::PRODUCTION);
}
