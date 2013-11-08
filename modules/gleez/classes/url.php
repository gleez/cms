<?php
/**
 * URL Class Helper
 *
 * @package    Gleez\Helpers
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class URL {

	/**
	 * Gets the base URL to the application
	 *
	 * To specify a protocol, provide the protocol as a string or request object.
	 * If a protocol is used, a complete URL will be generated using the
	 * `$_SERVER['HTTP_HOST']` variable.
	 *
	 * Example:
	 * ~~~
	 * // Absolute URL path with no host or protocol
	 * echo URL::base();
	 *
	 * // Absolute URL path with host, https protocol and index.php if set
	 * echo URL::base('https', TRUE);
	 *
	 * // Absolute URL path with host and protocol from $request
	 * echo URL::base($request);
	 * ~~~
	 *
	 * @param   mixed    $protocol  Protocol string, [Request], or boolean [Optional]
	 * @param   boolean  $index     Add index file to URL? [Optional]
	 *
	 * @return  string
	 *
	 * @uses    Kohana::$index_file
	 * @uses    Request::protocol
	 */
	public static function base($protocol = NULL, $index = FALSE)
	{
		// Start with the configured base URL
		$base_url = Kohana::$base_url;

		if ($protocol === TRUE)
		{
			// Use the initial request to get the protocol
			$protocol = Request::$initial;
		}

		if ($protocol instanceof Request)
		{
			if ( ! $protocol->secure())
			{
				// Use the current protocol
				list($protocol) = explode('/', strtolower($protocol->protocol()));
			}
			else
			{
				$protocol = 'https';
			}
		}

		if ( ! $protocol)
		{
			// Use the configured default protocol
			$protocol = parse_url($base_url, PHP_URL_SCHEME);
		}

		if ($index === TRUE AND ! empty(Kohana::$index_file))
		{
			// Add the index file to the URL
			$base_url .= Kohana::$index_file.'/';
		}

		if (is_string($protocol))
		{
			if ($port = parse_url($base_url, PHP_URL_PORT))
			{
				// Found a port, make it usable for the URL
				$port = ':'.$port;
			}

			if ($domain = parse_url($base_url, PHP_URL_HOST))
			{
				// Remove everything but the path from the URL
				$base_url = parse_url($base_url, PHP_URL_PATH);
			}
			else
			{
				// Attempt to use HTTP_HOST and fallback to SERVER_NAME
				$domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
			}

			// Add the protocol and domain to the base URL
			$base_url = $protocol.'://'.$domain.$port.$base_url;
		}

		return $base_url;
	}

	/**
	 * Callback used for encoding all non-ASCII characters, as per RFC 1738
	 * Used by URL::site()
	 *
	 * @param  array $matches  Array of matches from preg_replace_callback()
	 * @return string          Encoded string
	 */
	protected static function _rawurlencode_callback($matches)
	{
		return rawurlencode($matches[0]);
	}

	/**
	 * Merges the current GET parameters with an array of new or overloaded
	 * parameters and returns the resulting query string.
	 *
	 * Example:
	 * ~~~
	 * // Returns "?sort=title&limit=10" combined with any existing GET values
	 * $query = URL::query(array('sort' => 'title', 'limit' => 10));
	 * ~~~
	 *
	 * Typically you would use this when you are sorting query results,
	 * or something similar.
	 *
	 * [!!] Parameters with a NULL value are left out.
	 *
	 * @param   array    $params   Array of GET parameters [Optional]
	 * @param   boolean  $use_get  Include current request GET parameters [Optional]
	 * @return  string
	 */
	public static function query(array $params = NULL, $use_get = TRUE)
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
				$params = Arr::merge($_GET, $params);
			}
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
	 * Convert a phrase to a URL-safe title.
	 *
	 * Example:
	 * ~~~
	 * echo URL::title('My Blog Post'); // "my-blog-post"
	 * ~~~
	 *
	 * @param   string   $title       Phrase to convert
	 * @param   string   $separator   Word separator (any single character) [Optional]
	 * @param   boolean  $ascii_only  Transliterate to ASCII? [Optional]
	 *
	 * @return  string
	 *
	 * @uses    UTF8::transliterate_to_ascii
	 */
	public static function title($title, $separator = '-', $ascii_only = FALSE)
	{
		if ($ascii_only === TRUE)
		{
			// Transliterate non-ASCII characters
			$title = UTF8::transliterate_to_ascii($title);

			// Remove all characters that are not the separator, a-z, 0-9, or whitespace
			$title = preg_replace('![^'.preg_quote($separator).'a-z0-9\s]+!', '', strtolower($title));
		}
		else
		{
			// Remove all characters that are not the separator, letters, numbers, or whitespace
			$title = preg_replace('![^'.preg_quote($separator).'\pL\pN\s]+!u', '', UTF8::strtolower($title));
		}

		// Replace all separator characters and whitespace by a single separator
		$title = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $title);

		// Trim separators from the beginning and end
		return trim($title, $separator);
	}

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
			return self::site($url->uri(), $protocol);
		}

		if ($pagination AND $pagination->current_page > 1)
		{
			$url .= '/p' . $pagination->current_page;
		}

		return self::site($url, $protocol).self::query($qstring);
	}

	/**
	 * Test whether a URL is absolute
	 *
	 * @param   string  $url  The URL to test
	 * @return  boolean
	 */
	public static function is_absolute($url)
	{
		return (strpos($url, '://') === FALSE);
	}

	/**
	 * Test whether a URL is remote
	 *
	 * @param   string  $url  The URL to test
	 * @return  boolean
	 */
	public static function is_remote($url)
	{
		return (strpos($url, '://') !== FALSE);
	}

	/**
	 * Fetches an absolute site URL based on a URI segment.
	 * Added admin Theme support
	 *
	 * Example:
	 * ~~~
	 * echo URL::site('foo/bar');
	 * ~~~
	 *
	 * @param   string  $uri        Site URI to convert [Optional]
	 * @param   mixed   $protocol   Protocol string or [Request] class to use protocol from [Optional]
	 * @param   boolean $index		Include the index_page in the URL [Optional]
	 * @return  string
	 *
	 * @uses    UTF8::is_ascii
	 */
	public static function site($uri = '', $protocol = NULL, $index = TRUE)
	{
		// Chop off possible scheme, host, port, user and pass parts
		$path = preg_replace('~^[-a-z0-9+.]++://[^/]++/?~', '', trim($uri, '/'));

		if ( ! UTF8::is_ascii($path))
		{
			// Encode all non-ASCII characters, as per RFC 1738
			$path = preg_replace_callback('~([^/]+)~', 'URL::_rawurlencode_callback', $path);
		}

		// Concatenation URL
		return self::base($protocol, $index).$path;
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

		$current = self::canonical($name, $params);

		// Process the home URL
		if (empty($route_name))
		{
			$route_name = 'default';
		}
		if (empty($route_params))
		{
			$route_params = array('controller' => 'home');
		}

		$home = self::canonical($route_name, $route_params);

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

			$query = self::query(array('sort' => $col, 'order' => $order));


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

	/**
	 * Determine current url
	 *
	 * @param   mixed    $protocol
	 * @param   boolean  $index
	 * @param   boolean  $with_query_params
	 *
	 * @return  string
	 */
	public static function current($protocol = NULL, $index = FALSE, $with_query_params = TRUE)
	{
		static $uri;
		$query = null;
		if (!$with_query_params)
		{
			$query = self::query();
		}

		if (empty($uri))
		{
			$uri = self::site(Request::current()->uri());
		}

		return self::base($protocol, $index) . str_replace($query, '', ltrim($uri, '/'));
	}

	/**
	 * Determine if current url is active
	 *
	 * @param   string  $url
	 * @return  boolean
	 */
	public static function is_active($url)
	{
		if (preg_match('#^[A-Z][A-Z0-9+.\-]+://#i', $url))
		{
			// Don't check URIs with a scheme ... not really a URI is it?
			return FALSE;
		}

		$current = explode('/', trim(str_replace(self::base(), '', self::current()), '/'));
		ksort($current);
		$url = explode('/', trim(str_replace(self::base(), '', $url), '/'));
		ksort($url);

		if (0 == count(array_diff($url, $current)))
		{
			return TRUE;
		}

		$result = FALSE;

		if (count($url) < count($current))
		{
			for ($i = 0; $i == count($url); $i++)
			{
				$result = $url[$i] == $current[$i] OR $result;
			}
		}

		return $result;
	}
}
