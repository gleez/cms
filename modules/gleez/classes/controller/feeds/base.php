<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Base Feed Controller
 *
 * @package    Gleez\Feed\Controller
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Feeds_Base extends Controller {

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
	 * @var Kohana_Config
	 */
	protected $_config;

	/**
	 * Feed Cache
	 * @var Kohana_Cache
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
	 * Preparing feed
	 *
	 * @uses  Arr::get
	 * @uses  Config::load
	 * @uses  Config_Group::get
	 * @uses  URL::site
	 * @uses  Cache:get
	 * @uses  Feed::generator
	 */
	public function before()
	{
		// Start at which page?
		$this->_page = (int) Arr::get($_GET, 'p', 1);

		// How Many Items Should We Retrieve?
		// Configurable page size between 1 and 200, default 30
		$this->_limit = max(1, min(200, (int) Arr::get($_GET, 'l', $this->_page_size)));

		// For example: Term ID or Rag ID
		$this->_id = (int) $this->request->param('id', 0);

		// Offset
		$this->_offset = ($this->_page - 1) * $this->_limit;

		// Getting settings
		$this->_config = Kohana::$config->load('site');

		// Getting site URL
		$this->_site_url = $this->_config->get('site_url', URL::site(NULL, TRUE));

		// Initiate cache
		$this->_cache = Cache::instance('feeds');
		$this->_cache_key = "feed-{$this->request->controller()}-{$this->request->action()}-{$this->_limit}-{$this->_page}-{$this->_id}";

		// Fills the array elements
		$this->_items = $this->_cache->get($this->_cache_key, array());

		$this->_info = array(
			'title'       => $this->_config->get('site_name', 'Gleez CMS'),
			'description' => $this->_config->get('site_mission', __('Recently added posts')),
			'pubDate'     => time(),
			'generator'   => Feed::generator(),
			'link'        => $this->_site_url,
			'copyright'   => '2011-'.date('Y') . ' ' . $this->_config->get('site_name', 'Gleez Technologies'),
			'language'    => i18n::$lang,
			'image'	      => array(
				'link'  => $this->_site_url,
				'url'   => URL::site('/media/images/logo.png', TRUE),
				'title' => $this->_config->get('site_name', 'Gleez CMS')
			),
		);

		parent::before();
	}

	/**
	 * @uses  Feed::create
	 */
	public function after()
	{
		parent::after();

		if (isset($this->_items['title']))
		{
			unset($this->_items['title']);
		}

		echo Feed::create($this->_info, $this->_items);
	}

	/**
	 * @uses  DB::select
	 * @uses  Text::markup
	 * @uses  URL::site
	 * @uses  Cache::set
	 */
	public function action_index()
	{
		if ($this->_items === NULL OR empty($this->_items))
		{
			// Cache is Empty so Re-Cache
			$posts = DB::select(
					array('p.id', 'id'), 'p.title', 'p.format', 'p.type',
					array('p.teaser', 'description'),
					array('p.pubdate', 'pubDate'),
					array('a.alias', 'link')
				)
				->from(array('posts', 'p'))
				->join(array('paths', 'a'), 'LEFT')
				->on('a.route_controller', '=', 'p.type')
				->on('a.route_id', '=', 'p.id')
				->join_and('a.route_action', '=', "index")
				->where('p.type', '!=', 'post')
				->where('p.status', '=', 'publish')
				->where('p.promote', '=', 1)
				->order_by('pubdate', 'DESC')
				->limit($this->_limit)
				->offset($this->_offset)
				->execute()
				->as_array();

			// Encode HTML special characters in the description. and make link absolute
			for ($i = 0, $n = count($posts); $i < $n; $i++)
			{
				$link = is_null($posts[$i]['link']) ? $posts[$i]['type'].'/'.$posts[$i]['id'] : $posts[$i]['link'];
				$posts[$i]['description'] = Text::markup( $posts[$i]['description'], $posts[$i]['format'] );
				$posts[$i]['link']        = URL::site($link, TRUE);

				unset($posts[$i]['format'], $link );
			}

			$this->_cache->set($this->_cache_key, $posts, DATE::HOUR); // 1 Hour
			$this->_items = $posts;
		}
		if (isset($this->_items[0]))
		{
			$this->_info['pubDate'] = $this->_items[0]['pubDate'];
		}
	}

	public function action_view()
	{
		$id = (int) $this->request->param('id', 0);

		if ($this->_items === NULL OR empty($this->_items))
		{
			// Cache is Empty so Re-Cache
			$post = DB::select(
					array('p.id', 'id'), 'p.title', 'p.format', 'p.type',
					array('p.teaser', 'description'),
					array('p.pubdate', 'pubDate'),
					array('a.alias', 'link')
				)
				->from(array('posts', 'p'))
				->join(array('paths', 'a'), 'LEFT')
				->on('a.route_controller', '=', 'p.type')
				->on('a.route_id', '=', 'p.id')
				->join_and('a.route_action', '=', "index")
				->where('p.id', '=', $id)
				->where('p.status', '=', 'publish')
				->execute()
				->as_array();

			if (isset($post[0]))
			{
				// Encode HTML special characters in the description. and make link absolute
				$link = is_null($post[0]['link']) ? $post[0]['type'].'/'.$post[0]['id'] : $post[0]['link'];
				$post[0]['description'] = Text::markup( $post[0]['description'], $post[0]['format'] );
				$post[0]['link']        = URL::site($link, TRUE);

				unset($post[0]['format'], $link );

				$this->_items = array($post[0]);
				$this->_cache->set($this->_cache_key, $this->_items, DATE::HOUR); // 1 Hour
			}
			else
			{
				$this->_items = array();
			}
		}
		if (isset($this->_items[0]))
		{
			$this->_info['pubDate'] = $this->_items[0]['pubDate'];
		}
	}
}