<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Mongodb driver for accessing mongodb objects
 *
 *       This file is direct copy of the mangodb class from mangodb module
 * 
 * @package	Gleez
 * @category	MongoDB
 * @author	Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2012 Gleez Technologies
 * @license	http://gleezcms.org/license
 */
class Gleez_MongoDB {

	// Database instances
	public static $instances = array();

	public static function instance($name = 'default', array $config = NULL)
	{
		if ( ! isset(self::$instances[$name]))
		{
			if ($config === NULL)
			{
				// Load the configuration for this database
				$config = Kohana::$config->load('mongoDB')->$name;
			}

			new MangoDB($name,$config);
		}

		return self::$instances[$name];
	}

	// Instance name
	protected $_name;

	// Connected
	protected $_connected = FALSE;

	// Raw server connection
	protected $_connection;

	// Raw database connection;
	protected $_db;

	// Store config locally
	protected $_config;

	protected function __construct($name, array $config)
	{
		$this->_name = $name;

		$this->_config = $config;

		// Store the database instance
		MangoDB::$instances[$name] = $this;
	}

	final public function __destruct()
	{
		$this->disconnect();
	}

	final public function __toString()
	{
		return $this->_name;
	}

	public function connect()
	{
		if ( $this->_connection)
		{
			return;
		}

		// Extract the connection parameters, adding required variables
		extract($this->_config['connection'] + array(
			'hostnames'  => 'localhost:27017'
		));

		// Clear the connection parameters for security
		unset($this->_config['connection']);

		if ( isset($username) && isset($password))
		{
			// Add Username & Password to server string
			$hostnames = $username . ':' . $password . '@' . $hostnames . '/' . $database;
		}

		if ( strpos($hostnames, 'mongodb://') !== 0)
		{
			// Add required 'mongodb://' prefix
			$hostnames = 'mongodb://' . $hostnames;
		}

		if ( ! isset($options))
		{
			$options = array();
		}

		// We connect below in a separate try catch
		$options['connect'] = FALSE;

		// Create connection object
		$this->_connection = new Mongo($hostnames, $options);

		try
		{
			$this->_connection->connect();
		}
		catch ( MongoConnectionException $e)
		{
			// Unable to connect to the database server
			throw new Kohana_Exception('Unable to connect to MongoDB server at :hostnames',
				array(':hostnames' => $e->getMessage()));
		}

		if ( ! isset($database))
		{
			throw new Kohana_Exception('No database specified in MangoDB Config');
		}

		$this->_db = $this->_connection->selectDB($database);

		return $this->_connected = TRUE;
	}

	public function disconnect()
	{
		if ( $this->_connection)
		{
			$this->_connection->close();
		}

		$this->_db = $this->_connection = NULL;
	}

	/* Database Management */

	public function last_error()
	{
		return $this->_connected
			? $this->_db->lastError() 
			: NULL;
	}

	public function prev_error()
	{
		return $this->_connected
			? $this->_db->prevError()
			: NULL;
	}
	
	public function reset_error()
	{
		return $this->_connected
			? $this->_db->resetError()
			: NULL;
	}

	public function command( array $data)
	{
		return $this->_call('command', array(), $data);
	}

	public function execute( $code, array $args = array() )
	{
		return $this->_call('execute', array(
			'code' => $code,
			'args' => $args
		));
	}

	/* Collection management */

	public function create_collection ( string $name, $capped= FALSE, $size= 0, $max= 0 )
	{
		return $this->_call('create_collection', array(
			'name'    => $name,
			'capped'  => $capped,
			'size'    => $size,
			'max'     => $max
		));
	}

	public function drop_collection( $name )
	{
		return $this->_call('drop_collection', array(
			'name' => $name
		));
	}

	public function ensure_index ( $collection_name, $keys, $options = array())
	{
		return $this->_call('ensure_index', array(
			'collection_name' => $collection_name,
			'keys'            => $keys,
			'options'         => $options
		));
	}

	/* Data Management */

	public function batch_insert ( $collection_name, array $a )
	{
		return $this->_call('batch_insert', array(
			'collection_name' => $collection_name
		), $a);
	}

	public function count( $collection_name, array $query = array() )
	{
		return $this->_call('count', array(
			'collection_name' => $collection_name,
			'query'           => $query
		));
	}

	public function find_one($collection_name, array $query = array(), array $fields = array())
	{
		return $this->_call('find_one', array(
			'collection_name' => $collection_name,
			'query'           => $query,
			'fields'          => $fields
		));
	}

	public function find($collection_name, array $query = array(), array $fields = array())
	{
		return $this->_call('find', array(
			'collection_name' => $collection_name,
			'query'           => $query,
			'fields'          => $fields
		));
	}

