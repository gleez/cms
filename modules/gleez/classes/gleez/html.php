<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * HTML helper class. Provides generic methods for generating various HTML
 * tags and making output HTML safe.
 *
 * @package	Gleez
 * @category	Helpers
 * @author	Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2012 Gleez Technologies
 * @license	http://gleezcms.org/license
 */
class Gleez_HTML extends Kohana_HTML {
	
	public static $windowed_urls = TRUE;
	
	public static $current_route;

	/**
	 * Creates a resized image link to resize images on fly with caching.
	 *  width, height, type attributes are required to resize the image
	 *
	 *     echo HTML::resize('media/img/logo.png', array('alt' => 'My Company', 'width' => 50, 'height' => 50, 'type' => 'ratio'));
	 *
	 * @param   string   file name
	 * @param   array    default attributes + type = crop|ratio
	 * @param   mixed    protocol to pass to URL::base()
	 * @param   boolean  include the index page
	 * @return  string
	 * @uses    URL::base
	 * @uses    HTML::attributes
	 */
	public static function resize($file, array $attributes = NULL, $protocol = NULL, $index = FALSE)
	{
		if( strlen($file) <= 1 )
		{
			return;
		}
	
		if( isset($attributes['width']))
		{
			$width = $attributes['width'];
		}

		if( isset($attributes['height']))
		{
			$height = $attributes['height'];
		}

		if( isset($attributes['type']))
		{
			$type = $attributes['type'];
			unset($attributes['type']);
		}
		else
		{
			$type = 'crop';
		}
	
		if (strpos($file, '://') === FALSE)
		{
			if( isset($width) AND isset($height) )
			{
				$file = (strpos($file, 'media/') === FALSE) ? $file : str_replace('media/', '', $file);
				$file = "media/imagecache/$type/{$width}x{$height}/$file";
			}
			// Add the base URL
			$file = URL::base($protocol, $index).$file;
		}

		// Add the image link
		$attributes['src'] = $file;

		return '<img'.HTML::attributes($attributes).' />';
	}
	
	/**
         * Print out a themed set of links.
         */
        public static function links($links, $attributes = array('class' => 'links'))
        {
		$output = '';
		
		if (count($links) > 0)
		{
			$output = '<ul'. HTML::attributes($attributes) .'>';
			
			if (is_null(HTML::$current_route))
				HTML::$current_route = Url::site(Request::current()->uri());
		
			$num_links = count($links);
			$i = 1;
			foreach ($links as $item)
			{
				$class = 'link-' . $i;
				
				// Add first, last and active classes to the list of links to help out themers.
				if ($i == 1) {
					$class .= ' first';
				}
			
				// Check if the menu item URI is or contains the current URI
				if( is_object($item) AND HTML::is_active($item->link) )
				{
					$class .= ' active';
				}
				elseif( is_array($item) AND HTML::is_active($item['link']) )
				{
					$class .= ' active';
				}
				
				if ($i == $num_links) {
					$class .= ' last';
				}
				$output .= '<li'. HTML::attributes(array('class' => $class)) .'>';
			
				if( is_object($item))
				{
					$output .= HTML::anchor($item->link, $item->name);
				}
				elseif( is_array($item))
				{
					$output .= HTML::anchor($item['link'], $item['name']);
				}
				
				$i++;
				$output .= "</li>\n";
			}
			$output .= '</ul>';
		}
		
		return $output;
        }

	/**
         * Print out a themed set of tabs.
         */
        public static function tabs($tabs, $attributes = array('class' => 'tabs'))
        {
		$output = '';
		
		if (count($tabs) > 0)
		{
			if (is_null(HTML::$current_route))
				HTML::$current_route = Url::site(Request::current()->uri());
		
			$output = '<ul'. HTML::attributes($attributes) .'>';
			
			$num_links = count($tabs);
			$i = 1;
			foreach ($tabs as $tab)
			{
				$class = 'tab-' . $i;
				
				if( isset($tab['active']) OR ( isset($tab['link']) AND HTML::is_active($tab['link']) ) )
				{
					$class .= ' active';
				}
				
				// Add first, last and active classes to the list of links to help out themers.
				if ($i == 1) {
					$class .= ' first';
				}
				if ($i == $num_links) {
					$class .= ' last';
				}
				
				$output .= '<li'. HTML::attributes(array('class' => $class)) .'>';
				
				//sanitized link text
				$tab['text'] = Text::plain( $tab['text'] );
				
				if(empty($tab['link']))
				{
					$output .= '<span class="active">'.$tab['text'].'</span>';
				}
				else
				{
					$output .= HTML::anchor($tab['link'], $tab['text']);
				}
				$i++;
				$output .= "</li>\n";
			}
			$output .= '</ul>';
		}
		
		return $output;
        }

	/**
	 * Takes a URI and will return bool true if it matches or is contained (at
	 * the start) of the current request URI.
	 *
	 * @param string $uri
	 * @return bool
	 */
	public static function is_active($uri)
	{
		if (preg_match('#^[A-Z][A-Z0-9+.\-]+://#i', $uri))
		{
			// Don't check URIs with a scheme ... not really a URI is it?
			return false;
		}
		elseif ($uri)
		{
			return strpos(HTML::$current_route, Url::site($uri)) === 0;
		}
		else
		{
			return HTML::$current_route == Url::base();
		}
	}

	/**
	 * JavaScript source code block
	 *
	 * @param   string  $source
	 * @return  string
	 */
	public static function script_source($source) {
		$compiled = '';

		if (is_array($source)) {
			foreach ($source as $script) {
				$compiled .= HTML::script_source($script);
			}
		} else {
			$compiled = implode("\n", array('<script>', /*'// <![CDATA[',*/ trim($source), /*'// ]]>',*/ '</script>'));
		}
		return $compiled;
	}
	
	/**
	 * Cleans HTML with HTML Purifier
 	 *
	 * @static
	 * @param  string dirty html
	 * @return string
	 */
	public static function purify($html)
	{
		require_once Kohana::find_file('vendor', 'htmlpurifier/library/HTMLPurifier.includes');

		// config
		$purifier_cfg = HTMLPurifier_Config::createDefault();
		$purifier_cfg->set('AutoFormat.AutoParagraph', true);
		$purifier_cfg->set('AutoFormat.RemoveEmpty', true);
		$purifier_cfg->set('AutoFormat.RemoveEmpty.RemoveNbsp', true);
		//$purifier_cfg->set('HTML.TidyLevel', 'heavy');

		$purifier = new HTMLPurifier($purifier_cfg);
		$clean = $purifier->purify($html);
		unset($purifier, $purifier_cfg);

		return $clean;
	}

}