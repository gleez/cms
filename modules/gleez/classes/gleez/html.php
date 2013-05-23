<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * HTML Class Helper
 *
 * Provides generic methods for generating various HTML
 * tags and making output HTML safe.
 *
 * @package    Gleez\Helpers
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Gleez_HTML {

	/**
	 * @var  string  Current route
	 */
	public static $current_route;

	/**
	 * @var  array  preferred order of attributes
	 */
	public static $attribute_order = array
	(
		'action',
		'method',
		'type',
		'id',
		'name',
		'value',
		'href',
		'src',
		'width',
		'height',
		'cols',
		'rows',
		'size',
		'maxlength',
		'rel',
		'media',
		'accept-charset',
		'accept',
		'tabindex',
		'accesskey',
		'alt',
		'title',
		'class',
		'style',
		'selected',
		'checked',
		'readonly',
		'disabled',
	);

	/**
	 * @var  boolean  use strict XHTML mode?
	 */
	public static $strict = TRUE;

	/**
	 * @var  boolean  automatically target external URLs to a new window?
	 */
	public static $windowed_urls = TRUE;

	/**
	 * Convert special characters to HTML entities. All untrusted content
	 * should be passed through this method to prevent XSS injections.
	 *
	 *     echo HTML::chars($username);
	 *
	 * @param   string  $value          string to convert
	 * @param   boolean $double_encode  encode existing entities
	 * @return  string
	 */
	public static function chars($value, $double_encode = TRUE)
	{
		return htmlspecialchars( (string) $value, ENT_QUOTES, Kohana::$charset, $double_encode);
	}

	/**
	 * Convert all applicable characters to HTML entities. All characters
	 * that cannot be represented in HTML with the current character set
	 * will be converted to entities.
	 *
	 *     echo HTML::entities($username);
	 *
	 * @param   string  $value          string to convert
	 * @param   boolean $double_encode  encode existing entities
	 * @return  string
	 */
	public static function entities($value, $double_encode = TRUE)
	{
		return htmlentities( (string) $value, ENT_QUOTES, Kohana::$charset, $double_encode);
	}

	/**
	 * Create HTML link anchors. Note that the title is not escaped, to allow
	 * HTML elements within links (images, etc).
	 *
	 *     echo HTML::anchor('/user/profile', 'My Profile');
	 *
	 * @param   string  $uri        URL or URI string
	 * @param   string  $title      link text
	 * @param   array   $attributes HTML anchor attributes
	 * @param   mixed   $protocol   protocol to pass to URL::base()
	 * @param   boolean $index      include the index page
	 * @return  string
	 * @uses    URL::base
	 * @uses    URL::site
	 * @uses    HTML::attributes
	 */
	public static function anchor($uri, $title = NULL, array $attributes = NULL, $protocol = NULL, $index = TRUE)
	{
		if ($title === NULL)
		{
			// Use the URI as the title
			$title = $uri;
		}

		if ($uri === '')
		{
			// Only use the base URL
			$uri = URL::base($protocol, $index);
		}
		else
		{
			if (strpos($uri, '://') !== FALSE)
			{
				if (HTML::$windowed_urls === TRUE AND empty($attributes['target']))
				{
					// Make the link open in a new window
					$attributes['target'] = '_blank';
				}
			}
			elseif ($uri[0] !== '#')
			{
				// Make the URI absolute for non-id anchors
				$uri = URL::site($uri, $protocol, $index);
			}
		}

		// Add the sanitized link to the attributes
		$attributes['href'] = $uri;

		return '<a'.HTML::attributes($attributes).'>'.$title.'</a>';
	}

	/**
	 * Creates an HTML anchor to a file. Note that the title is not escaped,
	 * to allow HTML elements within links (images, etc).
	 *
	 *     echo HTML::file_anchor('media/doc/user_guide.pdf', 'User Guide');
	 *
	 * @param   string  $file       name of file to link to
	 * @param   string  $title      link text
	 * @param   array   $attributes HTML anchor attributes
	 * @param   mixed   $protocol   protocol to pass to URL::base()
	 * @param   boolean $index      include the index page
	 * @return  string
	 * @uses    URL::base
	 * @uses    HTML::attributes
	 */
	public static function file_anchor($file, $title = NULL, array $attributes = NULL, $protocol = NULL, $index = FALSE)
	{
		if ($title === NULL)
		{
			// Use the file name as the title
			$title = basename($file);
		}

		// Add the file link to the attributes
		$attributes['href'] = URL::site($file, $protocol, $index);

		return '<a'.HTML::attributes($attributes).'>'.$title.'</a>';
	}

	/**
	 * Creates an email (mailto:) anchor. Note that the title is not escaped,
	 * to allow HTML elements within links (images, etc).
	 *
	 *     echo HTML::mailto($address);
	 *
	 * @param   string  $email      email address to send to
	 * @param   string  $title      link text
	 * @param   array   $attributes HTML anchor attributes
	 * @return  string
	 * @uses    HTML::attributes
	 */
	public static function mailto($email, $title = NULL, array $attributes = NULL)
	{
		if ($title === NULL)
		{
			// Use the email address as the title
			$title = $email;
		}

		return '<a href="&#109;&#097;&#105;&#108;&#116;&#111;&#058;'.$email.'"'.HTML::attributes($attributes).'>'.$title.'</a>';
	}

	/**
	 * Creates a script link.
	 *
	 *     echo HTML::script('media/js/jquery.min.js');
	 *
	 * @param   string  $file       file name
	 * @param   array   $attributes default attributes
	 * @param   mixed   $protocol   protocol to pass to URL::base()
	 * @param   boolean $index      include the index page
	 * @return  string
	 * @uses    URL::base
	 * @uses    HTML::attributes
	 */
	public static function script($file, array $attributes = NULL, $protocol = NULL, $index = FALSE)
	{
		//allow theme to serve its own media assets
		if(strpos($file, 'media/js') !== FALSE)
		{
			$theme = Theme::$active;
			$file = str_replace(array('media/js'), "media/{$theme}/js", $file);
		}
		
		if (strpos($file, '://') === FALSE)
		{
			// Add the base URL
			$file = URL::site($file, $protocol, $index);
		}

		// Set the script link
		$attributes['src'] = $file;

		// Set the script type
		$attributes['type'] = 'text/javascript';

		return '<script'.HTML::attributes($attributes).'></script>';
	}

	/**
	 * Creates a image link.
	 *
	 *     echo HTML::image('media/img/logo.png', array('alt' => 'My Company'));
	 *
	 * @param   string  $file       file name
	 * @param   array   $attributes default attributes
	 * @param   mixed   $protocol   protocol to pass to URL::base()
	 * @param   boolean $index      include the index page
	 * @return  string
	 * @uses    URL::base
	 * @uses    HTML::attributes
	 */
	public static function image($file, array $attributes = NULL, $protocol = NULL, $index = FALSE)
	{
		if (strpos($file, '://') === FALSE)
		{
			// Add the base URL
			$file = URL::site($file, $protocol, $index);
		}

		// Add the image link
		$attributes['src'] = $file;

		return '<img'.HTML::attributes($attributes).' >';
	}

	/**
	 * Compiles an array of HTML attributes into an attribute string.
	 * Attributes will be sorted using HTML::$attribute_order for consistency.
	 *
	 *     echo '<div'.HTML::attributes($attrs).'>'.$content.'</div>';
	 *
	 * @param   array   $attributes attribute list
	 * @return  string
	 */
	public static function attributes(array $attributes = NULL)
	{
		if (empty($attributes))
			return '';

		$sorted = array();
		foreach (HTML::$attribute_order as $key)
		{
			if (isset($attributes[$key]))
			{
				// Add the attribute to the sorted list
				$sorted[$key] = $attributes[$key];
			}
		}

		// Combine the sorted attributes
		$attributes = $sorted + $attributes;

		$compiled = '';
		foreach ($attributes as $key => $value)
		{
			if ($value === NULL)
			{
				// Skip attributes that have NULL values
				continue;
			}

			if (is_int($key))
			{
				// Assume non-associative keys are mirrored attributes
				$key = $value;

				if ( ! HTML::$strict)
				{
					// Just use a key
					$value = FALSE;
				}
			}

			// Add the attribute key
			$compiled .= ' '.$key;

			if ($value OR HTML::$strict)
			{
				// Add the attribute value
				$compiled .= '="'.HTML::chars($value).'"';
			}
		}

		return $compiled;
	}

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
		//allow theme to serve its own media assets
		if(strpos($file, 'media/css') !== FALSE)
		{
			$theme = Theme::$active;
			$file = str_replace(array('media/css'), "media/{$theme}/css", $file);
		}
		
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

	/**
	 * Create a iconic button
	 *
	 * Example:
	 * <code>
	 *   echo HTML::icon('/paths/edit/1', 'icon-edit', array('class'=>'action-edit', 'title'=> __('Edit Alias')));
	 * </code>
	 *
	 * @param   string  $url    URL
	 * @param   string  $icon   FontAwesome like icon  class
	 * @param   array   $attrs  Attributes, for example CSS class or title [Optional]
	 * @return  string
	 */
	public static function icon($url, $icon, array $attrs = array())
	{
		return HTML::anchor($url, '<i class="'.$icon.'"></i>', $attrs);
	}

	/**
	 * Create a bootstrap label
	 *
	 * Example:
	 * <code>
	 *   echo HTML::label(__('Publish'), 'info');
	 * </code>
	 *
	 * @param   string  $text    Text
	 * @param   string  $label   bootstrap label  class
	 * @return  string
	 */
	public static function label($text, $label = 'default')
	{
		return '<span class="label label-'.$label.'">'.$text.'</span>';
	}

	/**
	 * Generates an array for select list with `items per page` values
	 *
	 * @return array
	 */
	public static function per_page()
	{
		return array(
			5 => 5,
			10 => 10,
			15 => 15,
			20 => 20,
			25 => 25,
			30 => 30,
			35 => 35,
			40 => 40,
			45 => 45,
			50 => 50,
			70 => 70,
			100 => 100,
			150 => 150,
			250 => 250,
			300 => 300,
		);
	}
}
