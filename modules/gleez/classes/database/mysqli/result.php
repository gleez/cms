<?php
/**
 * MySQLi database result
 *
 * See [Results](/database/results) for usage and examples.
 *
 * @package    Gleez\Database\Query\Result
 * @version    1.0.0
 * @author     Sandeep Sangamreddi - Gleez
 * @author     Sergey Yakovlev - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Database_MySQLi_Result extends Database_Result {

	protected $_internal_row = 0;

	/**
	 * Sets the total number of rows and stores the result locally
	 *
	 * @param  mixed   $result     Query result
	 * @param  string  $sql        SQL query
	 * @param  boolean $as_object  As object? [Optional]
	 * @param  array   $params     Object construct parameters [Optional]
	 *
	 * @link   http://php.net/manual/en/mysqli-result.num-rows.php mysqli_num_rows()
	 */
	public function __construct($result, $sql, $as_object = FALSE, array $params = NULL)
	{
		parent::__construct($result, $sql, $as_object, $params);

		// Find the number of rows in the result
		$this->_total_rows = mysqli_num_rows($result);
	}

	/**
	 * Result destruction cleans up all open result sets
	 *
	 * @link http://php.net/manual/en/mysqli-result.free.php mysqli_free_result()
	 */
	public function __destruct()
	{
		if (is_resource($this->_result))
		{
			mysqli_free_result($this->_result);
		}
	}

	/**
	 * Seek the arbitrary pointer in table
	 *
	 * @param   integer  $offset  The field offset. Must be between zero and the total number of rows minus one
	 * @return  boolean
	 *
	 * @link http://php.net/manual/en/mysqli-result.data-seek.php mysqli_data_seek()
	 */
	public function seek($offset)
	{
		if ($this->offsetExists($offset) AND mysqli_data_seek($this->_result, $offset))
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
	 *
	 * @link    http://php.net/manual/en/mysqli-result.fetch-object.php mysqli_fetch_object()
	 * @link    http://php.net/manual/en/mysqli-result.fetch-assoc.php mysqli_fetch_assoc()
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
			return mysqli_fetch_object($this->_result);
		}
		elseif (is_string($this->_as_object))
		{
			// Return an object of given class name
			return mysqli_fetch_object($this->_result, $this->_as_object, (is_null($this->_object_params) ? array() : $this->_object_params));
		}
		else
		{
			// Return an array of the row
			return mysqli_fetch_assoc($this->_result);
		}
	}
}