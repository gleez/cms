<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * @package    Gleez\Route
 * @version    1.0
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Gleez_Route extends Kohana_Route {

	/**
	 * Route name
	 * @var string
	 */
	protected $_name = NULL;

	/**
	 * Route filters
	 * @var array
	 */
	protected $_filters = array();

	/**
	 * Creates a new route
	 *
	 * Routes should always be created with [Route::set]
	 * or they will not be properly stored.
	 *
	 * Example:<br>
	 * <code>
	 *	$route = new Route($uri, $regex);
	 * </code>
	 *
	 * @param  string  $uri    Route URI pattern or lambda/callback function [Optional]
	 * @param  array   $regex  Key patterns [Optional]
	 */
	public function __construct($uri = NULL, array $regex = NULL)
	{
		if (is_null($uri))
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
	 * Saves or loads the route cache
	 *
	 * If your routes will remain the same for a long period of time,
	 * use this to reload the routes from the cache rather than redefining
	 * them on every page load.
	 *
	 * Set routes:<br>
	 * <code>
	 *	if ( ! Route::cache())
	 * 	{
	 *		Route::cache(TRUE);
	 *	}
	 * </code>
	 *
	 * @param   boolean  $save    Cache the current routes [Optional]
	 * @param   boolean  $append  Append, rather than replace, cached routes when loading [Optional]
	 * @return  void     When saving routes
	 * @return  boolean  When loading routes
	 *
	 * @uses    Kohana::cache
	 * @uses    Arr::merge
	 */
	public static function cache($save = FALSE, $append = FALSE)
	{
		if ($save)
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
					Route::$_routes = Arr::merge(Route::$_routes, $routes);
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
	 * Route filters
	 *
	 * Filters to be run before route parameters are returned.
	 *
	 * Exmaple:
	 *	$route->filter(
	 *		function(Route $route, $params)
	 *		{
	 *			if ($params AND $params['controller'] === 'welcome')
	 *			{
	 *				$params['controller'] = 'home';
	 *			}
	 *
	 *			return $params;
	 *		}
	 *	);
	 *
	 * To prevent a route from matching, return `FALSE`.
	 * To replace the route parameters, return an array.
	 *
	 * [!!] Default parameters are added before filters are called!
	 *
	 * @param   array   $callback  Callback string, array, or closure
	 * @return  Route
	 * @throws  Kohana_Exception
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
	 * Fix for pagination on lambda routes
	 *
	 * Tests if the route matches a given URI. A successful match will return
	 * all of the routed parameters as an array. A failed match will return
	 * boolean FALSE.
	 *
	 * // Params: controller = users, action = edit, id = 10<br>
	 * <code>
	 *	$params = $route->matches('users/edit/10');
	 * </code>
	 *
	 * This method should almost always be used within an if/else block:<br>
	 * <code>
	 *	if ($params = $route->matches($uri))
	 *	{
	 *		// Parse the parameters
	 *	}
	 * </code>
	 *
	 * @param   string  $uri  URI to match
	 * @return  array   On success
	 * @return  FALSE   On failure
	 *
	 * @uses    Arr::get
	 */
	public function matches($uri)
	{
		if ( ! preg_match($this->_route_regex, $uri, $matches))
		{
			return FALSE;
		}

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

}
