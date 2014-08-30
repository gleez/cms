<?php
/**
 * Gleez CMS (http://gleezcms.org)
 *
 * @link https://github.com/cleez/cms Canonical source repository
 * @copyright Copyright (c) 2011-2014 Gleez Technologies
 * @license http://gleezcms.org/license Gleez CMS License
 */

namespace Gleez\Mango;

use MongoClient;
use MongoDB;
use MongoCode;
use Config;
use JSON;
use Profiler;
use Arr;

/**
 * Gleez Mango Client
 *
 * This class wraps the functionality of \MongoClient (connection) and \MongoDB (database object) into one Client class.
 *
 * @method     array           authenticate(string $username, string $password)
 * @method     array           command(array $data, array $options = array())
 * @method     \MongoCollection createCollection(string $name, array $options = array())
 * @method     array           createDBRef(string $collection, mixed $a)
 * @method     array           drop()
 * @method     array           dropCollection(mixed $coll)
 * @method     array           execute(mixed $code, array $args = array())
 * @method     boolean         forceError()
 * @method     array           getCollectionNames(boolean $includeSystemCollections = false)
 * @method     array           getDBRef(array $ref)
 * @method     integer         getProfilingLevel()
 * @method     array           getReadPreference()
 * @method     boolean         getSlaveOkay()
 * @method     array           lastError()
 * @method     array           listCollections(boolean $includeSystemCollections = false)
 * @method     array           prevError()
 * @method     array           repair(boolean $preserve_cloned_files = false, boolean $backup_original_files = false)
 * @method     array           resetError()
 * @method     integer         setProfilingLevel(integer $level)
 * @method     boolean         setReadPreference(integer $read_preference, array $tags = array())
 * @method     boolean         setSlaveOkay(boolean $ok = true)
 *
 * @package    Gleez\Mango
 * @author     Gleez Team
 * @version    1.0.0
 */
class Client
{

	/**
	 * Gleez Mango Client version
	 * @type string
	 */
	const VERSION = '1.0.0';

	/**
	 * Minimal required version of PHP-extension MongoDB
	 * @type string
	 */
	const DRIVER_MIN_REQ = '1.4.5';

	/**
	 * Component config file
	 * @type string
	 */
	const CONFIG_FILE_NAME = 'mongodb';

	/**
	 * Current instance name
	 * @var string
	 */
	protected $name;

	/**
	 * \MongoDB object
	 * @var \MongoDB
	 */
	protected $db;

	/**
	 * Current configuration
	 * @var array
	 */
	protected $config;

	/**
	 * Raw server connection
	 * @var \MongoClient
	 */
	protected $connection;

	/**
	 * Connected?
	 * @var boolean
	 */
	protected $connected = false;

	/**
	 * Benchmark token
	 * @var string
	 */
	protected $benchmark = null;

	/**
	 * The class name for the \MongoCollection wrapper
	 * Defaults to [\Gleez\Mango\Collection]
	 * @var string
	 */
	private $collectionClass = '\Gleez\Mango\Collection';

	/**
	 * Port to connect to if no port is given
	 * @var integer
	 */
	protected $defaultPort;

	/**
	 * Host to connect to if no host is given
	 * @var string
	 */
	protected $defaultHost;

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
	 * Default Write Concern for new client driver
	 * @var mixed
	 */
	private $w = 1;

	/**
	 * The number of milliseconds to wait for \MongoDB::$w replications to take place
	 * @var int
	 */
	private $wtimeout = 10000;

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
	 * - If no group is supplied the [\Gleez\Mango\Client\::$default] group is used
	 *
	 * Examples<br>
	 * <code>
	 * // Load the default database
	 * $db = \Gleez\Mango\Client::instance();
	 *
	 * // Create a custom configured instance
	 * $db = \Gleez\Mango\Client::instance('myInstance', $configArray);
	 *
	 * // Access an instantiated group directly
	 * $myGroup = \Gleez\Mango\Client::$instances['foo'];
	 * </code>
	 *
	 * @param   string  $name      Config group name [Optional]
	 * @param   array   $config    Pass a configuration array to bypass the Gleez config [Optional]
	 * @param   boolean $override  Overrides current instance with a new one (useful for testing) [Optional]
	 *
	 * @return  \Gleez\Mango\Client
	 *
	 * @throws  \Gleez\Mango\Exception
	 *
	 * @uses   \Config::load
	 * @uses   \Config::get
	 * @uses   \Config::offsetExists
	 */
	public static function instance($name = null, array $config = null, $override = false)
	{
		if (is_null($name))
			$name = static::$default;

		if ($override || ! isset(static::$instances[$name])) {
			if (is_null($config)) {
				// Load the configuration
				$config = Config::load(static::CONFIG_FILE_NAME);

				if (!$config->offsetExists($name))
					throw new Exception('Failed to load Mango config group: :group',
						array(':group'  => $name)
					);

				// Gets config group
				$config = $config->get($name);
			}

			// Create the Mango instance
			new static($name, $config);
		}

		return static::$instances[$name];
	}

