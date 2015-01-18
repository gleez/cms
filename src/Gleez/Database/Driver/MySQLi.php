<?php
/**
 * Gleez CMS (http://gleezcms.org)
 *
 * @link https://github.com/gleez/cms Canonical source repository
 * @copyright Copyright (c) 2011-2015 Gleez Technologies
 * @license http://gleezcms.org/license Gleez CMS License
 */

namespace Gleez\Database\Driver;

use Gleez\Database\ConnectionException;
use Gleez\Database\DatabaseException;
use Gleez\Database\Database;
use Gleez\Database\Result;
use Exception;

/**
 * MySQLi database connection driver
 *
 * System Requirements:
 *
 * - PHP 5.3.9 or higher
 * - MySQL 5.0 or higher
 *
 * @package Gleez\Database\Driver
 * @version 2.1.3
 * @author  Gleez Team
 */
class MySQLi extends Database implements DriverInterface
{
	/**
	 * Database in use by each connection
	 * @var array
	 */
	protected static $_current_databases = array();

	/**
	 * Use SET NAMES to set the character set
	 * @var boolean
	 */
	protected static $_set_names = false;

	/**
	 * Identifier for this connection within the PHP driver
	 * @var string
	 */
	protected $_connection_id = null;

	/**
	 * MySQL uses a backticks for identifiers
	 * For enabling double quotation marks use SET sql_mode='ANSI_QUOTES';
	 * @var string
	 */
	protected $_identifier = '`';

	/**
	 * Raw server connection
	 * @var \mysqli
	 */
	protected $_connection = null;

	/**
	 * Check environment
	 *
	 * @return bool
	 * @throws \Gleez\Database\DatabaseException
	 */
	public function checkEnvironment()
	{
		if (!extension_loaded('mysqli')) {
			throw new DatabaseException(
				sprintf('The "mysqli" extension is required for %s driver but the extension is not loaded.', __CLASS__)
			);
		}

		return true;
	}

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
		// Don't allow to execute twice
		if ($this->_connection) {
			return $this;
		}

		// @todo Gleez use at least PHP 5.3.x & MySQL 5.x
		if (!self::$_set_names) {
			// Determine if we can use mysqli_set_charset(), which is only
			// available on PHP 5.2.3+ when compiled against MySQL 5.0+
			self::$_set_names = ! \function_exists('mysqli_set_charset');
		}

		// localize
		$config = $this->_config['connection'];

		$findConfigValue = function ($key, $default = null) use ($config) {
			if (isset($config[$key])) {
			return $config[$key];
			}

			return $default;
		};

		$hostname  = $findConfigValue('hostname', '');
		$database  = $findConfigValue('database', '');
		$username  = $findConfigValue('username', '');
		$password  = $findConfigValue('password', '');
		$port      = $findConfigValue('port', 3306);
		$socket    = $findConfigValue('socket');
		$persist   = $findConfigValue('persistent', false);
		$charset   = $findConfigValue('charset');
		$variables = $findConfigValue('variables', []);
		$nolock    = $findConfigValue('nolock');

		// Prevent this information from showing up in traces
		if ($password) {
			unset($this->_config['password']);
		}

		if ($username) {
			unset($this->_config['user']);
		}

		try
		{
			// See http://www.php.net/manual/en/mysqli.persistconns.php
			$this->_connection = new \MySQLi(($persist ? 'p:' : '') . $hostname, $username, $password, $database, (int) $port, $socket);
		}
		catch (\Exception $e)
		{
			// No connection exists
			$this->_connection = NULL;

			throw new ConnectionException($e->getMessage(), $e->getCode());
		}

		// \xFF is a better delimiter, but the PHP driver uses underscore
		$this->_connection_id = \sha1($hostname.'_'.$username.'_'.$password);

		if ($charset) {
			// Set the character set
			$this->set_charset($charset);
		}

		if ($variables && is_array($variables)) {
			// Set session variables
			$vars = array();

			foreach ($variables as $var => $val) {
				$vars[] = 'SESSION '.$var.' = '.$this->quote($val);
			}

			$this->_connection->query('SET '.\implode(', ', $vars));
		}

