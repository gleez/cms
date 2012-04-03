<?php defined('SYSPATH') or die('No direct script access.');
/**
 *  URL functions.
 *      
 * @package	Gleez
 * @category	URL
 * @author	Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2012 Gleez Technologies
 * @license	http://gleezcms.org/license
 */
class Gleez_URL extends Kohana_URL {
        
	/**
	 * Get the canonical URL.
	 *
	 * @access	public
	 * @param	mixed	the request object or string url
	 * @param	object	the pagination object
	 * @param	array	the query string parameters
	 * @param	mixed	the route protocol
	 * @return	string
	 */
	public static function canonical($url, $pagination = NULL, $qstring = NULL, $protocol = TRUE)
	{
		if($url instanceof Request)
		{
			//Message::debug( Debug::vars($url->uri()) );
			return URL::site($url->uri(), $protocol);
		}
	
		if($pagination AND $pagination->current_page > 1)
		{
			$url .= '/p' . $pagination->current_page;
		}
		
		return URL::site($url, $protocol).URL::query($qstring);
	}

	/**
	 * Test whether a URL is absolute.
	 *
	 * @access	public
	 * @param	string	the URL to test
	 * @return	boolean
	 */
	public static function is_absolute($url)
	{
		return (strpos($url, '://') !== FALSE);
	}

	/**
	 * Test whether a URL is the home page.
	 *
	 * @access	public
	 * @param	string	the home route name
	 * @param	array	the home route parameters (controller, action, etc)
	 * @return	boolean
	 */
	public static function is_homepage($home_route_name = NULL, $home_route_params = NULL)
	{
		// Process the current URL
		$request = Request::initial();
		$name = Route::name($request->route);
                
		$params = $request->param();
		$params['action'] = $request->action();
		$params['controller'] = $request->controller();
                
		$current = URL::canonical($name, $params);

		// Process the home URL
		if (empty($home_route_name))
		{
			$home_route_name = 'default';
		}
		if (empty($home_route_params))
		{
			$home_route_params = array('controller' => 'home');
		}
		$home = URL::canonical($home_route_name, $home_route_params);
		return ($current === $home);
	}
        
	/**
	 * create links to sort a column. Set $reverse to true to set asc as desc and vice versa.
	 *
	 * @access public
	 * @static
	 * @param string  $col
	 * @param bool    $reverse. (default: false)
	 * @return void
	 */
	public static function sortAnchor($col, $reverse = false)
	{
		$string = "";
		$orders = array('asc', 'desc');

		foreach ($orders as $order) {
			$class = "";
			$anchor_string = ($order == 'asc') ? "&and;" : "&or;";

			if ($reverse) {
				$order = ($order == 'asc') ? 'desc' : 'asc';
			}

			if (Arr::get($_GET, 'sort') == $col && Arr::get($_GET, 'order') == $order) {
				$class = "active";
			}

			$query = URL::query(array('sort' => $col, 'order' => $order));


			$string .= Html::anchor(Request::current()->uri() .  $query, $anchor_string, array('class' => $class . ' sort'));
		}

		return $string;
	}
	
	/**
	 * Merges the current GET parameters with an array of new or overloaded
	 * parameters and returns the resulting query string.
	 *
	 * Customized to sort the query string parameters and remove parameters
	 * without values.
	 *
	 *		// Returns "?sort=title&limit=10" combined with any existing GET values
	 *		$query = URL::query(array('sort' => 'title', 'limit' => 10));
	 *
	 * Typically you would use this when you are sorting query results,
	 * or something similar.
	 *
	 * [!!] Parameters with a NULL value are left out.
	 *
	 * @access	public
	 * @param	array	array of GET parameters
	 * @return	string
	 */
	public static function query1111(array $params = NULL, $use_get = TRUE)
	{
		if ($use_get)
		{
			if ($params === NULL)
			{
				// Use only the current parameters
				$params = $_GET;
			}
			else
			{
				// Merge the current and new parameters
				$params = array_merge($_GET, $params);
			}
		}

		if (empty($params))
		{
			// No query parameters
			return '';
		}

		$qstring = '';
		if ($params)
		{
			// Sort the query string parameters
			ksort($params);
			foreach ($params as $name => $value)
			{
				// Remove query string parameters without values
				if (empty($value))
				{
					unset($params[$name]);
				}
			}
			$qstring = http_build_query($params);
		}

		// Don't prepend '?' to an empty string
		return (empty($qstring)) ? '' : ('?'.$qstring);
	}
        
}