	/**
	 * Mango class constructor
	 *
	 * [!!] This method cannot be accessed directly, you must use [\Gleez\Mango\Client::instance].
	 *
	 * - Checks system requirements
	 * - Stores the database configuration and name of the instance locally
	 * - Sets \MongoCollection wrapper
	 * - Profiling setup
	 * - Sets DSN
	 *
	 * @param   string  $name    Mango instance name
	 * @param   array   $config  Mango config
	 *
	 * @throws  \Gleez\Mango\Exception
	 */
	protected function __construct($name, array $config)
	{
		if (!extension_loaded('mongo'))
			throw new Exception('The php-mongo extension is not installed or is disabled.');

		if (version_compare(\MongoClient::VERSION, static::DRIVER_MIN_REQ) < 0)
			throw new Exception('Minimal required version of MongoDB driver is :version', array(':version' => static::DRIVER_MIN_REQ));

		// Set the instance name
		$this->name = $name;

		// Set default hostname and port
		$this->setDsn();

		// Store the config locally
		$this->config = $this->prepareConfig($config);

		$server  = $this->config['connection']['hostnames'];
		$options = isset($this->config['connection']['options']) ? $this->config['connection']['options'] : array();

		if (strpos($server, 'mongodb://') !== 0)
			// Add 'mongodb://'
			$server = 'mongodb://' . $server;

		// Create \MongoClient object (but don't connect just yet)
		$this->connection = new MongoClient($server, array('connect' => false) + $options);

		// Save profiling option in a public variable
		$this->profiling = (isset($config['profiling']) && $config['profiling']);

		// Optional connect
		if (isset($options['connect']) && true === $options['connect'])
			$this->connect();

		// Set the collection class name
		$this->setCollectionClass();

		// Store the database instance
		static::$instances[$name] = $this;
	}

	/**
	 * Sets default hostname and port
	 *
	 * [!!] This is called automatically by [\Gleez\Mango\Client::__construct].
	 *
	 * - [\MongoClient::DEFAULT_HOST] is localhost
	 * - [\MongoClient::DEFAULT_P||T] is 27017
	 */
	protected function setDsn()
	{
		$this->defaultHost = ini_get('mongo.default_host') ?: MongoClient::DEFAULT_HOST;
		$this->defaultPort = ini_get('mongo.default_port') ?: MongoClient::DEFAULT_PORT;
	}

	/**
	 * Set the write concern for this database
	 *
	 * @since   1.0.0
	 *
	 * @link    http://php.net/manual/en/mongo.writeconcerns.php Write Concern
	 *
	 * @param   mixed $w         The write concern
	 * @param   int   $wtimeout  The maximum number of milliseconds to wait for the server to satisfy the write concern. [Optional]
	 *
	 * @return  \Gleez\Mango\Client
	 * @throws  \Gleez\Mango\Exception
	 */
	public function setWriteConcern($w, $wtimeout = 10000)
	{
		if (!is_numeric($wtimeout))
			throw new Exception('Param $wtimeout must be numeric value.');

		try {
			$this->config['connection']['options']['w'] = $w;
			$this->config['connection']['options']['wtimeout'] = (int) $wtimeout;

			// PECL mongo >=1.5.0
			if (version_compare(\MongoClient::VERSION, '1.5.0') >= 0)
				MongoDB::setWriteConcern($w, (int) $wtimeout);

		} catch(Exception $e) {
			throw new Exception($e->getMessage(), $e->getCode());
		}

		return $this;
	}

	/**
	 * Get an array describing the write concern for this database
	 *
	 * @since   1.0.0
	 *
	 * @link    http://php.net/manual/en/mongo.writeconcerns.php Write Concern
	 *
	 * @return  array
	 */
	public function getWriteConcern()
	{
		// PECL mongo >=1.5.0
		if (version_compare(\MongoClient::VERSION, '1.5.0') >= 0) {
			return MongoDB::getWriteConcern();
		}

		return array(
			'w' => $this->config['connection']['options']['w'],
			'wtimeout' => $this->config['connection']['options']['wtimeout'],
		);
	}

