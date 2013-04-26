<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * RSS and Atom feed helper
 *
 * @package    Gleez\Helpers
 * @author     Sergey Yakovlev - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Gleez_Feed {

	/** Default format is RSS 2.0 */
	const DEFAULT_FORMAT = 'rss2';

	/**
	 * Parses a remote feed into an array.
	 *
	 * @param   string   $feed   Remote feed URL
	 * @param   integer  $limit  Item limit to fetch [Optional]
	 * @return  array
	 *
	 * @uses    Valid::url
	 */
	public static function parse($feed, $limit = 0)
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
		if ($feed === FALSE)
		{
			return array();
		}

		$namespaces = $feed->getNamespaces(true);

		// Detect the feed type. RSS 1.0/2.0 and Atom 1.0 are supported.
		$feed = isset($feed->channel) ? $feed->xpath('//item') : $feed->entry;

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
	 * Creates a feed from the given parameters
	 *
	 * @param   array   $info      Feed information
	 * @param   array   $items     Items to add to the feed
	 * @param   string  $format    RSS Format [Optional]
	 * @param   string  $encoding  Define which encoding to use [Optional]
	 * @return  string
	 * @throws  Gleez_Exception
	 *
	 * @todo    Different formats support (eg. rss 1.0, atom, etc.)
	 *
	 * @uses    Arr::merge
	 * @uses    URL::is_absolute
	 */
	public static function create(array $info, array $items, $format = NULL, $encoding = NULL)
	{
		$generator = array(
			'title'     => 'Generated Feed',
			'link'      => '',
			'generator' => Feed::generator()
		);

		$format   = is_null($format) ? Feed::DEFAULT_FORMAT : $format;

		$info = Arr::merge($generator, $info);
		$feed = Feed::prepare_xml($encoding);

		foreach ($info as $name => $value)
		{
			if ($name === 'image')
			{
				// Create an image element
				$image = $feed->channel->addChild('image');

				if ( ! isset($value['link'], $value['url'], $value['title']))
				{
					throw new Gleez_Exception('Feed images require a link, url, and title');
				}

				if (URL::is_absolute($value['link']))
				{
					// Convert URIs to URLs
					$value['link'] = URL::site($value['link'], 'http');
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
					$value = URL::site($value, 'http');
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
					$value = URL::site($value, 'http');
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
	 * Gets Feed Generator Title
	 *
	 * @return string
	 */
	public static function generator()
	{
		return 'Gleez CMS v'. Gleez::VERSION . ' ' . '(http://gleezcms.org)';
	}

	/**
	 * Prepare XML skeleton
	 *
	 * @link    http://php.net/manual/en/function.simplexml-load-string.php
	 *
	 * @param   string  $encoding  Define which encoding to use [Optional]
	 * @return  SimpleXMLElement
	 */
	public static function prepare_xml($encoding = NULL)
	{
		$encoding = is_null($encoding) ? Kohana::$charset : $encoding;
		$feed = '<?xml version="1.0" encoding="'.$encoding.'"?><rss version="2.0"><channel></channel></rss>';

		return simplexml_load_string($feed);
	}

	/**
	 * Gets prepared header for XML document
	 *
	 * @param   Config  $config  Site config
	 * @return  array
	 *
	 * @uses    Config_Group::get
	 * @uses    URL::site
	 */
	public static function info($config)
	{
		$info = array(
			'title'       => $config->get('site_name', 'Gleez CMS'),
			'description' => $config->get('site_mission', __('Recently added posts')),
			'pubDate'     => time(),
			'generator'   => Feed::generator(),
			'link'        => $config->get('site_url', URL::site(NULL, TRUE)),
			'copyright'   => '2011-'.date('Y') . ' ' . $config->get('site_name', 'Gleez Technologies'),
			'language'    => i18n::$lang,
			'image'	      => array(
				'link'  => $config->get('site_url', URL::site(NULL, TRUE)),
				'url'   => URL::site('/media/images/logo.png', TRUE),
				'title' => $config->get('site_name', 'Gleez CMS')
			),
		);

		return $info;
	}

}