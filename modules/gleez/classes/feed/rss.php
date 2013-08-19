<?php
/**
 * RSS news aggregator
 *
 * @package    Gleez\Feed\RSS
 * @author     Sergey Yakovlev - Gleez
 * @version    1.0.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Feed_Rss extends Feed {

	/**
	 * Parse a remote feed into an array.
	 *
	 * @param   string   $feed   Remote feed URL
	 * @param   integer  $limit  Item limit to fetch [Optional]
	 *
	 * @return  array
	 *
	 * @uses    Valid::url
	 */
	public function parse($feed, $limit = 0)
	{
		// Make limit an integer
		$limit = (int) $limit;

		// Disable error reporting while opening the feed
		$error_level = error_reporting(0);

		// Allow loading by filename or raw XML string
		$load = (is_file($feed) OR Valid::url($feed)) ? 'simplexml_load_file' : 'simplexml_load_string';

		// Load the feed
		$feed = $load($feed, 'SimpleXMLElement', LIBXML_NOCDATA);

		// Restore error reporting
		error_reporting($error_level);

		// Feed could not be loaded
		if ( ! $feed)
		{
			return array();
		}

		$namespaces = $feed->getNamespaces(TRUE);

		// This only for RSS 1.0/2.0 are supported
		$feed = $feed->xpath('//item');

		$i = 0;
		$items = array();

		foreach ($feed as $item)
		{
			if ($limit > 0 AND $i++ === $limit)
				break;
			$item_fields = (array) $item;

			// get namespaced tags
			foreach ($namespaces as $ns)
			{
				$item_fields += (array) $item->children($ns);
			}
			$items[] = $item_fields;
		}

		return $items;
	}

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
	 *
	 * @uses    Arr::merge
	 * @uses    URL::is_absolute
	 * @uses    URL::site
	 */
	public function create(array $info, array $items, $encoding = NULL)
	{
		$generator = array(
			'title'     => Feed::NAME,
			'link'      => '',
			'generator' => Feed::getGenerator()
		);

		$info = Arr::merge($generator, $info);
		$feed = $this->prepareXML($encoding);

		foreach ($info as $name => $value)
		{
			if ($name === 'image')
			{
				// Create an image element
				$image = $feed->channel->addChild('image');

				if ( ! isset($value['link'], $value['url'], $value['title']))
				{
					throw new Feed_Exception('Feed images require a link, url, and title');
				}

				if (URL::is_absolute($value['url']))
				{
					// Convert URIs to URLs
					$value['url'] = URL::site($value['url'], TRUE);
				}

				// Create the image elements
				$image->addChild('link',  $value['link']);
				$image->addChild('url',   $value['url']);
				$image->addChild('title', $value['title']);
			}
			else
			{
				if (($name === 'pubDate' OR $name === 'lastBuildDate') AND (is_int($value) OR ctype_digit($value)))
				{
					// Convert timestamps to RFC 822 formatted dates
					$value = date('r', $value);
				}
				elseif (($name === 'link' OR $name === 'docs') AND URL::is_absolute($value))
				{
					// Convert URIs to URLs
					$value = URL::site($value, TRUE);
				}
				// Add the info to the channel
				$feed->channel->addChild($name, $value);
			}
		}

		foreach ($items as $item)
		{
			// Add the item to the channel
			$row = $feed->channel->addChild('item');

			foreach ($item as $name => $value)
			{
				if ($name === 'pubDate' AND (is_int($value) OR ctype_digit($value)))
				{
					// Convert timestamps to RFC 822 formatted dates
					$value = date('r', $value);
				}
				elseif (($name === 'link' OR $name === 'guid') AND URL::is_absolute($value))
				{
					// Convert URIs to URLs
					$value = URL::site($value, TRUE);
				}

				// Add the info to the row
				$row->addChild($name, $value);
			}

		}

		if (function_exists('dom_import_simplexml'))
		{
			// Convert the feed object to a DOM object
			$feed = dom_import_simplexml($feed)->ownerDocument;

			// DOM generates more readable XML
			$feed->formatOutput = TRUE;

			// Export the document as XML
			$feed = $feed->saveXML();
		}
		else
		{
			// Export the document as XML
			$feed = $feed->asXML();
		}

		return $feed;
	}

	/**
	 * Prepare XML skeleton
	 *
	 * @link    http://php.net/manual/en/function.simplexml-load-string.php simplexml_load_string()
	 * @param   string  $encoding  Define which encoding to use [Optional]
	 * @return  SimpleXMLElement
	 */
	public function prepareXML($encoding = NULL)
	{
		$encoding = is_null($encoding) ? Kohana::$charset : $encoding;
		$feed = '<?xml version="1.0" encoding="'.strtoupper($encoding).'"?><rss version="2.0"><channel></channel></rss>';

		return simplexml_load_string($feed);
	}

	/**
	 * Get default prepared header for XML document
	 *
	 * @return  array
	 */
	public function getInfo()
	{
		return $this->_info;
	}

	/**
	 * Set default prepared header for XML document
	 *
	 * @uses  Arr::get
	 * @uses  Route::url
	 * @uses  I18n::lang
	 * @uses  URL::site
	 */
	public function setInfo()
	{
		$this->_info = array(
			'title'       => Template::getSiteName(),
			'description' => Arr::get($this->_config, 'site_mission', __('Recently added posts')),
			'pubDate'     => time(),
			'generator'   => Feed::getGenerator(),
			'link'        => Route::url('rss', NULL, TRUE),
			'copyright'   => '2011-'.date('Y') . ' ' . Template::getSiteName(),
			'language'    => I18n::lang(),
			'ttl'         => Arr::get($this->_config, 'feed_ttl', Feed::DEFAULT_TTL),
			'image'	      => array(
				'link'  => URL::site(NULL, TRUE),
				'url'   => URL::site(Arr::get($this->_config, 'site_logo', 'media/images/logo.png'), TRUE),
				'title' => Template::getSiteName()
			),
		);
	}
}