	/**
	 * Prepare Mango config.
	 *
	 * [!!] This is called automatically by [\Gleez\Mango\Client::__construct].
	 *
	 * @param   array  $config  Mango config
	 * @return  array
	 */
	protected function prepareConfig(array $config)
	{
		// If the hostnames doesn't exist or it's empty, prepare default hostname
		if (!isset($config['connection']['hostnames']) || !$config['connection']['hostnames'])
			$config['connection']['hostnames'] = "{$this->defaultHost}:{$this->defaultPort}";

		// If the username doesn't exist or it's empty, then we don't need a password
		if (!isset($config['connection']['options']['username']) || !$config['connection']['options']['username'])
			unset($config['connection']['options']['username'], $config['connection']['options']['password']);

		// Password must be empty if user is exists
		if (isset($config['connection']['options']['username']) && (!isset($config['connection']['options']['password']) || !$config['connection']['options']['password']))
			$config['connection']['options']['password'] = '';

		if (!isset($config['connection']['options']['connectTimeoutMS']) || !$config['connection']['options']['connectTimeoutMS'])
			unset($config['connection']['options']['connectTimeoutMS']);

		// Replica Set
		if (!isset($config['connection']['options']['replicaSet']) || ! $config['connection']['options']['replicaSet'])
			unset($config['connection']['options']['replicaSet']);

		// The 'w' option specifies the Write Concern for the driver
		if (!isset($config['connection']['options']['w']))
			// The default value is 1.
			$config['connection']['options']['w'] = $this->w;

		if (!isset($config['connection']['options']['wtimeout']) || !is_numeric($config['connection']['options']['wtimeout']))
			$config['connection']['options']['wtimeout'] = $this->wtimeout;

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
		return $this->name;
	}

	/**
	 * Class destructor
	 */
	final public function __destruct()
	{
		try {
			$this->disconnect();
			$this->db = null;
			$this->connection = null;
			$this->connected  = false;
		} catch(Exception $e) {
			// can't throw exceptions in __destruct
		}
	}

	/**
	 * Fetch a collection by using object access syntax
	 *
	 * @param   string  $name  Name of the property being interacted with
	 *
	 * @return  \Gleez\Mango\Collection
	 */
	public function __get($name)
	{
		return $this->selectCollection($name);
	}

	/**
	 * Proxy all methods for the \MongoDB class
	 *
	 * Profiles all methods that have database interaction if profiling is enabled.
	 * The database connection is established lazily.
	 *
	 * Example:<br>
	 * <code>
	 * $db->selectCollectionNames(true);
	 * </code>
	 *
	 * @since   0.2.0
	 *
	 * @param   string  $name       Name of the method being called
	 * @param   array   $arguments  Enumerated array containing the parameters passed to the $name
	 * @return  mixed
	 *
	 * @throws  \Gleez\Mango\Exception
	 *
	 * @uses    \Profiler::start
	 * @uses    \Profiler::stop
	 * @uses    \JSON::encode
	 */
	public function __call($name, $arguments)
	{
		$this->isConnected() || $this->connect();

		if (!method_exists($this->db, $name))
			throw new Exception("Method doesn't exist: :method",
				array(':method' => get_class($this->db)."::{$name}")
			);

		if ($this->profiling) {
			$jsonArgs = array();
			foreach($arguments as $arg)
				$jsonArgs[] = JSON::encode($arg);

			$method          = ($name == 'command' ? 'runCommand' : $name);
			$this->benchmark = Profiler::start(__CLASS__."::{$this->name}", "db.{$method}(" . implode(', ', $jsonArgs) . ")");
		}

		$retval = call_user_func_array(array($this->db, $name), $arguments);

		if ($this->benchmark) {
			// Stop the benchmark
			Profiler::stop($this->benchmark);

			// Clean benchmark token
			$this->benchmark = null;
		}

		return $retval;
	}

