<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Drizzle database connection.
 *
 * @package    	Gleez/Database
 * @category   	Drivers
 * @author	Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2012 Gleez Technologies
 * @license	http://gleezcms.org/license
 */
class Gleez_Database_Drizzle extends Database {

	// Database in use by each connection
	protected static $_current_databases = array();

	// Identifier for this connection within the PHP driver
	protected $_connection_id;

	// MySQL uses a backtick for identifiers
	protected $_identifier = '`';

	public function connect()
	{
		if ($this->_connection)
			return;

		// Extract the connection parameters, adding required variabels
		extract($this->_config['connection'] + array(
			'database'   => '',
			'hostname'   => '',
			'username'   => '',
			'password'   => '',
			'port'       => 4427,
			'persistent' => FALSE,
		));

		// Prevent this information from showing up in traces
		unset($this->_config['connection']['username'], $this->_config['connection']['password']);

		try
		{
			// create drizzle object
			$drizzle = new drizzle();
		
			// Create a connection and force it to be a new link
			$this->_connection = $drizzle->addTcp($hostname, $port, $username, $password, $database, 0);
 		}
		catch (Exception $e)
		{
			// No connection exists
			$this->_connection = NULL;

			throw new Database_Exception(':error',
				array(':error' => $e->getMessage()),
				$e->getCode());
		}

		// \xFF is a better delimiter, but the PHP driver uses underscore
		$this->_connection_id = sha1($hostname.'_'.$username.'_'.$password);

	}

	/**
	 * Select the database
	 *
	 * @param   string  Database
	 * @return  void
	 */
	protected function _select_db($database)
	{
		if ( ! drizzle_select_db($database, $this->_connection))
		{
			// Unable to select database
			throw new Database_Exception(':error',
				array(':error' => drizzle_error($this->_connection)),
				drizzle_errno($this->_connection));
		}

		Database_Drizzle::$_current_databases[$this->_connection_id] = $database;
	}

	public function set_charset($charset)
	{
	}
	
	public function disconnect()
	{
		try
		{
			// Database is assumed disconnected
			$status = TRUE;

			if (is_resource($this->_connection))
			{
				if ($status = $this->_connection->close())
				{
					// Clear the connection
					$this->_connection = NULL;

					// Clear the instance
					parent::disconnect();
				}
			}
		}
		catch (Exception $e)
		{
			// Database is probably not disconnected
			$status = ! is_resource($this->_connection);
		}

		return $status;
	}

	public function query($type, $sql, $as_object = FALSE, array $params = NULL)
	{
		// Make sure the database is connected
		$this->_connection or $this->connect();

		if ( ! empty($this->_config['profiling']))
		{
			// Benchmark this query for the current instance
			$benchmark = Profiler::start("Database ({$this->_instance})", $sql);
		}

		if ( ! empty($this->_config['connection']['persistent']) AND $this->_config['connection']['database'] !== Database_Drizzle::$_current_databases[$this->_connection_id])
		{
			// Select database on persistent connections
			//$this->_select_db($this->_config['connection']['database']);
		}

		// Execute the query
		if (($result = $this->_connection->query($sql)) === FALSE)
		{
			if (isset($benchmark))
			{
				// This benchmark is worthless
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(':error [ :query ]',
				array(':error' => $this->_connection->error(), ':query' => $sql),
				$this->_connection->errorCode());
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}

		// Set the last query
		$this->last_query = $sql;

		if ($type === Database::SELECT)
		{
			// Return an iterator of results
			return new Database_Drizzle_Result($result, $sql, $as_object, $params);
		}
		elseif ($type === Database::INSERT)
		{
			// Return a list of insert id and rows created
			return array(
				$result->insertId(),
				$result->affectedRows(),
			);
		}
		else
		{
			// Return the number of rows affected
			return $result->affectedRows();
		}
	}

	public function datatype($type)
	{
		static $types = array
		(
			'blob'                      => array('type' => 'string', 'binary' => TRUE, 'character_maximum_length' => '65535'),
			'bool'                      => array('type' => 'bool'),
			'bigint unsigned'           => array('type' => 'int', 'min' => '0', 'max' => '18446744073709551615'),
			'datetime'                  => array('type' => 'string'),
			'decimal unsigned'          => array('type' => 'float', 'exact' => TRUE, 'min' => '0'),
			'double'                    => array('type' => 'float'),
			'enum'                      => array('type' => 'string'),
			'fixed'                     => array('type' => 'float', 'exact' => TRUE),
			'fixed unsigned'            => array('type' => 'float', 'exact' => TRUE, 'min' => '0'),
			'float unsigned'            => array('type' => 'float', 'min' => '0'),
			'int unsigned'              => array('type' => 'int', 'min' => '0', 'max' => '4294967295'),
			'integer unsigned'          => array('type' => 'int', 'min' => '0', 'max' => '4294967295'),
			'national varchar'          => array('type' => 'string'),
			'numeric unsigned'          => array('type' => 'float', 'exact' => TRUE, 'min' => '0'),
			'nvarchar'                  => array('type' => 'string'),
			'point'                     => array('type' => 'string', 'binary' => TRUE),
			'real unsigned'             => array('type' => 'float', 'min' => '0'),
			'set'                       => array('type' => 'string'),
			'text'                      => array('type' => 'string', 'character_maximum_length' => '65535'),
			'year'                      => array('type' => 'string'),
		);

		$type = str_replace(' zerofill', '', $type);

		if (isset($types[$type]))
			return $types[$type];

		return parent::datatype($type);
	}

	/**
	 * Start a SQL transaction
	 *
	 * @link http://dev.mysql.com/doc/refman/5.0/en/set-transaction.html
	 *
	 * @param string Isolation level
	 * @return boolean
	 */
	public function begin($mode = NULL)
	{
		// Make sure the database is connected
		$this->_connection or $this->connect();

		return (bool) $this->_connection->query('START TRANSACTION');
	}

	/**
	 * Commit a SQL transaction
	 *
	 * @param string Isolation level
	 * @return boolean
	 */
	public function commit()
	{
		// Make sure the database is connected
		$this->_connection or $this->connect();

		return (bool) $this->_connection->query('COMMIT');
	}

	/**
	 * Rollback a SQL transaction
	 *
	 * @param string Isolation level
	 * @return boolean
	 */
	public function rollback()
	{
		// Make sure the database is connected
		$this->_connection or $this->connect();

		return (bool) $this->_connection->query('ROLLBACK');
	}

	public function list_tables($like = NULL)
	{
		throw new Kohana_Exception('Database method :method is not supported by :class',
			array(':method' => __FUNCTION__, ':class' => __CLASS__));
	}

	public function list_columns($table, $like = NULL, $add_prefix = TRUE)
	{
		throw new Kohana_Exception('Database method :method is not supported by :class',
			array(':method' => __FUNCTION__, ':class' => __CLASS__));
	}

	public function escape($value)
	{
		// Make sure the database is connected
		$this->_connection or $this->connect();

		if (($value = $this->_connection->escape( (string) $value )) === FALSE)
		{
			throw new Database_Exception(':error',
				array(':error' => $this->_connection->error()),
				$this->_connection->errorCode());
		}

		// SQL standard is to use single-quotes for all values
		return "'$value'";
	}

} // End Database_Drizzle
