<?php
/**
 * MySQLi database result
 *
 * See [Results](/database/results) for usage and examples.
 *
 * @package    Gleez\Database\Query\Result
 * @version    1.0.0
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Database_MySQLi_Result extends Database_Result {

	/**
	 * @var int
	 */
	protected $_internal_row = 0;

	/**
	 * Sets the total number of rows and stores the result locally
	 *
	 * @param  mixed   $result     Query result
	 * @param  string  $sql        SQL query
	 * @param  boolean $as_object  As object? [Optional]
	 * @param  array   $params     Object construct parameters [Optional]
	 */
	public function __construct($result, $sql, $as_object = FALSE, array $params = NULL)
	{
		parent::__construct($result, $sql, $as_object, $params);

		// Find the number of rows in the result
		$this->_total_rows = $result->num_rows;
	}

	/**
	 * Result destruction cleans up all open result sets
	 */
	public function __destruct()
	{
		if (is_resource($this->_result))
		{
			$this->_result->free();
		}
	}

	/**
	 * Seek the arbitrary pointer in table
	 *
	 * @param   integer  $offset  The field offset. Must be between zero and the total number of rows minus one
	 *
	 * @return  boolean
	 */
	public function seek($offset)
	{
		if ($this->offsetExists($offset) AND $this->_result->data_seek($offset))
		{
			// Set the current row to the offset
			$this->_current_row = $this->_internal_row = $offset;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Returns the current row of a result set
	 *
	 * @return  mixed
	 */
	public function current()
	{
		if ($this->_current_row !== $this->_internal_row AND ! $this->seek($this->_current_row))
		{
			return NULL;
		}

		// Increment internal row for optimization assuming rows are fetched in order
		$this->_internal_row++;

		if ($this->_as_object === TRUE)
		{
			// Return an stdClass
			return $this->_result->fetch_object();
		}
		elseif (is_string($this->_as_object))
		{
			// Return an object of given class name
			return $this->_result->fetch_object($this->_as_object, (array) $this->_object_params);
		}
		else
		{
			// Return an array of the row
			return $this->_result->fetch_assoc();
		}
	}
}
