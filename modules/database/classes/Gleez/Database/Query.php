<?php
/**
 * MySQLi database Expression
 *
 * @package    Gleez\Database
 * @version    2.0.0
 * @author     Gleez Team
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
namespace Gleez\Database;

class Query {
	
	// SQL statement
	protected $_query;
	
	// Quoted query parameters
	protected $_parameters = array();
	
	// Character that is used to quote identifiers
	protected $_identifier = '`';
	
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
	 * The last chosen method (select, insert, replace, update, delete).
	 *
	 * @var  string
	 */
	protected $type = null;
	
	/**
	 * Return results as associative arrays or objects
	 *
	 * @var  bool|string
	 */
	protected $_as_object = FALSE;

	/**
	 * Parameters for __construct when using object results
	 *
	 * @var  array
	 */
	protected $_object_params = array();
	
	/**
	 * Creates a new SQL query of the specified type.
	 *
	 * @param   integer  $type  query type: Database::SELECT, Database::INSERT, etc
	 * @param   string   $sql   query string
	 * @return  void
	 */
	public function __construct($type, $sql)
	{
		$this->_type = $type;
		$this->_query = $sql;
	}

	/**
	 * Return the SQL query string.
	 *
	 * @return  string
	 */
	public function __toString()
	{
		try
		{
			// Return the SQL string
			return $this->compile(Database::instance());
		}
		catch (Exception $e)
		{
			return Gleez_Exception::text($e);
		}
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
		    $db = Database::instance($db);
		}
	
		if ($as_object === NULL)
		{
			$as_object = $this->_as_object;
		}

		if ($object_params === NULL)
		{
			$object_params = $this->_object_params;
		}

		$sql = $this->compile($db);

		// pass the object so execute compiles it by itself
		return $db->query($this->type, $sql, $as_object, $object_params);
	}
	
	/**
	 * Runs the compile function
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function compile($db = NULL)
	{
		if ( ! is_object($db))
		{
			// Get the database instance
			$db = Database::instance($db);
		}
		
		switch ($this->type) {
			case 'select':
				$this->compileSelect($db);
				break;
			case 'insert':
			case 'replace':
				$this->compileInsert($db);
				break;
			case 'update':
				$this->compileUpdate($db);
				break;
			case 'delete':
				$this->compileDelete($db);
				break;
		}

		// Import the SQL locally
		$sql = $this->_query;

		if ( ! empty($this->_parameters))
		{
			// Quote all of the values
			$values = array_map(array($db->getConnection(), 'quote'), $this->_parameters);

			// Replace the values in the SQL
			$sql = strtr($sql, $values);
		}

		return $sql;
	}
	
	/**
	 * Returns the latest compiled query
	 *
	 * @return  string  The last compiled query
	 */
	public function getCompiled()
	{
	    return $this->_query;
	}
	
	/**
	 * Compile the SQL partial for a JOIN statement and return it.
	 *
	 * @param   mixed  $db  Database instance or name of instance
	 * @return  string
	 */
	protected function compileJoin($db)
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
		$query .= ' '.$db->getConnection()->quoteTable($this->join['table']);

		if (! empty($this->using))
		{
			$quote_column = array($db->getConnection(), 'quoteIdentifier');
			
			// Quote and concat the columns
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
				$conditions[] = $db->getConnection()->quoteIdentifier($c1).$op.' '.$db->quoteIdentifier($c2);
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
					$and_conditions[] = $db->getConnection()->quoteIdentifier($c1).$op.' '.$db->quote($v1); 
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
	 * Compiles the WHERE part of the queries
	 * It interacts with the MATCH() and of course isn't usable stand-alone
	 * Used by: SELECT, DELETE, UPDATE
	 *
	 * @return  string  The compiled WHERE
	 */
	public function compileWhere($db)
	{
		return $this->_compileWhereHaving($db, $type = 'where');
	}
	
	/**
	 * Compiles the WHERE part of the queries
	 * It interacts with the MATCH() and of course isn't usable stand-alone
	 * Used by: SELECT, DELETE, UPDATE
	 *
	 * @return  string  The compiled WHERE
	 */
	public function compileHaving($db)
	{
		return $this->_compileWhereHaving($db, $type = 'having');
	}
	
	private function _compileWhereHaving($db, $type = 'where')
	{
		$query = '';

		$array = $this->where;
		if($type == 'having')
		{
			$array = $this->having;	
		}
		
		if (! empty($array) && $type == 'where') {
			$query .= 'WHERE ';
		}
		
		if (! empty($array) && $type == 'having')
		{
			$query .= 'HAVING ';
		}
		
		if ( ! empty($array)) {
			$just_opened = false;

			foreach ($array as $key => $where) {
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

				if (strtoupper($where['operator']) === 'BETWEEN' AND is_array($where['value'])) 
				{
					$query .= $db->getConnection()->quoteIdentifier($where['column']);
					$query .=' BETWEEN ';

					// BETWEEN always has exactly two arguments
					list($min, $max) = $where['value'];

					if ((is_string($min) AND array_key_exists($min, $this->_parameters)) === FALSE)
					{
						// Quote the value, it is not a parameter
						$min = $db->getConnection()->quote($min);
					}

					if ((is_string($max) AND array_key_exists($max, $this->_parameters)) === FALSE)
					{
						// Quote the value, it is not a parameter
						$max = $db->getConnection()->quote($max);
					}

					// Quote the min and max value
					$query .= $min.' AND '.$max;
				} 
				else 
				{
					// id can't be quoted!
					if ($where['column'] === 'id') 
					{
				    	$query .= 'id ';
					}
					else 
					{
				    	$query .= $db->getConnection()->quoteIdentifier($where['column']).' ';
					}

					if (strtoupper($where['operator']) === 'IN')
					{
					    $query .= 'IN ('.implode(', ', $db->getConnection()->quoteArr($where['value'])).') ';
					}
					elseif ((is_string($where['value']) AND array_key_exists($where['value'], $this->_parameters)) === FALSE)
					{
						// Quote the value, it is not a parameter
						$query .= $where['operator'].' '.$db->getConnection()->quote($where['value']).' ';
					} 
					else
					{
						$query .= $where['operator'].' '.$where['value'].' ';
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
	public function compileSelect($db)
	{
		$query = '';

		// Callback to quote tables
		$quoteTable = array($db->getConnection(), 'quoteTable');
	
		if ($this->type == 'select') {
			$query .= 'SELECT ';

			if ( ! empty($this->distinct)) {
				// Select only unique results
				$query .= 'DISTINCT ';
			}

			if ( empty($this->select)) {
				$query .= '* ';
			} else {
				$query .= implode(', ', $db->getConnection()->quoteIdentifierArr($this->select)).' ';
				//$query .= implode(', ', array_unique(array_map($quoteColumn, $this->select))).' ';
			}
		}

		if ( ! empty($this->from)) {
			$query .= 'FROM '.implode(', ', array_unique(array_map($quoteTable, $this->from))).' ';
		}

		if ( ! empty($this->join)) {
			// Add tables to join
			$query .= $this->compileJoin($db).' ';
		}

		$query .= $this->compileWhere($db);

		if ( ! empty($this->group_by)) {
			$query .= 'GROUP BY '.implode(', ', $db->getConnection()->quoteIdentifierArr($this->group_by)).' ';
		}

		if ( ! empty($this->within_group_order_by)) {
			$query .= 'WITHIN GROUP ORDER BY ';

			$order_arr = array();

			foreach ($this->within_group_order_by as $order) {
				$order_sub = $db->getConnection()->quoteIdentifier($order['column']).' ';

				if ($order['direction'] !== null) {
					$order_sub .= ((strtolower($order['direction']) === 'desc') ? 'DESC' : 'ASC');
				}

				$order_arr[] = $order_sub;
			}

			$query .= implode(', ', $order_arr).' ';
		}

		$query .= $this->compileHaving($db);

		if ( ! empty($this->order_by)) {
			$query .= 'ORDER BY ';

			$order_arr = array();

			foreach ($this->order_by as $order) {
			$order_sub = $db->getConnection()->quoteIdentifier($order['column']).' ';

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
				$options[] = $db->getConnection()->quoteIdentifier($option['name'])
				.' = '.$db->getConnection()->quote($option['value']);
			}

			$query .= 'OPTION '.implode(', ', $options);
		}

		$this->_query = $query;

		return $this;
	}
	
	/**
	 * Compiles the statements for INSERT or REPLACE
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function compileInsert($db)
	{
		if ($this->type == 'insert') {
			$query = 'INSERT ';
		} else {
			$query = 'REPLACE ';
		}

		if ($this->into !== null) {
			$query .= 'INTO '.$this->_identifier.$db->table_prefix().$this->into.$this->_identifier.' ';
		}

		if ( ! empty ($this->columns)) {
			$query .= '('.implode(', ', $db->getConnection()->quoteIdentifierArr($this->columns)).') ';
		}

		if ( ! empty ($this->values)) {
			$query .= 'VALUES ';
			$query_sub = '';

			foreach ($this->values as $value) {
				$query_sub[] = '('.implode(', ', $db->getConnection()->quoteArr($value)).')';
			}

			$query .= implode(', ', $query_sub);
		}

		$this->_query = $query;

		return $this;
	}
	
	/**
	 * Compiles the statements for UPDATE
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function compileUpdate($db)
	{
		$query = 'UPDATE ';

		if ($this->into !== null) {
			$query .= $this->_identifier.$db->table_prefix().$this->into.$this->_identifier.' ';
		}

		if ( ! empty($this->set)) {
			$query .= 'SET ';

			$query_sub = array();

			foreach ($this->set as $column => $value) 
			{
				// MVA support
				if (is_array($value)) {
					$query_sub[] = $db->getConnection()->quoteIdentifier($column)
					    .' = ('.implode(', ', $db->getConnection()->quoteArr($value)).')';
				} 
				else 
				{
					if ((is_string($value) AND array_key_exists($value, $this->_parameters)) === FALSE)
					{
						$query_sub[] = $db->getConnection()->quoteIdentifier($column).' = '.$db->getConnection()->quote($value);
					} 
					else
					{
						$query_sub[] = $db->getConnection()->quoteIdentifier($column).' = '.$value;
					}
				}
			}

			$query .= implode(', ', $query_sub).' ';
		}

		$query .= $this->compileWhere($db);

		// pass the
		$this->_query = $query;

		return $this;
	}
	
	/**
	 * Compiles the statements for DELETE
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function compileDelete($db)
	{
		$query = 'DELETE ';

		if ($this->into !== null) {
			$query .= 'FROM '.$this->_identifier.$db->table_prefix().$this->into.$this->_identifier.' ';
		} elseif ( ! empty($this->from)) {
			$query .= 'FROM '.$this->_identifier.$db->table_prefix().$this->from[0].$this->_identifier.' ';
		}

		if ( ! empty($this->where)) {
			$query .= $this->compileWhere($db);
		}

		$this->_query = $query;

		return $this;
	}
	
	/**
	 * Select the columns
	 * Gets the arguments passed as $SQL->select('one', 'two')
	 * Using it without arguments equals to having '*' as argument
	 *
	 * @return  \Gleez\Database\Database  The current object
	 */
	public function select($columns = NULL)
	{
		$this->reset();
		$this->type = 'select';
		$this->select = $columns;

		return $this;
	}

	public function selectArgs($columns = NULL)
	{
		$this->type = 'select';
		$this->select = array_merge($this->select, \func_get_args());

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

		$columns = \func_get_args();

		$this->using = array_merge($this->using, $columns);

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
	 * Return the table prefix defined in the current configuration.
	 *
	 *     $prefix = $db->table_prefix();
	 *
	 * @return  string
	 */
	public function table_prefix($db)
	{
		return $db->table_prefix();
	}
	
	/**
	 * Returns results as associative arrays
	 *
	 * @return  $this
	 */
	public function as_assoc()
	{
		$this->_as_object = FALSE;

		$this->_object_params = array();

		return $this;
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
		$this->limit = null;
		$this->_as_object = FALSE;
		$this->_object_params = array();
		$this->_parameters = array();
		$this->_query = NULL;
		
		return $this;
	}
	
}