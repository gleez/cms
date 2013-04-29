<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Extending Kohana Request and response wrapper
 *
 * @package    Gleez\Request
 * @version    1.1
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License Agreement
 */
class Gleez_Request extends Kohana_Request {

	/** Default maximum size of POST data */
	const DEFAULT_POST_MAX_SIZE = '1M';

	/**
	 * Request Redirect URL for ajax requests
	 * @var string
	 */
	public static $redirect_url;

	/**
	 * Redirects as the request response. If the URL does not include a
	 * protocol, it will be converted into a complete URL.
	 *
	 * Example:<br>
	 * <code>
	 *   $request->redirect($url);
	 * </code>
	 *
	 * [!!] No further processing can be done after this method is called!
	 *
	 * @param   string   $url   Redirect location
	 * @param   integer  $code  Status code: 301, 302, etc
	 * @return  void
	 * @uses    URL::site
	 * @uses    Request::send_headers
	 */
	public function redirect($url = '', $code = 302)
	{
		$referrer = $this->uri();

		if (strpos($referrer, '://') === FALSE)
		{
			$referrer = URL::site($referrer, TRUE, Kohana::$index_file);
		}

		if (strpos($url, '://') === FALSE)
		{
			// Make the URI into a URL
			$url = URL::site($url, TRUE, Kohana::$index_file);
		}

		// Check whether the current request is ajax request
		if ($this->is_ajax())
		{
			self::$redirect_url = $url;
			// Stop execution
			return;
		}

		if (($response = $this->response()) === NULL)
		{
			$response = $this->create_response();
		}

		echo $response->status($code)
			->headers('Location', $url)
			->headers('Referer', $referrer)
			->send_headers()
			->body();

		// Stop execution
		exit;
	}

	/**
	 * Overwrite to check and set maintenance mode
	 *
	 * @return  Response
	 *
	 * @uses    Gleez::block_ips
	 * @uses    Gleez::maintenance_mode
	 */
	public function execute()
	{
		if (Gleez::$installed)
		{
			// Deny access to blocked IP addresses
			Gleez::block_ips();

			// Check Maintenance Mode
			Gleez::maintenance_mode();
		}

		return parent::execute();
	}

	/**
	 * Fix for pagination on lambda routes
	 *
	 * Process URI
	 *
	 * @param   string  $uri     URI
	 * @param   array   $routes  Route [Optional]
	 * @return  array
	 */
	public static function process_uri($uri, $routes = NULL)
	{
		// Load routes
		$routes = (empty($routes)) ? Route::all() : $routes;
		$params = NULL;

		foreach ($routes as $name => $route)
		{
			// We found something suitable
			if ($params = $route->matches($uri))
			{
				// fix for pagination on lambda routes
				if ( ! isset($params['uri']))
				{
					$params['uri'] = $uri;
				}

				return array(
					'params' => $params,
					'route'  => $route,
					'name'   => $name,
				);
			}
		}

		return NULL;
	}

	/**
	 * Checks whether the request called by bot/crawller by useragent string
	 * Preg is faster than for loop
	 *
	 * @return boolean
	 *
	 * @todo use Request::$user_agent but it is null
	 */
	public static function is_crawler()
	{
		$crawlers = 'Bloglines subscriber|Dumbot|Sosoimagespider|QihooBot|FAST-WebCrawler'.
			'|Superdownloads Spiderman|LinkWalker|msnbot|ASPSeek|WebAlta Crawler|'.
			'Lycos|FeedFetcher-Google|Yahoo|YoudaoBot|AdsBot-Google|Googlebot|Scooter|'.
			'Gigabot|Charlotte|eStyle|AcioRobot|GeonaBot|msnbot-media|Baidu|CocoCrawler|'.
			'Google|Charlotte t|Yahoo! Slurp China|Sogou web spider|YodaoBot|MSRBOT|AbachoBOT|'.
			'Sogou head spider|AltaVista|IDBot|Sosospider|Yahoo! Slurp|'.
			'Java VM|DotBot|LiteFinder|Yeti|Rambler|Scrubby|Baiduspider|accoona';

		if (isset($_SERVER['HTTP_USER_AGENT']))
		{
			return (preg_match("/$crawlers/i", $_SERVER['HTTP_USER_AGENT']) > 0);
		}

		return FALSE;
	}

