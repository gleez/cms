<?php
/**
 * MySQLi database connection driver
 *
 * ### System Requirements
 *
 * - PHP 5.3.9 or higher
 * - MySQL 5.0 or higher
 *
 * @package    Gleez\Database\Drivers
 * @version    2.1.0
 * @author     Gleez Team
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */

namespace Gleez\Database;

class Driver_MySQLi extends Database {

	/**
	 * Database in use by each connection
	 * @var array
	 */
	protected static $_current_databases = array();

	/**
	 * Use SET NAMES to set the character set
	 * @var boolean
	 */
	protected static $_set_names;

	/**
	 * Identifier for this connection within the PHP driver
	 * @var string
	 */
	protected $_connection_id;

	/**
	 * MySQL uses a backticks for identifiers
	 * @var string
	 */
	protected $_identifier = '`';

	/**
	 * Raw server connection
	 * @var \mysqli
	 */
	protected $_connection;

	/**
	 * Connect to the database
	 *
	 * [!!] This is called automatically when the first query is executed.
	 *
	 * Example:
	 * ~~~
	 * $db->connect();
	 * ~~~
	 *
	 * @throws  \Gleez\Database\ConnectionException
	 */
	public function connect()
	{
		if ($this->_connection)
		{
			return;
		}

		if (is_null(self::$_set_names))
		{
			// Determine if we can use mysqli_set_charset(), which is only
			// available on PHP 5.2.3+ when compiled against MySQL 5.0+
			self::$_set_names = ! \function_exists('mysqli_set_charset');
		}

		/**
		 * Extract the connection parameters, adding required variables
		 *
		 * @var  $database   string
		 * @var  $hostname   string
		 * @var  $username   string
		 * @var  $password   string
		 * @var  $socket     string
		 * @var  $port       string
		 * @var  $persistent boolean
		 */
		extract($this->_config['connection'] + array(
			'database'   => '',
			'hostname'   => '',
			'username'   => '',
			'password'   => '',
			'socket'     => '',
			'port'       => 3306,
			'persistent' => FALSE,
		));

		// Prevent this information from showing up in traces
		unset($this->_config['connection']['username'], $this->_config['connection']['password']);

		try
		{
			// Create a persistent connection - only available with PHP 5.3+
			// See http://www.php.net/manual/en/mysqli.persistconns.php
			$this->_connection = new \MySQLi('p:'.$hostname, $username, $password, $database, (int)$port, $socket);
		}
		catch (\Exception $e)
		{
			// No connection exists
			$this->_connection = NULL;

			throw new ConnectionException($e->getMessage(), $e->getCode());
		}

		// \xFF is a better delimiter, but the PHP driver uses underscore
		$this->_connection_id = \sha1($hostname.'_'.$username.'_'.$password);

		if ( ! empty($this->_config['charset']))
		{
			// Set the character set
			$this->set_charset($this->_config['charset']);
		}

		if ( ! empty($this->_config['connection']['variables']) AND is_array($this->_config['connection']['variables']))
		{
			// Set session variables
			$variables = array();

			foreach ($this->_config['connection']['variables'] as $var => $val)
			{
				$variables[] = 'SESSION '.$var.' = '.$this->quote($val);
			}

			$this->_connection->query('SET '.\implode(', ', $variables));
		}
	}

