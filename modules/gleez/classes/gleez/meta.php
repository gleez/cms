<?php defined('SYSPATH') or die('No direct script access.');
/**
 *  Manager for rendering meta tags (<link> and <meta>).
 *
 * @package	Gleez
 * @category	Assets
 * @author	Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2012 Gleez Technologies
 * @license	http://gleezcms.org/license
 */
class Gleez_Meta {
        
        /**
	 * @var array an array of meta links
	 */
	public static $links = array();

	/**
	 * @var array an array of meta tags
	 */
	public static $tags = array();

	/**
	 * Meta Link wrapper
	 *
	 * Gets or sets Meta Links
	 *
	 * @access	public
	 * @param	string	the link URL
	 * @param	array	an associative array of link settings
	 * @return	Setting returns asset array, getting returns asset content
	 */
	public static function links($handle = NULL, $attributes = array())
	{
                // Return all meta links
		if ($handle === NULL)
		{
			return Meta::all_links();
		}
                
		if ( ! is_array($attributes))
		{
			$attributes = array();
		}
                
		$attributes['href'] = URL::is_absolute($handle) ? $handle : URL::site($handle, TRUE);
	
		//Make sure have only one 'canonical' link per request
		if( isset($attributes['rel']) AND $attributes['rel'] == 'canonical' )
		{
			$handle = 'canonical';
		}
	
                return Meta::$links[$handle] = array(	'url' => $attributes['href'], 'attrs' => $attributes );
	}

	/**
	 * Get a single Meta Link
	 *
	 * @param   string   Asset name
	 * @return  string   Asset HTML
	 */
	public static function get_link($handle)
	{
		if ( ! isset(Meta::$links[$handle]))
		{
			return FALSE;
		}
	
		$asset = Meta::$links[$handle];
                $attributes = $asset['attrs'];
                $output = '';
        
                $conditional = Arr::get($attributes, 'conditional');
		if ( ! empty($conditional))
		{
			unset($attributes['conditional']);
		}

		$link = '<link'.HTML::attributes($attributes).' />';
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
	 * @return   string   Asset HTML
	 */
	public static function all_links()
	{
		if (empty(Meta::$links))
		{
			return FALSE;
		}
		
		foreach (Meta::_sort(Meta::$links) as $handle => $data)
		{
			$assets[] = Meta::get_link($handle);
		}
		
		return implode(PHP_EOL, $assets)."\n";
	}

	/**
	 * Remove a Meta Link, or all
	 *
	 * @param   mixed   Asset name, or `NULL` to remove all
	 * @return  mixed   Empty array or void
	 */
	public static function remove_links($handle = NULL)
	{
		if ($handle === NULL)
		{
			return Meta::$links = array();
		}
		
		unset(Meta::$links[$handle]);
	}

	/**
	 * Meta Tag wrapper
	 *
	 * Gets or sets Meta Tags
	 *
	 * @access	public
	 * @param	the meta tag name
	 * @param	string	the meta tag value
	 * @param	array	an associative array of tag settings
	 * @return	Setting returns asset array, getting returns asset content
	 */
	public static function tags($handle = NULL, $value = NULL, $attributes = array())
	{
                // Return all meta links
		if ($handle === NULL)
		{
			return Meta::all_tags();
		}
                
		if ( ! is_array($attributes))
		{
			$attributes = array();
		}
                
		$name_type = isset($attributes['http_equiv']) ? 'http-equiv' : 'name';
		$attributes[$name_type] = $handle;
		$attributes['content'] = $value;
		
                return Meta::$tags[$handle] = array(	'handle' => $handle, 'value' => $value, 'attrs' => $attributes );
	}

	/**
	 * Get a single Meta tag
	 *
	 * @param   string   Asset name
	 * @return  string   Asset HTML
	 */
	public static function get_tag($handle)
	{
		if ( ! isset(Meta::$tags[$handle]))
		{
			return FALSE;
		}
		
		$asset = Meta::$tags[$handle];
                $attributes = $asset['attrs'];
                $output = '';
        
                $conditional = Arr::get($attributes, 'conditional');
		if ( ! empty($conditional))
		{
			unset($attributes['conditional']);
		}

		$meta = '<meta'.HTML::attributes($attributes).' />';
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
	 * @return   string   Asset HTML
	 */
	public static function all_tags()
	{
		if (empty(Meta::$tags))
		{
			return FALSE;
		}
		
		foreach (Meta::_sort(Meta::$tags) as $handle => $data)
		{
			$assets[] = Meta::get_tag($handle);
		}
		
		return implode(PHP_EOL, $assets)."\n";
	}

	/**
	 * Remove a Meta Tag, or all
	 *
	 * @param   mixed   Asset name, or `NULL` to remove all
	 * @return  mixed   Empty array or void
	 */
	public static function remove_tags($handle = NULL)
	{
		if ($handle === NULL)
		{
			return Meta::$tags = array();
		}
		
		unset(Meta::$tags[$handle]);
	}

	/**
	 * Sorts assets based on dependencies
	 *
	 * @param   array   Array of assets
	 * @return  array   Sorted array of assets
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
							continue;
							
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
	private function __contruct() {}
	private function __clone() {}
	
}