	/**
	 * Database connection
	 *
	 * [!!] This will automatically be called by any \MongoDB methods that are proxied via [\Gleez\Mango\Client::__call]
	 *
	 * [!!] This **may be** called automatically by [\Gleez\Mango\Client::__construct].
	 *
	 * @return  boolean  Connection status
	 *
	 * @throws  \Gleez\Mango\Exception
	 *
	 * @uses    \Arr::path
	 * @uses    \Profiler::start
	 * @uses    \Profiler::stop
	 */
	public function connect()
	{
		if ($this->isConnected())
			return true;

		try {
			if ($this->profiling)
				// Start a new benchmark
				$this->benchmark = Profiler::start(__CLASS__."::{$this->name}", 'connect()');

			// Connecting to the server
			$this->connected = $this->connection->connect();

			if ($this->benchmark) {
				// Stop the benchmark
				Profiler::stop($this->benchmark);

				// Clean benchmark token
				$this->benchmark = null;
			}
		} catch (\Exception $e) {
			throw new Exception('Unable to connect to MongoDB server. MongoDB said :message',
				array(':message' => $e->getMessage())
			);
		}

		$this->db = $this->isConnected()
			? $this->connection->selectDB(Arr::path($this->config, 'connection.options.db'))
			: null;

		return $this->isConnected();
	}

	/**
	 * Forcefully closes a connection to the database,
	 * even if persistent connections are being used.
	 *
	 * [!!] This is called automatically by [\Gleez\Mango\Client::__destruct].
	 */
	public function disconnect()
	{
		if ($this->isConnected()) {
			$connections = $this->connection->getConnections();

			foreach ($connections as $connection)
				// Close the connection to Mongo
				$this->connection->close($connection['hash']);

			$this->db = $this->benchmark = null;
			$this->connected = false;
		}
	}

	/**
	 * Returns connection status
	 *
	 * @since   0.2.0 Initial implementation
	 * @since   1.0.0 Renamed method
	 *
	 * @return  boolean
	 */
	public function isConnected()
	{
		return (bool) $this->connected;
	}

	/**
	 * Get an instance of \MongoDB directly
	 *
	 * @since   0.1.0  Initial method Mango::db
	 * @since   0.5.0  Renamed to Mango::getDb
	 * @since   1.0.0  Renamed to \Gleez\Mango\Client::getDb
	 *
	 * Example:<br>
	 * <code>
	 * \Gleez\Mango\Client::instance()->getDb();
	 * </code>
	 *
	 * @return \MongoDB
	 */
	public function getDb()
	{
		$this->isConnected() || $this->connect();

		return $this->db;
	}

	/**
	 * Allows one to override the default [\Gleez\Mango\Collection] class
	 *
	 * Class name must be defined in config.
	 * By default when using [\Gleez\Mango\Client::$collectionClass] it [\Gleez\Mango\Collection].
	 *
	 * [!!] This is called automatically by [\Gleez\Mango\Client::__construct].
	 *
	 * Example:<br>
	 * <code>
	 * $db->setCollectionClass('MyClass');
	 * </code>
	 *
	 * @since   0.2.0  Initial implementation
	 * @since   1.0.0  class_exists check, throws
	 *
	 * @param  string  $className  Class name [Optional]
	 *
	 * @return  \Gleez\Mango\Client
	 *
	 * @throws  \Gleez\Mango\Exception
	 */
	public function setCollectionClass($className = null)
	{
		if (empty($className)) {
			$className = (isset($this->config['collection']) && $this->config['collection'])
				? $this->config['collection']
				: $this->collectionClass;
		}

		if (class_exists($className))
			$this->collectionClass = $className;
		else
			throw new Exception(":class doesn't exists", array(':class' => $className));

		return $this;
	}

	/**
	 * Get collection class name
	 *
	 * @since   1.0.0
	 *
	 * @return  string
	 */
	public function getCollectionClass()
	{
		return $this->collectionClass;
	}

