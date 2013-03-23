<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * HTML helper class
 *
 * Provides generic methods for generating various HTML
 * tags and making output HTML safe.
 *
 * @package    Gleez\Helpers\HTML
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Gleez_HTML extends Kohana_HTML {

	/**
	 * @var  boolean  Automatically target external URLs to a new window?
	 */
	public static $windowed_urls = TRUE;

	/**
	 * @var  string  Current route
	 */
	public static $current_route;

	/**
	 * Creates a style sheet link element
	 *
	 * Example:<br>
	 * <code>
	 *	echo HTML::style('media/css/screen.css');
	 * <code>
	 *
	 * Note: Gleez by default use HTML5. In HTML5 attribute `type` not needed
	 *
	 * @param   string  $file       File name
	 * @param   array   $attrs      Default attributes [Optional]
	 * @param   mixed   $protocol   Protocol to pass to `URL::base()` [Optional]
	 * @param   boolean $index      Include the index page [Optional]
	 * @return  string
	 *
	 * @uses    URL::base
	 * @uses    URL::is_absolute
	 * @uses    HTML::attributes
	 */
	public static function style($file, array $attrs = NULL, $protocol = NULL, $index = FALSE)
	{
		if ( ! URL::is_absolute($file))
		{
			// Add the base URL
			$file = URL::site($file, $protocol, $index);
		}

		// Set the stylesheet link
		$attrs['href'] = $file;

		// Set the stylesheet rel
		$attrs['rel'] = 'stylesheet';

		return '<link'.HTML::attributes($attrs).'>';
	}

	/**
	 * Creates a resized image link to resize images on fly with caching
	 *
	 * Width, height and type attributes are required to resize the image.
	 *
	 * <code>
	 *   echo HTML::resize('media/img/logo.png', array('alt' => 'My Company', 'width' => 50, 'height' => 50, 'type' => 'ratio'));
	 * </code>
	 *
	 * @param   string   $file        File name
	 * @param   array    $attributes  Default attributes + type = crop|ratio [Optional]
	 * @param   mixed    $protocol    Protocol to pass to `URL::base()` [Optional]
	 * @param   boolean  $index       Include the index page [Optional]
	 * @return  string
	 *
	 * @uses    URL::base
	 * @uses    URL::site
	 * @uses    URL::is_remote
	 */
	public static function resize($file, array $attributes = NULL, $protocol = NULL, $index = FALSE)
	{
		if (strlen($file) <= 1)
		{
			return '';
		}

		if (isset($attributes['width']))
		{
			$width = $attributes['width'];
		}

		if (isset($attributes['height']))
		{
			$height = $attributes['height'];
		}

		if (isset($attributes['type']))
		{
			$type = $attributes['type'];
			unset($attributes['type']);
		}
		else
		{
			$type = 'crop';
		}

		if (URL::is_remote($file) === FALSE)
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

		return '<img'.HTML::attributes($attributes).'>';
	}

	/**
	 * Print out a themed set of links
	 *
	 * @param  array  $links       Links
	 * @param  array  $attributes  Attributes, for example CSS class [Optional]
	 * @return string Prepared HTML
	 *
	 * @uses   Request::uri
	 */
	public static function links($links, $attributes = array('class' => 'links'))
	{
		$output = '';

		if (count($links) > 0)
		{
			$output = '<ul'. HTML::attributes($attributes) .'>';

			if (is_null(HTML::$current_route))
			{
				HTML::$current_route = URL::site(Request::current()->uri());
			}

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
				$output .= "</li>".PHP_EOL;
			}
			$output .= '</ul>';
		}

		return $output;
	}

	/**
	 * Print out a themed set of tabs
	 *
	 * @param   array  $tabs        Tabs
	 * @param   array  $attributes  Attributes, for example CSS class [Optional]
	 * @return  string Prepared HTML
	 *
	 * @uses    Request::uri
	 */
	public static function tabs($tabs, $attributes = array('class' => 'tabs'))
	{
		$output = '';

		if (count($tabs) > 0)
		{
			if (is_null(HTML::$current_route))
			{
				HTML::$current_route = URL::site(Request::current()->uri());
			}

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
				$output .= "</li>".PHP_EOL;
			}
			$output .= '</ul>';
		}

		return $output;
	}

	/**
	 * Takes a URI and will return bool true if it matches or is contained (at
	 * the start) of the current request URI.
	 *
	 * @param   string  $uri  URI
	 * @return  boolean
	 */
	public static function is_active($uri)
	{
		if (preg_match('#^[A-Z][A-Z0-9+.\-]+://#i', $uri))
		{
			// Don't check URIs with a scheme ... not really a URI is it?
			return FALSE;
		}
		elseif ($uri)
		{
			return strpos(HTML::$current_route, URL::site($uri)) === 0;
		}
		else
		{
			return HTML::$current_route == URL::base();
		}
	}

	/**
	 * JavaScript source code block
	 *
	 * @param   string  $source  Script source
	 * @param   string  $type    Script type [Optional]
	 * @return  string
	 */
	public static function script_source($source, $type = 'text/javascript')
	{
		$compiled = '';

		if (is_array($source))
		{
			foreach ($source as $script)
			{
				$compiled .= HTML::script_source($script);
			}
		}
		else
		{
			$compiled = implode(PHP_EOL, array('<script type="'.$type.'">', trim($source), '</script>'));
		}

		return $compiled;
	}

	/**
	 * Cleans HTML with HTML Purifier
	 *
	 * @param   string  $html  Dirty html
	 * @return  string
	 *
	 * @link    http://htmlpurifier.org/ HTMLPurifier
	 */
	public static function purify($html)
	{
		// @todo
		require_once Kohana::find_file('vendor', 'htmlpurifier/library/HTMLPurifier.includes');

		// config
		$purifier_cfg = HTMLPurifier_Config::createDefault();
		$purifier_cfg->set('AutoFormat.AutoParagraph', true);
		$purifier_cfg->set('AutoFormat.RemoveEmpty', true);
		$purifier_cfg->set('AutoFormat.RemoveEmpty.RemoveNbsp', true);

		$purifier = new HTMLPurifier($purifier_cfg);
		$clean = $purifier->purify($html);

		unset($purifier, $purifier_cfg);

		return $clean;
	}

	/**
	 * Create a image tag for sprite images
	 *
	 * @param   mixed   $class  Image class name
	 * @param   string  $title  Image title [Optional]
	 * @return  string  An HTML-prepared image
	 *
	 * @uses    Route::uri
	 * @uses    HTML::image
	 */
	public static function sprite_img($class, $title = NULL)
	{
		$attr           = array();
		$attr['width']  = 16;
		$attr['height'] = 16;
		$image_class    = '';

		if (is_array($class))
		{
			foreach ($class as $name)
			{
				$image_class .= $name;
			}
		}
		elseif (is_string($class))
		{
			$image_class = $class;
		}

		$attr['class'] = 'icon ' . $image_class;

		if ( ! is_null($title))
		{
			$attr['title'] = $title;
		}

		return HTML::image(Route::get('media')->uri(array('file' => 'images/spacer.gif')), $attr);
	}

}
