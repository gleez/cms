<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Setting the Routes
 *
 * @package    Gleez
 * @category   Routing
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
if ( ! Route::cache())
{
	// Image resize
	Route::set('resize', 'media/imagecache/<type>/<dimensions>(/<file>)', array(
		'dimensions' => '\d+x\d+',
		'type'       => 'crop|ratio|resize',
		'file'       => '.+'
	))
	->defaults(array(
		'controller' => 'resize',
		'action'     => 'image',
		'type'       => 'resize'
	));

	// Static file serving (CSS, JS, images)
	Route::set('media', 'media/<file>', array('file' => '.+') )
	->defaults(array(
		'controller' => 'media',
		'action'     => 'serve',
		'file'       => NULL,
		));

	//Route::cache(FALSE, Kohana::$environment === Kohana::PRODUCTION);
}

/** Run Gleez Components */
Gleez::ready();

if ( ! Route::cache())
{
	/** Gleez backend routes */
	Route::set('admin/module', 'admin/modules(/<action>)')
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'modules',
	));

	Route::set('admin/page', 'admin/pages(/<action>(/<id>))(/p<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'action'     => 'index|list|settings|reset|confirm'
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'page',
		'action'     => 'list',
	));

	Route::set('admin/comment', 'admin/comments(/<action>(/<id>))(/p<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'action'     => 'index|list|process|view|delete|spam|pending'
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'comment',
		'action'     => 'list',
	));

	Route::set('admin/menu', 'admin/menus(/<action>(/<id>))(/p/<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'action'     => 'list|add|edit|delete|confirm'
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'menu',
		'action'     => 'list',
	));

	Route::set('admin/menu/item', 'admin/menu/manage/<id>(/<action>)(/p/<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'action'     => 'list|add|edit|delete|confirm',
		'slug'       => '[A-Za-z0-9-]+'
	))
	->defaults(array(
		'directory'  => 'admin/menu',
		'controller' => 'item',
		'action'     => 'list',
	));

	Route::set('admin/path', 'admin/paths(/<action>(/<id>))(/p<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'action'     => 'list|add|edit|delete'
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'path',
		'action'     => 'list',
	));

	Route::set('admin/tag', 'admin/tags(/<action>(/<id>))(/p<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'action'     => 'list|add|edit|delete'
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'tag',
		'action'     => 'list',
	));

	Route::set('admin/taxonomy', 'admin/taxonomy(/<action>(/<id>))(/p<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'action'     => 'list|add|edit|delete'
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'taxonomy',
		'action'     => 'list',
	));

	Route::set('admin/term', 'admin/terms(/<action>)/<id>(/p<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'action'     => 'list|add|edit|delete|confirm'
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'term',
		'action'     => 'list',
	));

	Route::set('admin/widget', 'admin/widgets(/<action>(/<id>))(/p<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'action'     => 'index|list|view|add|edit|delete|reset|confirm|clone'
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'widget',
	));

	Route::set('admin/format', 'admin/formats(/<action>(/<id>))', array(
		'id'         => '\d+',
		'action'     => 'index|view|add|edit|delete|configure|reset'
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'format',
	));

	Route::set('admin/setting', 'admin/settings(/<action>)')
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'setting',
	));

	Route::set('admin', 'admin(/<controller>)(/<action>)(/<id>)(/p<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+'
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'dashboard',
	));

	/** Gleez frontend routes */
	Route::set('autocomplete', 'autocomplete/<action>(/<type>)(/<string>)', array(
		'string'     => '([A-Za-z0-9\-\,\s]+)',
		'action'     => 'index|user|nick|tag|post',
		'type'       => 'post|page|blog|forum'
	))
	->defaults(array(
		'controller' => 'autocomplete',
		'type'       => 'blog',
	));

	Route::set('post', 'post(/<action>)(/<id>)(/p<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'action'     => 'index|list|view|add|edit|delete'
	))
	->defaults(array(
		'controller' => 'post',
		'action'     => 'index'
	));

	Route::set('page', 'page(/<action>)(/<id>)(/p<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'action'     => 'index|list|view|add|edit|delete|term'
	))
	->defaults(array(
		'controller' => 'page',
		'action'     => 'index'
	));

	Route::set('comment', 'comment(/<action>(/<id>))(/p<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'action'     => 'list|process|add|view|edit|delete'
	))
	->defaults(array(
		'controller' => 'comment',
		'action'     => 'list',
	));

	Route::set('comments', 'comments/<group>/<action>(/<id>)(/p<page>)(<format>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'format'     => '\.\w+',
	))
	->defaults(array(
		'controller' => 'comments',
		'group'      => 'page',
		'action'     => 'public',
		'format'     => '.xhtml',
	));

	Route::set('rss', 'rss(/<controller>)(/<action>)(/<id>)(/p<page>)(/l<limit>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'limit'      => '\d+'
	))
	->defaults( array(
		'directory'  => 'feeds',
		'controller' => 'base',
	));

	Route::set('welcome', 'welcome(/<action>)(/<id>)')
	->defaults(array(
		'controller' => 'welcome'
	));

	//Route::cache(FALSE, Kohana::$environment === Kohana::PRODUCTION);
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
	ACL::set('comment', array(
		'administer comment' => array(
			'title' => __('administer comments'),
			'restrict access' => TRUE,
			'description' => __('Administer comments and comment settings'),
		),
		'access comment' => array(
			'title' => __('Access comments'),
			'restrict access' => FALSE,
			'description' => __(''),
		),
		'post comment' =>  array(
			'title' => __('Post comments'),
			'restrict access' => FALSE,
			'description' => __(''),
		),
		'skip comment approval' =>  array(
			'title' => __('Skip comment approval'),
			'restrict access' => FALSE,
			'description' => __(''),
		),
		'edit own comment' =>  array(
			'title' => __('Edit own comments'),
			'restrict access' => FALSE,
			'description' => __(''),
		),
	));

	ACL::set('content', array(
		'administer content' => array(
			'title' => __('Administer content'),
			'restrict access' => TRUE,
			'description' => __(''),
		),
		'access content' => array(
			'title' => __('Access content'),
			'restrict access' => FALSE,
			'description' => __(''),
		),
		'view own unpublished content' => array(
			'title' => __('View own unpublished content'),
			'restrict access' => FALSE,
			'description' => __(''),
		),
		'administer page' => array(
			'title' => __('Administer pages'),
			'restrict access' => TRUE,
			'description' => __(''),
		),
		'create page' => array(
			'title' => __('Create pages'),
			'restrict access' => FALSE,
			'description' => __(''),
		),
		'edit own page' => array(
			'title' => __('Edit own page'),
			'restrict access' => FALSE,
			'description' => __(''),
		),
		'edit any page' => array(
			'title' => __('Edit any pages'),
			'restrict access' => FALSE,
			'description' => __(''),
		),
		'delete own page' => array(
			'title' => __('Delete own page'),
			'restrict access' => FALSE,
			'description' => __(''),
		),
		'delete any page' => array(
			'title' => __('Delete any pages'),
			'restrict access' => FALSE,
			'description' => __(''),
		),
	));

	ACL::set('site', array(
		'administer menu' => array(
			'title' => __('Administer Menus'),
			'restrict access' => TRUE,
			'description' => __(''),
		),
		'administer paths' => array(
			'title' => __('Administer paths'),
			'restrict access' => FALSE,
			'description' => __(''),
		),
		'administer site' => array(
			'title' => __('Administer site'),
			'restrict access' => TRUE,
			'description' => __(''),
		),
		'administer tags' => array(
			'title' => __('Administer tags'),
			'restrict access' => FALSE,
			'description' => __(''),
		),
		'administer terms' => array(
			'title' => __('Administer terms'),
			'restrict access' => FALSE,
			'description' => __(''),
		),
		'administer logs' => array(
			'title' => __('Administer logs'),
			'restrict access' => TRUE,
			'description' => __(''),
		),
	));

	/** Cache the module specific permissions in production */
	ACL::cache(Kohana::$environment === Kohana::PRODUCTION);
}