	/**
	 * Checks whether the request called by mobile device by useragent string
	 * Preg is faster than for loop
	 *
	 * @return boolean
	 *
	 * @todo use Request::$user_agent but it is null
	 */
	public static function is_mobile()
	{
		$devices = 'android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos';

		if (isset($_SERVER['HTTP_USER_AGENT']))
		{
			return (preg_match("/$devices/i", $_SERVER['HTTP_USER_AGENT']) > 0);
		}

		return FALSE;
	}

	/**
	 * Whether or not current request is DataTables
	 *
	 * @param   mixed  Request  Request [Optional]
	 * @return  boolean
	 * @uses    Request::current
	 */
	public static function is_datatables(Request $request = NULL)
	{
		$request = ($request) ? $request : Request::current();

		return (bool) $request->query('sEcho');
	}

	/**
	 * Returns whether this request is GET
	 *
	 * Thanks to nike-17@ya.ru
	 *
	 * Example:<br>
	 * <code>
	 *   $this->request->is_get();
	 * </code>
	 *
	 * @return  boolean
	 * @link    https://github.com/kohana/core/pull/286
	 */
	public function is_get()
	{
		return ($this->method() === Request::GET);
	}

	/**
	 * Returns whether this request is POST
	 *
	 * Thanks to nike-17@ya.ru
	 *
	 * Example:<br>
	 * <code>
	 *   $this->request->is_post();
	 * </code>
	 *
	 * @return  boolean
	 * @link    https://github.com/kohana/core/pull/286
	 */
	public function is_post()
	{
		return ($this->method() === Request::POST);
	}

	/**
	 * Returns whether this request is PUT
	 *
	 * Thanks to nike-17@ya.ru
	 *
	 * Example:<br>
	 * <code>
	 *   $this->request->is_put();
	 * </code>
	 *
	 * @return  boolean
	 * @link    https://github.com/kohana/core/pull/286
	 */
	public function is_put()
	{
		return ($this->method() === Request::PUT);
	}

	/**
	 * Returns whether this request is DELETE
	 *
	 * Thanks to nike-17@ya.ru
	 *
	 * Example:<br>
	 * <code>
	 *   $this->request->is_delete();
	 * </code>
	 *
	 * @return  boolean
	 * @link    https://github.com/kohana/core/pull/286
	 */
	public function is_delete()
	{
		return ($this->method() === Request::DELETE);
	}

	/**
	 * Determines if a file larger than the post_max_size has been uploaded
	 *
	 * PHP does not handle this situation gracefully on its own, so this method
	 * helps to solve that problem.
	 *
	 * @return  boolean
	 *
	 * @uses    Arr::get
	 * @link    http://php.net/post-max-size
	 */
	public static function post_max_size_exceeded()
	{
		// Make sure the request method is POST
		if ( ! Request::current()->is_post())
		{
			return FALSE;
		}

		// Error occurred if method is POST, and content length is too long
		return (Arr::get($_SERVER, 'CONTENT_LENGTH') > Request::get_post_max_size());
	}

	/**
	 * Gets POST max size in bytes
	 *
	 * @return  float
	 *
	 * @uses    Config::load
	 * @uses    Config_Group::get
	 * @uses    Kohana_Num::bytes
	 * @link    http://php.net/post-max-size
	 */
	public static function get_post_max_size()
	{
		$config = Kohana::$config->load('media');

		// Set post_max_size default value if it not exists
		if (is_null($config->get('post_max_size')))
		{
			$config->set('post_max_size', Request::DEFAULT_POST_MAX_SIZE);
		}

		// Get the post_max_size in bytes from php.ini
		$php_settings = Num::bytes(ini_get('post_max_size'));

		// Get the post_max_size in bytes from `config/media`
		$gleez_settings = Num::bytes($config->get('post_max_size'));

		return ($gleez_settings <= $php_settings) ? $gleez_settings : $php_settings;
	}

}