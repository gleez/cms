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
 * @version    2.0.0
 * @author     Gleez Team
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
namespace Gleez\Database;

class ConnectionException extends \Exception {};
class DatabaseException extends \Exception {};

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
			// Compare versions
			if (version_compare(PHP_VERSION, '5.3', '>=') AND (bool)$persistent)
			{
				// Create a persistent connection - only available with PHP 5.3+
				// See http://www.php.net/manual/en/mysqli.persistconns.php
				$this->_connection = new \MySQLi('p:'.$hostname, $username, $password, $database, (int)$port, $socket);
			}
			else
			{
				// Create a connection
				$this->_connection = new \MySQLi($hostname, $username, $password, $database, (int)$port, $socket);
			}
		}
		catch (Exception $e)
		{
			// No connection exists
			$this->_connection = NULL;

			throw new ConnectionException(':error', array(':error' => $e->getMessage()), $e->getCode());
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
	 * @throws  Database_Exception
	 */
	protected function _select_db($database)
	{
		if ( ! $this->_connection->select_db($database))
		{
			// Unable to select database
			throw new ConnectionException(':error', array(':error' => $this->_connection->error), $this->_connection->errno);
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
		if ($this->_query == TRUE)
		{
			$this->type       = $type;
			$this->last_query = $sql;
			
			return $this;
		}
		
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
		
		// Set the query to default
		$this->_query = TRUE;
		
		if ($type === 'select')
		{
			// Return an iterator of results
			$result = new Result($resource, $sql, $as_object, $params);
			return $result;
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
	 */
	public function begin($mode = NULL)
	{
		// Make sure the database is connected
		$this->_connection or $this->connect();

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
	 * @throws  \Foolz\SphinxQL\DatabaseException  If an error was encountered during server-side escape
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
	 * Set the value of a parameter in the query.
	 *
	 * @param   string   $param  parameter key to replace
	 * @param   mixed    $value  value to use
	 * @return  $this
	 */
	public function param($param, $value)
	{
		// Add or overload a new parameter
		$this->_parameters[$param] = $value;

		return $this;
	}

	/**
	 * Bind a variable to a parameter in the query.
	 *
	 * @param   string  $param  parameter key to replace
	 * @param   mixed   $var    variable to use
	 * @return  $this
	 */
	public function bind($param, & $var)
	{
		// Bind a value to a variable
		$this->_parameters[$param] =& $var;

		return $this;
	}

	/**
	 * Add multiple parameters to the query.
	 *
	 * @param   array  $params  list of parameters
	 * @return  $this
	 */
	public function parameters(array $params)
	{
		// Merge the new parameters in
		$this->_parameters = $params + $this->_parameters;

		return $this;
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
		
		if ($value instanceof \Gleez\Database\Expression) {
			
			$value = $value->value();
		} elseif ($value instanceof \Gleez\Database\Driver_MySQLi) {
			
			if ($value->last_query != NULL)
			{
				$value = '('.$value->last_query.') ';
			}
			else
			{
				$value = '('.$value->compile()->getCompiled().') ';
			}
		} elseif ($value === '*') {
			
			return $value;
		} elseif (strpos($value, '.') !== FALSE) {
			
			$pieces = explode('.', $value);
			$count  = count($pieces) ;
			
			foreach ($pieces as $key => $piece) {
				if ($count > 1 AND $key == 0 AND ($prefix = $this->table_prefix())) {
					$piece = $prefix.$piece;
				}
				$pieces[$key] = ($piece != '*') ? '`'.$piece.'`' : $piece;
			}

			$value = implode('.', $pieces);
		} else {
			
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
	public function quoteIdentifierArr(Array $array = array())
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
		elseif ($table instanceof \Gleez\Database\Driver_MySQLi) 
		{
			$table = '('.$table->compile()->getCompiled().') ';
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
	 * Based on FuelPHP's quoting function.
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
		elseif ($value instanceof \Gleez\Database\Driver_MySQLi) 
		{
			if ($value->last_query != NULL)
			{

				$value = '('.$value->last_query.') ';
			}
			else
			{
				$value = '('.$value->compile()->getCompiled().') ';
			}
			return $value;
		}
		elseif (strpos($value, '.') !== FALSE) {
			    $pieces = explode('.', $value);
			    $count  = count($pieces) ;
			    
			    foreach ($pieces as $key => $piece) {
				    if ($count > 1 AND $key == 0 AND ($prefix = $this->table_prefix())) {
					    $piece = $prefix.$piece;
				    }
				    $pieces[$key] = '`'.$piece.'`';
			    }
			    $value = implode('.', $pieces);
			    return $value;
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
	public function quoteArr(Array $array = array())
	{
		$result = array();
	
		foreach ($array as $key => $item) {
		    $result[$key] = $this->quote($item);
		}
	
		return $result;
	}
}