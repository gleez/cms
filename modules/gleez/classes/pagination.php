<?php
/**
 * Pagination links generator
 *
 * @package    Gleez\Base
 * @version    1.0.1
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Pagination {

	/** Merged configuration settings
	 * @var array
	 */
	protected $config = array(
		'current_page'      => array('source' => 'query_string', 'key' => 'page'),
		'total_items'       => 0,
		'items_per_page'    => 10,
		'view'              => 'pagination/basic',
		'auto_hide'         => TRUE,
		'first_page_in_url' => FALSE,
		'uri'               => NULL
	);

	// Current page number
	protected $current_page;

	/**
	 * Total item count
	 * @var integer
	 */
	protected $total_items;

	/**
	 * How many items to show per page
	 * @var integer
	 */
	protected $items_per_page;

	/**
	 * Total page count
	 * @var integer
	 */
	protected $total_pages;

	/**
	 * Item offset for the first item displayed on the current page
	 * @var integer
	 */
	protected $current_first_item;

	/**
	 * Item offset for the last item displayed on the current page
	 * @var integer
	 */
	protected $current_last_item;

	/**
	 * Previous page number; FALSE if the current page is the first one
	 * @var mixed
	 */
	protected $previous_page;

	/**
	 * Next page number; FALSE if the current page is the last one
	 * @var mixed
	 */
	protected $next_page;

	/**
	 * First page number; FALSE if the current page is the first one
	 * @var mixed
	 */
	protected $first_page;

	/**
	 * Last page number; FALSE if the current page is the last one
	 * @var mixed
	 */
	protected $last_page;

	/**
	 * Query offset
	 * @var integer
	 */
	protected $_offset;

	/**
	 * Request object
	 * @var Request
	 */
	protected $_request;

	/**
	 * Route to use for URIs
	 * @var Route
	 */
	protected $_route;

	/**
	 * Parameters to use with Route to create URIs
	 * @var array
	 */
	protected $_route_params = array();

	/**
	 * Requested URI
	 * @var string
	 */
	protected $_uri = '';

	/**
	 * Creates a new Pagination object
	 *
	 * @param   array    $config   Configuration [Optional]
	 * @param   Request  $request  Request [Optional]
	 *
	 * @return  Pagination
	 */
	public static function factory(array $config = array(), Request $request = NULL)
	{
		return new self($config, $request);
	}

	/**
	 * Class constructor
	 *
	 * @param   array    $config   Configuration [Optional]
	 * @param   Request  $request  Request [Optional]
	 */
	public function __construct(array $config = array(), Request $request = NULL)
	{
		// Overwrite system defaults with application defaults
		$this->config = $this->config_group() + $this->config;

		// Assign Request
		if (is_null($request))
		{
			$request = Request::current();
		}

		$this->_request = $request;

		// Assign default Route
		$this->_route = $request->route();

		// Assign default route params
		$this->_route_params = $request->param();

		if (isset($config['uri']))
		{
			$this->_uri = $config['uri'];
		}

		// Pagination setup
		$this->setup($config);
	}

	/**
	 * Retrieves a pagination config group from the config file
	 *
	 * One config group can refer to another as its parent, which will be
	 * recursively loaded.
	 *
	 * @param   string  $group  Pagination config group [Optional]
	 *
	 * @return  array
	 *
	 * @uses    Config::load
	 */
	public function config_group($group = 'default')
	{
		// Load the pagination config file
		$config_file = Config::load('pagination');

		// Initialize the $config array
		$config['group'] = (string) $group;

		// Recursively load requested config groups
		while (isset($config['group']) AND $config_file->offsetExists($config['group']))
		{
			// Temporarily store config group name
			$group = $config['group'];
			unset($config['group']);

			// Add config group values, not overwriting existing keys
			$config += $config_file->$group;
		}

		// Get rid of possible stray config group names
		unset($config['group']);

		// Return the merged config group settings
		return $config;
	}

	/**
	 * Loads configuration settings into the object
	 * and (re)calculates pagination if needed.
	 *
	 * Allows you to update config settings after a Pagination object
	 * has been constructed.
	 *
	 * @param   array   $config  Configuration [Optional]
	 * @return  object  Pagination
	 */
	public function setup(array $config = array())
	{
		if (isset($config['group']))
		{
			// Recursively load requested config groups
			$config += $this->config_group($config['group']);
		}

		// Overwrite the current config settings
		$this->config = $config + $this->config;

		// Only (re)calculate pagination when needed
		if (is_null($this->current_page)
			OR isset($config['current_page'])
			OR isset($config['total_items'])
			OR isset($config['items_per_page']))
		{
			// Retrieve the current page number
			if ( ! empty($this->config['current_page']['page']))
			{
				// The current page number has been set manually
				$this->current_page = (int) $this->config['current_page']['page'];
			}
			else
			{
				$query_key = $this->config['current_page']['key'];

				switch ($this->config['current_page']['source'])
				{
					case 'query_string':
						$this->current_page = ( ! is_null($this->_request->query($query_key)))
							? (int) $this->_request->query($query_key)
							: 1;
						break;

					case 'cms':
					case 'route':
						$this->current_page = (int) $this->_request->param($query_key, 1);
						break;
				}
			}

			// Calculate and clean all pagination variables
			$this->total_items        = (int) max(0, $this->config['total_items']);
			$this->items_per_page     = (int) max(1, $this->config['items_per_page']);
			$this->total_pages        = (int) ceil($this->total_items / $this->items_per_page);
			$this->current_page       = (int) min(max(1, $this->current_page), max(1, $this->total_pages));
			$this->current_first_item = (int) min((($this->current_page - 1) * $this->items_per_page) + 1, $this->total_items);
			$this->current_last_item  = (int) min($this->current_first_item + $this->items_per_page - 1, $this->total_items);
			$this->previous_page      = ($this->current_page > 1) ? $this->current_page - 1 : FALSE;
			$this->next_page          = ($this->current_page < $this->total_pages) ? $this->current_page + 1 : FALSE;
			$this->first_page         = ($this->current_page === 1) ? FALSE : 1;
			$this->last_page          = ($this->current_page >= $this->total_pages) ? FALSE : $this->total_pages;
			$this->_offset            = (int) (($this->current_page - 1) * $this->items_per_page);
		}

		return $this;
	}

	/**
	 * Generates the full URL for a certain page
	 *
	 * @param   integer  $page  Page number [Optional
	 *
	 * @return  string
	 *
	 * @uses    URL::site
	 */
	public function url($page = 1)
	{
		// Clean the page number
		$page = max(1, (int) $page);

		// gleez cms pagination
		$pager = '/p'. $page;

		// No page number in URLs to first page
		if ($page === 1 AND ! $this->config['first_page_in_url'])
		{
			$page = NULL;
			$pager = NULL;
		}

		switch ($this->config['current_page']['source'])
		{
			case 'query_string':

				return URL::site($this->_route->uri($this->_route_params).
					$this->query(array($this->config['current_page']['key'] => $page)));

			case 'route':
				return URL::site($this->_route->uri(
						array_merge(
							$this->_route_params,
							array($this->config['current_page']['key'] => $page)
						)
					). $this->query());
			case 'cms':
				return URL::site($this->_uri . $pager . $this->query());
		}

		return '#';
	}

	/**
	 * Checks whether the given page number exists.
	 *
	 * @param   integer  $page  Page number
	 *
	 * @return  boolean
	 *
	 * @uses    Valid::digit
	 */
	public function valid_page($page)
	{
		// Page number has to be a clean integer
		if ( ! Valid::digit($page))
		{
			return FALSE;
		}

		return $page > 0 AND $page <= $this->total_pages;
	}

	/**
	 * Renders the pagination links.
	 *
	 * @param   mixed   string of the view to use, or a Kohana_View object
	 * @return  string  pagination output (HTML)
	 */
	public function render($view = NULL)
	{
		// Automatically hide pagination whenever it is superfluous
		if ($this->config['auto_hide'] === TRUE AND $this->total_pages <= 1)
		{
			return '';
		}

		if (is_null($view))
		{
			// Use the view from config
			$view = $this->config['view'];
		}

		if ( ! $view instanceof View)
		{
			// Load the view file
			$view = View::factory($view);
		}

		// Pass on the whole Pagination object
		return $view->set(get_object_vars($this))->set('page', $this)->render();
	}


	/**
	 * Route setter/getter
	 *
	 * [!!] Note: This doesn't change the initial Route
	 *
	 * @param	Request  $request  Request [Optional]
	 *
	 * @return  Request     Route if used as getter
	 * @return  Pagination  Chainable as setter
	 */
	public function request(Request $request = NULL)
	{
		if (is_null($request))
		{
			return $this->_request;
		}

		$this->_request = $request;

		return $this;
	}

	/**
	 * Route setter/getter
	 *
	 * @param	mixed  $route  String route name/ Route object [Optional]
	 *
	 * @return  Route       Route if used as getter
	 * @return  Pagination  Chainable as setter
	 *
	 * @uses    Route::get
	 */
	public function route($route = NULL)
	{
		if (is_null($route))
		{
			return $this->_route;
		}

		if ($route instanceof Route)
		{
			$this->_route = $route;
		}
		else if (is_string($route))
		{
			$this->_route = Route::get($route);
		}

		return $this;
	}

	/**
	 * Route parameters setter/getter
	 *
	 * @param   array  $route_params  Route parameters to set [Optional]
	 *
	 * @return  array       Route parameters if used as getter
	 * @return  Pagination  Chainable as setter
	 */
	public function route_params(array $route_params = NULL)
	{
		if (is_null($route_params))
		{
			return $this->_route_params;
		}

		$this->_route_params = $route_params;

		return $this;
	}

	/**
	 * HMVC URI setter/getter
	 *
	 * @param   string  $uri  Route uri to set [Optional]
	 *
	 * @return  string      Route uri if used as getter
	 * @return  Pagination  Chainable as setter
	 */
	public function uri($uri = NULL)
	{
		if (is_null($uri))
		{
			return $this->_uri;
		}

		$this->_uri = (string)$uri;

		return $this;
	}

	/**
	 * URL::query() replacement for Pagination use only
	 *
	 * @param   array  $params  Parameters to override [Optional]
	 *
	 * @return  string
	 */
	public function query(array $params = NULL)
	{
		if (is_null($params))
		{
			// Use only the current parameters
			$params = $this->_request->query();
		}
		else
		{
			// Merge the current and new parameters
			$params = array_merge($this->_request->query(), $params);
		}

		if (empty($params))
		{
			// No query parameters
			return '';
		}

		// Note: http_build_query returns an empty string for a params array with only NULL values
		$query = http_build_query($params, '', '&');

		// Don't prepend '?' to an empty string
		return ($query === '') ? '' : ('?'.$query);
	}

	/**
	 * Renders the pagination links
	 *
	 * @return  string  Pagination output (HTML)
	 *
	 * @uses    Gleez_Exception::handler
	 */
	public function __toString()
	{
		try
		{
			return $this->render();
		}
		catch(Exception $e)
		{
			Gleez_Exception::handler($e);
			return '';
		}
	}

	/**
	 * Returns a Pagination property
	 *
	 * @param   string  $key  Property name
	 *
	 * @return  mixed   Pagination property; NULL if not found
	 */
	public function __get($key)
	{
		return isset($this->$key) ? $this->$key : NULL;
	}

	/**
	 * Updates a single config setting, and recalculates pagination if needed
	 *
	 * @param   string  config key
	 * @param   mixed   config value
	 * @return  void
	 */
	public function __set($key, $value)
	{
		$this->setup(array($key => $value));
	}
}