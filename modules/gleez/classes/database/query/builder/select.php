<?php

class Database_Query_Builder_Select extends Kohana_Database_Query_Builder_Select {

	/**
	 * Adds "AND ..." conditions for the last created JOIN statement.
	 *
	 * @param   mixed   column name or array($column, $alias) or object
	 * @param   string  logic operator
	 * @param   mixed   column name or array($column, $alias) or object
	 * @return  $this
	 */
	public function join_and($c1, $op, $c2)
	{
		$this->_last_join->join_and($c1, $op, $c2);

		return $this;
	}
}
