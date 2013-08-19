<?php
/**
 * Abstract template class for feed controllers
 *
 * @package    Gleez\Controller\Feed
 * @author     Gleez Team
 * @version    1.0.2
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Feeds_Template extends Controller {

	/**
	 * Default page size
	 * @var integer
	 */
	protected $_page_size = 30;

	/**
	 * Current page
	 * @var integer
	 */
	protected $_page;

	/**
	 * How Many Items Should We Retrieve?
	 * @var integer
	 */
	protected $_limit;

	/**
	 * The shift in list for getting
	 * @var integer
	 */
	protected $_offset;

	/**
	 * Current element ID
	 * @var integer
	 */
	protected $_id;

	/**
	 * Site URL
	 * @var string
	 */
	protected $_site_url;

	/**
	 * The configuration settings
	 * @var Config
	 */
	protected $_config;

	/**
	 * Feed Cache
	 * @var Cache
	 */
	protected $_cache;

	/**
	 * Cache key
	 * @var string
	 */
	protected $_cache_key;

	/**
	 * Feed items
	 * @var array
	 */
	protected $_items = array();

	/**
	 * Feed item
	 * @var array
	 */
	protected $_info;

	/**
	 * Feed ttl (min.)
	 * @var integer
	 */
	protected $_ttl;

	/**
	 * Current post type (page|blog|etc.)
	 * @var string
	 */
	protected $_type = 'page';

	/**
	 * Feed type (rss|atom)
	 * @var string
	 */
	protected $_feed_type;

	/**
	 * Feed object
	 * @var Feed
	 */
	protected $_feed;

	/**
	 * Preparing feed
	 *
	 * @uses  Arr::get
	 * @uses  Config::load
	 * @uses  Config_Group::get
	 * @uses  URL::site
	 * @uses  Cache:get
	 * @uses  Feed::generator
	 * @uses  Request::current
	 * @uses  Request::routes
	 */
	public function before()
	{
		// Get route name (rss|atom) for creating object (Gleez_Rss|Gleez_Atom)
		$this->_feed_type = Route::name(Request::current()->route());

		// Start at which page?
		$this->_page = (int) $this->request->param('p', 1);

		// How Many Items Should We Retrieve?
		// Configurable page size between 1 and 200, default 30
		$this->_limit = max(1, min(200, (int) $this->request->param('l', $this->_page_size)));

		// For example: Term ID or Rag ID
		$this->_id = (int) $this->request->param('id', 0);

		// Offset
		$this->_offset = ($this->_page == 1) ? $this->_page : ($this->_page - 1) * $this->_limit;

		// Getting settings
		$this->_config = Kohana::$config->load('site');

		// Getting site URL
		$this->_site_url = $this->_config->get('site_url', URL::site(NULL, TRUE));

		// Getting TTL
		$this->_ttl = $this->_config->get('feed_ttl', Date::HOUR * 60);

		// Initiate cache
		$this->_cache = Cache::instance('feeds');
		$this->_cache_key = "feed-{$this->request->controller()}-{$this->request->action()}-{$this->_limit}-{$this->_page}-{$this->_id}";

		// Fills the array elements
		$this->_items = $this->_cache->get($this->_cache_key, array());

		// Create feed object
		$this->_feed = Feed::instance($this->_feed_type);

		// Preparing header for XML document
		$this->_info = $this->_feed->getInfo();

		parent::before();

		$this->response->headers('Content-Type', 'text/xml');

		if (Kohana::$environment === Kohana::DEVELOPMENT)
		{
			Log::debug('Executing Controller: :controller, action: :action',
				array(':controller' => $this->request->controller(), ':action' => $this->request->action())
			);
		}
	}

	/**
	 * Create feed
	 *
	 * @uses  Feed::create
	 */
	public function after()
	{
		parent::after();

		if (isset($this->_items['title']))
		{
			unset($this->_items['title']);
		}

		echo $this->_feed->create($this->_info, $this->_items);
	}

}