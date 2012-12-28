<?php defined('SYSPATH') or die('No direct script access.');

if ( ! Route::cache())
{
        Route::set('user', 'user(/<action>)(/<id>)(/<token>)', array('action' =>
					'edit|login|logout|view|register|confirm|password|profile|photo', 'id' => '\d+'))
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

        Route::set('user/reset', 'user/reset(/<action>)(/<id>)(/<token>)(/<time>)', array('action' => 							'(password|confirm_password)', 'id' => '\d+', 'time' => '\d+'))
                ->defaults(array(
                	'controller' => 'user',
                	'action'     => 'confirm_password',
			'token'      => NULL,
			'time'	     => NULL,
                ));

        Route::set('admin/permission', 'admin/permissions(/<action>)')
                ->defaults(array(
			'directory'  => 'admin',
			'controller' => 'permission',
                        'action'     => 'list'
                ));

        Route::set('admin/role', 'admin/roles(/<action>(/<id>))(/p<page>)', array('id' => '\d+', 'page'  => '\d+',
									'action' => 'list|add|edit|delete'))
		->defaults(array(
			'directory'  => 'admin',
			'controller' => 'role',
                        'action'     => 'list'
                ));

        Route::set('admin/user', 'admin/users(/<action>(/<id>))(/p<page>)', array('id' => '\d+', 'page'  => '\d+',
									'action' => 'list|add|edit|delete'))
                ->defaults(array(
                        'directory'   => 'admin',
                        'controller'  => 'user',
                        'action'      => 'list',
                ));
}

ACL::set('user',  array('administer permissions' =>  array(
						'title' => __('Administer permissions'),
						'restrict access' => TRUE,
						'description' 	=> '',
						),
			       'administer users' =>  array(
						'title' => __('Administer users'),
						'restrict access' => TRUE,
						'description' => '',
						),
				'access profiles' =>  array(
						'title' => __('Access profiles'),
						'restrict access' => FALSE,
						'description' => '',
						),
				'edit profile' =>  array(
						'title' => __('Edit profile'),
						'restrict access' => FALSE,
						'description' => '',
						),
				'change own username' =>  array(
						'title' => __('Change own username'),
						'restrict access' => TRUE,
						'description' => '',
						),
				)
		);
