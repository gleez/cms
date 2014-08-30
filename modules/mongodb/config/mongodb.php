<?php

return array(
	/**
	 * Configuration Name
	 *
	 * Use this name when initializing a new \Gleez\Mango\Client instance
	 *
	 * Example:
	 * ~~~
	 * $db = \Gleez\Mango\Client::instance('default');
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
			 * The following options are available for \Gleez\Mango\Client:
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
				 *                            connection errors. TRUE — \Gleez\Mango\Client::instance fails and an
				 *                            exception is thrown. Next call to \Gleez\Mango\Client::instance will try
				 *                            to connect again. FALSE — Exception is thrown when you run first DB action.
				 *                            Next call to \Gleez\Mango\Client::instance will return same object.
				 * string   username          Database username. FALSE and '' are identical.
				 * string   password          Database password. FALSE and '' are identical.
				 * string   replicaSet        The name of the replica set to connect to. FALSE and '' are identical
				 * mixed    w                 When a write is given a Write Concern option ("w") the driver will send
				 *                            the query to MongoDB and piggy back a getLastError command (GLE) with
				 *                            the Write Concern option at the same time.
				 *                            See http://www.php.net/manual/en/mongo.writeconcerns.php
				 * int      wtimeout          The number of milliseconds to wait for \MongoDB::$w replications to take
				 *                            place.
				 */
				'db'               => 'gleez', // Required
				'connectTimeoutMS' => 10000,   // Optional
				'connect'          => FALSE,   // Optional
				'username'         => FALSE,   // Optional
				'password'         => FALSE,   // Optional
				'replicaSet'       => FALSE,   // Optional
				'w'                => 1,       // Optional
				'wtimeout'         => 10000,   // Optional
			),
		),
		/**
		 * Whether or not to use profiling.
		 * Note: [\Gleez::$profiling] should be enabled.
		 *
		 * boolean  profiling If enabled, profiling data will be shown through Gleez profiler library
		 */
		'profiling' => TRUE, // Optional

		/**
		 * You can override the class name for the \MongoCollection wrapper
		 * By default using [\Gleez\Mango\Client::collectionClass] it [\Gleez\Mango\Collection]
		 *
		 * string  collection  The class name for the \MongoCollection wrapper
		 */
		'collection' => '', // Optional
	)
);
