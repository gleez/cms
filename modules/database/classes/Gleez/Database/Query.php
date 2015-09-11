<?php
/**
 * Gleez CMS (http://gleezcms.org)
 *
 * @link https://github.com/gleez/cms Canonical source repository
 * @copyright Copyright (c) 2011-2015 Gleez Technologies
 * @license http://gleezcms.org/license Gleez CMS License
 */

namespace Gleez\Database;

/**
 * MySQLi Database Query
 *
 * @package Gleez\Database
 * @version 2.1.0
 * @author  Gleez Team
 */
class Query {

	/**
	 * SQL statement
	 * @var string
	 */
	protected $_query;

	/**
	 * Quoted query parameters
	 * @var array
	 */
	protected $_parameters = array();

	/**
	 * Character that is used to quote identifiers
	 * @var string
	 */
	protected $_identifier = '`';

	/**
	 * Array of select elements that will be comma separated.
	 *
	 * @var array
	 */
	protected $select = array();

	/**
	 * Distinct
	 *
	 * @var array
	 */
	protected $distinct = array();

	/**
	 * From in SQL is the list of indexes that will be used
	 *
	 * @var array
	 */
	protected $from = array();

	/**
	 * Using
	 *
	 * @var array
	 */
	protected $using = array();

	/**
	 * JOIN
	 *
	 * @var array
	 */
	protected $join = array();

	/**
	 * The last JOIN array index
	 */
	protected $last_join = 0;

	/**
	 * The list of where and parenthesis, must be inserted in order
	 *
	 * @var array
	 */
	protected $where = array();

	/**
	 * The list of matches for the MATCH function in SQL
	 *
	 * @var array
	 */
	protected $match = array();

	/**
	 * GROUP BY array to be comma separated
	 *
	 * @var array
	 */
	protected $group_by = array();

	/**
	 * ORDER BY array
	 *
	 * @var array
	 */
	protected $within_group_order_by = array();

	/**
	 * The list of having and parenthesis, must be inserted in order
	 *
	 * @var array
	 */
	protected $having = array();

	/**
	 * ORDER BY array
	 *
	 * @var array
	 */
	protected $order_by = array();

	/**
	 * When not null it adds an offset
	 *
	 * @var null|int
	 */
	protected $offset = null;

	/**
	 * When not null it adds a limit
	 *
	 * @var null|int
	 */
	protected $limit = null;

	/**
	 * Value of INTO query for INSERT or REPLACE
	 *
	 * @var null|string
	 */
	protected $into = null;

	/**
	 * Array of arrays of values for INSERT or REPLACE
	 *
	 * @var array
	 */
	protected $columns = array();

	/**
	 * Array OF ARRAYS of values for INSERT or REPLACE
	 *
	 * @var array
	 */
	protected $values = array();

	/**
	 * Array of arrays containing column and value for SET in UPDATE
	 *
	 * @var array
	 */
	protected $set = array();

	/**
	 * Array of OPTION specific to SQL
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * The last chosen method (select, insert, replace, update, delete).
	 *
	 * @var string
	 */
	protected $type = null;

	/**
	 * Return results as associative arrays or objects
	 *
	 * @var bool|string
	 */
	protected $_as_object = FALSE;

	/**
	 * Parameters for __construct when using object results
	 *
	 * @var array
	 */
	protected $_object_params = array();

	/**
	 * Creates a new SQL query of the specified type.
	 *
	 * @param   string  $type  query type: Database::SELECT, Database::INSERT, etc
	 * @param   string   $sql   query string
	 */
	public function __construct($type, $sql) {
		$this->type = $type;
		$this->_query = $sql;
	}

	/**
	 * Return the SQL query string.
	 *
	 * @return  string
	 *
	 * @throws  \Gleez_Exception
	 */
	public function __toString() {
		try
		{
			// Return the SQL string
			return $this->compile(Database::instance());
		} catch (\Exception $e) {
			return \Gleez_Exception::text($e);
		}
	}

	/**
	 * Runs the query built
	 *
	 * @param Database|string $db The database instance [Optional]
	 * @param bool|null|string $as_object Return results as associative arrays or objects? [Optional]
	 * @param bool|null|array $object_params Parameters for object results [Optional]
	 *
	 * @return  \Gleez\Database\Result  The result of the query
	 */
	public function execute($db = null, $as_object = null, $object_params = null) {
		// Get the database instance
		if (!$db instanceof Database) {
			$db = Database::instance($db);
		}

		if ($as_object === NULL) {
			$as_object = $this->_as_object;
		}

		if ($object_params === NULL) {
			$object_params = $this->_object_params;
		}

		$sql = $this->compile($db);

		// pass the object so execute compiles it by itself
		return $db->query($this->type, $sql, $as_object, $object_params);
	}