	/**
	 * Get a [\Gleez\Mango\Collection] instance with grid FS enabled
	 *
	 * Wraps \MongoCollection
	 *
	 * Example:<br>
	 * <code>
	 * $collection = $db->getGridFS('myfs');
	 * </code>
	 *
	 * @param   string  $prefix  The prefix for the files and chunks collections [Optional]
	 *
	 * @return  \Gleez\Mango\Collection
	 */
	public function getGridFS($prefix = 'fs')
	{
		$this->isConnected() || $this->connect();

		return new $this->collectionClass($prefix, $this->name, true);
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
	 * Get MongoDB version
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 *
	 * @throws  \Gleez\Mango\Exception
	 */
	public function getMongoVersion()
	{
		return $this->safeExecute('version()');
	}

	/**
	 * Get an instance of \MongoClient
	 *
	 * Example:<br>
	 * <code>
	 * $dbs = $db->getConnection()->->listDBs();
	 * </code>
	 *
	 * @since   0.3.4
	 *
	 * @return  \MongoClient
	 */
	public function getConnection()
	{
		return $this->connection;
	}

	/**
	 * Get a [\Gleez\Mango\Collection] instance
	 *
	 * Wraps \MongoCollection
	 *
	 * The name of the called class can be changed directly in the configuration.
	 *
	 * [!!] This is called automatically by [\Gleez\Mango\Client::__get].
	 *
	 * Example:<br>
	 * <code>
	 * $collection = $db->selectCollection('users');
	 * </code>
	 *
	 * @since   0.3.1
	 *
	 * @param   string  $name  Collection name
	 *
	 * @return  \Gleez\Mango\Collection
	 */
	public function selectCollection($name)
	{
		$this->isConnected() || $this->connect();

		return new $this->collectionClass($name, $this->name);
	}

	/**
	 * Runs JavaScript code on the database server
	 *
	 * This method allows you to run arbitrary JavaScript on the database.
	 * Same usage as \MongoDB::execute except it throws an exception on error.
	 *
	 * Example:<br>
	 * <code>
	 * $db->safeExecute('db.foo.count();');
	 * </code>
	 *
	 * @since   0.3.3
	 *
	 * @link    http://php.net/manual/en/mongodb.execute.php \MongoDB::execute
	 *
	 * @param   string|\MongoCode $code   \MongoCode or string to execute
	 * @param   array             $args   Arguments to be passed to code [Optional]
	 * @param   array             $scope  The scope to use for the code if $code is a string [Optional]
	 *
	 * @return  mixed
	 *
	 * @throws  \Gleez\Mango\Exception
	 */
	public function safeExecute($code, array $args = array(), array $scope = array())
	{
		if (!is_string($code) && !$code instanceof MongoCode)
			throw new Exception('The code must be a string or an instance of MongoCode');

		if (!$code instanceof MongoCode)
			$code = new MongoCode($code, $scope);

		$result = $this->execute($code, $args);

		if (empty($result['ok']))
			throw new Exception($result['errmsg'], $result['code']);

		return $result['retval'];
	}

	/**
	 * Execute a database command
	 *
	 * Almost everything that is not a CRUD operation can be done with a this method.
	 * Same usage as \MongoDB::command except it throws an exception on error.
	 *
	 * Example:<br>
	 * <code>
	 * $ages = $db->safeCommand(array("distinct" => "people", "key" => "age"));
	 * </code>
	 *
	 * @since   0.3.3
	 *
	 * @link    http://www.php.net/manual/en/mongodb.command.php \MongoDB::command
	 *
	 * @param   array  $command  The query to send
	 * @param   array  $options  An associative array of command options [Optional]
	 *
	 * @return  array
	 *
	 * @throws  \Gleez\Mango\Exception
	 */
	public function safeCommand(array $command, array $options = array())
	{
		$result = $this->command($command, $options);

		if (!empty($result['ok']))
			return $result;

		$message = isset($result['errmsg']) ? $result['errmsg'] : 'Error: ' . JSON::encode($result);
		$code    = isset($result['errno']) ? $result['errno'] : 0;

		throw new Exception($message, $code);
	}

	/**
	 * Update a document and return it
	 *
	 * Simple findAndModify helper
	 *
	 * @since   0.3.1
	 *
	 * @link    http://www.php.net/manual/en/mongocollection.findandmodify.php \MongoCollection::findAndModify
	 *
	 * @param   string  $collection  Collection name
	 * @param   array   $command     The query to send
	 *
	 * @return  mixed
	 *
	 * @throws  \Gleez\Mango\Exception
	 */
	public function findAndModify($collection, array $command)
	{
		$command = array_merge(array('findAndModify' => (string) $collection), $command);
		$result  = $this->safeCommand($command);

		return $result['value'];
	}

	/**
	 * Checks if the given collection exists in the currently referenced database
	 *
	 * @since   0.3.3
	 * @since   1.0.0 Renamed 'exists' → isCollectionExists
	 *
	 * @param   string  $collection  Collection name
	 *
	 * @return  boolean
	 *
	 * @throws  \Gleez\Mango\Exception
	 */
	public function isCollectionExists($collection)
	{
		return null !== $this->safeExecute("db.{$collection}.exists()");
	}
}
