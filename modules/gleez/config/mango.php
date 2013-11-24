<?php

return array(
	/**
	 * Configuration Name
	 *
	 * You use this name when initializing a new Gleez Mango instance
	 *
	 * Example:
	 * ~~~
	 * $db = Mango::instance('default');
	 * ~~~
	 *
	 * @var array
	 */
	'default' => array(
		/**
		 * Connection Setup
		 *
		 * See http://www.php.net/manual/en/class.mongoclient.php for more information
		 * or just edit (comment/uncomment) the keys below to your requirements
		 */
		'connection' => array(
			/**
			 * The following options are available for Mango:
			 *
			 * string  hostnames  Server hostname, or socket. Separate multiple hosts by commas.
			 *                    FALSE and '' are identical. Optional value
			 * array   options    Options array. Only 'db' is required
			 */
			'hostnames'  => 'localhost:27017', // Optional
			'options'    => array(
				/**
				 * The following extra options are available for MongoDB:
				 *
				 * string   db                Database to connect to. Cannot contain " ", "." or be the empty string.
				 *                            The name "system" is also reserved.
				 * integer  connectTimeoutMS  Default timeout. It is measured in milliseconds.
				 * boolean  connect           Connect to DB on creation connection. How do you want to deal with
				 *                            connection errors. TRUE - Mango::instance fails and an exception is
				 *                            thrown. Next call to Mango::instance will try to connect again.
				 *                            FALSE - Exception is thrown when you run first DB action.
				 *                            Next call to Mango::instance will return same object.
				 * string   username          Database username. FALSE and '' are identical.
				 * string   password          Database password. FALSE and '' are identical.
				 * string   replicaSet        The name of the replica set to connect to. FALSE and '' are identical
				 * mixed    w                 When a write is given a Write Concern option ("w") the driver will send
				 *                            the query to MongoDB and piggy back a getLastError command (GLE) with
				 *                            the Write Concern option at the same time.
				 *                            See http://www.php.net/manual/ru/mongo.writeconcerns.php
				 */
				'db'               => 'gleez', // Required
				'connectTimeoutMS' => 10000,   // Optional
				'connect'          => FALSE,   // Optional
				'username'         => FALSE,   // Optional
				'password'         => FALSE,   // Optional
				'replicaSet'       => FALSE,   // Optional
				'w'                => 1,       // Optional
			),
		),
		/**
		 * Whether or not to use profiling.
		 *
		 * boolean  profiling If enabled, profiling data will be shown through Gleez profiler library
		 */
		'profiling' => TRUE, // Optional

		/**
		 * You can override the class name for the MongoCollection wrapper
		 * By default using [Mango::$_collection_class] it [Mango_Collection]
		 *
		 * string  collection  The class name for the MongoCollection wrapper
		 */
		'collection' => '', // Optional
	)
);
