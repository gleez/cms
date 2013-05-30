<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * @package    Gleez\Route
 * @version    1.0.0
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
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
	 * @return  boolean FALSE on failure
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

	/**
	 * Create a URL from a route name
	 *
	 * This is a shortcut for:<br>
	 * <code>
	 *   URL::site(Route::get($name)->uri($params), $protocol);
	 * </code>
	 *
	 * @param   string  $name      Route name
	 * @param   array   $params    URI parameters [Optional]
	 * @param   mixed   $protocol  Protocol string or boolean, adds protocol and domain [Optional]
	 * @return  string
	 *
	 * @uses    URL::site
	 */
	public static function url($name, array $params = NULL, $protocol = NULL)
	{
		$route = Route::get($name);

		// Create a URI with the route and convert it to a URL
		if ($route->is_external())
		{
			return $route->uri($params);
		}

		return URL::site($route->uri($params), $protocol);
	}

	/**
	 * Generates a URI for the current route based on the parameters given.
	 *
	 *     // Using the "default" route: "users/profile/10"
	 *     $route->uri(array(
	 *         'controller' => 'users',
	 *         'action'     => 'profile',
	 *         'id'         => '10'
	 *     ));
	 *
	 * @param   array   $params URI parameters
	 * @return  string
	 * @throws  Kohana_Exception
	 * @uses    Route::REGEX_Key
	 */
	public function uri(array $params = NULL)
	{
		// Encode all params
		if (is_array($params))
		{
			//$params = array_map('urlencode', $params);
		}
		
		// Start with the routed URI
		$uri = $this->_uri;

		if (strpos($uri, '<') === FALSE AND strpos($uri, '(') === FALSE)
		{
			// This is a static route, no need to replace anything

			if ( ! $this->is_external())
				return $uri;

			// If the localhost setting does not have a protocol
			if (strpos($this->_defaults['host'], '://') === FALSE)
			{
				// Use the default defined protocol
				$params['host'] = Route::$default_protocol.$this->_defaults['host'];
			}
			else
			{
				// Use the supplied host with protocol
				$params['host'] = $this->_defaults['host'];
			}

			// Compile the final uri and return it
			return rtrim($params['host'], '/').'/'.$uri;
		}

		while (preg_match('#\([^()]++\)#', $uri, $match))
		{
			// Search for the matched value
			$search = $match[0];

			// Remove the parenthesis from the match as the replace
			$replace = substr($match[0], 1, -1);

			while (preg_match('#'.Route::REGEX_KEY.'#', $replace, $match))
			{
				list($key, $param) = $match;

				if (isset($params[$param]))
				{
					// Replace the key with the parameter value
					$replace = str_replace($key, $params[$param], $replace);
				}
				else
				{
					// This group has missing parameters
					$replace = '';
					break;
				}
			}

			// Replace the group in the URI
			$uri = str_replace($search, $replace, $uri);
		}

		while (preg_match('#'.Route::REGEX_KEY.'#', $uri, $match))
		{
			list($key, $param) = $match;

			if ( ! isset($params[$param]))
			{
				// Look for a default
				if (isset($this->_defaults[$param]))
				{
					$params[$param] = $this->_defaults[$param];
				}
				else
				{
					// Ungrouped parameters are required
					throw new Kohana_Exception('Required route parameter not passed: :param', array(
						':param' => $param,
					));
			}
			}

			$uri = str_replace($key, $params[$param], $uri);
		}

		// Trim all extra slashes from the URI
		$uri = preg_replace('#//+#', '/', rtrim($uri, '/'));

		if ($this->is_external())
		{
			// Need to add the host to the URI
			$host = $this->_defaults['host'];

			if (strpos($host, '://') === FALSE)
			{
				// Use the default defined protocol
				$host = Route::$default_protocol.$host;
			}

			// Clean up the host and prepend it to the URI
			$uri = rtrim($host, '/').'/'.$uri;
		}

		return $uri;
	}
}
