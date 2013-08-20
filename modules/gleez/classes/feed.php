<?php
/**
 * Abstract Feed class
 *
 * @package    Gleez\Feed
 * @author     Sergey Yakovlev - Gleez
 * @version    2.0.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
abstract class Feed {

	/* Default time to live (It's a number of minutes) */
	const DEFAULT_TTL = 1440;

	/* Generator name  */
	const NAME = 'Gleez Feed Generator';

	/**
	 * Default driver to use
	 * @var string
	 */
	public static $default = 'rss';

	/**
	 * Gleez_Gleez instances
	 * @var array
	 */
	public static $instances = array();

	/**
	 * Default prepared header for XML document
	 * @var array
	 */
	protected $_info;

	/**
	 * Site configuration
	 * @var array
	 */
	protected $_config;

	/**
	 * Get a singleton Feed instance
	 *
	 * @param   string  $driver  The name of the feed driver to use [Optional]
	 * @return  Feed
	 */
	public static function instance($driver = NULL)
	{
		// If there is no driver supplied
		if (is_null($driver))
		{
			// Use the default driver
			$driver = Feed::$default;
		}

		if (isset(Feed::$instances[$driver]))
		{
			// Return the current driver if initiated already
			return Feed::$instances[$driver];
		}

		// Load site configuration
		$config = Config::load('site')->as_array();

		// Create a new feed instance
		$feed_class = 'Feed_'.ucfirst($driver);
		Feed::$instances[$driver] = new $feed_class($config);

		// Return the instance
		return Feed::$instances[$driver];
	}

	/**
	 * Class constructor
	 *
	 * [!!] This method cannot be accessed directly, you must use [Feed::instance].
	 *
	 * @param   array   $config  Site configuration
	 *
	 * @throws  Feed_Exception
	 */
	public function __construct(array $config)
	{
		// Check if SimpleXML is installed
		if ( ! class_exists('SimpleXMLElement'))
		{
			throw new Feed_Exception('SimpleXML must be installed!');
		}

		// Store the config locally
		$this->_config = $config;

		// Set default prepared header for XML document
		$this->setInfo();
	}

	/**
	 * Parse a remote feed into an array
	 *
	 * @param   string   $feed   Remote feed URL
	 * @param   integer  $limit  Item limit to fetch [Optional]
	 *
	 * @return  array
	 */
	abstract public function parse($feed, $limit = 0);

	/**
	 * Create a feed from the given parameters
	 *
	 * @param   array   $info      Feed information
	 * @param   array   $items     Items to add to the feed
	 * @param   string  $encoding  Define which encoding to use [Optional]
	 *
	 * @return  string
	 *
	 * @throws  Feed_Exception
	 */
	abstract public function create(array $info, array $items, $encoding = NULL);

	/**
	 * Prepare XML skeleton
	 *
	 * @link    http://php.net/manual/en/function.simplexml-load-string.php simplexml_load_string()
	 * @param   string  $encoding  Define which encoding to use [Optional]
	 *
	 * @return  SimpleXMLElement
	 */
	abstract public function prepareXML($encoding = NULL);

	/**
	 * Get default prepared header for XML document
	 *
	 * @return  array
	 */
	abstract public function getInfo();

	/**
	 * Set default prepared header for XML document
	 */
	abstract public function setInfo();

	/**
	 * Get feed generator title and version
	 *
	 * @return  string
	 *
	 * @uses    Gleez::getVersion
	 */
	public static function getGenerator()
	{
		return Gleez::getVersion(TRUE, TRUE) . ' ' . '(http://gleezcms.org)';
	}

}