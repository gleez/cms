<?php

use Gleez\Database\Database;
use Gleez\Database\Query;
use Gleez\Database\Expression;
/**
 * A faux database connection for doing dry run migrations
 */
class Migration_Database extends \Gleez\Database\Driver_MySQLi {

	/**
	 * Creates a disposable instance of the faux connection
	 *
	 * @param  string $db_group The database group to use
	 * @param  array  $config   Config for the underlying DB connection
	 * @return Minion_Migration_Database
	 */
	public static function faux_instance($db_group = NULL, array $config = NULL)
	{
		if ($config === NULL)
		{
			if ($db_group === NULL)
			{
				$db_group = Database::$default;
			}

			$config = Config::get('database.'.$db_group);
		}

		return new Migration_Database('__minion_faux', $config);
	}

	/**
	 * The query stack used to store queries
	 * @var array
	 */
	protected $_queries = array();

	/**
	 * Gets the stack of queries that have been executed
	 *
	 * @return array
	 */
	public function get_query_stack()
	{
		return $this->_queries;
	}

	/**
	 * Resets the query stack to an empty state and returns the queries
	 *
	 * @return array Array of SQL queries that would've been executed
	 */
	public function reset_query_stack()
	{
		$queries = $this->_queries;

		$this->_queries = array();

		return $queries;
	}

	/**
	 * Appears to allow calling script to execute an SQL query, but merely logs
	 * it and returns NULL
	 *
	 * @return NULL
	 */
	public function query($type, $sql, $as_object = FALSE, array $params = NULL)
	{
		$this->_queries[] = $sql;

		return NULL;
	}

}