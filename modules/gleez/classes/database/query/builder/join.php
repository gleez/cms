<?php

class Database_Query_Builder_Join extends Kohana_Database_Query_Builder_Join {

        // AND ...
	protected $_and = array();

	/**
	 * Adds a new AND condition for joining. custom join for AND clause coz ON converts value to column
	 *
	 * @param   mixed   column name or array($column, $alias) or object
	 * @param   string  logic operator
	 * @param   string  value
	 * @return  $this
	 */
	public function join_and($c1, $op, $c2)
	{
		if ( ! empty($this->_using))
		{
			throw new Gleez_Exception('JOIN ... AND ... cannot be combined with JOIN ... USING ...');
		}

		$this->_and[] = array($c1, $op, $c2);

		return $this;
	}
        
	/**
	 * Compile the SQL partial for a JOIN statement and return it.
	 *
	 * @param   object  Database instance
	 * @return  string
	 */
	public function compile(Database $db)
	{
		if ($this->_type)
		{
			$sql = strtoupper($this->_type).' JOIN';
		}
		else
		{
			$sql = 'JOIN';
		}

		// Quote the table name that is being joined
		$sql .= ' '.$db->quote_table($this->_table);

		if ( ! empty($this->_using))
		{
			// Quote and concat the columns
			$sql .= ' USING ('.implode(', ', array_map(array($db, 'quote_column'), $this->_using)).')';
		}
		else
		{
			$conditions = array();
			foreach ($this->_on as $condition)
			{
				// Split the condition
				list($c1, $op, $c2) = $condition;

				if ($op)
				{
					// Make the operator uppercase and spaced
					$op = ' '.strtoupper($op);
				}

				// Quote each of the columns used for the condition
				$conditions[] = $db->quote_column($c1).$op.' '.$db->quote_column($c2);
			}

			// Concat the conditions "... AND ..."
			$sql .= ' ON ('.implode(' AND ', $conditions).')';
                        
                        $and_conditions = array();
                        foreach ($this->_and as $icondition)
			{
                                // Split the condition
				list($c1, $op, $v1) = $icondition;

                                if ($op)
				{
					// Make the operator uppercase and spaced
					$op = ' '.strtoupper($op);
				}

                                // Quote each of the columns used for the condition. v1 is quote value not column
				$and_conditions[] = $db->quote_column($c1).$op.' '.$db->quote($v1); 
                        }

                        if( !empty($and_conditions) )
                        {
                                // Concat the conditions "... AND ..."
                                $sql .= ' AND '.implode(' AND ', $and_conditions).'';
                        }
		}

		return $sql;
	}

}