		if ($nolock) {
			$this->_connection->query('SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;');
		}

		return $this;
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
	 * @param   string   $type       Database::SELECT, Database::INSERT, etc
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
	 * @return  \Gleez\Database\Result  Database result for SELECT queries
	 * @return  array    List (insert id, row count) for INSERT queries
	 * @return  integer  Number of affected rows for all other queries
	 */
	public function query($type, $sql, $as_object = FALSE, array $params = NULL)
	{
		// Make sure the database is connected
		$this->_connection OR $this->connect();

		if (!empty($this->_config['connection']['persistent']) &&
			isset(static::$_current_databases[$this->_connection_id]) &&
			$this->_config['connection']['database'] !== static::$_current_databases[$this->_connection_id]
		)
		{
			// Select database on persistent connections
			$this->_select_db($this->_config['connection']['database']);
		}

		// Execute the query
		if (($resource = $this->_connection->query($sql)) === FALSE)
		{
			throw new DatabaseException(sprintf('[%s] [%s]', $this->_connection->errno, $this->_connection->error), $this->_connection->errno);
		}

		// Set the last query
		$this->last_query = $sql;

		if ($type === Database::SELECT)
		{
			// Return an iterator of results
			$this->last_result = new Result($resource, $sql, $as_object, $params);
		}
		elseif ($type === Database::INSERT)
		{
			// Return a list of insert id and rows created
			$this->last_result = array(
				$this->_connection->insert_id,
				$this->_connection->affected_rows,
			);
		}
		else
		{
			// Return the number of rows affected
			$this->last_result = $this->_connection->affected_rows;
		}

		return $this->last_result;
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

		return $this->transactionCommit();
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

		return $this->transactionRollback();
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

	/**
	 * List all of the tables in the database.
	 * Optionally, a LIKE string can be used to search for specific tables.
	 *
	 * Example:<br>
	 * <code>
	 * // Get all tables in the current database
	 * $tables = $db->list_tables();
	 *
	 * // Get all user-related tables
	 * $tables = $db->list_tables('user%');
	 * </code>
	 *
	 * @param   string $like  Table to search for [Optional]
	 * @return  array
	 */
	public function list_tables($like = NULL)
	{
		// Make sure the database is connected
		$this->_connection OR $this->connect();

		is_string($like)
			// Search for table names
			? $result = $this->_connection->query('select', 'SHOW TABLES LIKE '.$this->quote($like), FALSE)
			// Find all table names
			: $result = $this->_connection->query('select', 'SHOW TABLES', FALSE);

		$tables = array();

		foreach ($result as $row)
			$tables[] = reset($row);

		return $tables;
	}

	/**
	 * Lists all of the columns in a table.
	 * Optionally, a LIKE string can be used to search for specific fields.
	 *
	 * Example:<br>
	 * <code>
	 * // Get all columns from the "users" table
	 * $columns = $db->list_columns('users');
	 *
	 * // Get all name-related columns
	 * $columns = $db->list_columns('users', '%name%');
	 *
	 * // Get the columns from a table that doesn't use the table prefix
	 * $columns = $db->list_columns('users', NULL, FALSE);
	 * </code>
	 *
	 * @since   2.1.0
	 *
	 * @param   string  $table       Table to get columns from
	 * @param   string  $like        Column to search for [Optional]
	 * @param   boolean $add_prefix  Whether to add the table prefix automatically or not [Optional]
	 *
	 * @return  array
	 */
	public function list_columns($table, $like = null, $add_prefix = true)
	{
		// Quote the table name
		$table = (bool)($add_prefix) ? $this->quoteTable($table) : $table;

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

	/**
	 * Get data type.
	 *
	 * Returns a normalized array describing the SQL data type.
	 * Example:<br>
	 * <code>
	 * $db->getDataType('char');
	 * </code>
	 *
	 * @since  2.1.0
	 *
	 * @param  string $type SQL data type
	 *
	 * @return array
	 */
	public function getDataType($type)
	{
		static $types = array
		(
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
			'geometry'                  => array('type' => 'string', 'binary' => TRUE),
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
			return $types[$type];

		return parent::getDataType($type);
	}
}
