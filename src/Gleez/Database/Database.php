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
 * @version    2.0.0
 * @author     Gleez Team
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
namespace Gleez\Database;

abstract class Database{
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
	 * @param   string 		$name 		Instance name [Optional]
	 * @param   array 		$config 	Configuration parameters [Optional]
	 * @param   bool 		$writable 	When replication is enabled, whether to return the master connection
	 * @return  Database
	 *
	 * @throws  Gleez_Exception
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

	public static function factory($name = NULL, array $config = NULL)
	{
		if ($name === NULL)
		{
			// Use the default instance name
			$name = self::$default;
		}

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
		
		// Create the database connection instance and store
		return $driver;
		
	}
	
	// Character that is used to quote identifiers
	protected $_identifier = '"';

	// Instance name
	protected $_instance;

	// Raw server connection
	protected $_connection;

	// Configuration array
	protected $_config;
	
	protected $_query = TRUE;
	
	// Quoted query parameters
	protected $_parameters = array();
	
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
	 * The last chosen method (select, insert, replace, update, delete).
	 *
	 * @var  string
	 */
	protected $type = null;
	
	/**
	 * Array of select elements that will be comma separated.
	 *
	 * @var  array
	 */
	protected $select = array();
	
	/**
	 * Distinct
	 *
	 * @var  array
	 */
	protected $distinct = array();
	
	/**
	 * From in SQL is the list of indexes that will be used
	 *
	 * @var  array
	 */
	protected $from = array();
	
	/**
	 * Using
	 *
	 * @var  array
	 */
	protected $using = array();
	
	/**
	 * JOIN
	 *
	 * @var  array
	 */
	protected $join = array();
	
	/**
	 * JOIN ON
	 *
	 * @var  array
	 */
	protected $join_on = array();
	
	/**
	 * JOIN AND
	 *
	 * @var  array
	 */
	protected $join_and = array();
	
	/**
	 * The list of where and parenthesis, must be inserted in order
	 *
	 * @var  array
	 */
	protected $where = array();
	
	/**
	 * The list of matches for the MATCH function in SQL
	 *
	 * @var  array
	 */
	protected $match = array();
	
	/**
	 * GROUP BY array to be comma separated
	 *
	 * @var  array
	 */
	protected $group_by = array();
	
	/**
	 * ORDER BY array
	 *
	 * @var  array
	 */
	protected $within_group_order_by = array();
	
	/**
	 * The list of having and parenthesis, must be inserted in order
	 *
	 * @var  array
	 */
	protected $having = array();
	
	/**
	 * ORDER BY array
	 *
	 * @var  array
	 */
	protected $order_by = array();
	
	/**
	 * When not null it adds an offset
	 *
	 * @var  null|int
	 */
	protected $offset = null;
	
	/**
	 * When not null it adds a limit
	 *
	 * @var  null|int
	 */
	protected $limit = null;
	
	/**
	 * Value of INTO query for INSERT or REPLACE
	 *
	 * @var  null|string
	 */
	protected $into = null;
	
	/**
	 * Value of INTO query for INSERT or REPLACE
	 *
	 * @var  null|string
	 */
	protected $_as_object = FALSE;
	
	protected $_object_params = array();
	
	/**
	 * Array of columns for INSERT or REPLACE
	 *
	 * @var  array
	 */
	protected $columns = array();
	
	/**
	 * Array OF ARRAYS of values for INSERT or REPLACE
	 *
	 * @var  array
	 */
	protected $values = array();
	
	/**
	 * Array arrays containing column and value for SET in UPDATE
	 *
	 * @var  array
	 */
	protected $set = array();
	
	/**
	 * Array of OPTION specific to SQL
	 *
	 * @var  array
	 */
	protected $options = array();
	
	/**
	 * The reference to the object that queued itself and created this object
	 *
	 * @var null|\Gleez\Database\Database
	 */
	protected $queue_prev = null;
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
	 * Returns the currently attached connection
	 *
	 * @returns \Gleez\Database\Connection
	 */
	public function getConnection()
	{
	    return static::$instances[$this->_instance];
	}
	
