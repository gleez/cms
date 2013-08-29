<?php
/**
 * Manager for rendering meta tags (<link> and <meta>)
 *
 * @package    Gleez\Helpers
 * @author     Gleez Team
 * @version    1.0.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Meta {

	/**
	 * An array of meta links
	 * @var array
	 */
	public static $links = array();

	/**
	 * An array of meta tags
	 * @var array
	 */
	public static $tags = array();

	/**
	 * Meta Link wrapper
	 *
	 * Gets or sets Meta Links
	 *
	 * @param   string  $handle  The link URL [Optional]
	 * @param   array	$attrs   An associative array of link settings [Optional]
	 *
	 * @return  array   Setting returns asset array
	 * @return  string  Getting returns asset content
	 *
	 * @uses    URL::is_absolute
	 * @uses    URL::site
	 */
	public static function links($handle = NULL, array $attrs = array())
	{
		// Return all meta links
		if (is_null($handle))
		{
			return self::all_links();
		}

		$attrs['href'] = URL::is_absolute($handle) ? $handle : URL::site($handle, TRUE);

		// Make sure have only one 'canonical' link per request
		if (isset($attrs['rel']) AND $attrs['rel'] == 'canonical')
		{
			$handle = 'canonical';
		}

		return self::$links[$handle] = array('url' => $attrs['href'], 'attrs' => $attrs);
	}

	/**
	 * Get a single Meta Link
	 *
	 * @param   string  $handle  Asset name
	 *
	 * @return  string
	 *
	 * @uses    Arr::get
	 * @uses    HTML::attributes
	 */
	public static function get_link($handle)
	{
		if ( ! isset(self::$links[$handle]))
		{
			return FALSE;
		}

		$asset       = self::$links[$handle];
		$attrs       = $asset['attrs'];
		$output      = '';
		$conditional = Arr::get($attrs, 'conditional');

		if ( ! empty($conditional))
		{
			unset($attrs['conditional']);
		}

		$link = '<link'.HTML::attributes($attrs).'>';

		if (empty($conditional))
		{
			$output .= $link;
		}
		else
		{
			$output .= "<!--[if {$conditional}]>{$link}<![endif]-->";
		}

		return $output;
	}

	/**
	 * Get all Meta Links
	 *
	 * @return  string   Asset HTML
	 * @return  boolean  FALSE when Meta::$links is empty
	 */
	public static function all_links()
	{
		if (empty(self::$links))
		{
			return FALSE;
		}

		$assets = array();

		foreach (self::_sort(self::$links) as $handle => $data)
		{
			$assets[] = self::get_link($handle);
		}

		return implode(PHP_EOL, $assets).PHP_EOL;
	}

	/**
	 * Remove a Meta Link, or all
	 *
	 * @param   mixed  $handle  Asset name, or NULL to remove all [Optional]
	 *
	 * @return  mixed  Empty array or void
	 */
	public static function remove_links($handle = NULL)
	{
		if (is_null($handle))
		{
			return self::$links = array();
		}

		unset(self::$links[$handle]);
	}

	/**
	 * Meta Tag wrapper
	 *
	 * Gets or sets Meta Tags
	 *
	 * @param   string  $handle  The meta tag name [Optional]
	 * @param   string  $value	 The meta tag value [Optional]
	 * @param   array   $attrs   An associative array of tag settings [Optional]
	 *
	 * @return  array   Setting returns asset array
	 * @return  string  Getting returns asset content
	 */
	public static function tags($handle = NULL, $value = NULL, $attrs = array())
	{
		// Return all meta links
		if (is_null($handle))
		{
			return self::all_tags();
		}

		if ( ! is_array($attrs))
		{
			$attrs = array();
		}

		$name_type = isset($attrs['http_equiv']) ? 'http-equiv' : 'name';
		$attrs[$name_type] = $handle;
		$attrs['content'] = $value;

		if ($handle == 'charset')
		{
			$attrs = array();
		}

		return self::$tags[$handle] = array('handle' => $handle, 'value' => $value, 'attrs' => $attrs);
	}

	/**
	 * Get a single Meta tag
	 *
	 * @param   string   $handle  Asset name
	 *
	 * @return  string   Asset HTML
	 * @return  boolean  When $handle not exists
	 *
	 * @uses    HTML::attributes
	 */
	public static function get_tag($handle)
	{
		if ( ! isset(self::$tags[$handle]))
		{
			return FALSE;
		}

		$asset       = self::$tags[$handle];
		$attrs       = $asset['attrs'];
		$output      = '';
		$conditional = Arr::get($attrs, 'conditional');

		if ($asset['handle'] == 'charset')
		{
			return '<meta charset="'.$asset['value'].'">';
		}

		if ( ! empty($conditional))
		{
			unset($attrs['conditional']);
		}

		$meta = '<meta'.HTML::attributes($attrs).'>';
		if (empty($conditional))
		{
			$output .= $meta;
		}
		else
		{
			$output .= "<!--[if {$conditional}]>{$meta}<![endif]-->";
		}

		return $output;
	}

	/**
	 * Get all Meta Tags
	 *
	 * @return  string   Asset HTML
	 * @return  boolean  FALSE when Meta::$tags is empty
	 */
	public static function all_tags()
	{
		if (empty(self::$tags))
		{
			return FALSE;
		}

		$assets = array();

		foreach (self::_sort(self::$tags) as $handle => $data)
		{
			$assets[] = self::get_tag($handle);
		}

		return implode(PHP_EOL, $assets).PHP_EOL;
	}

	/**
	 * Remove a Meta Tag, or all
	 *
	 * @param   string|NULL  $handle  Asset name, or NULL to remove all  [Optional]
	 *
	 * @return  mixed  Empty array or void
	 */
	public static function remove_tags($handle = NULL)
	{
		if (is_null($handle))
		{
			return self::$tags = array();
		}

		unset(self::$tags[$handle]);
	}

	/**
	 * Sorts assets based on dependencies
	 *
	 * @param   array  $assets  Array of assets
	 *
	 * @return  array  Sorted array of assets
	 */
	protected static function _sort($assets)
	{
		$original = $assets;
		$sorted   = array();

		while (count($assets) > 0)
		{
			foreach ($assets as $key => $value)
			{
				// No dependencies anymore, add it to sorted
				if (empty($assets[$key]['deps']))
				{
					$sorted[$key] = $value;
					unset($assets[$key]);
				}
				else
				{
					foreach ($assets[$key]['deps'] as $k => $v)
					{
						// Remove dependency if doesn't exist, if its dependent on itself, or if the dependent is dependent on it
						if ( ! isset($original[$v]) OR $v === $key OR (isset($assets[$v]) AND in_array($key, $assets[$v]['deps'])))
						{
							unset($assets[$key]['deps'][$k]);
							continue;
						}

						// This dependency hasn't been sorted yet
						if ( ! isset($sorted[$v]))
						{
							continue;
						}

						// This dependency is taken care of, remove from list
						unset($assets[$key]['deps'][$k]);
					}
				}
			}
		}

		return $sorted;
	}

	/**
	 * Enforce static usage
	 */
	private function __construct() {}
	private function __clone() {}

}