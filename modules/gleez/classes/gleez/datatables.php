<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Gleez jQuery DataTables support
 * 
 * @package    Gleez\Datatables
 * @author     Sandeep Sangamreddi - Gleez
 * @version    1.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Gleez_Datatables {
	
	/**
	 * Sort Ascending
	 * 
	 * @var		string
	 */
	const SORT_ASC = 'ASC';
	 
	/**
	 * Sort Descending
	 * 
	 * @var		string
	 */
	const SORT_DESC = 'DESC';	
	
	/**
	 * Factory pattern
	 * 
	 * @static
	 * @access	public
	 * @param	mixed	string|object
	 * @param	mixed	NULL|string
	 * @return	Datatables
	 * @throws	Kohana_Exception
	 */
	public static function factory(ORM $object = NULL)
	{
		return new Datatables($object); 
	}
	
        /**
	 * Whether or not current request is via DataTables
	 * 
	 * @static
	 * @access	public
	 * @param	mixed	NULL|Request
	 * @return	bool
	 */
	public static function is_request(Request $request = NULL)
	{
		$request = ($request) ? $request : Request::current();
		
		return (bool) $request->query('sEcho');
	}
        
	/**
	 * Object to perform paginate operations on
	 * 
	 * @access	protected
	 * @var		object
	 */
	protected $_object;
	
	protected $_object_name;
	
	/**
	 * Columns
	 * 
	 * @access	protected
	 * @var		array
	 */
	protected $_columns = array();
	
	/**
	 * Search columns
	 * 
	 * @access	protected
	 * @var		array
	 */
	protected $_search_columns = array();
	
	/**
	 * Count for request
	 * 
	 * @access	protected
	 * @var		int
	 */
	protected $_count = 0;
	
	/**
	 * Total count
	 * 
	 * @access	protected
	 * @var		int
	 */
	protected $_count_total = 0;
	
	/**
	 * Result
	 * 
	 * @access	protected
	 * @var		NULL
	 */
	protected $_result;	
	
        /**
	 * Rows
	 * 
	 * @access	protected
	 * @var		array
	 */
	protected $_rows = array();		
	
	/**
	 * View
	 * 
	 * @access	protected
	 * @var		NULL|string
	 */
	protected $_view;	
	
	/**
	 * Request
	 * 
	 * @access	protected
	 * @var		NULL|Request
	 */
	protected $_request;
	
	/**
	 * Cached render
	 * 
	 * @access	protected
	 * @var		string
	 */
	protected $_render;
        
	/**
	 * Initialize
	 * 
	 * @access	public
	 * @param	object
	 * @return	void
	 */	
	public function __construct($object)
	{
		$this->_object = $object;
		$this->_object_name = $object->object_name();
                //$this->_columns = array_keys( $object->table_columns() );
	}

	/**
	 * Apply limit
	 * 
	 * @access	protected
	 * @param	int
	 * @return	void
	 */
	protected function _limit($start, $length)
	{
		$this->_object->offset($start)->limit($length);
	}
        
	/**
	 * Apply sort
	 * 
	 * @access	protected
	 * @param	string
	 * @return	void
	 */
	protected function _sort($column, $direction)
	{
		$this->_object->order_by($this->_object_name.'.'.$column, mysql_real_escape_string($direction));
	}
	
	/**
	 * Apply search query
	 * 
	 * @access	protected
	 * @param	string
	 * @param	string
	 * @return	void
	 */
	protected function _search($query)
	{
		// Use search columns if specified; otherwise, search across all columns
		$columns = ( ! empty($this->_search_columns)) ? $this->_search_columns : $this->_columns;
		

		if (count($columns) > 0)
		{
			$query = '%' . mysql_real_escape_string($query) . '%';
			
			$this->_object->where_open();

			foreach ($columns as $key => $column)
			{
				if ($key === 0)
				{
					$this->_object->where($this->_object_name.'.'.$column, 'like', $query);
				}
				else
				{
					$this->_object->or_where($this->_object_name.'.'.$column, 'like', $query);
				}
			}

			$this->_object->where_close();
		}		
	}
	
	/**
	 * Count
	 * 
	 * @access	protected
	 * @return	int
	 */
	protected function _count()
	{
		return count($this->_result);
	}	
	
	/**
	 * Count total
	 * 
	 * @access	protected
	 * @return	int
	 */
	protected function _count_total()
        {
                return $this->_object->reset(FALSE)->count_all();
        }
	
	/**
	 * Execute result on object
	 * 
	 * @access	protected
	 * @return	mixed
	 */
	protected function _execute()
	{
		return $this->_object->find_all();
	}
	
	/**
	 * Set limit
	 * 
	 * @access	public
	 * @param	int
	 * @param	int
	 * @return	$this
	 */
	public function limit($start, $length)
	{
		$this->_limit($start, $length);
		
		return $this;
	}
	
	/**
	 * Set sort order
	 * 
	 * @access	public
	 * @param	string
	 * @param	mixed	SORT_ASC|SORT_DESC
	 * @return	$this
	 * @throws	Kohana_Exception
	 */
	public function sort($column, $direction = self::SORT_ASC)
	{
		if ( ! in_array($direction, array(self::SORT_ASC, self::SORT_DESC)))
			throw new Kohana_Exception('Invalid sort order of `' . $direction . '`.');
		
		$this->_sort($column, $direction);
		
		return $this;
	}
	
	/**
	 * Search query
	 * 
	 * @access	public
	 * @param	mixed
	 * @return	$this
	 */
	public function search($query)
	{
		$this->_search($query);
		
		return $this;
	}
	
	/**
	 * Get count based on post operations
	 * 
	 * @access	public
	 * @return	int
	 */
	public function count()
	{
		return (int) $this->_count;
	}
	
	/**
	 * Get total count prior to operations
	 * 
	 * @access	public
	 * @return	int
	 */
	public function count_total()
	{
		return (int) $this->_count_total;
	}
	
	/**
	 * Set or get columns
	 * 
	 * @access	public
	 * @param	mixed	NULL|string
	 * @return	mixed	$this|string
	 */
	public function columns(array $columns = NULL)
	{
		if ($columns === NULL)
			return $this->_columns;
		
		$this->_columns = $columns;
		
		return $this;
	}	
	
	/**
	 * Set or get search columns
	 * 
	 * @access	public
	 * @param	mixed	NULL|string
	 * @return	mixed	$this|string
	 */
	public function search_columns(array $columns = NULL)
	{
		if ($columns === NULL)
			return $this->_search_columns;
		
		$this->_search_columns = $columns;
		
		return $this;
	}		
	
	/**
	 * Get result
	 * 
	 * @access	public
	 * @return	mixed
	 */
	public function result()
	{
		return $this->_result;
	}	
	
	/**
	 * Execute
	 * 
	 * @access	public
	 * @param	mixed	NULL|Request
	 * @return	$this
	 */
	public function execute()
	{
                $request = $this->request();

		if ( ! $request instanceof Request)
			throw new Kohana_Exception('DataTables expecting valid Request. If within a sub-request, have controller pass `$this->request`.');
		
		$columns = $this->columns();
		$this->_count_total = $this->_count_total();
		
		if ($request->query('iSortCol_0') !== NULL)
		{
			for ($i = 0; $i < intval($request->query('iSortingCols')); $i++)
			{
				$column = $columns[intval($request->query('iSortCol_' . $i))];
				
				$sort = 'Datatables::SORT_' . strtoupper($request->query('sSortDir_' . $i));
				
				if (defined($sort))
				{
					$this->sort($column, constant($sort));
				}
			}	
		}
		
		if ($request->query('iDisplayStart') !== NULL && $request->query('iDisplayLength') != '-1')
		{
			$start = $request->query('iDisplayStart');
			$length = $request->query('iDisplayLength');
			
			$this->limit($start, $length);
		}

		if ($request->query('sSearch'))
		{
			$this->search($request->query('sSearch'));
		}
		
		
                $this->_result = $this->_execute();
		
		$this->_count = $this->_count();
	
		// Count should always match total unless search is being applied
		$this->_count = ($request->query('sSearch')) ? $this->count() : $this->_count_total;
		
		return $this;
	}
        
        /**
	 * Set or get View file path
	 * 
	 * @access	public
	 * @param	mixed	NULL|string
	 * @return	mixed	$this|string
	 */
	public function view($path = NULL)
	{
		if ($path === NULL)
			return $this->_view;
		
		$this->_view = $path;
		
		return $this;
	}
        
	/**
	 * Set or get Request
	 * 
	 * @access	public
	 * @param	mixed	NULL|Request
	 * @return	mixed	$this|Request|NULL
	 */
	public function request(Request $request = NULL)
	{
		if ($request === NULL)
		{
			if ($this->_request instanceof Request)
				return $this->_request;
				
			return Request::current();
		}
			
		$this->_request = $request;
		
		return $this;
	}
	
	/**
	 * Add row to output
	 * 
	 * @access	public
	 * @param	array
	 * @return	$this
	 */
	public function add_row(array $row)
	{
		$this->_rows[] = $row;
		
		return $this;
	}
        
	/**
	 * Render
	 * 
	 * @access	public
	 * @return	string
	 */
	public function __toString()
	{
		return $this->render();
	}	
	
	/**
	 * Render
	 * 
	 * @access	public
	 * @return	string
	 */
	public function render()
	{
		if ($this->_render === NULL)
		{
			if ($this->_view)
			{
				View::factory($this->_view, array('datatables' => $this))->render();
			}
			
			$this->request()->response()->headers('content-type', 'application/json; charset=' . Kohana::$charset);
			
			$this->_render = json_encode(array
			(
				'sEcho' 		=> intval($this->request()->query('sEcho')),
				'iTotalRecords' 	=> $this->_count_total,
				'iTotalDisplayRecords' 	=> $this->_count,
				'aaData' 		=> $this->_rows
			));
		}
		
		return $this->_render;
	}        
}