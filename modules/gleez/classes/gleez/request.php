<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Extending Kohana Request and response wrapper
 *
 * @package    Gleez\Request
 * @version    1.0
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License Agreement
 */
class Gleez_Request extends Kohana_Request {

	/**
	 * @var  string  request Subdomain
	 */
	public static $subdomain;

	/**
	 * @var  string  request Redirect URL for ajax requests
	 */
	public static $_redirect_url;

	/**
	 * Redirects as the request response. If the URL does not include a
	 * protocol, it will be converted into a complete URL.
	 *
	 *     $request->redirect($url);
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
		// Check whether the current request is ajax request
                if ( $this->is_ajax() )
                {
			self::$_redirect_url = $url;
			// Stop execution
			return;
		}
	
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
	 * Overwrite to check and set maintainance mode
	 */
	public function execute()
	{
		if( Gleez::$installed )
		{
			//Check Maintenance Mode
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
	 * @param   array   $routes  Route
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
	 * @todo use Request::$user_agent but it is null
	 * @return bool
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
	 * @todo use Request::$user_agent but it is null
	 * @return bool
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
	 * Returns whether this request is GET
	 * Thanks to nike-17@ya.ru
	 *
	 *      $this->request->is_get();
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
	 * Thanks to nike-17@ya.ru
	 *
	 *      $this->request->is_post();
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
	 * Thanks to nike-17@ya.ru
	 *
	 *      $this->request->is_put();
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
	 * Thanks to nike-17@ya.ru
	 *
	 *      $this->request->is_delete();
	 *
	 * @return  boolean
	 * @link    https://github.com/kohana/core/pull/286
	 */
	public function is_delete()
	{
		return ($this->method() === Request::DELETE);
	}
	
}