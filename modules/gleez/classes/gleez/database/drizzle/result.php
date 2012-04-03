<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Drizzle database result.   See [Results](/database/results) for usage and examples.
 *
 * @package    	Gleez/Database
 * @category   	Query/Result
 * @author	Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2012 Gleez Technologies
 * @license	http://gleezcms.org/license
 */
class Gleez_Database_Drizzle_Result extends Database_Result {

	protected $_internal_row = 0;

	public function __construct($result, $sql, $as_object = FALSE, array $params = NULL)
	{
		parent::__construct($result, $sql, $as_object, $params);

		// Find the number of rows in the result
		$this->_total_rows = $result->rowCount();
		//$this->_total_rows = drizzle_result_row_count($result);
	}

	public function __destruct()
	{
		if ($this->_result instanceof DrizzleResult)
		{
			//$this->_result->free();
			// free result set
			//drizzle_result_free($this->_result);
		}
	}

	public function seek($offset)
	{
		if ($this->offsetExists($offset) AND $this->_result->rowSeek($offset))
		{
			// Set the current row to the offset
			$this->_current_row = $this->_internal_row = $offset;

			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	public function current()
	{
		if ($this->_current_row !== $this->_internal_row AND ! $this->seek($this->_current_row))
			return NULL;

		// Increment internal row for optimization assuming rows are fetched in order
		$this->_internal_row++;

		if ($this->_as_object === TRUE)
		{
			// Return an stdClass
			return drizzle_query_result($this->_result);
		}
		elseif (is_string($this->_as_object))
		{
			// Return an object of given class name
			return drizzle_query_result($this->_result, $this->_as_object, $this->_object_params);
		}
		else
		{
			// Return an array of the row
			return drizzle_query_result($this->_result);
		}
	}

} // End Database_Drizzle_Result_Select