	/**
	 * Disconnect from the database
	 *
	 * [!!] This is called automatically by [Database::__destruct].
	 *
	 * Example:
	 * ~~~
	 * $db->disconnect();
	 * ~~~
	 *
	 * @return  boolean
	 */
	public function disconnect()
	{
		try
		{
			// Database is assumed disconnected
			$status = TRUE;

			if ($this->_connection instanceof \MySQLi)
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
		catch (\Exception $e)
		{
			// Database is probably not disconnected
			$status = ! ($this->_connection instanceof \MySQLi);
		}

		return $status;
	}

	/**
	 * Select the database
	 *
	 * [!!] This is called automatically by [Database_MySQLi::connect].
	 *
	 * @param   string  $database  Database name
	 *
	 * @throws  \Gleez\Database\ConnectionException
	 */
	protected function _select_db($database)
	{
		if ( ! $this->_connection->select_db($database))
		{
			// Unable to select database
			throw new ConnectionException($this->_connection->error, $this->_connection->errno);
		}

		static::$_current_databases[$this->_connection_id] = $database;
	}

	/**
	 * Set the connection character set
	 *
	 * [!!] This is called automatically by [Database_MySQLi::connect].
	 *
	 * Example:
	 * ~~~
	 * $db->set_charset('utf8');
	 * ~~~
	 *
	 * @param   string  $charset  Character set name
	 *
	 * @throws  \Gleez\Database\DatabaseException
	 */
	public function set_charset($charset)
	{
		// Make sure the database is connected
		$this->_connection OR $this->connect();

		if (self::$_set_names === TRUE)
		{
			// PHP is compiled against MySQL 4.x
			$status = (bool) $this->_connection->query('SET NAMES '.$this->quote($charset));
		}
		else
		{
			// PHP is compiled against MySQL 5.x
			$status = $this->_connection->set_charset($charset);
		}

		if ($status === FALSE)
		{
			throw new DatabaseException($this->_connection->error, $this->_connection->errno);
		}
	}

	/**
	 * Perform an SQL query of the given type
	 *
	 * Example:
	 * ~~~
	 * // Make a SELECT query and use objects for results
	 * $db->query(Database::SELECT, 'SELECT * FROM groups', TRUE);
	 *
	 * // Make a SELECT query and use "Model_User" for the results:
	 * $db->query(Database::SELECT, 'SELECT * FROM users LIMIT 1', 'Model_User');
	 * ~~~
	 *
	 * @param   integer  $type       Database::SELECT, Database::INSERT, etc
	 * @param   string   $sql        SQL query
	 * @param   boolean  $as_object  Result object class string, TRUE for stdClass, FALSE for assoc array [Optional]
	 * @param   array    $params     Object construct parameters for result class [Optional]
	 *
	 * @uses    Profiler::start
	 * @uses    Profiler::delete
	 * @uses    Profiler::stop
	 *
	 * @throws  \Gleez\Database\DatabaseException
	 *
	 * @return  object   Database_Result for SELECT queries
	 * @return  array    List (insert id, row count) for INSERT queries
	 * @return  integer  Number of affected rows for all other queries
	 */
	public function query($type, $sql, $as_object = FALSE, array $params = NULL)
	{
		// Make sure the database is connected
		$this->_connection OR $this->connect();

		if ( ! empty($this->_config['connection']['persistent']) AND $this->_config['connection']['database'] !== self::$_current_databases[$this->_connection_id])
		{
			// Select database on persistent connections
			$this->_select_db($this->_config['connection']['database']);
		}

		// Execute the query
		if (($resource = $this->_connection->query($sql)) === FALSE)
		{
			throw new DatabaseException('['.$this->_connection->errno.'] '.
			$this->_connection->error.' [ '.$sql.']', $this->_connection->errno);
		}

		// Set the last query
		$this->last_query = $sql;

		if ($type === 'select')
		{
			// Return an iterator of results
			return new Result($resource, $sql, $as_object, $params);
		}
		elseif ($type === 'insert')
		{
			// Return a list of insert id and rows created
			return array(
				$this->_connection->insert_id,
				$this->_connection->affected_rows,
			);
		}
		else
		{
			// Return the number of rows affected
			return $this->_connection->affected_rows;
		}
	}

	/**
	 * Start a SQL transaction
	 *
	 * @link http://dev.mysql.com/doc/refman/5.0/en/set-transaction.html
	 *
	 * @param string $mode  Isolation level
	 * @return boolean
	 *
	 * @throws \Gleez\Database\DatabaseException
	 */
	public function begin($mode = NULL)
	{
		// Make sure the database is connected
		$this->_connection or $this->connect();

		if ($mode AND ! $this->_connection->query("SET TRANSACTION ISOLATION LEVEL $mode"))
		{
			throw new DatabaseException($this->_connection->error, $this->_connection->errno);
		}

		return (bool) $this->_connection->query('START TRANSACTION');
	}

	/**
	 * Commit a SQL transaction
	 *
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
	 * @return boolean
	 */
	public function rollback()
	{
		// Make sure the database is connected
		$this->_connection or $this->connect();

		return (bool) $this->_connection->query('ROLLBACK');
	}

	/**
	 * Escapes the input with \MySQLi::real_escape_string.
	 *
	 * @param  string  $value  The string to escape
	 *
	 * @return  string  The escaped string
	 * @throws  \Gleez\Database\DatabaseException  If an error was encountered during server-side escape
	 */
	public function escape($value)
	{
	    //$this->ping();
	    $this->_connection OR $this->connect();

	    if (($value = $this->_connection->real_escape_string((string) $value)) === false) {
	        throw new DatabaseException($this->_connection->error, $this->_connection->errno);
	    }

	    // SQL standard is to use single-quotes for all values
		return "'$value'";
	}

	/**
	 * Get MySQL version
	 *
	 * Example:
	 * ~~~
	 * $db->version();
	 * ~~~
	 *
	 * @param   boolean  $full  Show full version [Optional]
	 *
	 * @return  string
	 */
	public function version($full = FALSE)
	{
		// Make sure the database is connected
		$this->_connection OR $this->connect();

		$result = $this->_connection->query('SHOW VARIABLES WHERE variable_name = '. $this->quote('version'));
		$row    = $result->fetch_object();

		return $full ? $row->Value : substr($row->Value, 0, strpos($row->Value, "-"));
	}

	public function list_tables($like = NULL)
	{
		// Make sure the database is connected
		$this->_connection OR $this->connect();

		if (is_string($like))
		{
			// Search for table names
			$result = $this->_connection->query('select', 'SHOW TABLES LIKE '.$this->quote($like), FALSE);
		}
		else
		{
			// Find all table names
			$result = $this->_connection->query('select', 'SHOW TABLES', FALSE);
		}

		$tables = array();
		foreach ($result as $row)
		{
			$tables[] = reset($row);
		}

		return $tables;
	}

	/**
	 * Lists all of the columns in a table.
	 *
	 * Optionally, a LIKE string can be used to search for specific fields.
	 *
	 * @since  2.2.1
	 *
	 * @param  string $table      Table to get columns from
	 * @param  string $like       Column to search for [Optional]
	 * @param  bool   $add_prefix Whether to add the table prefix automatically or not [Optional]
	 *
	 * @return array
	 */
	public function list_columns($table, $like = null, $add_prefix = true)
	{
		// Quote the table name
		$table = (bool)($add_prefix) ? $this->quoteTable($table) : $table;

		$result='';

		if (is_string($like))
			// Search for column names
			$result = $this->query(Database::SELECT, 'SHOW FULL COLUMNS FROM '.$table.' LIKE '.$this->quote($like), false);
		else
			// Find all column names
			$result = $this->query(Database::SELECT, 'SHOW FULL COLUMNS FROM '.$table, false);


		$count = 0;
		$columns = array();

		foreach ($result as $row)
		{
			/**
			 * @var $type string
			 * @var $length string|null
			 */
			list($type, $length) = $this->parseType($row['Type']);

			$column = $this->getDataType($type);
			$column['column_name']      = $row['Field'];
			$column['column_default']   = $row['Default'];
			$column['data_type']        = $type;
			$column['is_nullable']      = ($row['Null'] == 'YES');
			$column['ordinal_position'] = ++$count;
			$column['comment']          = $row['Comment'];
			$column['extra']            = $row['Extra'];
			$column['key']              = $row['Key'];
			$column['privileges']       = $row['Privileges'];

			$r = array();
			if (!isset($column['type']))
				$r[] = $column;

			if (isset($column['type']))
			{
				switch ($column['type'])
				{
					case 'float':
						if (isset($length))
							list($column['numeric_precision'], $column['numeric_scale']) = explode(',', $length);
						break;
					case 'int':
						if (isset($length))
							// MySQL attribute
							$column['display'] = $length;
						break;
					case 'string':
						switch ($column['data_type'])
						{
							case 'binary':
							case 'varbinary':
								$column['character_maximum_length'] = $length;
								break;
							case 'char':
							case 'varchar':
								$column['character_maximum_length'] = $length;
								break;
							case 'text':
							case 'tinytext':
							case 'mediumtext':
							case 'longtext':
								$column['collation_name'] = $row['Collation'];
								break;
							case 'enum':
							case 'set':
								$column['collation_name'] = $row['Collation'];
								$column['options'] = explode('\',\'', substr($length, 1, -1));
								break;
						}
						break;
				}
			}

			$columns[$row['Field']] = $column;
		}

		return $columns;
	}
}
