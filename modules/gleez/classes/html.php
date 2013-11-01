<?php
/**
 * HTML Helper
 *
 * Provides generic methods for generating various HTML
 * tags and making output HTML safe.
 *
 * @package    Gleez\Helpers
 * @author     Gleez Team
 * @version    1.1.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class HTML {

	/**
	 * Current route
	 * @var string
	 */
	public static $current_route;

	/**
	 * Preferred order of attributes
	 * @var array
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
	 * Use strict XHTML mode?
	 * @var boolean
	 */
	public static $strict = TRUE;

	/**
	 * Convert special characters to HTML entities
	 *
	 * All untrusted content should be passed through this method to prevent XSS injections.
	 *
	 * Example:
	 * ~~~
	 * echo HTML::chars($username);
	 * ~~~
	 *
	 * @param   string   $value          String to convert
	 * @param   boolean  $double_encode  Encode existing entities [Optional]
	 *
	 * @return  string
	 */
	public static function chars($value, $double_encode = TRUE)
	{
		return htmlspecialchars( (string) $value, ENT_QUOTES, Kohana::$charset, $double_encode);
	}

	/**
	 * Convert all applicable characters to HTML entities
	 *
	 * All characters that cannot be represented in HTML with the current character set
	 * will be converted to entities.
	 *
	 * Example:
	 * ~~~
	 * echo HTML::entities($username);
	 * ~~~
	 *
	 * @param   string   $value          String to convert
	 * @param   boolean  $double_encode  Encode existing entities [Optional]
	 *
	 * @return  string
	 */
	public static function entities($value, $double_encode = TRUE)
	{
		return htmlentities( (string) $value, ENT_QUOTES, Kohana::$charset, $double_encode);
	}

	/**
	 * Create HTML link anchors
	 *
	 * Note that the title is not escaped, to allow HTML elements within links (images, etc).
	 *
	 * Example:
	 * ~~~
	 * echo HTML::anchor('/user/profile', 'My Profile');
	 * ~~~
	 *
	 * @param   string  $uri         URL or URI string
	 * @param   string  $title       Link text [Optional]
	 * @param   array   $attributes  HTML anchor attributes [Optional]
	 * @param   mixed   $protocol    Protocol to pass to URL::base() [Optional]
	 * @param   boolean $index       Include the index page [Optional]
	 *
	 * @return  string
	 *
	 * @uses    URL::base
	 * @uses    URL::site
	 * @uses    URL::is_remote
	 */
	public static function anchor($uri, $title = NULL, array $attributes = NULL, $protocol = NULL, $index = TRUE)
	{
		if (is_null($title))
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
			if (URL::is_absolute($uri))
			{
				// Make the URI absolute for non-id anchors
				$uri = URL::site($uri, $protocol, $index);
			}
		}

		// Add the sanitized link to the attributes
		$attributes['href'] = $uri;

		return '<a'.self::attributes($attributes).'>'.$title.'</a>';
	}

	/**
	 * Creates an HTML anchor to a file
	 *
	 * Note that the title is not escaped, to allow HTML elements within links (images, etc).
	 *
	 * Example:
	 * ~~~
	 * echo HTML::file_anchor('media/doc/user_guide.pdf', 'User Guide');
	 * ~~~
	 *
	 * @param   string  $file        Name of file to link to
	 * @param   string  $title       Link text [Optional]
	 * @param   array   $attributes  HTML anchor attributes [Optional]
	 * @param   mixed   $protocol    Protocol to pass to URL::base() [Optional]
	 * @param   boolean $index       Include the index page [Optional]
	 *
	 * @return  string
	 *
	 * @uses    URL::base
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

		return '<a'.self::attributes($attributes).'>'.$title.'</a>';
	}

	/**
	 * Creates an email (mailto:) anchor
	 *
	 * Note that the title is not escaped, to allow HTML elements within links (images, etc).
	 *
	 * Example:
	 * ~~~
	 * echo HTML::mailto($address);
	 * ~~~
	 *
	 * @param   string  $email       Email address to send to
	 * @param   string  $title       Link text [Optional]
	 * @param   array   $attributes  HTML anchor attributes [Optional]
	 *
	 * @return  string
	 */
	public static function mailto($email, $title = NULL, array $attributes = NULL)
	{
		if (is_null($title))
		{
			// Use the email address as the title
			$title = $email;
		}

		return '<a href="&#109;&#097;&#105;&#108;&#116;&#111;&#058;'.$email.'"'.self::attributes($attributes).'>'.$title.'</a>';
	}

	/**
	 * Creates a script link
	 *
	 * Example:
	 * ~~~
	 * echo HTML::script('media/js/jquery.min.js');
	 * ~~~
	 *
	 * @param   string  $file        File name
	 * @param   array   $attributes  Default attributes [Optional]
	 * @param   mixed   $protocol    Protocol to pass to URL::base() [Optional]
	 * @param   boolean $index       Include the index page [Optional]
	 *
	 * @return  string
	 *
	 * @uses    URL::base
	 * @uses    URL::is_absolute
	 */
	public static function script($file, array $attributes = NULL, $protocol = NULL, $index = FALSE)
	{
		//allow theme to serve its own media assets
		if(strpos($file, 'media/js') !== FALSE AND Gleez::$installed AND strpos($file, 'guide/media') === FALSE)
		{
			$theme = Theme::$active;
			$file = str_replace(array('media/js'), "media/{$theme}/js", $file);
		}

		if (URL::is_absolute($file))
		{
			// Add the base URL
			$file = URL::site($file, $protocol, $index);
		}

		// Set the script link
		$attributes['src'] = $file;

		// Set the script type
		$attributes['type'] = 'text/javascript';

		return '<script'.self::attributes($attributes).'></script>';
	}

	/**
	 * Creates a image link
	 *
	 * Example:
	 * ~~~
	 * echo HTML::image('media/img/logo.png', array('alt' => 'My Company'));
	 * ~~~
	 *
	 * @param   string  $file        File name
	 * @param   array   $attributes  Default attributes [Optional]
	 * @param   mixed   $protocol    Protocol to pass to URL::base() [Optional]
	 * @param   boolean $index       Include the index page [Optional]
	 *
	 * @return  string
	 *
	 * @uses    URL::base
	 * @uses    URL::is_absolute
	 */
	public static function image($file, array $attributes = NULL, $protocol = NULL, $index = FALSE)
	{
		if (URL::is_absolute($file))
		{
			// Add the base URL
			$file = URL::site($file, $protocol, $index);
		}

		// Add the image link
		$attributes['src'] = $file;

		return '<img'.self::attributes($attributes).' >';
	}

	/**
	 * Compiles an array of HTML attributes into an attribute string
	 *
	 * Attributes will be sorted using HTML::$attribute_order for consistency.
	 *
	 * Example:
	 * ~~~
	 * echo '<div'.HTML::attributes($attrs).'>'.$content.'</div>';
	 * ~~~
	 *
	 * @param   array  $attributes  Attribute list [Optional]
	 *
	 * @return  string
	 */
	public static function attributes(array $attributes = NULL)
	{
		if (empty($attributes))
		{
			return '';
		}

		$sorted = array();
		foreach (self::$attribute_order as $key)
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

				if ( ! self::$strict)
				{
					// Just use a key
					$value = FALSE;
				}
			}

			// Add the attribute key
			$compiled .= ' '.$key;

			if ($value !== FALSE OR self::$strict)
			{
				// Add the attribute value
				$compiled .= '="'.self::chars($value).'"';
			}
		}

		return $compiled;
	}

	/**
	 * Creates a style sheet link element
	 *
	 * Example:
	 * ~~~
	 * echo HTML::style('media/css/screen.css');
	 * ~~~
	 *
	 * [!!] Note: Gleez by default use HTML5. In HTML5 attribute `type` not needed
	 *
	 * @param   string  $file       File name
	 * @param   array   $attrs      Default attributes [Optional]
	 * @param   mixed   $protocol   Protocol to pass to `URL::base()` [Optional]
	 * @param   boolean $index      Include the index page [Optional]
	 *
	 * @return  string
	 *
	 * @uses    URL::base
	 * @uses    URL::is_absolute
	 */
	public static function style($file, array $attrs = NULL, $protocol = NULL, $index = FALSE)
	{
		// allow theme to serve its own media assets
		if(strpos($file, 'media/css') !== FALSE AND Gleez::$installed AND strpos($file, 'guide/media') === FALSE)
		{
			$theme = Theme::$active;
			$file = str_replace(array('media/css'), "media/{$theme}/css", $file);
		}

		if (URL::is_absolute($file))
		{
			// Add the base URL
			$file = URL::site($file, $protocol, $index);
		}

		// Set the stylesheet link
		$attrs['href'] = $file;

		// Set the stylesheet rel
		$attrs['rel'] = 'stylesheet';

		return '<link'.self::attributes($attrs).'>';
	}

	/**
	 * Creates a resized image link to resize images on fly with caching
	 *
	 * Width, height and type attributes are required to resize the image.
	 *
	 * Example:
	 * ~~~
	 * echo HTML::resize('media/img/logo.png', array('alt' => 'My Company', 'width' => 50, 'height' => 50, 'type' => 'ratio'));
	 * ~~~
	 *
	 * @param   string   $file        File name
	 * @param   array    $attributes  Default attributes + type = crop|ratio [Optional]
	 * @param   mixed    $protocol    Protocol to pass to `URL::base()` [Optional]
	 * @param   boolean  $index       Include the index page [Optional]
	 *
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

		return '<img'.self::attributes($attributes).'>';
	}

	/**
	 * Print out a themed set of links
	 *
	 * @param   array  $links       Links
	 * @param   array  $attributes  Attributes, for example CSS class [Optional]
	 *
	 * @return  string
	 *
	 * @uses    Request::uri
	 */
	public static function links($links, $attributes = array('class' => 'links'))
	{
		$output = '';

		if (count($links) > 0)
		{
			$output = '<ul'. self::attributes($attributes) .'>';

			$num_links = count($links);
			$i = 1;

			foreach ($links as $item)
			{
				$class = 'link-' . $i;

				// Add first, last and active classes to the list of links to help out themers.
				if ($i == 1)
				{
					$class .= ' first';
				}

				// Check if the menu item URI is or contains the current URI
				if(is_object($item) AND self::is_active($item->link))
				{
					$class .= ' active';
				}
				elseif(is_array($item) AND self::is_active($item['link']))
				{
					$class .= ' active';
				}

				if ($i == $num_links)
				{
					$class .= ' last';
				}
				$output .= '<li'.self::attributes(array('class' => $class)) .'>';

				if( is_object($item))
				{
					$output .= self::anchor($item->link, $item->name);
				}
				elseif( is_array($item))
				{
					$output .= self::anchor($item['link'], $item['name']);
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
	 *
	 * @return  string Prepared HTML
	 *
	 * @uses    Request::uri
	 */
	public static function tabs($tabs, $attributes = array('class' => 'tabs'))
	{
		$output = '';

		if (count($tabs) > 0)
		{
			$output = '<ul'.self::attributes($attributes).'>';

			$num_links = count($tabs);
			$i = 1;

			foreach ($tabs as $tab)
			{
				$class = 'tab-' . $i;

				if(isset($tab['active']) OR ( isset($tab['link']) AND self::is_active($tab['link'])))
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

				$output .= '<li'.self::attributes(array('class' => $class)).'>';

				//sanitized link text
				$tab['text'] = Text::plain( $tab['text'] );

				if(empty($tab['link']))
				{
					$output .= '<span class="active">'.$tab['text'].'</span>';
				}
				else
				{
					$output .= self::anchor($tab['link'], $tab['text']);
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
	 *
	 * @return  boolean
	 */
	public static function is_active($uri)
	{
		return URL::is_active($uri);
	}

	/**
	 * JavaScript source code block
	 *
	 * @param   string  $source  Script source
	 * @param   string  $type    Script type [Optional]
	 *
	 * @return  string
	 */
	public static function script_source($source, $type = 'text/javascript')
	{
		$compiled = '';

		if (is_array($source))
		{
			foreach ($source as $script)
			{
				$compiled .= self::script_source($script);
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
	 *
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

		return self::image(Route::get('media')->uri(array('file' => 'images/spacer.gif')), $attr);
	}

	/**
	 * Create a iconic button
	 *
	 * Example:
	 * ~~~
	 * echo HTML::icon('/paths/edit/1', 'icon-edit', array('class'=>'action-edit', 'title'=> __('Edit Alias')));
	 * ~~~
	 *
	 * @param   string  $url    URL
	 * @param   string  $icon   FontAwesome like icon  class
	 * @param   array   $attrs  Attributes, for example CSS class or title [Optional]
	 *
	 * @return  string
	 */
	public static function icon($url, $icon, array $attrs = array())
	{
		return self::anchor($url, '<i class="'.$icon.'"></i>', $attrs);
	}

	/**
	 * Create a bootstrap label
	 *
	 * Example:
	 * ~~~
	 * echo HTML::label(__('Publish'), 'info');
	 * ~~~
	 *
	 * @param   string  $text   Text
	 * @param   string  $label  Bootstrap label class [Optional]
	 *
	 * @return  string
	 */
	public static function label($text, $label = 'default')
	{
		switch (strtolower($label))
		{
			case 'publish':
				$status = 'success';
			break;
			case 'private':
			case 'notice':
				$status = 'info';
			break;
			case 'archive':
				$status = 'inverse';
			break;
			case 'debug':
			case 'draft':
				$status = 'default';
			break;
			case 'critical':
			case 'error':
			case 'emergency':
				$status = 'important';
			break;
			case 'alert':
				$status = 'warning';
			break;
			default:
				$status = $label;
		}

		return '<span class="label label-'.strtolower($status).'">'.$text.'</span>';
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
