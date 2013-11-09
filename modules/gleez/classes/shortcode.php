<?php
/**
 * Class for creating bbcode like tags "shortcodes."
 * The tag and attribute parsing or regular expression code is
 * based on the Textpattern tag parser.
 *
 * Taken from wordpress:
 * @link        http://codex.wordpress.org/Shortcode
 *
 * A few examples are below:
 *
 * [shortcode /]
 * [shortcode foo="bar" baz="bing" /]
 * [shortcode foo="bar"]content[/shortcode]
 *
 * @package    Gleez\Shortcode
 * @author     Gleez Team
 * @version    1.0.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://wordpress.org/about/license
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Shortcode {

	/**
	 * Container for storing shortcode tags and their hook to call for the shortcode
	 * @var array
	 */
	protected static $_tags = array();

	/**
	 * Indicates whether shortcodes are cached
	 * @var  boolean
	 */
	protected static $_cache = FALSE;

	/**
	 * Add hook for shortcode tag
	 *
	 * There can only be one hook for each shortcode.
	 * Which means that if another plugin has a similar shortcode, it will
	 * override yours or yours will override theirs depending on which order
	 * the plugins are included and/or ran.
	 *
	 * @param   string          $tag       Shortcode tag to be searched in post content.
	 * @param   callable        $callback  Hook to run when shortcode is found.
	 * @param   string|boolean  $asset     CSS or JS or both to be added. css|js|both [Optional]
	 *
	 * @return  array
	 *
	 * @throws  Gleez_Exception
	 *
	 * @uses    Assets::css
	 * @uses    Assets::js
	 */
	public static function set($tag, $callback, $asset = FALSE)
	{
		if ( ! is_callable($callback) )
		{
			throw new Gleez_Exception('Invalid Shortcode::callback specified');
		}

		self::$_tags[$tag] = $callback;

		if($asset AND $asset == 'css') Assets::css($tag, "media/css/shortcodes/$tag.css");
		if($asset AND $asset == 'js')  Assets::js($tag, "media/js/shortcodes/$tag.js");

		return self::$_tags;
	}

	/**
	 * Removes hook for shortcode
	 *
	 * @param   string  $tag  Shortcode tag to remove hook for.
	 * @return  array
	 */
	public static function remove($tag)
	{
		if( isset(self::$_tags[$tag]) ) unset(self::$_tags[$tag]);

		return self::$_tags;
	}

	/**
	 * Clear all shortcodes
	 *
	 * This function is simple, it clears all of the shortcode tags by
	 * replacing the shortcodes global by a empty array. This is actually
	 * a very efficient method for removing all shortcodes.
	 *
	 * @return  array
	 */
	public static function remove_all()
	{
		self::$_tags = array();

		return self::$_tags;
	}

	/**
	 * Retrieve(s) all named shortcodes
	 *
	 * Example:
	 * ~~~
	 * $filters = Shortcode::all();
	 * ~~~
	 *
	 * @return  array  Shortcodes by name
	 */
	public static function all()
	{
		return self::$_tags;
	}

	/**
	 * Saves or loads the Shortcode cache
	 *
	 * If your Shortcodes will remain the same for a long period of time,
	 * use this to reload the Shortcodes from the cache rather than redefining
	 * them on every page load.
	 *
	 * Example:
	 * ~~~
	 * if ( ! Shortcode::cache())
	 * {
	 *     // Set Shortcodes here
	 *     Shortcode::cache(TRUE);
	 * }
	 * ~~~
	 *
	 * @param   boolean  $save    Cache the current Shortcodes [Optional]
	 * @param   boolean  $append  Append, rather than replace, cached Shortcodes when loading [Optional]
	 *
	 * @return  boolean
	 *
	 * @uses    Kohana::cache
	 */
	public static function cache($save = FALSE, $append = FALSE)
	{
		$cache = Cache::instance();

		if ($save === TRUE)
		{
			// Cache all defined shortcodes
			return $cache->set('Shortcode::cache()', self::$_tags);
		}
		else
		{
			if ($tags = $cache->get('Shortcode::cache()'))
			{
				if ($append)
				{
					// Append cached Shortcodes
					self::$_tags += $tags;
				}
				else
				{
					// Replace existing Shortcodes
					self::$_tags = $tags;
				}

				// Shortcodes were cached
				return self::$_cache = TRUE;
			}
			else
			{
				// Shortcodes were not cached
				return self::$_cache = FALSE;
			}
		}
	}

	/**
	 * Search content for shortcodes and filter shortcodes through their hooks.
	 *
	 * If there are no shortcode tags defined, then the content will be returned
	 * without any filtering. This might cause issues when plugins are disabled but
	 * the shortcode will still show up in the post or content.
	 *
	 * @param       string          $content Content to search for shortcodes
	 * @return      string          Content with shortcodes filtered out.
	 */
	public static function process($content)
	{
		if (empty(self::$_tags) OR !is_array(self::$_tags))
			return $content;

		$pattern = self::get_regex();
		return preg_replace_callback( "/$pattern/s", 'self::execute', $content );
	}

	/**
	 * Regular Expression callable for do_shortcode() for calling shortcode hook.
	 * @see get_shortcode_regex for details of the match array contents.
	 *
	 * @param array $m Regular expression match array
	 * @return mixed False on failure.
	 */
	protected static function execute( $m )
	{
		// allow [[foo]] syntax for escaping a tag
		if ( $m[1] == '[' && $m[6] == ']' )
		{
			return substr($m[0], 1, -1);
		}

		$tag = $m[2];
		$attr = self::parse_atts( $m[3] );

		if ( isset( $m[5] ) )
		{
			// enclosing tag - extra parameter
			return $m[1] . call_user_func( self::$_tags[$tag], $attr, $m[5], $tag ) . $m[6];
		}
		else
		{
			// self-closing tag
			return $m[1] . call_user_func( self::$_tags[$tag], $attr, NULL,  $tag ) . $m[6];
		}
	}

	/**
	 * Retrieve the shortcode regular expression for searching.
	 *
	 * The regular expression combines the shortcode tags in the regular expression
	 * in a regex class.
	 *
	 * The regular expression contains 6 different sub matches to help with parsing.
	 *
	 * 1 - An extra [ to allow for escaping shortcodes with double [[]]
	 * 2 - The shortcode name
	 * 3 - The shortcode argument list
	 * 4 - The self closing /
	 * 5 - The content of a shortcode when it wraps some content.
	 * 6 - An extra ] to allow for escaping shortcodes with double [[]]
	 *
	 * @return string The shortcode search regular expression
	 */
	protected static function get_regex()
	{
		$tagnames = array_keys(self::$_tags);
		$tagregexp = join( '|', array_map('preg_quote', $tagnames) );

		// WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag()
		return
			'\\['                              // Opening bracket
			. '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
			. "($tagregexp)"                     // 2: Shortcode name
			. '\\b'                              // Word boundary
			. '('                                // 3: Unroll the loop: Inside the opening shortcode tag
			.     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
			.     '(?:'
			.         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
			.         '[^\\]\\/]*'               // Not a closing bracket or forward slash
			.     ')*?'
			. ')'
			. '(?:'
			.     '(\\/)'                        // 4: Self closing tag ...
			.     '\\]'                          // ... and closing bracket
			. '|'
			.     '\\]'                          // Closing bracket
			.     '(?:'
			.         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
			.             '[^\\[]*+'             // Not an opening bracket
			.             '(?:'
			.                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
			.                 '[^\\[]*+'         // Not an opening bracket
			.             ')*+'
			.         ')'
			.         '\\[\\/\\2\\]'             // Closing shortcode tag
			.     ')?'
			. ')'
			. '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
	}

	/**
	 * Retrieve all attributes from the shortcodes tag.
	 *
	 * The attributes list has the attribute name as the key and the value of the
	 * attribute as the value in the key/value pair. This allows for easier
	 * retrieval of the attributes, since all attributes have to be known.
	 *
	 * @param string $text
	 * @return array List of attributes and their value.
	 */
	public static function parse_atts($text) {
		$atts = array();
		$pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
		$text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);

		if ( preg_match_all($pattern, $text, $match, PREG_SET_ORDER) )
		{
			foreach ($match as $m)
			{
				if (!empty($m[1]))
					$atts[strtolower($m[1])] = stripcslashes($m[2]);
				elseif (!empty($m[3]))
					$atts[strtolower($m[3])] = stripcslashes($m[4]);
				elseif (!empty($m[5]))
					$atts[strtolower($m[5])] = stripcslashes($m[6]);
				elseif (isset($m[7]) and strlen($m[7]))
					$atts[] = stripcslashes($m[7]);
				elseif (isset($m[8]))
					$atts[] = stripcslashes($m[8]);
			}
		}
		else
		{
			$atts = ltrim($text);
		}

		return $atts;
	}

	/**
	 * Combine user attributes with known attributes and fill in defaults when needed.
	 *
	 * The pairs should be considered to be all of the attributes which are
	 * supported by the caller and given as a list. The returned attributes will
	 * only contain the attributes in the $pairs list.
	 *
	 * If the $atts list has unsupported attributes, then they will be ignored and
	 * removed from the final returned list.
	 *
	 * @param array $pairs Entire list of supported attributes and their defaults.
	 * @param array $atts User defined attributes in shortcode tag.
	 * @return array Combined and filtered attribute list.
	 */
	public static function attributes($pairs, $atts)
	{
		$atts = (array)$atts;
		$out = array();

		foreach($pairs as $name => $default)
		{
			if ( array_key_exists($name, $atts) )
			{
				$out[$name] = $atts[$name];
			}
			else
			{
				$out[$name] = $default;
			}
		}

		return $out;
	}

	/**
	 * Remove all shortcode tags from the given content.
	 *
	 * @param string $content Content to remove shortcode tags.
	 * @return string Content without shortcode tags.
	 */
	protected static function strip( $content )
	{
		if (empty(self::$_tags) OR !is_array(self::$_tags))
			return $content;

		$pattern = self::get_regex();
		return preg_replace_callback( "/$pattern/s", 'self::strip_tag', $content );
	}

	protected static function strip_tag( $m )
	{
		// allow [[foo]] syntax for escaping a tag
		if ( $m[1] == '[' AND $m[6] == ']' )
		{
			return substr($m[0], 1, -1);
		}

		return $m[1] . $m[6];
	}

}
