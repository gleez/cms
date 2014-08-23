<?php
/**
 * Gleez CMS (http://gleezcms.org)
 *
 * @link https://github.com/gleez/database Canonical source repository
 * @copyright Copyright (c) 2011-2014 Gleez Technologies
 * @license http://gleezcms.org/license Gleez CMS License
 */

use Gleez\Database\Database;
use Gleez\Database\Query;
use Gleez\Database\Expression;

/**
 * Gleez DB
 *
 * Provides a helpers to get Database related objects for [making queries](../database/query).
 *
 * Shortcut     | Returned Object
 * -------------|---------------
 * [`DB::query()`](#query)   | [Gleez\Database\Result]
 * [`DB::insert()`](#insert) | [Gleez\Database\Query]
 * [`DB::select()`](#select),<br />[`DB::select_array()`](#select_array) | [Database_Query_Builder_Select]
 * [`DB::update()`](#update) | [Database_Query_Builder_Update]
 * [`DB::delete()`](#delete) | [Database_Query_Builder_Delete]
 * [`DB::expr()`](#expr)     | [Database_Expression]
 *
 * You pass the same parameters to these functions as you pass to the objects they return.
 *
 * @package Gleez\Database
 * @version 2.1.1
 * @author Gleez Team
 */
class DB {

	protected static $_config = NULL;

	/**
	 * Avoid directly creating
	 */
	private function __construct() {}

	/**
	 * Create a new Query object.
	 *
	 * Example:<br>
	 * <code>
	 * // Create a new SELECT query
	 * $query = DB:query(\Gleez\Database\Database::SELECT, 'SELECT * FROM users');
	 *
	 * // Create a new DELETE query
	 * $query = DB::query(Database::DELETE, 'DELETE FROM users WHERE id = 5');
	 * </code>
	 *
	 * Specifying the type changes the returned result. When using
	 * `Database::SELECT`, a [Database_Query_Result] will be returned.
	 * `Database::INSERT` queries will return the insert id and number of rows.
	 * For all other queries, the number of affected rows is returned.
	 *
	 * @param   integer  $type  type: Database::SELECT, Database::UPDATE, etc
	 * @param   string   $sql   SQL statement
	 *
	 * @return  \Gleez\Database\Result
	 */
	public static function query($type, $sql)
	{
		return Database::instance()->query($type, $sql);
	}

	/**
	 * Create a new Query object
	 *
	 * Each argument will be treated as a column.
	 * To generate a `foo AS bar` alias, use an array.
	 *
	 * Example:<br>
	 * <code>
	 * // SELECT id, username
	 * $query = DB::select('id', 'username');
	 * // SELECT id AS user_id
	 * $query = DB::select(array('id', 'user_id'));
	 * </code>
	 *
	 * @param string $columns Query string [Optional]
	 * @return \Gleez\Database\Query
	 */
	public static function select($columns = NULL)
	{
		$query = new Query('select', NULL);
		return $query->select(\func_get_args());
	}

	/**
	 * Create a new Query object from an array of columns.
	 *
	 * Example:<br>
	 * <code>
	 * // SELECT id, username
	 * $query = DB::select_array(array('id', 'username'));
	 * </code>
	 *
	 * @param   array   $columns  columns to select
	 * @return  \Gleez\Database\Query
	 */
	public static function select_array(array $columns = NULL)
	{
		$query = new Query('select', NULL);
		return $query->select($columns);
	}

	/**
	 * Create a new Query object.
	 *
	 * Example::<br>
	 * <code>
	 * // INSERT INTO users (id, username)
	 * $query = DB::insert('users', array('id', 'username'));
	 * </code>
	 *
	 * @param   string  $table    table to insert into
	 * @param   array   $columns  list of column names or array($column, $alias) or object
	 *
	 * @return  \Gleez\Database\Query
	 */
	public static function insert($table = NULL, array $columns = NULL)
	{
		$query = new Query('insert', NULL);
		return $query->insert($table, $columns);
	}

	/**
	 * Create a new Query object.
	 *
	 * Example:<br>
	 * <code>
	 * // UPDATE users
	 * $query = DB::update('users');
	 * </code>
	 *
	 * @param   string  $table  table to update
	 * @return  \Gleez\Database\Query
	 */
	public static function update($table = NULL)
	{
		$query = new Query('update', NULL);
		return $query->update($table);
	}

	/**
	 * Create a new Query object.
	 *
	 * Example:<br>
	 * <code>
	 * // DELETE FROM users
	 * $query = DB::delete('users');
	 * </code>
	 *
	 * @param   string  $table  Table to delete from [Optional]
	 * @return  \Gleez\Database\Query
	 */
	public static function delete($table = NULL)
	{
		$query = new Query('delete', NULL);
		return $query->delete($table);
	}

	/**
	 * Create a new Expression object which is not escaped.
	 *
	 * An expression is the only way to use SQL functions within query builders.
	 * Example:<br>
	 * <code>
	 * $expression = DB::expr('COUNT(users.id)');
	 * $query = DB::update('users')->set(array('login_count' => DB::expr('login_count + 1')))->where('id', '=', $id);
	 * $users = ORM::factory('user')->where(DB::expr("BINARY `hash`"), '=', $hash)->find();
	 * <code>
	 *
	 * @param   string  $string      Expression
	 * @param   array   $parameters  Parameters [Optional]
	 *
	 * @return  \Gleez\Database\Expression
	 */
	public static function expr($string, $parameters = array())
	{
		return new Expression($string);
	}

	public static function version()
	{
		return Database::instance(NULL, self::$_config)->version();
	}
}
