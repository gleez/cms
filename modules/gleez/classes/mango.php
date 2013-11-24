<?php
/**
 * Gleez Mango
 *
 * ### Introduction
 *
 * This class wraps the functionality of MongoClient (connection) and MongoDB (database object)
 * into one Mango class and can be instantiated simply by:
 *
 * ~~~
 *   $db = Mango::instance();
 * ~~~
 *
 * The above will assume the 'default' configuration from the `config/mango.php` file.
 * Alternatively it may be instantiated with the name and configuration specified as arguments:
 *
 * ~~~
 * $db = Mango::instance('test', array(
 *     'test' => array(
 *         'connection' => array(
 *             'hostnames'  => 'mongodb://whisky:13000/?replicaset=seta',
 *             'options'    => array(
 *                 'db'       => 'MyDB',
 *                 'username' => 'username',
 *                 'password' => 'password',
 *                 ...
 *             )
 *         ),
 *         'profiling' => TRUE,
 *         ...
 *     )
 * ));
 * ~~~
 *
 * The [Mango_Collection] class will gain access to the server by calling the instance method with a
 * configuration name, so if the configuration name is not present in the config file then the
 * instance should be created before using any classes that extend [Mango_Collection].
 *
 * Mango can proxy all methods of MongoDB to the database instance as well as select collections
 * using the [Mango::__get] magic method and allows for easy benchmarking if profiling is enabled.
 *
 * ### System Requirements
 *
 * - MongoDB 2.4 or higher
 * - PHP-extension MongoDB 1.4.0 or higher
 *
 * This class has appeared thanks to such wonderful projects as:
 *
 * + [Wouterrr/MangoDB](https://github.com/Wouterrr/MangoDB)
 * + [colinmollenhour/mongodb-php-odm](https://github.com/colinmollenhour/mongodb-php-odm)
 *
 * @method     array           authenticate(string $username, string $password)
 * @method     array           command(array $data, array $options = array())
 * @method     MongoCollection createCollection(string $name, array $options = array())
 * @method     array           createDBRef(string $collection, mixed $a)
 * @method     array           drop()
 * @method     array           dropCollection(mixed $coll)
 * @method     array           execute(mixed $code, array $args = array())
 * @method     boolean         forceError()
 * @method     array           getCollectionNames(boolean $includeSystemCollections = FALSE)
 * @method     array           getDBRef(array $ref)
 * @method     integer         getProfilingLevel()
 * @method     array           getReadPreference()
 * @method     boolean         getSlaveOkay()
 * @method     array           lastError()
 * @method     array           listCollections(boolean $includeSystemCollections = FALSE)
 * @method     array           prevError()
 * @method     array           repair(boolean $preserve_cloned_files = FALSE, boolean $backup_original_files = FALSE)
 * @method     array           resetError()
 * @method     integer         setProfilingLevel(integer $level)
 * @method     boolean         setReadPreference(integer $read_preference, array $tags = array())
 * @method     boolean         setSlaveOkay(boolean $ok = TRUE)
 *
 * @package    Gleez\Mango\Database
 * @author     Gleez Team
 * @version    0.4.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Mango {

	/**
	 * Instance name
	 * @var string
	 */
	protected $_name;

	/**
	 * MongoDB object
	 * @var MongoDB
	 */
	protected $_db;

	/**
	 * Configuration array
	 * @var array
	 */
	protected $_config;

	/**
	 * Raw server connection
	 * @var MongoClient
	 */
	protected $_connection;

	/**
	 * Connected?
	 * @var boolean
	 */
	protected $_connected = FALSE;

	/**
	 * Benchmark token
	 * @var string
	 */
	protected $_benchmark = NULL;

	/**
	 * The class name for the MongoCollection wrapper
	 * Defaults to [Mango_Collection]
	 * @var string
	 */
	protected $_collection_class = 'Mango_Collection';

	/**
	 * Port to connect to if no port is given
	 * @var integer
	 */
	protected $_default_port;

	/**
	 * Host to connect to if no host is given
	 * @var string
	 */
	protected $_default_host;

	/**
	 * Default instance name
	 * @var string
	 */
	public static $default = 'default';

	/**
	 * Mango instances
	 * @var array
	 */
	public static $instances = array();

	/**
	 * Default WriteConcern for new client driver
	 * @var mixed
	 */
	public static $default_write_concern = 1;

	/**
	 * A flag to indicate if profiling is enabled and to allow it to be enabled/disabled on the fly
	 * @var boolean
	 */
	public $profiling;

	/**
	 * Get a singleton Mango instance
	 *
	 * ### Features
	 *
	 * - If configuration is not specified, it will be loaded from the Mango configuration
	 *   file using the same group as the name
	 * - If no group is supplied the [Mango::$default] group is used
	 *
	 * @param   string  $name      Config group name [Optional]
	 * @param   array   $config    Pass a configuration array to bypass the Gleez config [Optional]
	 * @param   boolean $override  Overrides current instance with a new one (useful for testing) [Optional]
	 *
	 * Examples:
	 * ~~~
	 * // Load the default database
	 * $db = Mango::instance();
	 *
	 * // Create a custom configured instance:
	 * $db = Mango::instance('custom', $config);
	 *
	 * // Access an instantiated group directly:
	 * $foo_group = Mango::$instances['foo'];
	 * ~~~
	 *
	 * @return  Mango
	 *
	 * @throws  Mango_Exception
	 */
	public static function instance($name = NULL, array $config = NULL, $override = FALSE)
	{
		if (is_null($name))
		{
			$name = self::$default;
		}

		if ($override OR ! isset(self::$instances[$name]))
		{
			if (is_null($config))
			{
				// Load the configuration
				$config = Config::load('mango');

				if ( ! $config->offsetExists($name))
				{
					throw new Mango_Exception('Failed to load Mango config group: :group',
						array(':group'  => $name)
					);
				}

				// Gets config group
				$config = $config->get($name);
			}

			// Create the Mango instance
			new self($name, $config);
		}

		return self::$instances[$name];
	}

	/**
	 * Mango class constructor
	 *
	 * [!!] This method cannot be accessed directly, you must use [Mango::instance].
	 *
	 * - Checks system requirements
	 * - Stores the database configuration and name of the instance locally
	 * - Sets MongoCollection wrapper
	 * - Profiling setup
	 * - Sets DSN
	 *
	 * @param   string  $name    Mango instance name
	 * @param   array   $config  Mango config
	 *
	 * @throws  Gleez_Exception
	 *
	 * @uses    Arr::get
	 * @uses    Arr::merge
	 */
	protected function __construct($name, array $config)
	{
		if ( ! extension_loaded('mongo'))
		{
			throw new Mango_Exception('The php-mongo extension is not installed or is disabled.');
		}

		// Set the instance name
		$this->_name = $name;

		// Set default hostname and port
		$this->_setDSN();

		// Store the config locally
		$this->_config = $this->_prepareConfig($config);

		$server  = $this->_config['connection']['hostnames'];
		$options = Arr::get($this->_config['connection'], 'options', array());

		if (strpos($server, 'mongodb://') !== 0)
		{
			// Add 'mongodb://'
			$server = 'mongodb://' . $server;
		}

		// safe mode
		if (class_exists('MongoClient'))
		{
			$mongo_class = 'MongoClient';
		}
		else
		{
			$mongo_class = 'Mongo';
			unset($options['w']);
		}

		// Create MongoClient (or Mongo) object (but don't connect just yet)
		$this->_connection = new $mongo_class($server, Arr::merge(array('connect' => FALSE), $options));

		// Save profiling option in a public variable
		$this->profiling = (isset($config['profiling']) AND $config['profiling']) AND Gleez::$profiling;

		// Optional connect
		if (Arr::get($options, 'connect', TRUE))
		{
			$this->connect();
		}

		// Set the collection class name
		$this->setCollectionClass();

		// Store the database instance
		self::$instances[$name] = $this;
	}

	/**
	 * Sets default hostname and port
	 *
	 * [!!] This is called automatically by [Mango::__construct].
	 *
	 * - [MongoClient::DEFAULT_HOST] is localhost
	 * - [MongoClient::DEFAULT_PORT] is 27017
	 */
	protected function _setDSN()
	{
		$default_host = ini_get('mongo.default_host');
		$default_port = ini_get('mongo.default_port');

		$this->_default_host = (empty($default_host)) ? MongoClient::DEFAULT_HOST : $default_host;
		$this->_default_port = (empty($default_port)) ? MongoClient::DEFAULT_PORT : $default_port;
	}

	/**
	 * Prepare Mango config
	 *
	 * [!!] This is called automatically by [Mango::__construct].
	 *
	 * @param   array  $config  Mango config
	 * @return  array
	 */
	protected function _prepareConfig(array $config)
	{
		// If the hostnames doesn't exist or it's empty, prepare default hostname
		if ( ! isset($config['connection']['hostnames']) OR ! $config['connection']['hostnames'])
		{
			$config['connection']['hostnames'] = "{$this->_default_host}:{$this->_default_port}";
		}

		// If the username doesn't exist or it's empty, then we don't need a password
		if ( ! isset($config['connection']['options']['username']) OR ! $config['connection']['options']['username'])
		{
			unset($config['connection']['options']['username'], $config['connection']['options']['password']);
		}

		// Password must be empty if user is exists
		if (isset($config['connection']['options']['username']) AND ( ! isset($config['connection']['options']['password']) OR ! $config['connection']['options']['password']))
		{
			$config['connection']['options']['password'] = '';
		}

		if ( ! isset($config['connection']['options']['connectTimeoutMS']) OR ! $config['connection']['options']['connectTimeoutMS'])
		{
			unset($config['connection']['options']['connectTimeoutMS']);
		}

		/**
		 * Replica Set
		 *
		 * See [Replica Set Fundamental Concepts](http://docs.mongodb.org/manual/core/replication/)
		 */
		if ( ! isset($config['connection']['options']['replicaSet']) OR ! $config['connection']['options']['replicaSet'])
		{
			unset($config['connection']['options']['replicaSet']);
		}

		// The 'w' option specifies the Write Concern for the driver
		if ( ! isset($config['connection']['options']['w']))
		{
			// The default value is 1.
			$config['connection']['options']['w'] = self::$default_write_concern;
		}

		return $config;
	}

	/**
	 * Returns the instance name
	 *
	 * @return  string
	 */
	final public function __toString()
	{
		// Current instance name
		return $this->_name;
	}

	/**
	 * Class destructor
	 */
	final public function __destruct()
	{
		try
		{
			$this->disconnect();
			$this->_db = NULL;
			$this->_connection = NULL;
			$this->_connected  = FALSE;
		}
		catch(Exception $e)
		{
			// can't throw exceptions in __destruct
		}
	}

	/**
	 * Fetch a collection by using object access syntax
	 *
	 * @param   string  $name  Name of the property being interacted with
	 *
	 * @return  Mango_Collection
	 */
	public function __get($name)
	{
		return $this->selectCollection($name);
	}

	/**
	 * Proxy all methods for the MongoDB class
	 *
	 * Profiles all methods that have database interaction if profiling is enabled.
	 * The database connection is established lazily.
	 *
	 * Usage:
	 * ~~~
	 * $db->selectCollectionNames(TRUE);
	 * ~~~
	 *
	 * @since   0.2.0
	 *
	 * @param   string  $name       Name of the method being called
	 * @param   array   $arguments  Enumerated array containing the parameters passed to the $name
	 * @return  mixed
	 *
	 * @throws  Mango_Exception
	 *
	 * @uses    Profiler::start
	 * @uses    Profiler::stop
	 * @uses    JSON::encode
	 */
	public function __call($name, $arguments)
	{
		$this->_connected OR $this->connect();

		if ( ! method_exists($this->_db, $name))
		{
			throw new Mango_Exception("Method doesn't exist: :method",
				array(':method' => "MongoDB::{$name}")
			);
		}

		if ($this->profiling)
		{
			$json_args = array();
			foreach($arguments as $arg)
			{
				$json_args[] = JSON::encode($arg);
			}

			$method           = ($name == 'command' ? 'runCommand' : $name);
			$this->_benchmark = Profiler::start(__CLASS__."::{$this->_name}", "db.{$method}(" . implode(', ', $json_args) . ")");
		}

		$return_value = call_user_func_array(array($this->_db, $name), $arguments);

		if ($this->_benchmark)
		{
			// Stop the benchmark
			Profiler::stop($this->_benchmark);

			// Clean benchmark token
			$this->_benchmark = NULL;
		}

		return $return_value;
	}

	/**
	 * Database connection
	 *
	 * [!!] This will automatically be called by any MongoDB methods that are proxied via [Mango::__call]
	 *
	 * [!!] This **may be** called automatically by [Mango::__construct].
	 *
	 * @return  boolean  Connection status
	 *
	 * @throws  Gleez_Exception
	 *
	 * @uses    Arr::path
	 * @uses    Profiler::start
	 * @uses    Profiler::stop
	 */
	public function connect()
	{
		if ($this->_connected)
		{
			return TRUE;
		}

		try
		{
			if ($this->profiling)
			{
				// Start a new benchmark
				$this->_benchmark = Profiler::start(__CLASS__."::{$this->_name}", 'connect()');
			}

			// Connecting to the server
			$this->_connected = $this->_connection->connect();

			if ($this->_benchmark)
			{
				// Stop the benchmark
				Profiler::stop($this->_benchmark);

				// Clean benchmark token
				$this->_benchmark = NULL;
			}
		}
		catch (MongoConnectionException $e)
		{
			throw new Mango_Exception('Unable to connect to MongoDB server. MongoDB said :message',
				array(':message' => $e->getMessage())
			);
		}

		$this->_connected    = $this->_connection->connected;
		$this->_db           = $this->_connected
			? $this->_connection->selectDB(Arr::path($this->_config, 'connection.options.db'))
			: NULL;

		return $this->_connected;
	}

	/**
	 * Forcefully closes a connection to the database,
	 * even if persistent connections are being used.
	 *
	 * [!!] This is called automatically by [Mango::__destruct].
	 */
	public function disconnect()
	{
		if ($this->_connected)
		{
			$connections = $this->_connection->getConnections();

			foreach ($connections as $connection)
			{
				// Close the connection to Mongo
				$this->_connection->close($connection['hash']);
			}

			$this->_db = $this->_benchmark = NULL;
			$this->_connected = FALSE;
		}
	}

	/**
	 * Returns connection status
	 *
	 * @since   0.2.0
	 *
	 * @return  boolean
	 */
	public function is_connected()
	{
		return (bool)$this->_connected;
	}

	/**
	 * Get an instance of MongoDB directly
	 *
	 * Example:
	 * ~~~
	 * Mango::instance()->db();
	 * ~~~
	 *
	 * @return MongoDB
	 */
	public function db()
	{
		$this->_connected OR $this->connect();

		return $this->_db;
	}

	/**
	 * Allows one to override the default [Mango_Collection] class
	 *
	 * Class name must be defined in config.
	 * By default when using [Mango::$_collection_class] it [Mango_Collection].
	 *
	 * [!!] This is called automatically by [Mango::__construct].
	 *
	 * Example:
	 * ~~~
	 * $db->setCollectionClass('MyClass');
	 * ~~~
	 *
	 * @since   0.2.0
	 *
	 * @param  string  $class_name  Class name [Optional]
	 */
	public function setCollectionClass($class_name = NULL)
	{
		if (is_null($class_name))
		{
			$this->_collection_class = ((isset($this->_config['collection']) AND $this->_config['collection']) ? $this->_config['collection'] : $this->_collection_class);
		}
		else
		{
			$this->_collection_class = $class_name;
		}
	}

	/**
	 * Get a [Mango_Collection] instance with grid FS enabled
	 *
	 * Wraps MongoCollection
	 *
	 * Example:
	 * ~~~
	 * $collection = $db->getGridFS('myfs');
	 * ~~~
	 *
	 * @param   string  $prefix  The prefix for the files and chunks collections [Optional]
	 *
	 * @return  Mango_Collection
	 */
	public function getGridFS($prefix = 'fs')
	{
		$this->_connected OR $this->connect();

		return new $this->_collection_class($prefix, $this->_name, TRUE);
	}

	/**
	 * Get the currently referenced database
	 *
	 * @since   0.4.0
	 *
	 * @link    http://docs.mongodb.org/manual/reference/method/db.getName/
	 *
	 * @return  string
	 */
	public function getName()
	{
		return $this->safeExecute('db.getName()');
	}

	/**
	 * Get an instance of MongoClient
	 *
	 * Example:
	 * ~~~
	 * // Get Mango instance
	 * $db = Mango::instance();
	 *
	 * // Get MongoClient instance
	 * $connection = $db->getConnection();
	 *
	 * // For example, list databases
	 * $connection->listDBs()
	 * ~~~
	 *
	 * @since   0.3.4
	 *
	 * @return  MongoClient
	 */
	public function getConnection()
	{
		return $this->_connection;
	}

	/**
	 * Get a [Mango_Collection] instance
	 *
	 * Wraps MongoCollection
	 *
	 * The name of the called class can be changed directly in the configuration.
	 *
	 * [!!] This is called automatically by [Mango::__get].
	 *
	 * Example:
	 * ~~~
	 * $collection = $db->selectCollection('users');
	 * ~~~
	 *
	 * @since   0.3.1
	 *
	 * @param   string  $name  Collection name
	 *
	 * @return  Mango_Collection
	 */
	public function selectCollection($name)
	{
		$this->_connected OR $this->connect();

		return new $this->_collection_class($name, $this->_name);
	}

	/**
	 * Runs JavaScript code on the database server
	 *
	 * This method allows you to run arbitrary JavaScript on the database.
	 * Same usage as MongoDB::execute except it throws an exception on error.
	 *
	 * Example:
	 * ~~~
	 * $db->safeExecute('db.foo.count();');
	 * ~~~
	 *
	 * @since   0.3.3
	 *
	 * @link    http://php.net/manual/en/mongodb.execute.php MongoDB::execute
	 *
	 * @param   string|MongoCode  $code   MongoCode or string to execute
	 * @param   array             $args   Arguments to be passed to code [Optional]
	 * @param   array             $scope  The scope to use for the code if $code is a string [Optional]
	 *
	 * @return  mixed
	 *
	 * @throws  Mango_Exception
	 * @throws  MongoException
	 */
	public function safeExecute($code, array $args = array(), $scope = array())
	{
		if ( ! is_string($code) AND ! $code instanceof MongoCode)
		{
			throw new Mango_Exception('The code must be a string or an instance of MongoCode');
		}

		if ( ! $code instanceof MongoCode)
		{
			$code = new MongoCode($code, $scope);
		}

		$result = $this->execute($code, $args);

		if (empty($result['ok']))
		{
			throw new MongoException($result['errmsg'], $result['code']);
		}

		return $result['retval'];
	}

	/**
	 * Execute a database command
	 *
	 * Almost everything that is not a CRUD operation can be done with a this method.
	 * Same usage as MongoDB::command except it throws an exception on error.
	 *
	 * Example:
	 * ~~~
	 * $ages = $db->safeCommand(array("distinct" => "people", "key" => "age"));
	 * ~~~
	 *
	 * @since   0.3.3
	 *
	 * @link    http://www.php.net/manual/en/mongodb.command.php MongoDB::command
	 *
	 * @param   array  $command  The query to send
	 * @param   array  $options  An associative array of command options [Optional]
	 *
	 * @return  array
	 *
	 * @throws  MongoException
	 */
	public function safeCommand(array $command, array $options = array())
	{
		$result = $this->command($command, $options);

		if (empty($result['ok']))
		{
			$message = isset($result['errmsg']) ? $result['errmsg'] : 'Error: ' . json_encode($result);
			$code    = isset($result['errno']) ? $result['errno'] : 0;

			throw new MongoException($message, $code);
		}

		return $result;
	}

	/**
	 * Update a document and return it
	 *
	 * Simple findAndModify helper
	 *
	 * @since   0.3.1
	 *
	 * @link    http://www.php.net/manual/en/mongocollection.findandmodify.php MongoCollection::findAndModify
	 *
	 * @param   string  $collection  Collection name
	 * @param   array   $command     The query to send
	 *
	 * @return  mixed
	 *
	 * @throws  MongoException
	 */
	public function findAndModify($collection, array $command)
	{
		$command = array_merge(array('findAndModify' => (string)$collection), $command);
		$result  = $this->safeCommand($command);

		return $result['value'];
	}

	/**
	 * Checks if the given $collection exists in the currently referenced database
	 *
	 * @since   0.3.3
	 *
	 * @param   string  $collection  Collection name
	 *
	 * @return  boolean
	 */
	public function exists($collection)
	{
		$result = $this->safeExecute("db.{$collection}.exists()");

		return ( ! is_null($result));
	}
}
