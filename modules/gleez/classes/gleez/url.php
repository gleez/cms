<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * URL functions
 *
 * @package    Gleez\Helpers\URL
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Gleez_URL extends Kohana_URL {

	/**
	 * Get the canonical URL
	 *
	 * @param   mixed   $url         The request object or string URL
	 * @param   object  $pagination  The pagination object [Optional]
	 * @param   array   $qstring     The query string parameters [Optional]
	 * @param   mixed   $protocol    The route protocol [Optional]
	 * @return  string
	 *
	 * @uses    Request::uri
	 */
	public static function canonical($url, $pagination = NULL, $qstring = NULL, $protocol = TRUE)
	{
		if ($url instanceof Request)
		{
			return URL::site($url->uri(), $protocol);
		}

		if ($pagination AND $pagination->current_page > 1)
		{
			$url .= '/p' . $pagination->current_page;
		}

		return URL::site($url, $protocol).URL::query($qstring);
	}

	/**
	 * Test whether a URL is absolute
	 *
	 * @param   string  $url  The URL to test
	 * @return  boolean
	 */
	public static function is_absolute($url)
	{
		return (strpos($url, '://') !== FALSE);
	}

	/**
	 * Test whether a URL is remote
	 *
	 * @param   string  $url  The URL to test
	 * @return  boolean
	 */
	public static function is_remote($url)
	{
		return (strpos(strtolower($url), 'http://') !== FALSE)
			OR (strpos(strtolower($url), 'https://') !== FALSE)
			OR (strpos(strtolower($url), 'ftp://') !== FALSE);
	}

	/**
	 * Test whether a URL is the home page
	 *
	 * @param   string  $route_name    The home route name [Optional]
	 * @param   array   $route_params  The home route parameters [Optional]
	 * @return  boolean
	 *
	 * @uses    Request::initial
	 * @uses    Route::name
	 */
	public static function is_homepage($route_name = NULL, $route_params = NULL)
	{
		// Process the current URL
		$request = Request::initial();
		$name = Route::name($request->route);

		$params = $request->param();
		$params['action'] = $request->action();
		$params['controller'] = $request->controller();

		$current = URL::canonical($name, $params);

		// Process the home URL
		if (empty($route_name))
		{
			$route_name = 'default';
		}
		if (empty($route_params))
		{
			$route_params = array('controller' => 'home');
		}

		$home = URL::canonical($route_name, $route_params);

		return ($current === $home);
	}

	/**
	 * Create links to sort a column
	 *
	 * Set $reverse to true to set asc as desc and vice versa.
	 *
	 * @param   string   $col
	 * @param   boolean  $reverse [Optional]
	 * @return  string
	 *
	 * @uses    Arr::get
	 * @uses    HTML::anchor
	 * @uses    Request::uri
	 */
	public static function sortAnchor($col, $reverse = FALSE)
	{
		$string = "";
		$orders = array('asc', 'desc');

		foreach ($orders as $order)
		{
			$class = "";
			$anchor_string = ($order == 'asc') ? "&and;" : "&or;";

			if ($reverse)
			{
				$order = ($order == 'asc') ? 'desc' : 'asc';
			}

			if (Arr::get($_GET, 'sort') == $col && Arr::get($_GET, 'order') == $order)
			{
				$class = "active";
			}

			$query = URL::query(array('sort' => $col, 'order' => $order));


			$string .= HTML::anchor(Request::current()->uri() . $query, $anchor_string, array('class' => $class . ' sort'));
		}

		return $string;
	}

	/**
	 * Splits url into array of it's pieces as follows:
	 * [scheme]://[user]:[pass]@[host]/[path]?[query]#[fragment]
	 * In addition it adds 'query_params' key which contains array of
	 * url-decoded key-value pairs
	 *
	 * @param   string  $url An URL
	 * @return  array
	 */
	public static function explode($url)
	{
		$url = parse_url($url);
		$url['query_params'] = array();

		// On seriously malformed URLs, parse_url() may return FALSE.
		if (isset($url['query']))
		{
			$pairs = explode('&', $url['query']);
			foreach($pairs as $pair)
			{
				if (trim($pair) == '')
				{
					continue;
				}

				list($sKey, $sValue) = explode('=', $pair);

				$url['query_params'][$sKey] = urldecode($sValue);
			}
		}

		return $url;
	}

}