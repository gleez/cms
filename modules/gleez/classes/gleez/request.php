<?php defined('SYSPATH') or die('No direct script access.');

class Gleez_Request extends Kohana_Request {

	/**
	 * @var  string  request Subdomain
	 */
	public static $subdomain;

	/**
	 * Add subdomain support
	 *  @see https://github.com/jeanmask/subdomain
	 *  Thanks to jean@webmais.net.br
	 */
	public static function factory($uri = TRUE, HTTP_Cache $cache = NULL, $injected_routes = array())
	{
		self::$subdomain = Request::catch_subdomain() ;

		return parent::factory($uri, $cache, $injected_routes) ;
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
	
		$isCrawler = (preg_match("/$crawlers/i", Request::$user_agent) > 0);
		
		return $isCrawler;
	}
	
	/**
	 * Checks subdomain support
	 */
	public static function catch_subdomain($base_url = NULL, $host = NULL)
	{
		if($base_url === NULL) $base_url = parse_url(Kohana::$base_url, PHP_URL_HOST) ;
	
		if($host === NULL)
		{
			if( Kohana::$is_cli ) return FALSE ;
	
			$host = $_SERVER['HTTP_HOST'] ;
		}
	
		if(empty($base_url) OR empty($host) OR in_array($host, Route::$localhosts) OR Valid::ip($host))
		{
			return FALSE ;
		}
	
		$sub_pos = (int)strpos($host, $base_url) - 1 ;
		
		if($sub_pos > 0)
		{
			$subdomain = substr($host,0,$sub_pos) ;
			
			if( !empty($subdomain) ) return $subdomain ;
		}
		
		return Route::SUBDOMAIN_EMPTY ;
	}
}
