<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates and returns an XSS safe version of string, or an empty string
 * if $string is not valid UTF-8.
 *
 * Note: by design, this class does not do any permission checking.
 *
 * @package	Gleez
 * @category	Input Filter
 * @author	Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2012 Gleez Technologies
 * @license	http://gleezcms.org/license
 */
class Gleez_InputFilter {
        
        /**
	 * Allowed elements.
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
	 * Protocols that are ok for use in URIs.
	 */
	protected $allowed_protocols = array(
		'http', 'https', 'ftp', 'mailto', 'irc', 'news', 'nntp', 'callto',
                'rtsp', 'mms', 'svn',  'sftp', 'ssh', 'telnet', 'webcal',
	);

        /**
         * The text
         *
         * @var  string
         */
        protected $_text;
        
        protected $_config;
        
	protected $benchmark = FALSE;

        /**
         * Create new Core object and initialize our own settings
         *
         * @param   string   Text string to filter html
         * @param   id       Format id
         * @param   boolen   bool admin (used for admin user)
         * 
	 * @return  void
         */
        public function __construct($text, $format = 1, $filter = NULL)
        {
                // Be sure to only profile if it's enabled
		if (Kohana::$profiling === TRUE)
		{
			// Start a new benchmark
			$this->benchmark = Profiler::start('Gleez Filter', __FUNCTION__);
		}
                
                // Load the configuration for this type
                $config = Kohana::$config->load('inputfilter');
        
                if($config->allowed_protocols AND is_array($config->allowed_protocols))
                {
                        $this->allowed_protocols = $config->allowed_protocols;
                }
        
                if($config->allowed_tags AND is_array($config->allowed_tags))
                {
                        $this->allowed_tags = $config->allowed_tags;
                }
        
		if(!array_key_exists($format, $config->formats))
		{
			//make sure a valid format id exists, if not set default format id
			$format = (int) $Config->get('default_format', 1);
		}
        
                if(isset($filter['settings']['allowed_html']))
                {
                        $this->allowed_tags = preg_split('/\s+|<|>/', $filter['settings']['allowed_html'], -1,
							 PREG_SPLIT_NO_EMPTY);
                }
        
                $this->_text  = $text;
		$this->_config = $config;
        
		Kohana::$log->add(Log::DEBUG, 'Input Filter Library initialized');
        }
        
