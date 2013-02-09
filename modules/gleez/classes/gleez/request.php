<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Extending Kohana Request and response wrapper
 *
 * @package     Gleez
 * @category    Controller
 * @author      Sandeep Sangamreddi - Gleez
 * @copyright   (c) 2012 Gleez Technologies
 * @license     http://gleezcms.org/license
 */
class Gleez_Request extends Kohana_Request {

  /** @var  string  request Subdomain */
  public static $subdomain;

  /**
   * Add subdomain support
   * Thanks to jean@webmais.net.br
   *
   * @param   string      $uri  URI of the request
   * @param   HTTP_Cache  $cache
   * @param   array       $injected_routes An array of routes to use, for testing
   * @return  void
   * @link    https://github.com/jeanmask/subdomain
   */
	public static function factory($uri = TRUE, HTTP_Cache $cache = NULL, $injected_routes = array())
	{
		self::$subdomain = Request::catch_subdomain() ;

		return parent::factory($uri, $cache, $injected_routes);
	}

  /**
   * Overwrite to check and set maintainance mode
   *
   * @return Response
   */
	public function execute()
	{
		if(Gleez::$installed)
		{
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
	 * @param   array   $routes  Route
	 * @return  array
	 */
	public static function process_uri($uri, $routes = NULL)
	{
		// Load routes
		$routes = (empty($routes)) ? Route::all() : $routes;
		$params = array();

		foreach ($routes as $name => $route)
		{
      $params = $route->matches($uri);
			// We found something suitable
			if (! empty($params))
			{
				// Fix for pagination on lambda routes
				if (! isset($params['uri']))
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
   * Checks whether the request called by bot/crawller by useragent string.
   * Preg is faster than for loop
   *
   * @return string
   */
	public static function is_crawler()
	{
    // @todo this list must be configurable
		$crawlers = 'Bloglines subscriber|Dumbot|Sosoimagespider|QihooBot|FAST-WebCrawler'.
			'|Superdownloads Spiderman|LinkWalker|msnbot|ASPSeek|WebAlta Crawler|'.
			'Lycos|FeedFetcher-Google|Yahoo|YoudaoBot|AdsBot-Google|Googlebot|Scooter|'.
			'Gigabot|Charlotte|eStyle|AcioRobot|GeonaBot|msnbot-media|Baidu|CocoCrawler|'.
			'Google|Charlotte t|Yahoo! Slurp China|Sogou web spider|YodaoBot|MSRBOT|AbachoBOT|'.
			'Sogou head spider|AltaVista|IDBot|Sosospider|Yahoo! Slurp|'.
			'Java VM|DotBot|LiteFinder|Yeti|Rambler|Scrubby|Baiduspider|accoona';

		$isCrawler = (preg_match("/$crawlers/i", Request::$user_agent) > 0);

		return $isCrawler;
	}

  /**
   * Checks subdomain support
   *
   * @param   string  $base_url
   * @param   mixed   $host
   * @return  boolean|string
   */
	public static function catch_subdomain($base_url = NULL, $host = NULL)
	{
		if(is_null($base_url))
    {
      $base_url = parse_url(Kohana::$base_url, PHP_URL_HOST);
    }

		if(is_null($host))
		{
			if(Kohana::$is_cli)
        return FALSE;
			$host = $_SERVER['HTTP_HOST'];
		}

		if(empty($base_url) OR empty($host) OR in_array($host, Route::$localhosts) OR Valid::ip($host))
		{
			return FALSE;
		}

		$sub_pos = (int)strpos($host, $base_url) - 1;

		if($sub_pos > 0)
		{
			$subdomain = substr($host,0,$sub_pos) ;

			if(! empty($subdomain))
        return $subdomain;
		}

		return Route::SUBDOMAIN_EMPTY;
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
  public function is_get() {
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
  public function is_post() {
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
  public function is_put() {
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
  public function is_delete() {
    return ($this->method() === Request::DELETE);
  }

}
