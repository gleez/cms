<?php
/**
 * Gleez CMS (http://gleezcms.org)
 *
 * @link https://github.com/gleez/cms Canonical source repository
 * @copyright Copyright (c) 2011-2015 Gleez Technologies
 * @license http://gleezcms.org/license Gleez CMS License
 */

namespace Gleez\Database;

use Countable;
use Iterator;
use SeekableIterator;
use ArrayAccess;
use ReflectionClass;

/**
 * MySQLi Database Expression
 *
 * @package Gleez\Database
 * @version 2.1.0
 * @author  Gleez Team
 */
class Result implements Countable, Iterator, SeekableIterator, ArrayAccess
{

	// Executed SQL for this result
	protected $_query;

	// Raw result resource
	protected $_result;

	// Total number of rows and current row
	protected $_total_rows  = 0;
	protected $_current_row = 0;

	// Return rows as an object or associative array
	protected $_as_object;

	// Parameters for __construct when using object results
	protected $_object_params = NULL;

	/**
	 * @var int
	 */
	protected $_internal_row = 0;

	/**
	 * @var \ReflectionClass
	 */
	protected $_reflect_class = NULL;

	/**
	 * Sets the total number of rows and stores the result locally.
	 *
	 * @param   mixed   $result     query result
	 * @param   string  $sql        SQL query
	 * @param   mixed   $as_object
	 * @param   array   $params
	 */
	public function __construct($result, $sql, $as_object = FALSE, array $params = NULL)
	{
		// Store the result locally
		$this->_result = $result;

		// Store the SQL locally
		$this->_query = $sql;

		if (is_object($as_object))
		{
			// Get the object class name
			$as_object = get_class($as_object);
		}

		// Results as objects or associative arrays
		$this->_as_object = $as_object;

		if ($params)
		{
			// Object constructor params
			$this->_object_params = $params;
		}

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
	 * Get a cached database result from the current result iterator.
	 */
	public function cached()
	{
		//return new Database_Result_Cached($this->as_array(), $this->_query, $this->_as_object);
		throw new DatabaseException('Not Implemented');
	}

	/**
	 * Return all of the rows in the result as an array.
	 *
	 *     // Indexed array of all rows
	 *     $rows = $result->as_array();
	 *
	 *     // Associative array of rows by "id"
	 *     $rows = $result->as_array('id');
	 *
	 *     // Associative array of rows, "id" => "name"
	 *     $rows = $result->as_array('id', 'name');
	 *
	 * @param   string  $key    column for associative keys
	 * @param   string  $value  column for values
	 * @return  array
	 */
	public function as_array($key = NULL, $value = NULL)
	{
		$results = array();

		if ($key === NULL AND $value === NULL)
		{
			// Indexed rows

			foreach ($this as $row)
			{
				$results[] = $row;
			}
		}
		elseif ($key === NULL)
		{
			// Indexed columns

			if ($this->_as_object)
			{
				foreach ($this as $row)
				{
					$results[] = $row->$value;
				}
			}
			else
			{
				foreach ($this as $row)
				{
					$results[] = $row[$value];
				}
			}
		}
		elseif ($value === NULL)
		{
			// Associative rows

			if ($this->_as_object)
			{
				foreach ($this as $row)
				{
					$results[$row->$key] = $row;
				}
			}
			else
			{
				foreach ($this as $row)
				{
					$results[$row[$key]] = $row;
				}
			}
		}
		else
		{
			// Associative columns
			if ($this->_as_object)
			{
				foreach ($this as $row)
				{
					$results[$row->$key] = $row->$value;
				}
			}
			else
			{
				foreach ($this as $row)
				{
					$results[$row[$key]] = $row[$value];
				}
			}
		}

		$this->rewind();

		return $results;
	}

	public function each_as_array()
	{
		$results = array();

		foreach($this as $row)
		{
			//$results[] = $row->as_array();
			$results[] = $row;
		}
		return $results;
	}

	/**
	 * Return the named column from the current row.
	 *
	 *     // Get the "id" value
	 *     $id = $result->get('id');
	 *
	 * @param   string  $name     column to get
	 * @param   mixed   $default  default value if the column does not exist
	 * @return  mixed
	 */
	public function get($name, $default = NULL)
	{
		$row = $this->current();

		if ($this->_as_object)
		{
			if (isset($row->$name))
				return $row->$name;
		}
		else
		{
			if (isset($row[$name]))
				return $row[$name];
		}

		return $default;
	}

	/**
	 * Implements [Countable::count], returns the total number of rows.
	 *
	 *     echo count($result);
	 *
	 * @return  integer
	 */
	public function count()
	{
		return $this->_total_rows;
	}

	/**
	 * Implements [ArrayAccess::offsetExists], determines if row exists.
	 *
	 *     if (isset($result[10]))
	 *     {
	 *         // Row 10 exists
	 *     }
	 *
	 * @param   int     $offset
	 * @return  boolean
	 */
	public function offsetExists($offset)
	{
		return ($offset >= 0 AND $offset < $this->_total_rows);
	}

	/**
	 * Implements [ArrayAccess::offsetGet], gets a given row.
	 *
	 *     $row = $result[10];
	 *
	 * @param   int     $offset
	 * @return  mixed
	 */
	public function offsetGet($offset)
	{
		if ( ! $this->seek($offset))
		{
			return NULL;
		}

		return $this->current();
	}

	/**
	 * Implements [ArrayAccess::offsetSet], throws an error.
	 *
	 * [!!] You cannot modify a database result.
	 *
	 * @param   int     $offset
	 * @param   mixed   $value
	 * @return  void
	 * @throws  \Gleez\Database\DatabaseException
	 */
	final public function offsetSet($offset, $value)
	{
		throw new DatabaseException('Database results are read-only');
	}

	/**
	 * Implements [ArrayAccess::offsetUnset], throws an error.
	 *
	 * [!!] You cannot modify a database result.
	 *
	 * @param   int     $offset
	 * @return  void
	 * @throws  \Gleez\Database\DatabaseException
	 */
	final public function offsetUnset($offset)
	{
		throw new DatabaseException('Database results are read-only');
	}

	/**
	 * Implements [Iterator::key], returns the current row number.
	 *
	 *     echo key($result);
	 *
	 * @return  integer
	 */
	public function key()
	{
		return $this->_current_row;
	}

	/**
	 * Implements [Iterator::next], moves to the next row.
	 *
	 *     next($result);
	 *
	 * @return  $this
	 */
	public function next()
	{
		++$this->_current_row;
		return $this;
	}

	/**
	 * Implements [Iterator::prev], moves to the previous row.
	 *
	 *     prev($result);
	 *
	 * @return  $this
	 */
	public function prev()
	{
		$this->_current_row;
		return $this;
	}

	/**
	 * Implements [Iterator::rewind], sets the current row to zero.
	 *
	 *     rewind($result);
	 *
	 * @return  $this
	 */
	public function rewind()
	{
		$this->_current_row = 0;
		return $this;
	}

	/**
	 * Implements [Iterator::valid], checks if the current row exists.
	 *
	 * [!!] This method is only used internally.
	 *
	 * @return  boolean
	 */
	public function valid()
	{
		return $this->offsetExists($this->_current_row);
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

		if (defined('HHVM_VERSION'))
		{
			return $this->currentHHVM();
		}

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

	/**
	 * Returns the current row of a result set under HHVM
	 * The problem was fetch_object creates new instance of a given class,
	 * and attaches resulted key/value pairs after the class was constructed.
	 *
	 * @return  mixed
	 */
	private function currentHHVM()
	{
		if ($this->_as_object === TRUE OR is_string($this->_as_object))
		{
			if ($this->_reflect_class === NULL)
			{
				// Create reflection class of given classname or stdClass
				$this->_reflect_class = new ReflectionClass(is_string($this->_as_object) ? $this->_as_object : 'stdClass');
			}

			// Support ORM with loaded, when the class has __set and __construct if its ORM
			if($this->_reflect_class->hasMethod('__set') === TRUE && $this->_reflect_class->hasMethod('__construct') === TRUE)
			{
				// Get row as associated array
				$row = $this->_result->fetch_assoc();

				// Get new instance without constructing it
				$object = $this->_reflect_class->newInstanceWithoutConstructor();

				foreach ($row as $column => $value)
				{
					// Trigger the class setter
					$object->__set($column, $value);
				}

				// Construct the class with no parameters
				$object->__construct(NULL);

				return $object;
			}
			elseif (is_string($this->_as_object))
			{
				// Return an object of given class name
				return $this->_result->fetch_object($this->_as_object, (array) $this->_object_params);
			}
			else
			{
				// Return an stdClass
				return $this->_result->fetch_object();
			}
		}

		// Get row as associated array
		return $this->_result->fetch_assoc();
	}
}
