<?php
/**
 * MySQLi database connection driver
 *
 * ### System Requirements
 *
 * - PHP 5.3 or higher
 * - MySQL 5.0 or higher
 *
 * @package    Gleez\Database\Drivers
 * @version    1.1.0
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Database_MySQLi extends Database {

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
	 * @var mysqli
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
	 * @throws  Database_Exception
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
			self::$_set_names = ! function_exists('mysqli_set_charset');
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
			// Compare versions
			if (version_compare(PHP_VERSION, '5.3', '>=') AND (bool)$persistent)
			{
				// Create a persistent connection - only available with PHP 5.3+
				// See http://www.php.net/manual/en/mysqli.persistconns.php
				$this->_connection = new mysqli('p:'.$hostname, $username, $password, $database, (int)$port, $socket);
			}
			else
			{
				// Create a connection
				$this->_connection = new mysqli($hostname, $username, $password, $database, (int)$port, $socket);
			}
		}
		catch (Exception $e)
		{
			// No connection exists
			$this->_connection = NULL;

			throw new Database_Exception(':error', array(':error' => $e->getMessage()), $e->getCode());
		}

		// \xFF is a better delimiter, but the PHP driver uses underscore
		$this->_connection_id = sha1($hostname.'_'.$username.'_'.$password);

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

			$this->_connection->query('SET '.implode(', ', $variables));
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

			if ($this->_connection instanceof mysqli)
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
			$status = ! ($this->_connection instanceof mysqli);
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
	 * @throws  Database_Exception
	 */
	protected function _select_db($database)
	{
		if ( ! $this->_connection->select_db($database))
		{
			// Unable to select database
			throw new Database_Exception(':error', array(':error' => $this->_connection->error), $this->_connection->errno);
		}

		self::$_current_databases[$this->_connection_id] = $database;
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
	 * @throws  Database_Exception
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
			throw new Database_Exception(':error', array(':error' => $this->_connection->error), $this->_connection->errno);
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
	 * @throws  Database_Exception
	 *
	 * @return  object   Database_Result for SELECT queries
	 * @return  array    List (insert id, row count) for INSERT queries
	 * @return  integer  Number of affected rows for all other queries
	 */
	public function query($type, $sql, $as_object = FALSE, array $params = NULL)
	{
		// Make sure the database is connected
		$this->_connection OR $this->connect();

		if ((bool)$this->_config['profiling'] AND Kohana::$profiling)
		{
			// Benchmark this query for the current instance
			$benchmark = Profiler::start(__CLASS__." ({$this->_instance})", $sql);
		}

		if ( ! empty($this->_config['connection']['persistent']) AND $this->_config['connection']['database'] !== self::$_current_databases[$this->_connection_id])
		{
			// Select database on persistent connections
			$this->_select_db($this->_config['connection']['database']);
		}

		// Execute the query
		if (($result = $this->_connection->query($sql)) === FALSE)
		{
			if (isset($benchmark))
			{
				// This benchmark is worthless
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(':error [ :query ]', array(
				':error' => $this->_connection->error,
				':query' => $sql
			), $this->_connection->errno);
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
			return new Database_MySQLi_Result($result, $sql, $as_object, $params);
		}
		elseif ($type === Database::INSERT)
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
	 * Returns a normalized array describing the SQL data type
	 *
	 * Example:
	 * ~~~
	 * $db->datatype('char');
	 * ~~~
	 *
	 * @param   string  $type  SQL data type
	 *
	 * @return  array
	 */
	public function datatype($type)
	{
		static $types = array(
			'blob'                      => array('type' => 'string', 'binary' => TRUE, 'character_maximum_length' => '65535'),
			'bool'                      => array('type' => 'bool'),
			'bigint unsigned'           => array('type' => 'int', 'min' => '0', 'max' => '18446744073709551615'),
			'datetime'                  => array('type' => 'string'),
			'decimal unsigned'          => array('type' => 'float', 'exact' => TRUE, 'min' => '0'),
			'double'                    => array('type' => 'float'),
			'double precision unsigned' => array('type' => 'float', 'min' => '0'),
			'double unsigned'           => array('type' => 'float', 'min' => '0'),
			'enum'                      => array('type' => 'string'),
			'fixed'                     => array('type' => 'float', 'exact' => TRUE),
			'fixed unsigned'            => array('type' => 'float', 'exact' => TRUE, 'min' => '0'),
			'float unsigned'            => array('type' => 'float', 'min' => '0'),
			'int unsigned'              => array('type' => 'int', 'min' => '0', 'max' => '4294967295'),
			'integer unsigned'          => array('type' => 'int', 'min' => '0', 'max' => '4294967295'),
			'longblob'                  => array('type' => 'string', 'binary' => TRUE, 'character_maximum_length' => '4294967295'),
			'longtext'                  => array('type' => 'string', 'character_maximum_length' => '4294967295'),
			'mediumblob'                => array('type' => 'string', 'binary' => TRUE, 'character_maximum_length' => '16777215'),
			'mediumint'                 => array('type' => 'int', 'min' => '-8388608', 'max' => '8388607'),
			'mediumint unsigned'        => array('type' => 'int', 'min' => '0', 'max' => '16777215'),
			'mediumtext'                => array('type' => 'string', 'character_maximum_length' => '16777215'),
			'national varchar'          => array('type' => 'string'),
			'numeric unsigned'          => array('type' => 'float', 'exact' => TRUE, 'min' => '0'),
			'nvarchar'                  => array('type' => 'string'),
			'point'                     => array('type' => 'string', 'binary' => TRUE),
			'real unsigned'             => array('type' => 'float', 'min' => '0'),
			'set'                       => array('type' => 'string'),
			'smallint unsigned'         => array('type' => 'int', 'min' => '0', 'max' => '65535'),
			'text'                      => array('type' => 'string', 'character_maximum_length' => '65535'),
			'tinyblob'                  => array('type' => 'string', 'binary' => TRUE, 'character_maximum_length' => '255'),
			'tinyint'                   => array('type' => 'int', 'min' => '-128', 'max' => '127'),
			'tinyint unsigned'          => array('type' => 'int', 'min' => '0', 'max' => '255'),
			'tinytext'                  => array('type' => 'string', 'character_maximum_length' => '255'),
			'year'                      => array('type' => 'string'),
		);

		$type = str_replace(' zerofill', '', $type);

		if (isset($types[$type]))
		{
			return $types[$type];
		}

		return parent::datatype($type);
	}

	/**
	 * Start a SQL transaction
	 *
	 * @link    http://dev.mysql.com/doc/refman/5.0/en/set-transaction.html
	 *
	 * Example:
	 * ~~~
	 * $db->begin();
	 * ~~~
	 *
	 * @param   string  $mode  Isolation level [Optional]
	 *
	 * @return  boolean
	 *
	 * @throws  Database_Exception
	 */
	public function begin($mode = NULL)
	{
		// Make sure the database is connected
		$this->_connection OR $this->connect();

		if ($mode AND ! $this->_connection->query("SET TRANSACTION ISOLATION LEVEL $mode"))
		{
			throw new Database_Exception(':error', array(
					':error' => $this->_connection->error
			), $this->_connection->errno);
		}

		return (bool) $this->_connection->query('START TRANSACTION');
	}

	/**
	 * Commit a SQL transaction
	 *
	 * Commit the database changes:
	 * ~~~
	 * $db->commit();
	 * ~~~
	 *
	 * @return  boolean
	 */
	public function commit()
	{
		// Make sure the database is connected
		$this->_connection OR $this->connect();

		return (bool) $this->_connection->query('COMMIT');
	}

	/**
	 * Rollback a SQL transaction
	 *
	 * Undo the changes:
	 * ~~~
	 * $db->rollback();
	 * ~~~
	 *
	 * @return  boolean
	 */
	public function rollback()
	{
		// Make sure the database is connected
		$this->_connection OR $this->connect();

		return (bool) $this->_connection->query('ROLLBACK');
	}

	/**
	 * Getting a list of tables
	 *
	 * @param   string  $like
	 * @return  array
	 */
	public function list_tables($like = NULL)
	{
		if (is_string($like))
		{
			// Search for table names
			$result = $this->query(Database::SELECT, 'SHOW TABLES LIKE '.$this->quote($like));
		}
		else
		{
			// Find all table names
			$result = $this->query(Database::SELECT, 'SHOW TABLES');
		}

		$tables = array();

		foreach ($result as $row)
		{
			$tables[] = reset($row);
		}

		return $tables;
	}

	/**
	 * Getting a list of columns
	 *
	 * @param   string   $table       Table
	 * @param   string   $like        LIKE condition [Optional]
	 * @param   boolean  $add_prefix  Add prefix? [Optional]
	 * @return  array
	 */
	public function list_columns($table, $like = NULL, $add_prefix = TRUE)
	{
		// Quote the table name
		$table = ($add_prefix === TRUE) ? $this->quote_table($table) : $table;

		if (is_string($like))
		{
			// Search for column names
			$result = $this->query(Database::SELECT, 'SHOW FULL COLUMNS FROM '.$table.' LIKE '.$this->quote($like), FALSE);
		}
		else
		{
			// Find all column names
			$result = $this->query(Database::SELECT, 'SHOW FULL COLUMNS FROM '.$table, FALSE);
		}

		$count = 0;
		$columns = array();
		foreach ($result as $row)
		{
			list($type, $length) = $this->_parse_type($row['Type']);

			$column = $this->datatype($type);

			$column['column_name']      = $row['Field'];
			$column['column_default']   = $row['Default'];
			$column['data_type']        = $type;
			$column['is_nullable']      = ($row['Null'] == 'YES');
			$column['ordinal_position'] = ++$count;

			switch ($column['type'])
			{
				case 'float':
					if (isset($length))
					{
						list($column['numeric_precision'], $column['numeric_scale']) = explode(',', $length);
					}
					break;
				case 'int':
					if (isset($length))
					{
						// MySQL attribute
						$column['display'] = $length;
					}
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

			// MySQL attributes
			$column['comment']      = $row['Comment'];
			$column['extra']        = $row['Extra'];
			$column['key']          = $row['Key'];
			$column['privileges']   = $row['Privileges'];

			$columns[$row['Field']] = $column;
		}

		return $columns;
	}

	/**
	 * Escapes special characters in a string for use in an SQL statement
	 *
	 * @param   string  $value  Value to escape
	 *
	 * @return  string
	 *
	 * @throws  Database_Exception
	 */
	public function escape($value)
	{
		// Make sure the database is connected
		$this->_connection OR $this->connect();

		if (($value = $this->_connection->real_escape_string( (string) $value)) === FALSE)
		{
			throw new Database_Exception(':error', array(
				':error' => $this->_connection->error
			), $this->_connection->errno);
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
}