        public function __destruct()
	{
		if (isset($this->benchmark))
		{
			// Stop the benchmark
			Profiler::stop($this->benchmark);
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
         * @param   string  Text string to filter html
         * @param   int     Format id
         * @param   array   Array of allowed tags
         * 
	 * @return
         *   An XSS safe version of $string, or an empty string if $string is not
         *   valid UTF-8.
	 */
	public static function factory($text, $format = 1, $filter = NULL)
	{
                return new InputFilter($text, $format, $filter);
	}

        /**
         * Returns all available formats
         *
         */
	public static function formats()
	{
		$config = Kohana::$config->load('inputfilter');
	
		$formats = array();
		foreach($config->formats as $id => $format)
		{
			$formats[$id] = $format['name'];
		}
	
		return $formats;
	}
	
        /**
         * Returns all available filters
         *
         */
	public static function filters()
	{
		$filters =  new stdClass;
		Module::event('filter_info', $filters);
		
		return $filters->list;
	}
        
        public static function callback($callback, $text, $format, $filter)
        {
                $args = func_get_args();
                array_shift($args);
        
                if (is_string($callback) AND strpos($callback, '::') !== FALSE)
		{
			// Make the static callback into an array
			$callback = explode('::', $callback, 2);
		}
        
                if ( $callback )
		{
                        try
                        {
                            return  call_user_func_array($callback, $args);
                        }
                        catch (Exception $e)
                        {
                                Kohana::$log->add(Log::ERROR, __('Filter callback :class for :filter',
                                                        array(':class' => $e->getMessage(), 'filter' => $filter['name'])));
                                return $text;
                        }
                }
        
                return $text;
        }
        
        /**
         * Checks whether a string is valid UTF-8.
         *
         * All functions designed to filter input should use drupal_validate_utf8
         * to ensure they operate on valid UTF-8 strings to prevent bypass of the
         * filter.
         *
         * When text containing an invalid UTF-8 lead byte (0xC0 - 0xFF) is presented
         * as UTF-8 to Internet Explorer 6, the program may misinterpret subsequent
         * bytes. When these subsequent bytes are HTML control characters such as
         * quotes or angle brackets, parts of the text that were deemed safe by filters
         * end up in locations that are potentially unsafe; An onerror attribute that
         * is outside of a tag, and thus deemed safe by a filter, can be interpreted
         * by the browser as if it were inside the tag.
         *
         * The function does not return FALSE for strings containing character codes
         * above U+10FFFF, even though these are prohibited by RFC 3629.
         *
         * @param $text
         *   The text to check.
         * @return
         *   TRUE if the text is valid UTF-8, FALSE if not.
         */
        public static function valid_utf8( $string )
        {
                if (strlen($string) == 0)
                {
                        return TRUE;
                }
                // With the PCRE_UTF8 modifier 'u', preg_match() fails silently on strings
                // containing invalid UTF-8 byte sequences. It does not reject character
                // codes above U+10FFFF (represented by 4 or more octets), though.
                return (preg_match('/^./us', $string) == 1);
        }

	/**
         * Filters an HTML string to prevent cross-site-scripting (XSS) vulnerabilities.
         *
         * Based on kses by Ulf Harnhammar, see http://sourceforge.net/projects/kses.
         * For examples of various XSS attacks, see: http://ha.ckers.org/xss.html.
         *
         * This code does four things:
         * - Removes characters and constructs that can trick browsers.
         * - Makes sure all HTML entities are well-formed.
         * - Makes sure all HTML tags and attributes are well-formed.
         * - Makes sure no HTML tags contain URLs with a disallowed protocol (e.g.
         *   javascript:).
	 *
	 * 
	 * @param  string       Input string.
	 * @return string       An XSS safe version of $string, or an empty string if
	 *                      $string is not valid UTF-8.
	 */
	public function filter_xss( $string )
	{
                // Only operate on valid UTF-8 strings. This is necessary to prevent cross
                // site scripting issues on Internet Explorer 6.
                if (!self::valid_utf8($string))
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
				|                 # or
				<!--.*?-->        # a comment
				|                 # or
				<[^>]*(>|$)       # a string that starts with a <, up until the > or the end of the string
				|                 # or
				>                 # just a >
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
                
                if (!preg_match('%^<\s*(/\s*)?([a-zA-Z0-9]+)([^>]*)>?|(<!--.*?-->)$%', $string, $matches))
                {
                        // Seriously malformed
                        return '';
                }
            
                $slash = trim($matches[1]);
                $elem =& $matches[2];
                $attrlist =& $matches[3];
                $comment =& $matches[4];
                
                if ($comment)
                {
                        $elem = '!--';
                }
                
                if (!isset($allowed_html[strtolower($elem)]))
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
         * Decodes all HTML entities (including numerical ones) to regular UTF-8 bytes.
         *
         * Double-escaped entities will only be decoded once ("&amp;lt;" becomes "&lt;",
         * not "<"). Be careful when using this function, as decode_entities can revert
         * previous sanitization efforts (&lt;script&gt; will become <script>).
         *
         * @param $text
         *   The text to decode entities in.
         *
         * @return
         *   The input $text, with all HTML entities decoded once.
         */
        private function decode_entities($text)
        {
                return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        }
        
        /**
         * Processes a string of HTML attributes.
         *
         * @return
         *   Cleaned up version of the HTML attributes.
         */
        private function xss_attributes($attr)
        {
                $attrarr  = array();
                $mode     = 0;
                $attrname = '';
                
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
                                                $skip     = ($attrname == 'style' || substr($attrname, 0, 2) == 'on');
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
                                                if (!$skip)
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
                                                
                                                if (!$skip)
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
                                                
                                                if (!$skip)
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
                                                
                                                if (!$skip)
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
        ^
        (
        "[^"]*("|$)     # - a string that starts with a double quote, up until the next double quote or the end of the string
        |               # or
        \'[^\']*(\'|$)| # - a string that starts with a quote, up until the next quote or the end of the string
        |               # or
        \S              # - a non-whitespace character
        )*              # any number of the above three
        \s*             # any number of whitespaces
        /x', '', $attr);
                                $mode = 0;
                        }
                }
                
                // The attribute list ends with a valueless attribute like "selected".
                if ($mode == 1 && !$skip)
                {
                        $attrarr[] = $attrname;
                }
                return $attrarr;
        }
        
        /**
         * Processes an HTML attribute value and ensures it does not contain an URL with a disallowed protocol (e.g. javascript:).
         *
         * @param $string
         *   The string with the attribute value.
         * @param $decode
         *   (Deprecated) Whether to decode entities in the $string. Set to FALSE if the
         *   $string is in plain text, TRUE otherwise. Defaults to TRUE. This parameter
         *   is deprecated and will be removed in Drupal 8. To process a plain-text URI,
         *   call drupal_strip_dangerous_protocols() or check_url() instead.
         * @return
         *   Cleaned up and HTML-escaped version of $string.
         */
        private function xss_bad_protocol($string, $decode = TRUE)
        {
                // Get the plain text representation of the attribute value (i.e. its meaning).
                // @todo Remove the $decode parameter in Drupal 8, and always assume an HTML
                //   string that needs decoding.
                if ($decode)
                {
                        $string = $this->decode_entities($string);
                }
                return Text::plain($this->strip_dangerous_protocols($string));
        }
        
        /**
         * Strips dangerous protocols (e.g. 'javascript:') from a URI.
         *
         * This function must be called for all URIs within user-entered input prior
         * to being output to an HTML attribute value. It is often called as part of
         * check_url() or filter_xss(), but those functions return an HTML-encoded
         * string, so this function can be called independently when the output needs to
         * be a plain-text string for passing to t(), l(), drupal_attributes(), or
         * another function that will call check_plain() separately.
         *
         * @param $uri
         *   A plain-text URI that might contain dangerous protocols.
         *
         * @return
         *   A plain-text URI stripped of dangerous protocols. As with all plain-text
         *   strings, this return value must not be output to an HTML page without
         *   check_plain() being called on it. However, it can be passed to functions
         *   expecting plain-text strings.
         *
         */
        private function strip_dangerous_protocols($uri)
        {
                static $allowed_protocols;
                
                if (!isset($allowed_protocols))
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
                                if (!isset($allowed_protocols[strtolower($protocol)]))
                                {
                                        $uri = substr($uri, $colonpos + 1);
                                }
                        }
                } while ($before != $uri);
                
                return $uri;
        }


}