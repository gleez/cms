<?php
/**
 * Database connection wrapper/helper.
 *
 * You may get a database instance using `Gleez\Database\Database::instance('name')` where
 * name is the [config](database/config) group.
 *
 * This class provides connection instance management via Database Drivers, as
 * well as quoting, escaping and other related functions.
 *
 * @package    Gleez\Database\Core
 * @version    2.2.1
 * @author     Gleez Team
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
namespace Gleez\Database;

// Grab the files for HHVM
require_once __DIR__ . '/Driver/MySQLi.php';
require_once __DIR__ . '/Expression.php';
require_once __DIR__ . '/Result.php';
require_once __DIR__ . '/Query.php';

class ConnectionException extends \Exception {};
class DatabaseException extends \Exception {};

abstract class Database {
	// Query types
	const SELECT =  'select';
	const INSERT =  'insert';
	const UPDATE =  'update';
	const DELETE =  'delete';

	/**
	 * Default instance name
	 * @var string
	 */
	public static $default = 'default';

	/**
	 * Database instances
	 * @var array
	 */
	public static $instances = array();

	/**
	 * @var string Cache of the name of the readonly connection
	 */
	protected static $_readonly = array();

	/**
	 * Ready for use queries
	 *
	 * @var  array
	 */
	protected static $show_queries = array(
		'meta'              => 'SHOW META',
		'warnings'          => 'SHOW WARNINGS',
		'status'            => 'SHOW STATUS',
		'tables'            => 'SHOW TABLES',
		'variables'         => 'SHOW VARIABLES',
		'variablesSession'  => 'SHOW SESSION VARIABLES',
		'variablesGlobal'   => 'SHOW GLOBAL VARIABLES',
	);

	/**
	 * Get a singleton Database instance
	 *
	 * If configuration is not specified, it will be loaded from the database
	 * configuration file using the same group as the name.
	 *
	 * ### Examples
	 *
	 * Load the default database:<br>
	 * <code>
	 *   $db = Database::instance();
	 * </code>
	 *
	 * Create a custom configured instance:<br>
	 * <code>
	 *   $db = Database::instance('custom', $config);
	 * </code>
	 *
	 * @param   string 		$name      Instance name [Optional]
	 * @param   array 		$config    Configuration parameters [Optional]
	 * @param   bool 		$writable  When replication is enabled, whether to return the master connection
	 *
	 * @return  \Gleez\Database\Database
	 *
	 * @throws  \Gleez_Exception
	 */
	public static function instance($name = NULL, array $config = NULL, $writable = TRUE)
	{
		if ($name === NULL)
		{
			// Use the default instance name
			$name = static::$default;
		}

		if ( ! $writable and ($readonly = \Config::get("database.{$name}.readonly", false)))
		{
			! isset(static::$_readonly[$name]) and static::$_readonly[$name] = \Arr::get($readonly, array_rand($readonly));
			$name = static::$_readonly[$name];
		}

		if ( ! isset(static::$instances[$name]))
		{
			if ($config === NULL)
			{
				// Load the configuration for this database
				$config = \Config::get("database.{$name}");
			}

			if ( ! isset($config['type']))
			{
				throw new \Gleez_Exception('Database type not defined in :name configuration',
				array(':name' => $name));
			}

			// Set the driver class name
			$driver = '\Gleez\Database\Driver_'.ucfirst($config['type']);

			// Create the database connection instance
			$driver = new $driver($name, $config);

			// Store the database instance
			static::$instances[$name] = $driver;
		}

		return static::$instances[$name];
	}

	/**
	 * Instance name
	 * @var string
	 */
	protected $_instance;

	/**
	 * Raw server connection
	 * @var mixed
	 */
	protected $_connection;

	/**
	 * Configuration
	 * @var array
	 */
	protected $_config;

	/**
	 * The last result object.
	 *
	 * @var  array
	 */
	protected $last_result = null;

	/**
	 * The last compiled query executed.
	 *
	 * @var  string
	 */
	protected $last_query = null;

	/**
	 * Stores the database configuration locally and name the instance.
	 *
	 * [!!] This method cannot be accessed directly, you must use [Database::instance].
	 *
	 * @param  string  $name    Instance name
	 * @param  array   $config  Configuration parameters
	 */
	public function __construct($name, array $config)
	{
		// Set the instance name
		$this->_instance = $name;

		// Store the config locally
		$this->_config = $config;

		if (empty($this->_config['table_prefix']))
		{
			$this->_config['table_prefix'] = '';
		}
	}

	/**
	 * Disconnect from the database when the object is destroyed.
	 *
	 *     // Destroy the database instance
	 *     unset(Database::instances[(string) $db], $db);
	 *
	 * [!!] Calling `unset($db)` is not enough to destroy the database, as it
	 * will still be stored in `Database::$instances`.
	 *
	 * @return  void
	 */
	public function __destruct()
	{
		$this->disconnect();
	}

	/**
	 * Returns the database instance name.
	 *
	 *     echo (string) $db;
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return $this->_instance;
	}

	/**
	 * Disconnect from the database. This is called automatically by [Database::__destruct].
	 * Clears the database instance from [Database::$instances].
	 *
	 *     $db->disconnect();
	 *
	 * @return  boolean
	 */
	public function disconnect()
	{
		unset(Database::$instances[$this->_instance]);

		return TRUE;
	}

	/**
	 * Returns the currently attached connection
	 *
	 * @returns \Gleez\Database\Connection
	 */
	public function getConnection()
	{
		return static::$instances[$this->_instance];
	}

	/**
	 * Get last query
	 * @return string
	 */
	public function getLastQuery()
	{
		return $this->last_query;
	}

	/**
	 * Avoids having the expressions escaped
	 *
	 * Example
	 *    $sq->where('time', '>', Database::expr('CURRENT_TIMESTAMP'));
	 *    // WHERE `time` > CURRENT_TIMESTAMP
	 *
	 * @param  string  $string  The string to keep unaltered
	 *
	 * @return  \Gleez\Database\Expression  The new Expression
	 */
	public static function expr($string = '')
	{
		return new Expression($string);
	}

	/**
	 * Returns the result of the last query
	 *
	 * @return  array  The result of the last query
	 */
	public function getResult()
	{
		return $this->last_result;
	}

	/**
	 * Begins transaction
	 */
	public function transactionBegin()
	{
		$this->getConnection()->query('BEGIN');
	}

	/**
	 * Commits transaction
	 */
	public function transactionCommit()
	{
		$this->getConnection()->query('COMMIT');
	}

	/**
	 * Rollbacks transaction
	 */
	public function transactionRollback()
	{
		$this->getConnection()->query('ROLLBACK');
	}

	/**
	 * Count the number of records in a table.
	 *
	 *     // Get the total number of records in the "users" table
	 *     $count = $db->count_records('users');
	 *
	 * @param   mixed    $table  table name string or array(query, alias)
	 * @return  integer
	 */
	public function count_records($table)
	{
		// Quote the table name
		$table = $this->quoteTable($table);

		$info = $this->query(self::SELECT, 'SELECT COUNT(*) AS total_row_count FROM '.$table, FALSE);

		return isset($info[0]['total_row_count']) ? $info[0]['total_row_count'] : FALSE;
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
	abstract public function list_tables($like = NULL);

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
	 * @param   string  $table       Table to get columns from
	 * @param   string  $like        Column to search for [Optional]
	 * @param   boolean $add_prefix  Whether to add the table prefix automatically or not [Optional]
	 *
	 * @return  array
	 */
	abstract public function list_columns($table, $like = NULL, $add_prefix = TRUE);

	/**
	 * Return the table prefix defined in the current configuration.
	 *
	 *     $prefix = $db->table_prefix();
	 *
	 * @return  string
	 */
	public function table_prefix()
	{
		return $this->_config['table_prefix'];
	}

	/**
	 * Wraps the input with identifiers when necessary.
	 *
	 * @param  \Gleez\Database\Expression|string  $value  The string to be quoted, or an Expression to leave it untouched
	 *
	 * @return  \Gleez\Database\Expression|string  The untouched Expression or the quoted string
	 */
	public function quoteIdentifier($value)
	{
		// Identifiers are escaped by repeating them
		$escaped_identifier = $this->_identifier.$this->_identifier;

		if (is_array($value))
		{
			list($value, $alias) = $value;
			$alias = str_replace($this->_identifier, $escaped_identifier, $alias);
		}

		if ($value instanceof \Gleez\Database\Expression)
		{
			$value = $value->value();
		}
		elseif ($value instanceof \Gleez\Database\Query)
		{
			$value = '('.$value->compile($this).') ';
		}
		elseif ($value === '*')
		{
			return $value;
		}
		elseif (strpos($value, '.') !== FALSE) {

			$pieces = explode('.', $value);
			$count  = count($pieces) ;

			foreach ($pieces as $key => $piece) {
				if ($count > 1 AND $key == 0 AND ($prefix = $this->table_prefix())) {
					$piece = $prefix.$piece;
				}
				$pieces[$key] = ($piece != '*') ? '`'.$piece.'`' : $piece;
			}

			$value = implode('.', $pieces);
		}
		else {
			$value = $this->_identifier.$value.$this->_identifier;
		}

		if (isset($alias))
		{
			// Attach table prefix to alias
			$value .= ' AS '.$this->_identifier.$alias.$this->_identifier;
		}

		return $value;
	}

	/**
	 * Calls $this->quoteIdentifier() on every element of the array passed.
	 *
	 * @param  array  $array  An array of strings to be quoted
	 *
	 * @return  array  The array of quoted strings
	 */
	public function quoteIdentifierArr(Array $array)
	{
		$result = array();

		foreach ($array as $key => $item) {
			$result[$key] = $this->quoteIdentifier($item);
		}

		return $result;
	}

	/**
	 * Quote a database table name and adds the table prefix if needed.
	 *
	 *     $table = $db->quote_table($table);
	 *
	 * Objects passed to this function will be converted to strings.
	 * [Database_Expression] objects will be compiled.
	 * [Database_Query] objects will be compiled and converted to a sub-query.
	 * All other objects will be converted using the `__toString` method.
	 *
	 * @param   mixed   $table  table name or array(table, alias)
	 * @return  string
	 * @uses    Database::quote_identifier
	 * @uses    Database::table_prefix
	 */
	public function quoteTable($table)
	{
		// Identifiers are escaped by repeating them
		$escaped_identifier = $this->_identifier.$this->_identifier;

		if (is_array($table))
		{
			list($table, $alias) = $table;
			$alias = str_replace($this->_identifier, $escaped_identifier, $alias);
		}

		if ($table instanceof \Gleez\Database\Expression)
		{
			$table = $table->value();
		}
		elseif ($table instanceof \Gleez\Database\Query)
		{
			$table = '('.$table->compile($this).') ';
		}
		else
		{
			// Convert to a string
			$table = (string) $table;

			$table = str_replace($this->_identifier, $escaped_identifier, $table);

			if (strpos($table, '.') !== FALSE)
			{
				$parts = explode('.', $table);

				if ($prefix = $this->table_prefix())
				{
					// Get the offset of the table name, last part
					$offset = count($parts) - 1;

					// Add the table prefix to the table name
					$parts[$offset] = $prefix.$parts[$offset];
				}

				foreach ($parts as & $part)
				{
					// Quote each of the parts
					$part = $this->_identifier.$part.$this->_identifier;
				}

				$table = implode('.', $parts);
			}
			else
			{
				// Add the table prefix
				$table = $this->_identifier.$this->table_prefix().$table.$this->_identifier;
			}
		}

		if (isset($alias))
		{
			// Attach table prefix to alias
			$table .= ' AS '.$this->_identifier.$this->table_prefix().$alias.$this->_identifier;
		}

		return $table;
	}

	/**
	 * Adds quotes around values when necessary.
	 *
	 * @param  \Gleez\Database\Expression|string  $value  The input string, eventually wrapped in an expression to leave it untouched
	 *
	 * @return  \Gleez\Database\Expression|string  The untouched Expression or the quoted string
	 */
	public function quote($value)
	{
		if ($value === NULL)
		{
			return 'NULL';
		}
		elseif ($value === TRUE)
		{
			return "'1'";
		}
		elseif ($value === FALSE)
		{
			return "'0'";
		}
		elseif ($value instanceof \Gleez\Database\Expression)
		{
			// Use the raw expression
			return $value->value();
		}
		elseif ($value instanceof \Gleez\Database\Query)
		{
			return '('.$value->compile($this).') ';
		}
		elseif (is_int($value))
		{
			return (int) $value;
		}
		elseif (is_float($value))
		{
			// Convert to non-locale aware float to prevent possible commas
			return sprintf('%F', $value);
		}
		elseif (is_array($value))
		{
			// Supports MVA attributes
			return '('.implode(',', $this->quoteArr($value)).')';
		}

		return $this->escape($value);
	}

	/**
	* Calls $this->quote() on every element of the array passed.
	*
	* @param  array  $array  The array of strings to quote
	*
	* @return  array  The array of quotes strings
	*/
	public function quoteArr(Array $array)
	{
		$result = array();

		foreach ($array as $key => $item) {
			$result[$key] = $this->quote($item);
		}

		return $result;
	}

	/**
	 * Extracts the text between parentheses, if any.
	 *
	 * Example:<br>
	 * <code>
	 * list($type, $length) = $db->parseType('CHAR(6)');
	 * </code>
	 *
	 * @since  2.2.1
	 *
	 * @param  string $type
	 *
	 * @return  array list containing the type and length, if any
	 */
	protected function parseType($type)
	{
		if (false === ($open = strpos($type, '(')))
			// No length specified
			return array($type, null);

		// Closing parenthesis
		$close = strrpos($type, ')', $open);

		// Length without parentheses
		$length = substr($type, $open + 1, $close - 1 - $open);

		// Type without the length
		$type = substr($type, 0, $open).substr($type, $close + 1);

		return array($type, $length);
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
	 * @since  2.2.1
	 *
	 * @param  string $type SQL data type
	 *
	 * @return array
	 */
	public function getDataType($type)
	{
		static $types = array
		(
			// SQL-92
			'bit' => array('type' => 'string', 'exact' => true),
			'bit varying' => array('type' => 'string'),
			'char' => array('type' => 'string', 'exact' => true),
			'char varying' => array('type' => 'string'),
			'character' => array('type' => 'string', 'exact' => true),
			'character varying' => array('type' => 'string'),
			'date' => array('type' => 'string'),
			'dec' => array('type' => 'float', 'exact' => true),
			'decimal' => array('type' => 'float', 'exact' => true),
			'double precision' => array('type' => 'float'),
			'float' => array('type' => 'float'),
			'int' => array('type' => 'int', 'min' => '-2147483648', 'max' => '2147483647'),
			'integer' => array('type' => 'int', 'min' => '-2147483648', 'max' => '2147483647'),
			'interval' => array('type' => 'string'),
			'national char' => array('type' => 'string', 'exact' => true),
			'national char varying' => array('type' => 'string'),
			'national character' => array('type' => 'string', 'exact' => true),
			'national character varying' => array('type' => 'string'),
			'nchar' => array('type' => 'string', 'exact' => true),
			'nchar varying' => array('type' => 'string'),
			'numeric' => array('type' => 'float', 'exact' => true),
			'real' => array('type' => 'float'),
			'smallint' => array('type' => 'int', 'min' => '-32768', 'max' => '32767'),
			'time' => array('type' => 'string'),
			'time with time zone' => array('type' => 'string'),
			'timestamp' => array('type' => 'string'),
			'timestamp with time zone' => array('type' => 'string'),
			'varchar' => array('type' => 'string'),
			// SQL:1999
			'binary large object' => array('type' => 'string', 'binary' => true),
			'blob' => array('type' => 'string', 'binary' => true),
			'boolean' => array('type' => 'bool'),
			'char large object' => array('type' => 'string'),
			'character large object' => array('type' => 'string'),
			'clob' => array('type' => 'string'),
			'national character large object' => array('type' => 'string'),
			'nchar large object' => array('type' => 'string'),
			'nclob' => array('type' => 'string'),
			'time without time zone' => array('type' => 'string'),
			'timestamp without time zone' => array('type' => 'string'),
			// SQL:2003
			'bigint' => array('type' => 'int', 'min' => '-9223372036854775808', 'max' => '9223372036854775807'),
			// SQL:2008
			'binary' => array('type' => 'string', 'binary' => true, 'exact' => true),
			'binary varying' => array('type' => 'string', 'binary' => true),
			'varbinary' => array('type' => 'string', 'binary' => true)
		);

		if (isset($types[$type]))
			return $types[$type];

		return array();
	}
}