	public function group( $collection_name, $keys , array $initial , $reduce, array $condition= array() )
	{
		return $this->_call('group', array(
			'collection_name' => $collection_name,
			'keys'            => $keys,
			'initial'         => $initial,
			'reduce'          => $reduce,
			'condition'       => $condition
		));
	}

	public function update($collection_name, array $criteria, array $newObj, $options = array())
	{
		return $this->_call('update', array(
			'collection_name' => $collection_name,
			'criteria'        => $criteria,
			'options'         => $options
		), $newObj);
	}

	public function insert($collection_name, array $a, $options = array())
	{
		return $this->_call('insert', array(
			'collection_name' => $collection_name,
			'options'         => $options
		), $a);
	}

	public function remove($collection_name, array $criteria, $options = array())
	{
		return $this->_call('remove', array(
			'collection_name' => $collection_name,
			'criteria'        => $criteria,
			'options'         => $options
		));
	}

	public function save($collection_name, array $a, $options = array())
	{
		return $this->_call('save', array(
			'collection_name' => $collection_name,
			'options'         => $options
		), $a);
	}

	/* File management */

	public function gridFS( $arg1 = NULL, $arg2 = NULL)
	{
		$this->_connected OR $this->connect();

		if ( ! isset($arg1))
		{
			$arg1 = isset($this->_config['gridFS']['arg1'])
				? $this->_config['gridFS']['arg1']
				: 'fs';
		}

		if ( ! isset($arg2) && isset($this->_config['gridFS']['arg2']))
		{
			$arg2 = $this->_config['gridFS']['arg2'];
		}

		return $this->_db->getGridFS($arg1,$arg2);
	}

	public function get_file(array $criteria = array())
	{
		return $this->_call('get_file', array(
			'criteria' => $criteria
		));
	}

	public function get_files(array $query = array(), array $fields = array())
	{
		return $this->_call('get_files', array(
			'query'  => $query,
			'fields' => $fields
		));
	}

	public function set_file_bytes($bytes, array $extra = array(), array $options = array())
	{
		return $this->_call('set_file_bytes', array(
			'bytes'   => $bytes,
			'extra'   => $extra,
			'options' => $options
		));
	}

	public function set_file($filename, array $extra = array(), array $options = array())
	{
		return $this->_call('set_file', array(
			'filename' => $filename,
			'extra'    => $extra,
			'options'  => $options
		));
	}

	public function remove_file( array $criteria = array(), array $options = array())
	{
		return $this->_call('remove_file', array(
			'criteria' => $criteria,
			'options'  => $options
		));
	}

	/* 
	 * All commands for which benchmarking could be useful
	 * are executed by this method
	 *
	 * This allows for easy benchmarking
	 */
	protected function _call($command, array $arguments = array(), array $values = NULL)
	{
		$this->_connected OR $this->connect();

		extract($arguments);

		if ( ! empty($this->_config['profiling']))
		{
			$_bm_name = isset($collection_name)
			 ? $collection_name . '.' . $command
			 : $command;

			$_bm = Profiler::start("MongoDB {$this->_name}",$_bm_name);
		}

		if ( isset($collection_name))
		{
			$c = $this->_db->selectCollection($collection_name);
		}

		switch ( $command)
		{
			case 'ensure_index':
				$r = $c->ensureIndex($keys, $options);
			break;
			case 'create_collection':
				$r = $this->_db->createCollection($name,$capped,$size,$max);
			break;
			case 'drop_collection':
				$r = $this->_db->dropCollection($name);
			break;
			case 'command':
				$r = $this->_db->command($values);
			break;
			case 'execute':
				$r = $this->_db->execute($code,$args);
			break;
			case 'batch_insert':
				$r = $c->batchInsert($values);
			break;
			case 'count':
				$r = $c->count($query);
			break;
			case 'find_one':
				$r = $c->findOne($query,$fields);
			break;
			case 'find':
				$r = $c->find($query,$fields);
			break;
			case 'group':
				$r = $c->group($keys,$initial,$reduce,$condition);
			break;
			case 'update':
				$r = $c->update($criteria, $values, $options);
			break;
			case 'insert':
				$r = $c->insert($values, $options);
			break;
			case 'remove':
				$r = $c->remove($criteria,$options);
			break;
			case 'save':
				$r = $c->save($values, $options);
			break;
			case 'get_file':
				$r = $this->gridFS()->findOne($criteria);
			break;
			case 'get_files':
				$r = $this->gridFS()->find($query, $fields);
			break;
			case 'set_file_bytes':
				$r = $this->gridFS()->storeBytes($bytes, $extra, $options);
			break;
			case 'set_file':
				$r = $this->gridFS()->storeFile($filename, $extra, $options);
			break;
			case 'remove_file':
				$r = $this->gridFS()->remove($criteria, $options);
			break;
		}

		if ( isset($_bm))
		{
			Profiler::stop($_bm);
		}

		return $r;
	}
}