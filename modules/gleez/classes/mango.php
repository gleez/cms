<?php defined('SYSPATH') OR die('No direct script access allowed.');
/**
 * Gleez Mango
 *
 * ### Introduction
 *
 * This class wraps the functionality of MongoClient (connection)
 * and MongoDB (database object) into one class and can be instantiated simply by:
 *
 * <code>
 *   $db = Mango::instance();
 * </code>
 *
 * The above will assume the 'default' configuration from the `config/mango.php` file.
 * Alternatively it may be instantiated with the name and configuration specified as arguments:
 *
 * <pre>
 *   $db = Mongo_Database::instance('test', array(
 *     'test' => array(
 *       'connection' => array(
 *         'hostnames'  => 'mongodb://whisky:13000/?replicaset=seta',
 *         'options'    => array('db' => 'MyDB', ...)
 *       ),
 *       'profiling' => TRUE,
 *       ...
 *     )
 *   ));
 * </pre>
 *
 * [Mango] can proxy all methods of MongoDB to the database instance as well as select collections
 * using the [Mango::__get] magic method and allows for easy benchmarking if profiling is enabled.
 *
 * ### System Requirements
 *
 * - PHP 5.3 or higher
 * - MongoDB 2.4 or higher
 * - PHP-extension MongoDB 1.4 or higher
 *
 * @package    Gleez\Mango\Database
 * @author     Sergey Yakovlev - Gleez
 * @version    0.2.0
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
	 * @param   string  $name    Config group name [Optional]
	 * @param   array   $config  Pass a configuration array to bypass the Gleez config [Optional]
	 *
	 * ### Usage
	 *
	 * Load the default database:<br>
	 * <code>
	 *   $db = Mango::instance();
	 * </code>
	 *
	 * Create a custom configured instance:<br>
	 * <code>
	 *   $db = Mango::instance('custom', $config);
	 * </code>
	 *
	 * Access an instantiated group directly:<br>
	 * <code>
	 *   $foo_group = Mango::$instances['foo'];
	 * </code>
	 *
	 * @return  Mango
	 *
	 * @throws  Mango_Exception
	 */
	public static function instance($name = NULL, array $config = NULL)
	{
		if (is_null($name))
		{
			$name = Mango::$default;
		}

		if ( ! isset(Mango::$instances[$name]))
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
			new Mango($name, $config);
		}

		return Mango::$instances[$name];
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

		// Create MongoClient object (but don't connect just yet)
		$this->_connection = new MongoClient($server, Arr::merge(array('connect' => FALSE), $options));

		// Optional connect
		if (Arr::get($options, 'connect', TRUE))
		{
			$this->connect();
		}

		// Set the collection class name
		$this->setCollectionClass();

		// Save profiling option in a public variable
		// @todo Kohana::$profiling => Gleez::$profiling
		$this->profiling = (isset($config['profiling']) AND $config['profiling']) AND Kohana::$profiling;

		// Store the database instance
		Mango::$instances[$name] = $this;
	}

	/**
	 * Sets default hostname and port
	 *
	 * [!!] This is called automatically by [Mango::__construct].
	 */
	protected function _setDSN()
	{
		$default_host = ini_get('mongo.default_host');
		$default_port = ini_get('mongo.default_port');

		$this->_default_host = (is_null($default_host) OR empty($default_host)) ? MongoClient::DEFAULT_HOST : $default_host;
		$this->_default_port = (is_null($default_port) OR empty($default_port)) ? MongoClient::DEFAULT_PORT : $default_port;
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

		return $config;
	}

	/**
	 * Returns the instance name
	 *
	 * @return  string  Instance name
	 */
	final public function __toString()
	{
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
	 * Usage:<br>
	 * <code>
	 *   $db->getCollectionNames(TRUE);
	 * </code>
	 *
	 * @since   0.2.0
	 *
	 * @param   string  $name       Name of the method being called
	 * @param   array   $arguments  Enumerated array containing the parameters passed to the `$name`
	 * @return  mixed
	 *
	 * @throws  Mango_Exception
	 *
	 * @uses    Profiler::start
	 * @uses    Profiler::stop
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
			foreach($arguments as $argument)
			{
				$json_arguments[] = json_encode((is_array($argument) ? (object)$argument : $argument));
				$method           = ($name == 'command' ? 'runCommand' : $name);
				$this->_benchmark = Profiler::start("Mango::$this->_name", "db.$method(" . implode(', ', $json_arguments) . ")");
			}
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
				$this->_benchmark = Profiler::start("Mango::$this->_name", 'connect()');
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
	 * Example:<br>
	 * <code>
	 *   Mango::instance()->db();
	 * </code>
	 *
	 * @return MongoDB
	 */
	public function db()
	{
		$this->_connected OR $this->connect();

		return $this->_db;
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
	 * @since   0.2.0
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
	 * Allows one to override the default [Mango_Collection] class
	 *
	 * Class name must be defined in config.
	 * By default using [Mango::$_collection_class] it [Mango_Collection].
	 *
	 * [!!] This is called automatically by [Mango::__construct].
	 *
	 * Example:<br>
	 * <code>
	 *   $db->setCollectionClass('MyClass');
	 * </code>
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
	 * @param   string  $prefix  The prefix for the files and chunks collections [Optional]
	 * @return  Mango_Collection
	 */
	public function getGridFS($prefix = 'fs')
	{
		$this->_connected OR $this->connect();

		return new $this->_collection_class($prefix, $this->_name, TRUE);
	}
}