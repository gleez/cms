<?php

return array(
	'db' => array(
		/**
		 * Database settings for session storage
		 *
		 * string   group  Database config group name
		 * string   table  The name of the session table
		 * integer  gc     Number of requests before gc is invoked
		 * columns  array  Custom column names
		 */
		'group'   => 'default',
		'table'   => 'sessions',
		'gc'      => 500,
		'columns' => array(
			/**
			 * Table columns name
			 *
			 * string  session_id   Session identifier
			 * string  last_active  Timestamp of the last activity
			 * string  contents     Serialized session data
			 * string  hostname     Host name
			 * string  user_id      The used ID
			 */
			'session_id'  => 'session_id',
			'last_active' => 'last_active',
			'contents'    => 'contents',
			'hostname'    => 'hostname',
			'user_id'     => 'user_id'
		),
	),
	'mango' => array(
		/**
		 * MongoDB settings for session storage
		 *
		 * string   group       Mango config group name
		 * string   collection  The name of the session collection
		 * integer  gc          Number of requests before gc is invoked
		 * array    fields      Custom field names
		 */
		'group'       => 'default',
		'collection'  => 'sessions',
		'gc'          => 500,
		'fields'      => array(
			/**
			 * Collection field name
			 *
			 * string  session_id   Session identifier
			 * string  last_active  Timestamp of the last activity
			 * string  contents     Serialized session data
			 * string  hostname     Host name
			 * string  user_id      The used ID
			 */
			'session_id'  => 'session_id',
			'last_active' => 'last_active',
			'contents'    => 'contents',
			'hostname'    => 'hostname',
			'user_id'     => 'user_id'
		),
	),
	'cookie' => array(
		'encrypted' => FALSE,
	),
);