	/**
	 * Runs the compile function
	 *
	 * @param   \Gleez\Database\Database $db  The database instance [Optional]
	 * @return  string
	 */
	public function compile($db = null) {
		// Get the database instance
		if (!$db instanceof Database) {
			$db = Database::instance($db);
		}

		switch ($this->type) {
		case Database::SELECT:
			$this->compileSelect($db);
			break;
		case Database::INSERT:
		case Database::REPLACE:
			$this->compileInsert($db);
			break;
		case Database::UPDATE:
			$this->compileUpdate($db);
			break;
		case Database::DELETE:
			$this->compileDelete($db);
			break;
		}

		// Import the SQL locally
		$sql = $this->_query;

		if (!empty($this->_parameters)) {
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
	public function getCompiled() {
		return $this->_query;
	}

	/**
	 * Compile the SQL partial for a JOIN statement and return it.
	 *
	 * @param   \Gleez\Database\Database  $db  Database instance or name of instance
	 * @param   array  The array of join condition
	 * @return  string
	 */
	protected function compileJoin(Database $db, $join) {
		if (!empty($join['type'])) {
			$query = strtoupper($join['type']) . ' JOIN';
		} else {
			$query = 'JOIN';
		}

		// Quote the table name that is being joined
		$query .= ' ' . $db->getConnection()->quoteTable($join['table']);

		if (!empty($this->using)) {
			$quote_column = array($db->getConnection(), 'quoteIdentifier');

			// Quote and concat the columns
			$query .= ' USING (' . implode(', ', array_map($quote_column, $this->using)) . ')';
		} elseif (isset($join['on']) && !empty($join['on'])) {
			$conditions = array();
			foreach ($join['on'] as $k => $condition) {
				// Split the condition
				list($c1, $op, $c2) = $condition;

				if ($op) {
					// Make the operator uppercase and spaced
					$op = ' ' . strtoupper($op);
				}

				// Quote each of the columns used for the condition
				$conditions[] = $db->getConnection()->quoteIdentifier($c1) . $op . ' ' . $db->quoteIdentifier($c2);
			}

			// Concat the conditions "... AND ..."
			$query .= ' ON (' . implode(' AND ', $conditions) . ')';

			if (isset($join['and']) && !empty($join['and'])) {
				$and_conditions = array();

				foreach ($join['and'] as $icondition) {
					// Split the condition
					list($c1, $op, $v1) = $icondition;

					if ($op) {
						// Make the operator uppercase and spaced
						$op = ' ' . strtoupper($op);
					}

					// Quote each of the columns used for the condition. v1 is quote value not column
					$and_conditions[] = $db->getConnection()->quoteIdentifier($c1) . $op . ' ' . $db->quote($v1);
				}

				if (!empty($and_conditions)) {
					// Concat the conditions "... AND ..."
					$query .= ' AND ' . implode(' AND ', $and_conditions) . '';
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
	 * @param   \Gleez\Database\Database  $db  The database instance
	 * @return  string  The compiled WHERE
	 */
	public function compileWhere(Database $db) {
		return $this->_compileWhereHaving($db, $type = 'where');
	}

	/**
	 * Compiles the WHERE part of the queries
	 * It interacts with the MATCH() and of course isn't usable stand-alone
	 * Used by: SELECT, DELETE, UPDATE
	 *
	 * @param   \Gleez\Database\Database  $db  The database instance
	 * @return  string  The compiled WHERE
	 */
	public function compileHaving(Database $db) {
		return $this->_compileWhereHaving($db, $type = 'having');
	}

	/**
	 * @param \Gleez\Database\Database $db
	 * @param string $type
	 *
	 * @return string
	 */
	private function _compileWhereHaving(Database $db, $type = 'where') {
		$query = '';

		$array = $this->where;
		if ($type == 'having') {
			$array = $this->having;
		}

		if (!empty($array) && $type == 'where') {
			$query .= 'WHERE ';
		}

		if (!empty($array) && $type == 'having') {
			$query .= 'HAVING ';
		}

		if (!empty($array)) {
			$just_opened = false;

			foreach ($array as $key => $where) {
				if (in_array($where['ext_operator'], array('AND (', 'OR (', ')', '('))) {
					// if match is not empty we've got to use an operator
					if ($key == 0 || !empty($this->match)) {
						$query .= '(';

						$just_opened = true;
					} else {
						$query .= $where['ext_operator'] . ' ';
						if ($where['ext_operator'] != ')') {
							$just_opened = true;
						}
					}
					continue;
				}

				if ($key > 0 && !$just_opened || !empty($this->match)) {
					$query .= $where['ext_operator'] . ' '; // AND/OR
				}

				$just_opened = false;

				if (strtoupper($where['operator']) === 'BETWEEN' AND is_array($where['value'])) {
					$query .= $db->getConnection()->quoteIdentifier($where['column']);
					$query .= ' BETWEEN ';

					// BETWEEN always has exactly two arguments
					list($min, $max) = $where['value'];

					if ((is_string($min) AND array_key_exists($min, $this->_parameters)) === FALSE) {
						// Quote the value, it is not a parameter
						$min = $db->getConnection()->quote($min);
					}

					if ((is_string($max) AND array_key_exists($max, $this->_parameters)) === FALSE) {
						// Quote the value, it is not a parameter
						$max = $db->getConnection()->quote($max);
					}

					// Quote the min and max value
					$query .= $min . ' AND ' . $max;
				} else {
					// id can't be quoted!
					if ($where['column'] === 'id') {
						$query .= 'id ';
					} elseif (!is_null($where['column'])) {
						$query .= $db->getConnection()->quoteIdentifier($where['column']) . ' ';
					}

					if (strtoupper($where['operator']) === 'IN') {
						$query .= 'IN (' . implode(', ', $db->getConnection()->quoteArr($where['value'])) . ') ';
					} elseif (is_null($where['value'])) {
						if ($where['operator'] == '=') {
							$query .= 'IS NULL ';
						} elseif ($where['operator'] == '!=') {
							$query .= 'IS NOT NULL ';
						}
					} elseif ((is_string($where['value']) AND array_key_exists($where['value'], $this->_parameters)) === FALSE) {
						// Quote the value, it is not a parameter
						$query .= $where['operator'] . ' ' . $db->getConnection()->quote($where['value']) . ' ';
					} else {
						$query .= $where['operator'] . ' ' . $where['value'] . ' ';
					}
				}
			}
		}

		return $query;
	}

	/**
	 * Compiles the statements for SELECT
	 *
	 * @param   \Gleez\Database\Database  $db  The Database instance
	 * @return  \Gleez\Database\Query  The current object
	 */
	public function compileSelect(Database $db) {
		$query = '';

		// Callback to quote tables
		$quoteTable = array($db->getConnection(), 'quoteTable');

		if ($this->type == Database::SELECT) {
			$query .= 'SELECT ';

			if (!empty($this->distinct)) {
				// Select only unique results
				$query .= 'DISTINCT ';
			}

			if (empty($this->select)) {
				$query .= '* ';
			} else {
				$query .= implode(', ', $db->getConnection()->quoteIdentifierArr($this->select)) . ' ';
				//$query .= implode(', ', array_unique(array_map($quoteColumn, $this->select))).' ';
			}
		}

		if (!empty($this->from)) {
			$query .= 'FROM ' . implode(', ', array_unique(array_map($quoteTable, $this->from))) . ' ';
		}

		if (!empty($this->join)) {
			$statements = array();
			foreach ($this->join as $join) {
				// Compile each of the join statements
				$statements[] = $this->compileJoin($db, $join);
			}

			// Add tables to join
			$query .= implode(' ', $statements) . ' ';
		}

		$query .= $this->compileWhere($db);

		if (!empty($this->group_by)) {
			$query .= 'GROUP BY ' . implode(', ', $db->getConnection()->quoteIdentifierArr($this->group_by)) . ' ';
		}

		if (!empty($this->within_group_order_by)) {
			$query .= 'WITHIN GROUP ORDER BY ';

			$order_arr = array();

			foreach ($this->within_group_order_by as $order) {
				$order_sub = $db->getConnection()->quoteIdentifier($order['column']) . ' ';

				if ($order['direction'] !== null) {
					$order_sub .= ((strtolower($order['direction']) === 'desc') ? 'DESC' : 'ASC');
				}

				$order_arr[] = $order_sub;
			}

			$query .= implode(', ', $order_arr) . ' ';
		}

		$query .= $this->compileHaving($db);

		if (!empty($this->order_by)) {
			$query .= 'ORDER BY ';

			$order_arr = array();

			foreach ($this->order_by as $order) {
				$order_sub = $db->getConnection()->quoteIdentifier($order['column']) . ' ';

				if ($order['direction'] !== null) {
					$order_sub .= ((strtolower($order['direction']) === 'desc') ? 'DESC' : 'ASC');
				}

				$order_arr[] = $order_sub;
			}

			$query .= implode(', ', $order_arr) . ' ';
		}

		if ($this->limit !== null || $this->offset !== null) {
			if ($this->offset === null) {
				$this->offset = 0;
			}

			if ($this->limit === null) {
				$this->limit = 9999999999999;
			}

			$query .= 'LIMIT ' . ((int) $this->offset) . ', ' . ((int) $this->limit) . ' ';
		}

		if (!empty($this->options)) {
			$options = array();

			foreach ($this->options as $option) {
				$options[] = $db->getConnection()->quoteIdentifier($option['name'])
				. ' = ' . $db->getConnection()->quote($option['value']);
			}

			$query .= 'OPTION ' . implode(', ', $options);
		}

		$this->_query = trim($query);

		return $this;
	}

	/**
	 * Compiles the statements for INSERT or REPLACE
	 *
	 * @param   \Gleez\Database\Database  $db  The database instance
	 * @return  \Gleez\Database\Query  The current object
	 */
	public function compileInsert(Database $db) {
		if ($this->type == Database::INSERT) {
			$query = 'INSERT ';
		} else {
			$query = 'REPLACE ';
		}

		if ($this->into !== null) {
			$query .= 'INTO ' . $this->_identifier . $db->table_prefix() . $this->into . $this->_identifier . ' ';
		}

		if (!empty($this->columns)) {
			$query .= '(' . implode(', ', $db->getConnection()->quoteIdentifierArr($this->columns)) . ') ';
		}

		if (!empty($this->values)) {
			$query .= 'VALUES ';
			$query_sub = '';

			foreach ($this->values as $value) {
				$query_sub[] = '(' . implode(', ', $db->getConnection()->quoteArr($value)) . ')';
			}

			$query .= implode(', ', $query_sub);
		}

		$this->_query = trim($query);

		return $this;
	}

	/**
	 * Compiles the statements for UPDATE
	 *
	 * @param   \Gleez\Database\Database  $db  The database instance
	 * @return  \Gleez\Database\Query  The current object
	 */
	public function compileUpdate(Database $db) {
		$query = 'UPDATE ';

		if ($this->into !== null) {
			$query .= $this->_identifier . $db->table_prefix() . $this->into . $this->_identifier . ' ';
		}

		if (!empty($this->set)) {
			$query .= 'SET ';

			$query_sub = array();

			foreach ($this->set as $column => $value) {
				// MVA support
				if (is_array($value)) {
					$query_sub[] = $db->getConnection()->quoteIdentifier($column)
					. ' = (' . implode(', ', $db->getConnection()->quoteArr($value)) . ')';
				} else {
					if ((is_string($value) AND array_key_exists($value, $this->_parameters)) === FALSE) {
						$query_sub[] = $db->getConnection()->quoteIdentifier($column) . ' = ' . $db->getConnection()->quote($value);
					} else {
						$query_sub[] = $db->getConnection()->quoteIdentifier($column) . ' = ' . $value;
					}
				}
			}

			$query .= implode(', ', $query_sub) . ' ';
		}

		$query .= $this->compileWhere($db);

		$this->_query = trim($query);

		return $this;
	}

	/**
	 * Compiles the statements for DELETE
	 *
	 * @param   \Gleez\Database\Database  $db  The database instance
	 * @return  \Gleez\Database\Query  The current object
	 */
	public function compileDelete(Database $db) {
		$query = 'DELETE ';

		if ($this->into !== null) {
			$query .= 'FROM ' . $this->_identifier . $db->table_prefix() . $this->into . $this->_identifier . ' ';
		} elseif (!empty($this->from)) {
			$query .= 'FROM ' . $this->_identifier . $db->table_prefix() . $this->from[0] . $this->_identifier . ' ';
		}

		if (!empty($this->where)) {
			$query .= $this->compileWhere($db);
		}

		$this->_query = trim($query);

		return $this;
	}

	/**
	 * Select the columns
	 * Gets the arguments passed as $SQL->select('one', 'two')
	 * Using it without arguments equals to having '*' as argument
	 *
	 * @param mixed $columns
	 *
	 * @return \Gleez\Database\Query The current object
	 */
	public function select($columns = NULL) {
		$this->reset();
		$this->type = Database::SELECT;
		$this->select = $columns;

		return $this;
	}

	public function selectArgs($columns = NULL) {
		$this->type = Database::SELECT;
		$this->select = array_merge($this->select, \func_get_args());

		return $this;
	}

	/**
	 * Set the table and columns for an insert.
	 *
	 * @param mixed $table Table name or array($table, $alias) or object [Optional]
	 * @param array $columns Column names [Optional]
	 * @return  \Gleez\Database\Query
	 */
	public function insert($table = NULL, array $columns = NULL) {
		$this->reset();

		if ($table) {
			// Set the initial table name
			$this->into($table);
		}

		if ($columns) {
			// Set the column names
			$this->columns($columns);
		}

		$this->type = Database::INSERT;
		return $this;
	}

	/**
	 * Activates the REPLACE mode
	 *
	 * @return  \Gleez\Database\Query  The current object
	 */
	public function replace() {
		$this->reset();
		$this->type = Database::REPLACE;

		return $this;
	}

	/**
	 * @param  mixed $index
	 *
	 * @return \Gleez\Database\Query  The current object
	 */
	public function update($index) {
		$this->reset();
		$this->type = Database::UPDATE;
		$this->into($index);

		return $this;
	}

	/**
	 * Activates the DELETE mode
	 *
	 * @param   mixed $table
	 *
	 * @return  \Gleez\Database\Query  The current object
	 */
	public function delete($table = NULL) {
		$this->reset();
		if ($table) {
			// Set the inital table name
			$this->into($table);
		}
		$this->type = Database::DELETE;

		return $this;
	}

	/**
	 * FROM clause (Sphinx-specific since it works with multiple indexes)
	 * func_get_args()-enabled
	 *
	 * @param  array  $tables  An array of indexes to use
	 *
	 * @return \Gleez\Database\Query  The current object
	 */
	public function from($tables) {
		$tables = \func_get_args();

		$this->from = array_merge($this->from, $tables);

		return $this;
	}

	/**
	 * Enables or disables selecting only unique columns using "SELECT DISTINCT"
	 *
	 * @param   boolean $value Enable or disable distinct columns [Optional]
	 * @return  \Gleez\Database\Query
	 */
	public function distinct($value = true) {
		// Add pending database call which is executed after query type is determined
		$this->distinct[] = (bool) $value;

		return $this;
	}

	/**
	 * Adds addition tables to "JOIN ...".
	 *
	 * @param   mixed   $table Column name or array($column, $alias) or object
	 * @param   string  $type  Join type (LEFT, RIGHT, INNER, etc) [Optional]
	 * @return  \Gleez\Database\Query
	 */
	public function join($table, $type = NULL) {
		if (!is_null($type)) {
			// Set the JOIN type
			$type = (string) $type;
		}

		//Store the index for reference the on conditions
		$this->last_join = $this->last_join + 1;

		// Set the table to JOIN on
		$this->join[$this->last_join] = array('table' => $table, 'type' => $type);

		return $this;
	}

	/**
	 * Adds "ON ..." conditions for the last created JOIN statement.
	 *
	 * @param   mixed   $c1  Column name or array($column, $alias) or object
	 * @param   string  $op  Logic operator
	 * @param   mixed   $c2  Column name or array($column, $alias) or object
	 * @return  \Gleez\Database\Query
	 *
	 * @throws  \InvalidArgumentException
	 */
	public function on($c1, $op, $c2) {
		if (!empty($this->using)) {
			throw new \InvalidArgumentException('JOIN ... ON ... cannot be combined with JOIN ... USING ...');
		}

		// Add pending database call which is executed after query type is determined
		$this->join[$this->last_join]['on'][] = array($c1, $op, $c2);

		return $this;
	}

	/**
	 * Adds "ON ..." conditions for the last created JOIN statement.
	 *
	 * @param   mixed   $c1  Column name or array($column, $alias) or object
	 * @param   string  $op  Logic operator
	 * @param   mixed   $c2  Column name or array($column, $alias) or object
	 *
	 * @return  \Gleez\Database\Query
	 *
	 * @throws  \InvalidArgumentException
	 */
	public function join_and($c1, $op, $c2) {
		if (!empty($this->using)) {
			throw new \InvalidArgumentException('JOIN ... ON ... cannot be combined with JOIN ... USING ...');
		}

		// Add pending database call which is executed after query type is determined
		$this->join[$this->last_join]['and'][] = array($c1, $op, $c2);

		return $this;
	}

	/**
	 * Adds "ON ..." conditions for the last created JOIN statement.
	 *
	 * @param   mixed   $c1  Column name or array($column, $alias) or object
	 * @param   string  $op  Logic operator
	 * @param   mixed   $c2  Column name or array($column, $alias) or object
	 *
	 * @return  \Gleez\Database\Query
	 */
	public function my_on($c1, $op, $c2) {
		return $this->join_and($c1, $op, $c2);
	}

	/**
	 * Adds "USING ..." conditions for the last created JOIN statement.
	 *
	 * @param   string  $columns  Column name
	 *
	 * @return  \Gleez\Database\Query
	 *
	 * @throws  \InvalidArgumentException
	 */
	public function using($columns) {
		if (!empty($this->join_on)) {
			throw new \InvalidArgumentException('JOIN ... ON ... cannot be combined with JOIN ... USING ...');
		}

		$columns = \func_get_args();

		$this->using = array_merge($this->using, $columns);

		return $this;
	}

	/**
	 * WHERE clause
	 *
	 * Examples:
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
	 *    $sq->where('column', '=', NULL)
	 *    // WHERE `column` IS NULL
	 *
	 *    $sq->where('column', '!=', NULL)
	 *    // WHERE `column` IS NOT NULL
	 *
	 *    $sq->where(NULL, 'NOT EXISTS', $subQuery)
	 *    // WHERE NOT EXISTS (SELECT * FROM ....)
	 *
	 * @param   string   $column    The column name
	 * @param   string   $operator  The operator to use
	 * @param   string   $value     The value to check against [Optional]
	 * @param   boolean  $or        If it should be prepended with OR (true) or AND (false) [Optional]
	 *
	 * @return  \Gleez\Database\Query  The current object
	 */
	public function where($column, $operator, $value = null, $or = false) {
		$this->where[] = array(
			'ext_operator' => $or ? 'OR' : 'AND',
			'column' => $column,
			'operator' => $operator,
			'value' => $value,
		);

		return $this;
	}

	public function and_where($column, $operator, $value = null) {
		return $this->where($column, $operator, $value);
	}

	public function or_where($column, $operator, $value = null) {
		return $this->where($column, $operator, $value, true);
	}

	/**
	 * OR WHERE - at this time (Sphinx 2.0.2) it's not available
	 *
	 * @param  string  $column    The column name
	 * @param  string  $operator  The operator to use
	 * @param  mixed   $value     The value to compare against [Optional]
	 *
	 * @return \Gleez\Database\Query  The current object
	 */
	public function orWhere($column, $operator, $value = null) {
		$this->where($column, $operator, $value, true);

		return $this;
	}

	public function where_open() {
		$this->where[] = array('ext_operator' => '(');

		return $this;
	}

	/**
	 * Opens a parenthesis prepended with AND (where necessary)
	 *
	 * @return  \Gleez\Database\Query  The current object
	 */
	public function whereOpen() {
		$this->where[] = array('ext_operator' => 'AND (');

		return $this;
	}

	/**
	 * Opens a new "AND WHERE (...)" grouping.
	 *
	 * @return  \Gleez\Database\Query
	 */
	public function and_where_open() {
		return $this->whereOpen();
	}

	/**
	 * Opens a new "OR WHERE (...)" grouping.
	 *
	 * @return  \Gleez\Database\Query
	 */
	public function or_where_open() {
		return $this->orWhereOpen();
	}

	/**
	 * Opens a parenthesis prepended with OR (where necessary)
	 *
	 * @return  \Gleez\Database\Query  The current object
	 */
	public function orWhereOpen() {
		$this->where[] = array('ext_operator' => 'OR (');

		return $this;
	}

	public function where_close() {
		return $this->whereClose();
	}

	/**
	 * Closes an open "AND WHERE (...)" grouping.
	 *
	 * @return  \Gleez\Database\Query
	 */
	public function and_where_close() {
		return $this->whereClose();
	}

	/**
	 * Closes an open "OR WHERE (...)" grouping.
	 *
	 * @return  \Gleez\Database\Query
	 */
	public function or_where_close() {
		return $this->whereClose();
	}

	/**
	 * Closes a parenthesis in WHERE
	 *
	 * @return  \Gleez\Database\Query  The current object
	 */
	public function whereClose() {
		$this->where[] = array('ext_operator' => ')');

		return $this;
	}

	/**
	 * GROUP BY clause
	 * Adds to the previously added columns
	 *
	 * @param  string  $column  A column to group by
	 *
	 * @return  \Gleez\Database\Query  The current object [Optional]
	 */
	public function groupBy($column) {
		$this->group_by[] = $column;

		return $this;
	}

	/**
	 * Creates a "GROUP BY ..." filter.
	 *
	 * @param   mixed   $columns  Column name or array($column, $alias) or object
	 * @param   ...
	 * @return  \Gleez\Database\Query
	 */
	public function group_by($columns) {
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
	 * @return  \Gleez\Database\Query  The current object
	 */
	public function withinGroupOrderBy($column, $direction = null) {
		$this->within_group_order_by[] = array('column' => $column, 'direction' => $direction);

		return $this;
	}

	/**
	 * Alias of and_having()
	 *
	 * @param   mixed   $column    Column name or array($column, $alias) or object
	 * @param   string  $operator  Logic operator
	 * @param   mixed   $value     Column value [Optional]
	 * @param   bool    $or        Use 'AND' or  'OR'? [Optional]
	 *
	 *
	 * @return  \Gleez\Database\Query
	 */
	public function having($column, $operator, $value = NULL, $or = FALSE) {
		// if ($value === null) {
		// 	$value = $operator;
		// 	$operator = '=';
		// }

		$this->having[] = array(
			'ext_operator' => $or ? 'OR' : 'AND',
			'column' => $column,
			'operator' => $operator,
			'value' => $value,
		);

		return $this;
	}

	/**
	 * Creates a new "AND HAVING" condition for the query.
	 *
	 * @param   mixed   $column    Column name or array($column, $alias) or object
	 * @param   string  $operator  Logic operator
	 * @param   mixed   $value     Column value [Optional]
	 *
	 * @return  \Gleez\Database\Query
	 */
	public function and_having($column, $operator, $value = NULL) {
		return $this->having($column, $operator, $value);
	}

	/**
	 * Creates a new "OR HAVING" condition for the query.
	 *
	 * @param   mixed   $column    Column name or array($column, $alias) or object
	 * @param   string  $operator  Logic operator
	 * @param   mixed   $value     Column value [Optional]
	 *
	 * @return  \Gleez\Database\Query
	 */
	public function or_having($column, $operator, $value = NULL) {
		return $this->having($column, $operator, $value, true);
	}

	/**
	 * Alias of and_having_open()
	 *
	 * @return  \Gleez\Database\Query
	 */
	public function having_open() {
		return $this->and_having_open();
	}

	/**
	 * Opens a new "AND HAVING (...)" grouping.
	 *
	 * @return  \Gleez\Database\Query
	 */
	public function and_having_open() {
		$this->having[] = array('ext_operator' => 'AND (');

		return $this;
	}

	/**
	 * Opens a new "OR HAVING (...)" grouping.
	 *
	 * @return  \Gleez\Database\Query
	 */
	public function or_having_open() {
		$this->having[] = array('ext_operator' => 'OR (');

		return $this;
	}

	/**
	 * Closes an open "AND HAVING (...)" grouping.
	 *
	 * @return  \Gleez\Database\Query
	 */
	public function having_close() {
		$this->having[] = array('ext_operator' => ')');

		return $this;
	}

	/**
	 * Closes an open "AND HAVING (...)" grouping.
	 *
	 * @return  \Gleez\Database\Query
	 */
	public function and_having_close() {
		return $this->having_close();
	}

	/**
	 * Closes an open "OR HAVING (...)" grouping.
	 *
	 * @return  \Gleez\Database\Query
	 */
	public function or_having_close() {
		return $this->having_close();
	}

	/**
	 * Applies sorting with "ORDER BY ..."
	 *
	 * @param   mixed   $column     Column name or array($column, $alias) or object
	 * @param   string  $direction  Direction of sorting [Optional]
	 *
	 * @return  \Gleez\Database\Query
	 */
	public function order_by($column, $direction = NULL) {
		return $this->orderBy($column, $direction);
	}

	/**
	 * ORDER BY clause
	 * Adds to the previously added columns
	 *
	 * @param   string  $column     The column to order on
	 * @param   string  $direction  The ordering direction (asc/desc) [Optional]
	 *
	 * @return  \Gleez\Database\Query  The current object
	 */
	public function orderBy($column, $direction = null) {
		$this->order_by[] = array('column' => $column, 'direction' => $direction);

		return $this;
	}

	/**
	 * LIMIT clause
	 * Supports also LIMIT offset, limit
	 *
	 * @param   int       $offset  Offset if $limit is specified, else limit
	 * @param   null|int  $limit   The limit to set, null for no limit [Optional]
	 *
	 * @return  \Gleez\Database\Query  The current object
	 */
	public function limit($offset, $limit = null) {
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
	 * @return  \Gleez\Database\Query  The current object
	 */
	public function offset($offset) {
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
	 * @return  \Gleez\Database\Query  The current object
	 */
	public function option($name, $value) {
		$this->options[] = array('name' => $name, 'value' => $value);

		return $this;
	}

	/**
	 * INTO clause
	 * Used by: INSERT, REPLACE
	 *
	 * @param  string  $index  The index to insert/replace into
	 *
	 * @return  \Gleez\Database\Query  The current object
	 */
	public function into($index) {
		$this->into = $index;

		return $this;
	}

	/**
	 * Set columns
	 * Used in: INSERT, REPLACE
	 * func_get_args()-enabled
	 *
	 * @param   mixed  $array  The array of columns [Optional]
	 *
	 * @return  \Gleez\Database\Query  The current object
	 */
	public function columns($array) {
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
	 * @param   mixed  $array  The array of values matching the columns from Query::columns
	 *
	 * @return  \Gleez\Database\Query  The current object
	 */
	public function values($array) {
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
	 * @param   string  $column  The column name
	 * @param   string  $value   The value
	 *
	 * @return  \Gleez\Database\Query  The current object
	 */
	public function value($column, $value) {
		if ($this->type == Database::INSERT || $this->type == Database::REPLACE) {
			$this->columns[] = $column;
			$this->values[0][] = $value;
		} else {
			$this->set[$column] = $value;
		}

		return $this;
	}

	/**
	 * Allows passing an array with the key as column and value as value
	 * Used in: INSERT, REPLACE, UPDATE
	 *
	 * @param   array  $array  Array of key-values
	 *
	 * @return  \Gleez\Database\Query  The current object
	 */
	public function set($array) {
		foreach ($array as $key => $item) {
			$this->value($key, $item);
		}

		return $this;
	}

	/**
	 * Return the table prefix defined in the current configuration.
	 *
	 * @param   object $db
	 *
	 * @return  string
	 */
	public function table_prefix($db) {
		return $db->table_prefix();
	}

	/**
	 * Returns results as associative arrays
	 *
	 * @return  \Gleez\Database\Query
	 */
	public function as_assoc() {
		$this->_as_object = FALSE;

		$this->_object_params = array();

		return $this;
	}

	/**
	 * Returns results as objects
	 *
	 * @param   bool|string  $class   Class name or TRUE for stdClass [Optional]
	 * @param   array        $params  Object parameters [Optional]
	 *
	 * @return  \Gleez\Database\Query
	 */
	public function as_object($class = true, array $params = array()) {
		$this->_as_object = $class;

		if (!empty($params)) {
			// Add object parameters
			$this->_object_params = $params;
		}

		return $this;
	}

	/**
	 * Set the value of a parameter in the query.
	 *
	 * @param   string   $param  Parameter key to replace
	 * @param   mixed    $value  Value to use
	 *
	 * @return  \Gleez\Database\Query
	 */
	public function param($param, $value) {
		// Add or overload a new parameter
		$this->_parameters[$param] = $value;

		return $this;
	}

	/**
	 * Bind a variable to a parameter in the query.
	 *
	 * @param   string  $param  parameter key to replace
	 * @param   mixed   $var    variable to use
	 *
	 * @return  \Gleez\Database\Query
	 */
	public function bind($param, &$var) {
		// Bind a value to a variable
		$this->_parameters[$param] = &$var;

		return $this;
	}

	/**
	 * Add multiple parameters to the query.
	 *
	 * @param   array  $params  list of parameters
	 * @return  \Gleez\Database\Query
	 */
	public function parameters(array $params) {
		// Merge the new parameters in
		$this->_parameters = $params + $this->_parameters;

		return $this;
	}

	/**
	 * Clears the existing query build for new query when using the same SQL instance.
	 *
	 * @return  \Gleez\Database\Query  The current object
	 */
	public function reset() {
		$this->select =
		$this->from =
		$this->distinct =
		$this->using =
		$this->join =
		$this->where =
		$this->match =
		$this->group_by =
		$this->within_group_order_by =
		$this->having =
		$this->order_by =
		$this->columns =
		$this->values =
		$this->set =
		$this->options =
		$this->_object_params =
		$this->_parameters = array();

		$this->offset =
		$this->limit =
		$this->into =
		$this->_query = null;

		$this->_as_object = false;
		$this->last_join = 0;

		return $this;
	}

}