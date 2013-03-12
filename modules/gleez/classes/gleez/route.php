<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Remove the default action from the URI
 */
class Gleez_Route extends Kohana_Route {
	
	/**
	 * @var  string  route name
	 */
	protected $_name = NULL;

	/**
	 * @var string route SUBDOMAIN
	 */
	protected $_subdomain ;

	/**
	 * @var  array  route filters
	 */
	protected $_filters = array();
	
	/**
	 * Add Sub-domains and filters support
	 * 
	 *  @see https://github.com/jeanmask/subdomain
	 *  Thanks to jean@webmais.net.br
	 */
	public function __construct($uri = NULL, $regex = NULL)
	{
		if ($uri === NULL)
		{
			// Assume the route is from cache
			return;
		}

		if ( ! empty($uri))
		{
			$this->_uri = $uri;
		}

		if ( ! empty($regex))
		{
			$this->_regex = $regex;
		}

		// Store the compiled regex locally
		$this->_route_regex = Route::compile($uri, $regex);
	}

	/**
	 * Saves or loads the route cache. If your routes will remain the same for
	 * a long period of time, use this to reload the routes from the cache
	 * rather than redefining them on every page load.
	 *
	 *     if ( ! Route::cache())
	 *     {
	 *         // Set routes here
	 *         Route::cache(TRUE);
	 *     }
	 *
	 * @param   boolean $save   cache the current routes
	 * @param   boolean $append append, rather than replace, cached routes when loading
	 * @return  void    when saving routes
	 * @return  boolean when loading routes
	 * @uses    Kohana::cache
	 */
	public static function cache($save = FALSE, $append = FALSE)
	{
		if ($save === TRUE)
		{
			// Cache all defined routes
			Kohana::cache('Route::cache()', Route::$_routes);
		}
		else
		{
			if ($routes = Kohana::cache('Route::cache()'))
			{
				if ($append)
				{
					// Append cached routes
					Route::$_routes += $routes;
				}
				else
				{
					// Replace existing routes
					Route::$_routes = $routes;
				}

				// Routes were cached
				return Route::$cache = TRUE;
			}
			else
			{
				// Routes were not cached
				return Route::$cache = FALSE;
			}
		}
	}

		/**
	 * Filters to be run before route parameters are returned:
	 *
	 *     $route->filter(
	 *         function(Route $route, $params)
	 *         {
	 *             if ($params AND $params['controller'] === 'welcome')
	 *             {
	 *                 $params['controller'] = 'home';
	 *             }
	 *
	 *             return $params;
	 *         }
	 *     );
	 *
	 * To prevent a route from matching, return `FALSE`. To replace the route
	 * parameters, return an array.
	 *
	 * [!!] Default parameters are added before filters are called!
	 *
	 * @throws  Kohana_Exception
	 * @param   array   $callback   callback string, array, or closure
	 * @return  $this
	 */
	public function filter($callback)
	{
		if ( ! is_callable($callback))
		{
			throw new Kohana_Exception('Invalid Route::callback specified');
		}

		$this->_filters[] = $callback;

		return $this;
	}
	
	/**
	 * Fix for pagination on lambda routes; add subdomain support
	 * 
	 * Tests if the route matches a given URI. A successful match will return
	 * all of the routed parameters as an array. A failed match will return
	 * boolean FALSE.
	 *
	 *     // Params: controller = users, action = edit, id = 10
	 *     $params = $route->matches('users/edit/10');
	 *
	 * This method should almost always be used within an if/else block:
	 *
	 *     if ($params = $route->matches($uri))
	 *     {
	 *         // Parse the parameters
	 *     }
	 *
	 * @param   string  URI to match
	 * @return  array   on success
	 * @return  FALSE   on failure
	 */
	public function matches($uri, $subdomain = NULL)
	{
		if ( ! preg_match($this->_route_regex, $uri, $matches))
			return FALSE;

		$params = array();
		foreach ($matches as $key => $value)
		{
			if (is_int($key))
			{
				// Skip all unnamed keys
				continue;
			}

			// Set the value for all matched keys
			$params[$key] = $value;
		}

		foreach ($this->_defaults as $key => $value)
		{
			if ( ! isset($params[$key]) OR $params[$key] === '')
			{
				// Set default values for any key that was not matched
				$params[$key] = $value;
			}
		}

		if ($this->_filters)
		{
			foreach ($this->_filters as $callback)
			{
				// Execute the filter
				$return = call_user_func($callback, $uri, $this, $params);

				if ($return === FALSE)
				{
					// Filter has aborted the match
					return FALSE;
				}
				elseif (is_array($return))
				{
					// Filter has modified the parameters
					$params = $return;
					
					// fix for pagination on lambda routes
					$this->_uri = Arr::get($params, 'uri', '');
				}
			}
		}

		return $params;
	}
    
} // End Route