	/**
	 * Used for the SHOW queries
	 *
	 * @param  string  $method      The method
	 * @param  array   $parameters  The parameters
	 *
	 * @return  array  The result of the SHOW query
	 * @throws  \BadMethodCallException  If there's no such a method
	 */
	public function __call($method, $parameters)
	{
	    if (isset(static::$show_queries[$method])) {
			$ordered = array();
			$result = $this->getConnection()->query(static::$show_queries[$method]);

			if ($method === 'tables') {
				return $result;
			}

			foreach ($result as $item) {
				$ordered[$item['Variable_name']] = $item['Value'];
			}

			return $ordered;
		}
	
		throw new \BadMethodCallException($method);
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
	 * Runs the query built
	 *
	 * @return  array  The result of the query
	 */
	public function execute($db = NULL, $as_object = NULL, $object_params = NULL)
	{
		if ( ! is_object($db))
		{
		    // Get the database instance
		    //$db = Gleez\Database\Core::instance($db);
		}
	
		if ($as_object === NULL)
		{
			$as_object = $this->_as_object;
		}

		if ($object_params === NULL)
		{
			$object_params = $this->_object_params;
		}
		
		// For query statements
		if ($this->last_query != NULL && $this->_query === TRUE)
		{
			$sql = $this->last_query;
			
			if ( ! empty($this->_parameters))
			{
				// Quote all of the values
				$values = array_map(array($this->getConnection(), 'quote'), $this->_parameters);
		
				// Replace the values in the SQL
				$sql = strtr($sql, $values);
		
				$this->last_query = $sql;
			}
		}
		else
		{
			$sql = $this->compile()->getCompiled();
		}
		
		$this->_query = FALSE;
		
		// pass the object so execute compiles it by itself
		return $this->last_result = $this->query($this->type, $sql, $as_object, $object_params);
	}
	
	/**
	 * Executes a batch of queued queries
	 *
	 * @return  array  The array of results from MySQLi
	 * @throws  SQLException  In case no query is in queue
	 */
	public function executeBatch()
	{
		if (count($this->getQueue()) == 0) {
			throw new Exception('There is no Queue present to execute.');
		}

		$queue = array();

		foreach ($this->getQueue() as $sq) {
			$queue[] = $sq->compile()->getCompiled();
		}

		return $this->last_result = $this->getConnection()->multiQuery($queue);
	}
	
	/**
	 * Enqueues the current object and returns a new one
	 *
	 * @return  \Gleez\Database\Database  A new SQL object with the current object referenced
	 */
	public function enqueue()
	{
		$sq = new static($this->getConnection());
		$sq->setQueuePrev($this);

		return $sq;
	}
	
	/**
	 * Returns the ordered array of enqueued objects
	 *
	 * @return  \Gleez\Database\Database[]  The ordered array of enqueued objects
	 */
	public function getQueue()
	{
		$queue = array();
		$curr = $this;

		do {
			if ($curr->type != null) {
			    $queue[] = $curr;
			}
		} while ($curr = $curr->getQueuePrev());

		return array_reverse($queue);
	}
	
	/**
	 * Gets the enqueued object
	 *
	 * @return SQL|null
	 */
	public function getQueuePrev()
	{
	    return $this->queue_prev;
	}
	
	/**
	 * Sets the reference to the enqueued object
	 *
	 * @param  $sq  The object to set as previous
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function setQueuePrev($sq)
	{
	    $this->queue_prev = $sq;
	
	    return $this;
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
	 * Returns the latest compiled query
	 *
	 * @return  string  The last compiled query
	 */
	public function getCompiled()
	{
	    return $this->last_query;
	}
	
	/**
	 * SET syntax
	 *
	 * @param  string   $name    The name of the variable
	 * @param  mixed    $value   The value o the variable
	 * @param  boolean  $global  True if the variable should be global, false otherwise
	 *
	 * @return  array  The result of the query
	 */
	public function setVariable($name, $value, $global = false)
	{
		$query = 'SET ';

		if ($global) {
			$query .= 'GLOBAL ';
		}

		$user_var = strpos($name, '@') === 0;

		// if it has an @ it's a user variable and we can't wrap it
		if ($user_var) {
			$query .= $name.' ';
		} else {
			$query .= $this->getConnection()->quoteIdentifier($name).' ';
		}

		// user variables must always be processed as arrays
		if ($user_var && ! is_array($value)) {
			$query .= '= ('.$this->getConnection()->quote($value).')';
		} elseif (is_array($value)) {
			$query .= '= ('.implode(', ', $this->getConnection()->quoteArr($value)).')';
		} else {
			$query .= '= '.$this->getConnection()->quote($value);
		}

		$this->getConnection()->query($query);
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
	 * CALL SNIPPETS syntax
	 *
	 * @param  string  $data
	 * @param  string  $index
	 * @param  array   $extra
	 *
	 * @return  array  The result of the query
	 */
	public function callSnippets($data, $index, $extra = array())
	{
	    array_unshift($extra, $index);
	    array_unshift($extra, $data);
	
	    return $this->getConnection()->query('CALL SNIPPETS('.implode(', ', $this->getConnection()->quoteArr($extra)).')');
	}
	
	/**
	 * CALL KEYWORDS syntax
	 *
	 * @param  string       $text
	 * @param  string       $index
	 * @param  null|string  $hits
	 *
	 * @return  array  The result of the query
	 */
	public function callKeywords($text, $index, $hits = null)
	{
	    $arr = array($text, $index);
	    if ($hits !== null) {
			$arr[] = $hits;
	    }
	
	    return $this->getConnection()->query('CALL KEYWORDS('.implode(', ', $this->getConnection()->quoteArr($arr)).')');
	}
	
	/**
	 * DESCRIBE syntax
	 *
	 * @param  string  $index  The name of the index
	 *
	 * @return  array  The result of the query
	 */
	public function describe($index)
	{
	    return $this->getConnection()->query('DESCRIBE '.$this->getConnection()->quoteIdentifier($index));
	}
	
	/**
	 * CREATE FUNCTION syntax
	 *
	 * @param  string  $udf_name
	 * @param  string  $returns   Whether INT|BIGINT|FLOAT
	 * @param  string  $so_name
	 *
	 * @return  array  The result of the query
	 */
	public function createFunction($udf_name, $returns, $so_name)
	{
	    return $this->getConnection()->query('CREATE FUNCTION '.$this->getConnection()->quoteIdentifier($udf_name).
		' RETURNS '.$returns.' SONAME '.$this->getConnection()->quote($so_name));
	}
	
	/**
	 * DROP FUNCTION syntax
	 *
	 * @param  string  $udf_name
	 *
	 * @return  array  The result of the query
	 */
	public function dropFunction($udf_name)
	{
	    return $this->getConnection()->query('DROP FUNCTION '.$this->getConnection()->quoteIdentifier($udf_name));
	}
	
	/**
	 * ATTACH INDEX * TO RTINDEX * syntax
	 *
	 * @param  string  $disk_index
	 * @param  string  $rt_index
	 *
	 * @return  array  The result of the query
	 */
	public function attachIndex($disk_index, $rt_index)
	{
	    return $this->getConnection()->query('ATTACH INDEX '.$this->getConnection()->quoteIdentifier($disk_index).
		' TO RTINDEX '. $this->getConnection()->quoteIdentifier($rt_index));
	}
	
	/**
	 * FLUSH RTINDEX syntax
	 *
	 * @param  string  $index
	 *
	 * @return  array  The result of the query
	 */
	public function flushRtIndex($index)
	{
	    return $this->getConnection()->query('FLUSH RTINDEX '.$this->getConnection()->quoteIdentifier($index));
	}
	
	/**
	 * Returns results as objects
	 *
	 * @param   string  $class  classname or TRUE for stdClass
	 * @param   array   $params
	 * @return  $this
	 */
	public function as_object($class = TRUE, array $params = NULL)
	{
		$this->_as_object = $class;
		
		if ($params)
		{
			// Add object parameters
			$this->_object_params = $params;
		}

		return $this;
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
		
		// To execute the query statement
		$this->_query = FALSE;
		
		$info = $this->query(self::SELECT, 'SELECT COUNT(*) AS total_row_count FROM '.$table, FALSE);
		
		return isset($info[0]['total_row_count']) ? $info[0]['total_row_count'] : FALSE;
	}
	
	/**
	 * Runs the compile function
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function compile()
	{
		switch ($this->type) {
		case 'select':
		    $this->compileSelect();
		    break;
		case 'insert':
		case 'replace':
		    $this->compileInsert();
		    break;
		case 'update':
		    $this->compileUpdate();
		    break;
		case 'delete':
		    $this->compileDelete();
		    break;
		}

		return $this;
	}
	
	/**
	 * Compile the SQL partial for a JOIN statement and return it.
	 *
	 * @param   mixed  $db  Database instance or name of instance
	 * @return  string
	 */
	protected function compileJoin()
	{
		if (! empty($this->join['type']))
		{
			$query = strtoupper($this->join['type']).' JOIN';
		}
		else
		{
			$query = 'JOIN';
		}

		// Quote the table name that is being joined
		$query .= ' '.$this->getConnection()->quoteTable($this->join['table']);

		if (! empty($this->using))
		{
			$quote_column = array($this->getConnection(), 'quoteIdentifier');
			
			// Quote and concat the columns
			//$query .= ' USING ('.implode(', ', array_map(array($this, 'quote_column'), $this->using)).')';
			$query .= ' USING ('.implode(', ', array_map($quote_column, $this->using)).')';
		}
		elseif ( ! empty($this->join_on))
		{
			$conditions = array();
			foreach ($this->join_on as $condition)
			{
				// Split the condition
				list($c1, $op, $c2) = $condition;

				if ($op)
				{
					// Make the operator uppercase and spaced
					$op = ' '.strtoupper($op);
				}

				// Quote each of the columns used for the condition
				$conditions[] = $this->getConnection()->quoteIdentifier($c1).$op.' '.$this->quote($c2);
			}

			// Concat the conditions "... AND ..."
			$query .= ' ON ('.implode(' AND ', $conditions).')';
		 
			if (! empty($this->join_and))
			{
				$and_conditions = array();
				
				foreach ($this->join_and as $icondition)
				{
					// Split the condition
					list($c1, $op, $v1) = $icondition;

					if ($op) {
						// Make the operator uppercase and spaced
						$op = ' '.strtoupper($op);
					}

					// Quote each of the columns used for the condition. v1 is quote value not column
					$and_conditions[] = $this->getConnection()->quoteIdentifier($c1).$op.' '.$this->quote($v1); 
				}
				
				if( !empty($and_conditions) ) {
					// Concat the conditions "... AND ..."
					$query .= ' AND '.implode(' AND ', $and_conditions).'';
				}
			}
		}

		return $query;
	}
	
	/**
	 * Compiles the MATCH part of the queries
	 * Used by: SELECT, DELETE, UPDATE
	 *
	 * @return  string  The compiled MATCH
	 */
	public function compileMatch()
	{
		$query = '';

		if ( ! empty($this->match)) {
			$query .= 'WHERE ';
		}

		if ( ! empty($this->match)) {
			$query .= "MATCH(";
			$pre = '';

			foreach ($this->match as $match) {
				$pre .= '@'.$match['column'].' ';

			    if ($match['half']) {
					$pre .= $this->halfEscapeMatch($match['value']);
			    } else {
					$pre .= $this->escapeMatch($match['value']);
			    }

			    $pre .= ' ';
			}

			$query .= $this->getConnection()->escape(trim($pre)).") ";
		}

		return $query;
	}
	
	/**
	 * Compiles the WHERE part of the queries
	 * It interacts with the MATCH() and of course isn't usable stand-alone
	 * Used by: SELECT, DELETE, UPDATE
	 *
	 * @return  string  The compiled WHERE
	 */
	public function compileWhere()
	{
		$query = '';

		if (empty($this->match) && ! empty($this->where)) {
			$query .= 'WHERE ';
		}

		if ( ! empty($this->where)) {
			$just_opened = false;

			foreach ($this->where as $key => $where) {
				if (in_array($where['ext_operator'], array('AND (', 'OR (', ')', '('))) {
					// if match is not empty we've got to use an operator
					if ($key == 0 || ! empty($this->match)) {
					    $query .= '(';

					    $just_opened = true;
					} else {
					    $query .= $where['ext_operator'].' ';
					    	if ($where['ext_operator'] != ')') {
							$just_opened = true;
					    }
					}
					continue;
				}

				if ($key > 0 && ! $just_opened || ! empty($this->match)) {
					$query .= $where['ext_operator'].' '; // AND/OR
				}

				$just_opened = false;

				if (strtoupper($where['operator']) === 'BETWEEN') {
					$query .= $this->getConnection()->quoteIdentifier($where['column']);
					$query .=' BETWEEN ';
					$query .= $this->getConnection()->quote($where['value'][0]).' AND '
				    .$this->getConnection()->quote($where['value'][1]).' ';
				} else {
					// id can't be quoted!
					if ($where['column'] === 'id') {
				    	$query .= 'id ';
					} else {
				    	$query .= $this->getConnection()->quoteIdentifier($where['column']).' ';
					}

					if (strtoupper($where['operator']) === 'IN') {
					    //$query .= 'IN ('.implode(', ', $this->getConnection()->quote($where['value'])).') ';
					    $query .= 'IN ('.implode(', ', $this->getConnection()->quoteArr($where['value'])).') ';
					} else {
					    $query .= $where['operator'].' '.$this->getConnection()->quote($where['value']).' ';
					}
				}
			}
		}

		return $query;
	}
	
	/**
	 * Compiles the WHERE part of the queries
	 * It interacts with the MATCH() and of course isn't usable stand-alone
	 * Used by: SELECT, DELETE, UPDATE
	 *
	 * @return  string  The compiled WHERE
	 */
	public function compileHaving()
	{
		$query = '';

		if (! empty($this->having)) {
			$query .= 'HAVING ';
		}

		if ( ! empty($this->having)) {
			$just_opened = false;

			foreach ($this->having as $key => $having) {
				if (in_array($having['ext_operator'], array('AND (', 'OR (', ')', '('))) {
					// if match is not empty we've got to use an operator
					if ($key == 0 ) {
					    $query .= '(';

					    $just_opened = true;
					} else {
					    $query .= $having['ext_operator'].' ';
					    	if ($having['ext_operator'] != ')') {
							$just_opened = true;
					    }
					}

						continue;
					}

					if ($key > 0 && ! $just_opened) {
						$query .= $having['ext_operator'].' '; // AND/OR
					}

					$just_opened = false;

					if (strtoupper($having['operator']) === 'BETWEEN') {
						$query .= $this->getConnection()->quoteIdentifier($having['column']);
						$query .=' BETWEEN ';
						$query .= $this->getConnection()->quote($having['value'][0]).' AND '
					    .$this->getConnection()->quote($having['value'][1]).' ';
					} else {
					// id can't be quoted!
					if ($having['column'] === 'id') {
					    $query .= 'id ';
					} else {
					    $query .= $this->getConnection()->quoteIdentifier($having['column']).' ';
					}

					if (strtoupper($having['operator']) === 'IN') {
						$query .= 'IN ('.implode(', ', $this->getConnection()->quoteArr($having['value'])).') ';
					} else {
						$query .= $having['operator'].' '.$this->getConnection()->quote($having['value']).' ';
					}
				}
			}
		}

		return $query;
	}
	
	/**
	 * Compiles the statements for SELECT
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function compileSelect()
	{
		$query = '';
		$quote_table = array($this->getConnection(), 'quoteTable');

		if ($this->type == 'select') {
		    $query .= 'SELECT ';

		    if ( ! empty($this->distinct)) {
			    // Select only unique results
			    $query .= 'DISTINCT ';
		    }
		    
		    if ( ! empty($this->select)) {
			$query .= implode(', ', $this->getConnection()->quoteIdentifierArr($this->select)).' ';
		    } else {
			$query .= '* ';
		    }
		}

		if ( ! empty($this->from)) {
		    //$query .= 'FROM '.implode(', ', $this->getConnection()->quoteTable($this->from)).' ';
		    $query .= 'FROM '.implode(', ', array_unique(array_map($quote_table, $this->from))).' ';
		}

		if ( ! empty($this->join)) {
			// Add tables to join
			$query .= $this->compileJoin().' ';
		}
		    
		$query .= $this->compileMatch().$this->compileWhere();

		if ( ! empty($this->group_by)) {
		    $query .= 'GROUP BY '.implode(', ', $this->getConnection()->quoteIdentifierArr($this->group_by)).' ';
		}

		if ( ! empty($this->within_group_order_by)) {
		    $query .= 'WITHIN GROUP ORDER BY ';

		    $order_arr = array();

		    foreach ($this->within_group_order_by as $order) {
			$order_sub = $this->getConnection()->quoteIdentifier($order['column']).' ';

			if ($order['direction'] !== null) {
			    $order_sub .= ((strtolower($order['direction']) === 'desc') ? 'DESC' : 'ASC');
			}

			$order_arr[] = $order_sub;
		    }

		    $query .= implode(', ', $order_arr).' ';
		}

		    $query .= $this->compileHaving();
		    
		if ( ! empty($this->order_by)) {
		    $query .= 'ORDER BY ';

		    $order_arr = array();

		    foreach ($this->order_by as $order) {
			$order_sub = $this->getConnection()->quoteIdentifier($order['column']).' ';

			if ($order['direction'] !== null) {
			    $order_sub .= ((strtolower($order['direction']) === 'desc') ? 'DESC' : 'ASC');
			}

			$order_arr[] = $order_sub;
		    }

		    $query .= implode(', ', $order_arr).' ';
		}

		if ($this->limit !== null || $this->offset !== null) {
			if ($this->offset === null) {
			    $this->offset = 0;
			}

			if ($this->limit === null) {
			    $this->limit = 9999999999999;
			}

			$query .= 'LIMIT '.((int) $this->offset).', '.((int) $this->limit).' ';
		}

		if (!empty($this->options)) {
			$options = array();

			foreach ($this->options as $option) {
			    $options[] = $this->getConnection()->quoteIdentifier($option['name'])
				.' = '.$this->getConnection()->quote($option['value']);
			}

			$query .= 'OPTION '.implode(', ', $options);
		}

		$this->last_query = $query;

		return $this;
	}
	
	/**
	 * Compiles the statements for INSERT or REPLACE
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function compileInsert()
	{
		if ($this->type == 'insert') {
			$query = 'INSERT ';
		} else {
			$query = 'REPLACE ';
		}

		if ($this->into !== null) {
			$query .= 'INTO '.$this->_identifier.$this->table_prefix().$this->into.$this->_identifier.' ';
		}

		if ( ! empty ($this->columns)) {
			$query .= '('.implode(', ', $this->getConnection()->quoteIdentifierArr($this->columns)).') ';
		}

		if ( ! empty ($this->values)) {
			$query .= 'VALUES ';
			$query_sub = '';

			foreach ($this->values as $value) {
		    	$query_sub[] = '('.implode(', ', $this->getConnection()->quoteArr($value)).')';
			}

			$query .= implode(', ', $query_sub);
		}

		$this->last_query = $query;

		return $this;
	}
	
	/**
	 * Compiles the statements for UPDATE
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function compileUpdate()
	{
		$query = 'UPDATE ';

		if ($this->into !== null) {
			$query .= $this->_identifier.$this->table_prefix().$this->into.$this->_identifier.' ';
		}

		if ( ! empty($this->set)) {
			$query .= 'SET ';

			$query_sub = array();

			foreach ($this->set as $column => $value) {
				// MVA support
				if (is_array($value)) {
					$query_sub[] = $this->getConnection()->quoteIdentifier($column)
					    .' = ('.implode(', ', $this->getConnection()->quoteArr($value)).')';
				} else {
					$query_sub[] = $this->getConnection()->quoteIdentifier($column)
					    .' = '.$this->getConnection()->quote($value);
				}
			}

			$query .= implode(', ', $query_sub).' ';
		}

		$query .= $this->compileMatch().$this->compileWhere();

		$this->last_query = $query;

		return $this;
	}
	
	/**
	 * Compiles the statements for DELETE
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function compileDelete()
	{
		$query = 'DELETE ';

		if ($this->into !== null) {
			$query .= 'FROM '.$this->_identifier.$this->table_prefix().$this->into.$this->_identifier.' ';
		} elseif ( ! empty($this->from)) {
			$query .= 'FROM '.$this->_identifier.$this->table_prefix().$this->from[0].$this->_identifier.' ';
		}

		if ( ! empty($this->where)) {
			$query .= $this->compileWhere();
		}

		$this->last_query = $query;

		return $this;
	}
	
	/**
	 * Select the columns
	 * Gets the arguments passed as $SQL->select('one', 'two')
	 * Using it without arguments equals to having '*' as argument
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function select(array $columns = NULL)
	{
		$this->reset();
		$this->type = 'select';
		if ( ! empty($columns))
		{
			// Set the initial columns
			$this->select = $columns;
		}
	
		return $this;
	}
	
	/**
	 * Set the table and columns for an insert.
	 *
	 * @param   mixed  $table    table name or array($table, $alias) or object
	 * @param   array  $columns  column names
	 * @return  void
	 */
	public function insert($table = NULL, array $columns = NULL)
	{
		$this->reset();
		
		if ($table)
		{
			// Set the inital table name
			$this->into($table);
		}

		if ($columns)
		{
			// Set the column names
			$this->columns($columns);
		}

		$this->type = 'insert';
		return $this;
	}
	
	/**
	 * Activates the REPLACE mode
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function replace()
	{
		$this->reset();
		$this->type = 'replace';

		return $this;
	}
	
	/**
	 * Activates the UPDATE mode
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function update($index)
	{
		$this->reset();
		$this->type = 'update';
		$this->into($index);

		return $this;
	}
	
	/**
	 * Activates the DELETE mode
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function delete($table = NULL)
	{
		$this->reset();
		if ($table)
		{
			// Set the inital table name
			$this->into($table);
		}
		$this->type = 'delete';

		return $this;
	}
	
	/**
	 * FROM clause (Sphinx-specific since it works with multiple indexes)
	 * func_get_args()-enabled
	 *
	 * @param  array  $array  An array of indexes to use
	 *
	 * @return \Gleez\Database\Database  The current object
	 */
	public function from($tables)
	{
		$tables = \func_get_args();

		$this->from = array_merge($this->from, $tables);

		return $this;
	}
	
	/**
	 * Enables or disables selecting only unique columns using "SELECT DISTINCT"
	 *
	 * @param   boolean  enable or disable distinct columns
	 * @return  $this
	 */
	public function distinct($value)
	{
		// Add pending database call which is executed after query type is determined
		$this->distinct[] = (bool) $value;

		return $this;
	}
	
	/**
	 * Adds addition tables to "JOIN ...".
	 *
	 * @param   mixed   column name or array($column, $alias) or object
	 * @param   string  join type (LEFT, RIGHT, INNER, etc)
	 * @return  $this
	 */
	public function join($table, $type = NULL)
	{
		// Set the table to JOIN on
		$this->join['table'] = $table;
		$this->join['type']  = '';

		if ($type !== NULL)
		{
			// Set the JOIN type
			$this->join['type'] = (string) $type;
		}

		return $this;
	}
 
	/**
	 * Adds "ON ..." conditions for the last created JOIN statement.
	 *
	 * @param   mixed   column name or array($column, $alias) or object
	 * @param   string  logic operator
	 * @param   mixed   column name or array($column, $alias) or object
	 * @return  $this
	 */
	public function on($c1, $op, $c2)
	{
		if ( ! empty($this->using))
		{
			throw new Exception('JOIN ... ON ... cannot be combined with JOIN ... USING ...');
		}

		// Add pending database call which is executed after query type is determined
		$this->join_on[] = array($c1, $op, $c2);

		return $this;
	}

	/**
	 * Adds "ON ..." conditions for the last created JOIN statement.
	 *
	 * @param   mixed   column name or array($column, $alias) or object
	 * @param   string  logic operator
	 * @param   mixed   column name or array($column, $alias) or object
	 * @return  $this
	 */
	public function join_and($c1, $op, $c2)
	{
		if ( ! empty($this->using))
		{
			throw new Exception('JOIN ... ON ... cannot be combined with JOIN ... USING ...');
		}

		// Add pending database call which is executed after query type is determined
		$this->join_and[] = array($c1, $op, $c2);

		return $this;
	}

	/**
	 * Adds "ON ..." conditions for the last created JOIN statement.
	 *
	 * @param   mixed   column name or array($column, $alias) or object
	 * @param   string  logic operator
	 * @param   mixed   column name or array($column, $alias) or object
	 * @return  $this
	 */
	public function my_on($c1, $op, $c2)
	{
		return $this->join_and($c1, $op, $c2);
	}
	
	/**
	 * Adds "USING ..." conditions for the last created JOIN statement.
	 *
	 * @param   string  $columns  column name
	 * @return  $this
	 */
	public function using($columns)
	{
		if ( ! empty($this->join_on))
		{
			throw new Exception('JOIN ... ON ... cannot be combined with JOIN ... USING ...');
		}

		$columns = func_get_args();

		$this->using = array_merge($this->using, $columns);

		return $this;
	}
	
	/**
	 * MATCH clause (Sphinx-specific)
	 *
	 * @param  string   $column  The column name
	 * @param  string   $value   The value
	 * @param  boolean  $half    Exclude ", |, - control characters from being escaped
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function match($column, $value, $half = false)
	{
		$this->match[] = array('column' => $column, 'value' => $value, 'half' => $half);

		return $this;
	}
	
	/**
	 * WHERE clause
	 *
	 * Examples:
	 *    $sq->where('column', 'value');
	 *    // WHERE `column` = 'value'
	 *
	 *    $sq->where('column', '=', 'value');
	 *    // WHERE `column` = 'value'
	 *
	 *    $sq->where('column', '>=', 'value')
	 *    // WHERE `column` >= 'value'
	 *
	 *    $sq->where('column', 'IN', array('value1', 'value2', 'value3'));
	 *    // WHERE `column` IN ('value1', 'value2', 'value3')
	 *
	 *    $sq->where('column', 'BETWEEN', array('value1', 'value2'))
	 *    // WHERE `column` BETWEEN 'value1' AND 'value2'
	 *    // WHERE `example` BETWEEN 10 AND 100
	 *
	 * @param  string   $column    The column name
	 * @param  string   $operator  The operator to use
	 * @param  string   $value     The value to check against
	 * @param  boolean  $or        If it should be prepended with OR (true) or AND (false) - not available as for Sphinx 2.0.2
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function where($column, $operator, $value = null, $or = false)
	{
		if ($value === null) {
			$value = $operator;
			$operator = '=';
		}

		$this->where[] = array(
			'ext_operator' => $or ? 'OR' : 'AND',
			'column'       => $column,
			'operator'     => $operator,
			'value'        => $value
		);

		return $this;
	}
	
	public function and_where($column, $operator, $value = null)
	{
	    return $this->where($column, $operator, $value);
	}
	
	public function or_where($column, $operator, $value = null)
	{
	    return $this->where($column, $operator, $value, true);
	}
	
	/**
	 * OR WHERE - at this time (Sphinx 2.0.2) it's not available
	 *
	 * @param  string  $column    The column name
	 * @param  string  $operator  The operator to use
	 * @param  mixed   $value     The value to compare against
	 *
	 * @return \Gleez\Database\Database  The current object
	 */
	public function orWhere($column, $operator, $value = null)
	{
		$this->where($column, $operator, $value, true);

		return $this;
	}
	
	public function where_open()
	{
		$this->where[] = array('ext_operator' => '(');

		return $this;
	}
	
	/**
	 * Opens a parenthesis prepended with AND (where necessary)
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function whereOpen()
	{
		$this->where[] = array('ext_operator' => 'AND (');

		return $this;
	}
	
	/**
	 * Opens a new "AND WHERE (...)" grouping.
	 *
	 * @return  $this
	 */
	public function and_where_open()
	{
		return $this->whereOpen();	
	}
	
	/**
	 * Opens a new "OR WHERE (...)" grouping.
	 *
	 * @return  $this
	 */
	public function or_where_open()
	{
		return $this->orWhereOpen();
	}
	
	/**
	 * Opens a parenthesis prepended with OR (where necessary)
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function orWhereOpen()
	{
		$this->where[] = array('ext_operator' => 'OR (');
	    
		return $this;
	}
	
	public function where_close()
	{
		return $this->whereClose();
	}
	
	/**
	 * Closes an open "AND WHERE (...)" grouping.
	 *
	 * @return  $this
	 */
	public function and_where_close()
	{
		return $this->whereClose();
	}
	
	/**
	 * Closes an open "OR WHERE (...)" grouping.
	 *
	 * @return  $this
	 */
	public function or_where_close()
	{
		return $this->whereClose();
	}
	
	/**
	 * Closes a parenthesis in WHERE
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function whereClose()
	{
		$this->where[] = array('ext_operator' => ')');

		return $this;
	}
	
	/**
	 * GROUP BY clause
	 * Adds to the previously added columns
	 *
	 * @param  string  $column  A column to group by
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function groupBy($column)
	{
		$this->group_by[] = $column;

		return $this;
	}
	
	/**
	 * Creates a "GROUP BY ..." filter.
	 *
	 * @param   mixed   column name or array($column, $alias) or object
	 * @param   ...
	 * @return  $this
	 */
	public function group_by($columns)
	{
		return $this->groupBy($columns);
	}
	
	/**
	 * WITHIN GROUP ORDER BY clause (SQL-specific)
	 * Adds to the previously added columns
	 * Works just like a classic ORDER BY
	 *
	 * @param  string  $column     The column to group by
	 * @param  string  $direction  The group by direction (asc/desc)
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function withinGroupOrderBy($column, $direction = null)
	{
		$this->within_group_order_by[] = array('column' => $column, 'direction' => $direction);

		return $this;
	}
	
	/**
	 * Alias of and_having()
	 *
	 * @param   mixed   column name or array($column, $alias) or object
	 * @param   string  logic operator
	 * @param   mixed   column value
	 * @return  $this
	 */
	public function having($column, $operator, $value = NULL, $or = FALSE)
	{
		if ($value === null) {
			$value = $operator;
			$operator = '=';
		}

		$this->having[] = array(
			'ext_operator' => $or ? 'OR' : 'AND',
			'column'       => $column,
			'operator'     => $operator,
			'value'        => $value
		);

		return $this;
	}
	
	/**
	 * Creates a new "AND HAVING" condition for the query.
	 *
	 * @param   mixed   column name or array($column, $alias) or object
	 * @param   string  logic operator
	 * @param   mixed   column value
	 * @return  $this
	 */
	public function and_having($column, $operator, $value = NULL)
	{
		return $this->having($column, $operator, $value);
	}
	
	/**
	 * Creates a new "OR HAVING" condition for the query.
	 *
	 * @param   mixed   column name or array($column, $alias) or object
	 * @param   string  logic operator
	 * @param   mixed   column value
	 * @return  $this
	 */
	public function or_having($column, $operator, $value = NULL)
	{
		return $this->having($column, $operator, $value, true);
	}
	
	/**
	 * Alias of and_having_open()
	 *
	 * @return  $this
	 */
	public function having_open()
	{
		return $this->and_having_open();
	}

	/**
	 * Opens a new "AND HAVING (...)" grouping.
	 *
	 * @return  $this
	 */
	public function and_having_open()
	{
		$this->having[] = array('ext_operator' => 'AND (');

		return $this;
	}
	
	/**
	 * Opens a new "OR HAVING (...)" grouping.
	 *
	 * @return  $this
	 */
	public function or_having_open()
	{
		$this->having[] = array('ext_operator' => 'OR (');

		return $this;
	}

	/**
	 * Closes an open "AND HAVING (...)" grouping.
	 *
	 * @return  $this
	 */
	public function having_close()
	{
		$this->having[] = array('ext_operator' => ')');
		
		return $this;
	}
	
	/**
	 * Closes an open "AND HAVING (...)" grouping.
	 *
	 * @return  $this
	 */
	public function and_having_close()
	{
		return $this->having_close();
	}

	/**
	 * Closes an open "OR HAVING (...)" grouping.
	 *
	 * @return  $this
	 */
	public function or_having_close()
	{
		return $this->having_close();
	}
	
	/**
	 * Applies sorting with "ORDER BY ..."
	 *
	 * @param   mixed   column name or array($column, $alias) or object
	 * @param   string  direction of sorting
	 * @return  $this
	 */
	public function order_by($column, $direction = NULL)
	{
		return $this->orderBy($column, $direction);
	}
	
	/**
	 * ORDER BY clause
	 * Adds to the previously added columns
	 *
	 * @param  string  $column     The column to order on
	 * @param  string  $direction  The ordering direction (asc/desc)
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function orderBy($column, $direction = null)
	{
		$this->order_by[] = array('column' => $column, 'direction' => $direction);

		return $this;
	}
	
	/**
	 * LIMIT clause
	 * Supports also LIMIT offset, limit
	 *
	 * @param  int       $offset  Offset if $limit is specified, else limit
	 * @param  null|int  $limit   The limit to set, null for no limit
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function limit($offset, $limit = null)
	{
		if ($limit === null) {
			$this->limit = (int) $offset;
			return $this;
		}

		$this->offset($offset);
		$this->limit = (int) $limit;

		return $this;
	}
	
	/**
	 * OFFSET clause
	 *
	 * @param  int  $offset  The offset
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function offset($offset)
	{
		$this->offset = (int) $offset;

		return $this;
	}
	
	/**
	 * OPTION clause (SQL-specific)
	 * Used by: SELECT
	 *
	 * @param  string  $name   Option name
	 * @param  string  $value  Option value
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function option($name, $value)
	{
		$this->options[] = array('name' => $name, 'value' => $value);

		return $this;
	}
	
	/**
	 * INTO clause
	 * Used by: INSERT, REPLACE
	 *
	 * @param  string  $index  The index to insert/replace into
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function into($index)
	{
		$this->into = $index;

		return $this;
	}
	
	/**
	 * Set columns
	 * Used in: INSERT, REPLACE
	 * func_get_args()-enabled
	 *
	 * @param  array  $array  The array of columns
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function columns($array = array())
	{
		if (is_array($array)) {
			$this->columns = $array;
		} else {
			$this->columns = \func_get_args();
		}

		return $this;
	}
	
	/**
	 * Set VALUES
	 * Used in: INSERT, REPLACE
	 * func_get_args()-enabled
	 *
	 * @param  array  $array  The array of values matching the columns from $this->columns()
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function values($array)
	{
		if (is_array($array)) {
			$this->values[] = $array;
		} else {
			$this->values[] = \func_get_args();
		}

		return $this;
	}
	
	/**
	 * Set column and relative value
	 * Used in: INSERT, REPLACE
	 *
	 * @param  string  $column  The column name
	 * @param  string  $value   The value
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function value($column, $value)
	{
		if ($this->type === 'insert' || $this->type === 'replace') {
			$this->columns[] = $column;
			$this->values[0][] = $value;
		} 
		else {
			$this->set[$column] = $value;
		}

		return $this;
	}
	
	/**
	 * Allows passing an array with the key as column and value as value
	 * Used in: INSERT, REPLACE, UPDATE
	 *
	 * @param  array  $array  Array of key-values
	 *
	 * @return \Gleez\Database\Database  The current object
	 */
	public function set($array)
	{
		foreach ($array as $key => $item)
		{
			$this->value($key, $item);
		}

		return $this;
	}
	
	/**
	 * Escapes the query for the MATCH() function
	 *
	 * @param  string  $string The string to escape for the MATCH
	 *
	 * @return  string  The escaped string
	 */
	public function escapeMatch($string)
	{
		$from = array('\\', '(', ')', '|', '-', '!', '@', '~', '"', '&', '/', '^', '$', '=');
		$to = array('\\\\', '\(', '\)', '\|', '\-', '\!', '\@', '\~', '\"', '\&', '\/', '\^', '\$', '\=');

		return str_replace($from, $to, $string);
	}
	
	/**
	 * Escapes the query for the MATCH() function
	 * Allows some of the control characters to pass through for use with a search field: -, |, "
	 * It also does some tricks to wrap/unwrap within " the string and prevents errors
	 *
	 * @param  string  $string  The string to escape for the MATCH
	 *
	 * @return  string  The escaped string
	 */
	public function halfEscapeMatch($string)
	{
		$from_to = array(
		'\\' => '\\\\',
		'(' => '\(',
		')' => '\)',
		'!' => '\!',
		'@' => '\@',
		'~' => '\~',
		'&' => '\&',
		'/' => '\/',
		'^' => '\^',
		'$' => '\$',
		'=' => '\=',
		);

		$string = str_replace(array_keys($from_to), array_values($from_to), $string);

		// this manages to lower the error rate by a lot
		if (substr_count($string, '"') % 2 !== 0) {
			$string .= '"';
		}

		$from_to_preg = array(
		"'\"([^\s]+)-([^\s]*)\"'" => "\\1\-\\2",
		"'([^\s]+)-([^\s]*)'" => "\"\\1\-\\2\""
		);

		$string = preg_replace(array_keys($from_to_preg), array_values($from_to_preg), $string);

		return $string;
	}
	
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
	 * Clears the existing query build for new query when using the same SQL instance.
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function reset()
	{
		$this->select = array();
		$this->from = array();
		$this->using = array();
		$this->join = array();
		$this->join_on = array();
		$this->join_and = array();
		$this->where = array();
		$this->match = array();
		$this->group_by = array();
		$this->within_group_order_by = array();
		$this->having = array();
		$this->order_by = array();
		$this->offset = null;
		$this->into = null;
		$this->columns = array();
		$this->values = array();
		$this->set = array();
		$this->options = array();

		return $this;
	}
	
}
