<?php

return array(
	'memcache' => array(
		'driver'             => 'memcache',
		'default_expire'     => 3600,
		'compression'        => FALSE,              // Use Zlib compression (can cause issues with integers)
		'servers'            => array(
			array(
				'host'             => 'localhost',  // Memcache Server
				'port'             => 11211,        // Memcache port number
				'persistent'       => FALSE,        // Persistent connection
				'weight'           => 1,
				'timeout'          => 1,
				'retry_interval'   => 15,
				'status'           => TRUE,
			),
		),
		'instant_death'      => TRUE,               // Take server offline immediately on first fail (no retry)
	),
	'memcachetag' => array(
		'driver'             => 'memcachetag',
		'default_expire'     => 3600,
		'compression'        => FALSE,              // Use Zlib compression (can cause issues with integers)
		'servers'            => array(
			array(
				'host'             => 'localhost',  // Memcache Server
				'port'             => 11211,        // Memcache port number
				'persistent'       => FALSE,        // Persistent connection
				'weight'           => 1,
				'timeout'          => 1,
				'retry_interval'   => 15,
				'status'           => TRUE,
			),
		),
		'instant_death'      => TRUE,
	),
	'apc'      => array(
		'driver'             => 'apc',
		'default_expire'     => 3600,
	),
	'wincache' => array(
		'driver'             => 'wincache',
		'default_expire'     => 3600,
	),
	'sqlite'   => array(
		'driver'             => 'sqlite',
		'default_expire'     => 3600,
		'database'           => APPPATH.'cache/gleez-cache.sql3',
		'schema'             => 'CREATE TABLE caches(id VARCHAR(500) PRIMARY KEY, tags VARCHAR(255), expiration INTEGER, cache TEXT)',
		'index'              => 'CREATE UNIQUE INDEX [cache_unique] ON [caches] ([id])',
	),
	'eaccelerator'           => array(
		'driver'             => 'eaccelerator',
	),
	'xcache'   => array(
		'driver'             => 'xcache',
		'default_expire'     => 3600,
	),
	'file'    => array(
		'driver'             => 'file',
		'cache_dir'          => APPPATH.'cache',
		'default_expire'     => 3600,
		'ignore_on_delete'   => array(
			'.gitignore',
			'.git',
			'.svn'
		)
	),
	'mango'  => array(
		'driver'             => 'mango',
		'group'              => 'default',
		'collection'         => 'cache',
		'default_expire'     => 3600,
	),
);