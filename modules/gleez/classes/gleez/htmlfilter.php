<?php
/**
 * Input HTML Filter
 *
 * Creates and returns an XSS safe version of string, or an empty string
 * if $string is not valid UTF-8.
 *
 * @todo This class does not do any permission checking.
 *
 * @package    Gleez\Security
 * @author     Gleez Team
 * @version    1.1.2
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Gleez_HTMLFilter {


	/**
	 * Allowed elements
	 * @var array
	 */
	protected $allowed_tags = array(
		// http://www.w3.org/TR/html4/struct/global.html#h-7.5.4
		'div', 'span',
		// http://www.w3.org/TR/html4/struct/links.html#h-12.2
		'a',
		// http://www.w3.org/TR/html4/struct/text.html#h-9.2.1
		'strong', 'em', 'code', 'kbd', 'dfn', 'samp', 'var', 'cite', 'abbr', 'acronym',
		// http://www.w3.org/TR/html4/struct/text.html#h-9.2.2
		'blockquote', 'q',
		// http://www.w3.org/TR/html4/struct/text.html#h-9.2.3
		'sub', 'sup',
		// http://www.w3.org/TR/html4/struct/text.html#h-9.3.1
		'p',
		// http://www.w3.org/TR/html4/struct/text.html#h-9.3.2.1
		'br',
		// http://www.w3.org/TR/html4/struct/text.html#h-9.3.4
		'pre',
		// http://www.w3.org/TR/html4/struct/text.html#h-9.4
		'ins', 'del',
		// http://www.w3.org/TR/html4/struct/lists.html#h-10.2
		'ol', 'ul', 'li',
		// http://www.w3.org/TR/html4/struct/lists.html#h-10.3
		'dl', 'dt', 'dd',
		// http://www.w3.org/TR/html4/present/graphics.html#h-15.2.1
		'b', 'i', 'u', 's', 'tt',
		// http://www.w3.org/TR/html4/struct/global.html#h-7.5.5
		'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
		// http://www.w3.org/TR/html4/struct/global.html#h-7.5.6
		'address',
		// http://www.w3.org/TR/html4/struct/dirlang.html#h-8.2.4
		'bdo',
		// http://www.w3.org/TR/html4/struct/tables.html#h-11.2.1
		'table',
		// http://www.w3.org/TR/html4/struct/tables.html#h-11.2.2
		'caption',
		// http://www.w3.org/TR/html4/struct/tables.html#h-11.2.3
		'thead', 'tfoot', 'tbody',
		// http://www.w3.org/TR/html4/struct/tables.html#h-11.2.4
		'colgroup', 'col',
		// http://www.w3.org/TR/html4/struct/tables.html#h-11.2.5
		'tr',
		// http://www.w3.org/TR/html4/struct/tables.html#h-11.2.6
		'th', 'td',
		// http://www.w3.org/TR/html4/struct/objects.html#h-13.2
		'img',
		// http://www.w3.org/TR/html4/struct/objects.html#h-13.6.1
		'map', 'area',
		// http://www.w3.org/TR/html4/present/graphics.html#h-15.2.1 (the non-deprecated ones)
		'tt', 'i', 'b', 'big', 'small',
		// http://www.w3.org/TR/html4/present/graphics.html#h-15.3
		'hr',
		// http://www.w3.org/TR/html4/present/frames.html#h-16.2.1
		'frameset',
		// http://www.w3.org/TR/html4/present/frames.html#h-16.2.2
		'frame',
		// http://www.w3.org/TR/html4/present/frames.html#h-16.4.1
		'noframes',
		// http://www.w3.org/TR/html4/present/frames.html#h-16.5
		'iframe',
	);

	/**
	 * Protocols that are ok for use in URIs
	 * @var array
	 */
	protected $allowed_protocols = array(
		'http',
		'https',
		'ftp',
		'mailto',
		'irc',
		'news',
		'nntp',
		'callto',
		'rtsp',
		'mms',
		'svn',
		'sftp',
		'ssh',
		'telnet',
		'webcal',
		'git',
	);

	/**
	 * The text
	 * @var string
	 */
	protected $_text;

	/**
	 * HTMLFilter config
	 * @var Config_Group
	 */
	protected $_config;

	/**
	 * Benchmark token
	 * @var string
	 */
	protected $_benchmark;

	/**
	 * Create new Core object and initialize our own settings
	 *
	 * @param  string   $text    Text string to filter html
	 * @param  integer  $format  Format id [Optional]
	 * @param  array    $filter  Array of allowed tags [Optional]
	 *
	 * @used   Config::load
	 * @used   Config::get
	 * @used   Profiler::start
	 */
	public function __construct($text, $format = 1, array $filter = NULL)
	{
		// Be sure to only profile if it's enabled
		if (Gleez::$profiling)
		{
			// Start a new benchmark
			$this->benchmark = Profiler::start('Gleez Filter', __FUNCTION__);
		}
		// Load the configuration for this type
		$config = Config::load('inputfilter');

		if ($config->allowed_protocols AND is_array($config->allowed_protocols))
		{
			$this->allowed_protocols = $config->allowed_protocols;
		}

		if ($config->allowed_tags AND is_array($config->allowed_tags))
		{
			$this->allowed_tags = $config->allowed_tags;
		}

		if ( ! array_key_exists($format, $config->formats))
		{
			// make sure a valid format id exists, if not set default format id
			$format = (int) $config->get('default_format', 1);
		}

		if (isset($filter['settings']['allowed_html']))
		{
			$this->allowed_tags = preg_split('/\s+|<|>/', $filter['settings']['allowed_html'], -1, PREG_SPLIT_NO_EMPTY);
		}

		$this->_text   = (string)$text;
		$this->_config = $config;

		if (Kohana::PRODUCTION !== Kohana::$environment)
		{
			Log::debug('HTML Filter Library initialized');
		}
	}

	public function __destruct()
	{
		if (isset($this->_benchmark))
		{
			// Stop the benchmark
			Profiler::stop($this->_benchmark);
		}
	}

	/**
	 * Magic method __toString()
	 * @return  string
	 */
	public function __toString()
	{
		return (string) $this->render();
	}

	/**
	 * Magic method __toString() only works on echo/print so we need this
	 * @return  string
	 */
	public function render()
	{
		return (string) $this->filter_xss($this->_text);
	}

	/**
	 * Creates and returns an XSS safe version of $string
	 *
	 * Returns an XSS safe version of `$string`, or an empty
	 * string if `$string` is not valid UTF-8.
	 *
	 * @param   string   $text    Text string to filter html
	 * @param   integer  $format  Format id [Optional]
	 * @param   array    $filter  Array of allowed tags [Optional]
	 *
	 * @return  HTMLFilter
	 */
	public static function factory($text, $format = 1, array $filter = NULL)
	{
		return new HTMLFilter($text, $format, $filter);
	}

	/**
	 * Filters an HTML string to prevent cross-site-scripting (XSS) vulnerabilities
	 *
	 * Returns an XSS safe version of $string, or an empty string if $string is not valid UTF-8.
	 *
	 * This code does four things:
	 * - Removes characters and constructs that can trick browsers
	 * - Makes sure all HTML entities are well-formed
	 * - Makes sure all HTML tags and attributes are well-formed
	 * - Makes sure no HTML tags contain URLs with a disallowed protocol (e.g. javascript:)
	 *
	 * Based on [kses](http://sourceforge.net/projects/kses) by Ulf Harnhammar.
	 * For examples of various XSS attacks, see: http://ha.ckers.org/xss.html.
	 *
	 * @param   string  $string  Input string
	 *
	 * @return  string
	 *
	 * @uses    Valid::utf8
	 */
	public function filter_xss( $string )
	{
		// Only operate on valid UTF-8 strings. This is necessary to prevent cross
		// site scripting issues on Internet Explorer 6.
		if ( ! Valid::utf8($string))
		{
			return '';
		}

		// Remove NULL characters (ignored by some browsers)
		$string = str_replace(chr(0), '', $string);

		// Remove Netscape 4 JS entities
		$string = preg_replace('%&\s*\{[^}]*(\}\s*;?|$)%', '', $string);

		// Defuse all HTML entities
		$string = str_replace('&', '&amp;', $string);

		// Change back only well-formed entities in our whitelist
		// Decimal numeric entities
		$string = preg_replace('/&amp;#([0-9]+;)/', '&#\1', $string);

		// Hexadecimal numeric entities
		$string = preg_replace('/&amp;#[Xx]0*((?:[0-9A-Fa-f]{2})+;)/', '&#x\1', $string);

		// Named entities
		$string = preg_replace('/&amp;([A-Za-z][A-Za-z0-9]*;)/', '&\1', $string);

		return preg_replace_callback('%(
			<(?=[^a-zA-Z!/])  # a lone <
			| <!--.*?-->        # a comment
			| <[^>]*(>|$)       # a string that starts with a <, up until the > or the end of the string
			| >                 # just a >
		)%x', array($this, 'xss_split'), $string);
	}

	protected function xss_split($m)
	{
		$allowed_html = array_flip($this->allowed_tags);

		$string = $m[1];

		if (substr($string, 0, 1) != '<')
		{
			// We matched a lone ">" character
			return '&gt;';
		}
		elseif (strlen($string) == 1)
		{
			// We matched a lone "<" character
			return '&lt;';
		}

		if ( ! preg_match('%^<\s*(/\s*)?([a-zA-Z0-9]+)([^>]*)>?|(<!--.*?-->)$%', $string, $matches))
		{
			// Seriously malformed
			return '';
		}

		$slash    = trim($matches[1]);
		// @todo php 5.5 issue
		$elem     = & $matches[2];
		$attrlist = & $matches[3];
		$comment  = & $matches[4];

		if ($comment)
		{
			$elem = '!--';
		}

		if ( ! isset($allowed_html[strtolower($elem)]))
		{
			// Disallowed HTML element
			return '';
		}

		if ($comment)
		{
			return $comment;
		}

		if ($slash != '')
		{
			return "</$elem>";
		}

		// Is there a closing XHTML slash at the end of the attributes?
		$attrlist    = preg_replace('%(\s?)/\s*$%', '\1', $attrlist, -1, $count);
		$xhtml_slash = $count ? ' /' : '';

		// Clean up attributes
		$attr2 = implode(' ', $this->xss_attributes($attrlist));
		$attr2 = preg_replace('/[<>]/', '', $attr2);
		$attr2 = strlen($attr2) ? ' ' . $attr2 : '';

		return "<$elem$attr2$xhtml_slash>";
	}

	/**
	 * Decodes all HTML entities (including numerical ones) to regular UTF-8 bytes
	 *
	 * Returns the input $text, with all HTML entities decoded once.
	 *
	 * [!!] Note: Be careful when using this function, as decode_entities can revert
	 *      previous sanitization efforts (&lt;script&gt; will become <script>).
	 *
	 * Double-escaped entities will only be decoded once ("&amp;lt;" becomes "&lt;",
	 * not "<").
	 *
	 * @param   string $text  The text to decode entities in.
	 *
	 * @return  string
	 */
	private function decode_entities($text)
	{
		return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Processes a string of HTML attributes
	 *
	 * Returns cleaned up version of the HTML attributes
	 *
	 * @param   string  $attr  The html attribute to process
	 *
	 * @return  array
	 */
	private function xss_attributes($attr)
	{
		$attrarr  = array();
		$mode     = 0;
		$attrname = '';
		$skip     = FALSE;

		while (strlen($attr) != 0)
		{
			// Was the last operation successful?
			$working = 0;

			switch ($mode)
			{
				case 0:
					// Attribute name, href for instance
					if (preg_match('/^([-a-zA-Z]+)/', $attr, $match))
					{
						$attrname = strtolower($match[1]);
						$skip     = ($attrname == 'style' OR substr($attrname, 0, 2) == 'on');
						$working  = $mode = 1;
						$attr     = preg_replace('/^[-a-zA-Z]+/', '', $attr);
					}
					break;

				case 1:
					// Equals sign or valueless ("selected")
					if (preg_match('/^\s*=\s*/', $attr))
					{
						$working = 1;
						$mode    = 2;
						$attr    = preg_replace('/^\s*=\s*/', '', $attr);
						break;
					}

					if (preg_match('/^\s+/', $attr))
					{
						$working = 1;
						$mode    = 0;

						if ( ! $skip)
						{
							$attrarr[] = $attrname;
						}

						$attr = preg_replace('/^\s+/', '', $attr);
					}
					break;

				case 2:
					// Attribute value, a URL after href= for instance
					if (preg_match('/^"([^"]*)"(\s+|$)/', $attr, $match))
					{
						$thisval = $this->xss_bad_protocol($match[1]);

						if ( ! $skip)
						{
							$attrarr[] = "$attrname=\"$thisval\"";
						}

						$working = 1;
						$mode    = 0;
						$attr    = preg_replace('/^"[^"]*"(\s+|$)/', '', $attr);

						break;
					}

					if (preg_match("/^'([^']*)'(\s+|$)/", $attr, $match))
					{
						$thisval = $this->xss_bad_protocol($match[1]);

						if ( ! $skip)
						{
							$attrarr[] = "$attrname='$thisval'";
						}

						$working = 1;
						$mode    = 0;
						$attr    = preg_replace("/^'[^']*'(\s+|$)/", '', $attr);

						break;
					}

					if (preg_match("%^([^\s\"']+)(\s+|$)%", $attr, $match))
					{
						$thisval = $this->xss_bad_protocol($match[1]);

						if ( ! $skip)
						{
							$attrarr[] = "$attrname=\"$thisval\"";
						}

						$working = 1;
						$mode    = 0;
						$attr    = preg_replace("%^[^\s\"']+(\s+|$)%", '', $attr);
					}
					break;
			}

			if ($working == 0)
			{
				// not well formed, remove and try again
				$attr = preg_replace('/
					^(
					"[^"]*("|$)       # - a string that starts with a double quote, up until the next double quote or the end of the string
					| \'[^\']*(\'|$)| # - a string that starts with a quote, up until the next quote or the end of the string
					\S                # - a non-whitespace character
					)*                # any number of the above three
					\s*               # any number of whitespaces
					/x', '', $attr);

				$mode = 0;
			}
		}

		// The attribute list ends with a valueless attribute like "selected".
		if ($mode == 1 AND ! $skip)
		{
			$attrarr[] = $attrname;
		}

		return $attrarr;
	}

	/**
	 * Processes an HTML attribute value and ensures it doesn't contain an URL with a disallowed protocol
	 *
	 * Returns cleaned up and HTML-escaped version of `$string`
	 *
	 * @param   string   $string  The string with the attribute value
	 * @param   boolean  $decode  Whether to decode entities in the $string? [Optional]
	 * @return  string
	 */
	private function xss_bad_protocol($string, $decode = TRUE)
	{
		// Get the plain text representation of the attribute value (i.e. its meaning).
		if ($decode)
		{
			$string = $this->decode_entities($string);
		}

		return Text::plain($this->strip_dangerous_protocols($string));
	}

	/**
	 * Strips dangerous protocols (e.g. 'javascript:') from a URI
	 *
	 * Returns a plain-text URI stripped of dangerous protocols.
	 *
	 * This function must be called for all URIs within user-entered input prior
	 * to being output to an HTML attribute value.
	 *
	 * @param   string  $uri  A plain-text URI that might contain dangerous protocols
	 *
	 * @return  string
	 */
	private function strip_dangerous_protocols($uri)
	{
		static $allowed_protocols;

		if ( ! isset($allowed_protocols))
		{
			$allowed_protocols = array_flip($this->allowed_protocols);
		}

		// Iteratively remove any invalid protocol found.
		do
		{
			$before   = $uri;
			$colonpos = strpos($uri, ':');

			if ($colonpos > 0)
			{
				// We found a colon, possibly a protocol. Verify.
				$protocol = substr($uri, 0, $colonpos);

				// If a colon is preceded by a slash, question mark or hash, it cannot
				// possibly be part of the URL scheme. This must be a relative URL, which
				// inherits the (safe) protocol of the base document.
				if (preg_match('![/?#]!', $protocol))
				{
					break;
				}

				// Check if this is a disallowed protocol. Per RFC2616, section 3.2.3
				// (URI Comparison) scheme comparison must be case-insensitive.
				if ( ! isset($allowed_protocols[strtolower($protocol)]))
				{
					$uri = substr($uri, $colonpos + 1);
				}
			}
		} while ($before != $uri);

		return $uri;
	}

}
