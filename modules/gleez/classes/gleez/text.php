<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Text helper for formatting text for output for security
 *
 * Code taken from drupal filter module and anqh text class
 *
 * @package	Gleez
 * @category	Text
 * @author	Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2012 Gleez Technologies
 * @license	http://gleezcms.org/license
 */
abstract class Gleez_Text extends Kohana_Text {

	// vars for moving href links to bottom filter
	protected static $_link_count = 0;
	protected static $_link_list  = '';
	
	/**
	 * Encode special characters in a plain-text string for display as HTML.
	 *
	 * Also validates strings as UTF-8 to prevent cross site scripting attacks
	 * on Internet Explorer 6.
	 *
	 * @param $text
	 *   The text to be checked or processed.
	 *
	 * @return
	 *   An HTML safe version of $text, or an empty string if $text is not
	 *   valid UTF-8.
	 */
	public static function plain($text)
	{
		return HTML::chars($text);
	}

	/**
	 * Empty paragraph killer: because users are sometimes overzealous
	 * with the return key. Multiple returns will not break the site's style.
	 *
	 * When entering more than one carriage return, only the first will be honored.
	 * 
	 * @param $text
	 *   The text to be checked or processed.
	 */
	public static function emptyparagraph($text)
	{
		return preg_replace('#<p[^>]*>(\s|&nbsp;?)*</p>#', '', $text); 
	}
	
        /**
         * Scan input and make sure that all HTML tags are properly closed and nested.
         *
         * @param   string   Text string to filter html
         */
        public static function htmlcorrector($text)
        {
                return Text::dom_serialize(Text::dom_load($text));
        }

        /**
         * Parses an HTML snippet and returns it as a DOM object.
         *
         * This function loads the body part of a partial (X)HTML document
         * and returns a full DOMDocument object that represents this document.
         * You can use dom_serialize() to serialize this DOMDocument
         * back to a XHTML snippet.
         *
         *   The partial (X)HTML snippet to load. Invalid mark-up
         *   will be corrected on import.
         *   
         * @param   string   Text string to filter html
         * 
         * @return
         *   A DOMDocument that represents the loaded (X)HTML snippet.
         */
        static function dom_load($text)
        {
		// Ignore warnings during HTML soup loading.
		$dom_document = @DOMDocument::loadHTML('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
				"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html
				xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type"
				content="text/html; charset=utf-8" /></head><body>' . $text . '</body></html>');
		return $dom_document;
        }
        
        /**
         * Converts a DOM object back to an HTML snippet.
         *
         * The function serializes the body part of a DOMDocument
         * back to an XHTML snippet.
         *
         * The resulting XHTML snippet will be properly formatted
         * to be compatible with HTML user agents.
         *
         * @param $dom_document
         *   A DOMDocument object to serialize, only the tags below
         *   the first <body> node will be converted.
         *   
         * @return
         *   A valid (X)HTML snippet, as a string.
         */
        private static function dom_serialize($dom_document)
        {
                $body_node    = $dom_document->getElementsByTagName('body')->item(0);
                $body_content = '';
	
		foreach ($body_node->getElementsByTagName('script') as $node)
		{
			Text::_escape_cdata_element($dom_document, $node);
		}

		foreach ($body_node->getElementsByTagName('style') as $node)
		{
			Text::_escape_cdata_element($dom_document, $node, '/*', '*/');
		}
	
                foreach ($body_node->childNodes as $child_node)
                {
                        $body_content .= $dom_document->saveXML($child_node);
                }

		return preg_replace('|<([^> ]*)/>|i', '<$1 />', $body_content);
        }

	/**
	 * Adds comments around the <!CDATA section in a dom element.
	 *
	 * DOMDocument::loadHTML in filter_dom_load() makes CDATA sections from the
	 * contents of inline script and style tags.  This can cause HTML 4 browsers to
	 * throw exceptions.
	 *
	 * This function attempts to solve the problem by creating a DocumentFragment,
	 * commenting the CDATA tag.
	 *
	 * @param $dom_document
	 *   The DOMDocument containing the $dom_element.
	 * @param $dom_element
	 *   The element potentially containing a CDATA node.
	 * @param $comment_start
	 *   String to use as a comment start marker to escape the CDATA declaration.
	 * @param $comment_end
	 *   String to use as a comment end marker to escape the CDATA declaration.
	*/
	private static function _escape_cdata_element($dom_document, $dom_element, $comment_start = '//', $comment_end = '') {
		foreach ($dom_element->childNodes as $node)
		{
			if (get_class($node) == 'DOMCdataSection')
			{
				$embed_prefix = "\n<!--{$comment_start}--><![CDATA[{$comment_start} ><!--{$comment_end}\n";
				$embed_suffix = "\n{$comment_start}--><!]]>{$comment_end}\n";
				$fragment = $dom_document->createDocumentFragment();
				$fragment->appendXML($embed_prefix . $node->data . $embed_suffix);
				$dom_element->appendChild($fragment);
				$dom_element->removeChild($node);
			}
		}
	}

	/**
	 * Converts text email addresses and anchors into links. Existing links
	 * will not be altered.
	 *
	 *     echo Text::auto_link($text);
	 *
	 * [!!] This method is not foolproof since it uses regex to parse HTML.
	 *
	 * @param   string   text to auto link
	 * @return  string
	 * @uses    Text::auto_link_urls
	 * @uses    Text::auto_link_emails
	 */
	public static function autoLink($text, $format, $filter)
	{
		// Auto link emails first to prevent problems with "www.domain.com@example.com"
		return Autolink::filter($text);
	}
	
	/**
	 * Replace runs of multiple whitespace characters with a single space.
	 *
	 * @access	public
	 * @param	string	the string to normalize
	 * @return	string
	 */
	public static function normalize_spaces($string)
	{
		$normalized = $string;
		if ( ! empty($normalized))
		{
			$normalized = preg_replace('/[\s\n\r\t]+/', ' ', $string);
			$normalized = UTF8::trim($normalized);
		}
		return $normalized;
	}

	/**
	 * Extract link URLs from HTML content.
	 *
	 * @access	public
	 * @param	string	the HTML
	 * @param	boolean	remove duplicate URLs?
	 * @return	array
	 */
	public static function get_urls($html, $unique = FALSE)
	{
		$regexp = "/<a[^>]+href\s*=\s*[\"|']([^\s\"']+)[\"|'][^>]*>[^<]*<\/a>/i";
		preg_match_all($regexp, stripslashes($html), $matches);
		$matches = $matches[1];
	
		if ($unique)
		{
			$matches = array_values(array_unique($matches));
		}
	
		return $matches;
	}
	
	/**
	 * Standardize newlines
	 *
	 * @access	public
	 * @param	string	the value
	 * @return	string
	 */
	public static function standardize($value)
	{
		if (strpos($value, "\r") !== FALSE)
		{
			// Standardize newlines
			$value = str_replace(array("\r\n", "\r"), "\n", $value);
		}

		return $value;
	}
	
        /**
         * Run all the enabled filters on a piece of text.
         *
         * Note: Because filters can inject JavaScript or execute PHP code, security is
         * vital here. When a user supplies a text format, you should validate it using
         * filter_access() before accepting/using it. This is normally done in the
         * validation stage of the Form API. You should for example never make a preview
         * of content in a disallowed format.
         *
         * @param $text
         *   The text to be filtered.
         * @param $format_id
         *   The format id of the text to be filtered. If no format is assigned, the
         *   fallback format will be used.
         * @param $langcode
         *   Optional: the language code of the text to be filtered, e.g. 'en' for
         *   English. This allows filters to be language aware so language specific
         *   text replacement can be implemented.
         * @param $cache
         *   Boolean whether to cache the filtered output in the {cache_filter} table.
         *   The caller may set this to FALSE when the output is already cached
         *   elsewhere to avoid duplicate cache lookups and storage.
         */
        public static function markup($text, $format_id = FALSE, $langcode = FALSE, $cache = FALSE)
        {
		$config = Kohana::$config->load('inputfilter');
		$format_id = isset($format_id) ? (int) $format_id : (int) $config->get('default_format', 1);
        	$langcode  = isset($langcode) ? $langcode : I18n::$lang;

                // Check for a cached version of this piece of text.
                $cache_id = $format_id . ':' . $langcode . ':' . hash('sha256', $text);
                if ($cache AND $cached = Cache::instance('cache_filter')->get($cache_id))
                {
                        return $cached;
                }
               
                // Convert all Windows and Mac newlines to a single newline, so filters
                // only need to deal with one possibility.
                $text = str_replace(array("\r\n", "\r"), "\n", $text);
		//$text = str_replace('<!--break-->', '', $text);
        
                $textObj = new ArrayObject(array(
				'text' 	   => (string) $text,
				'format'   => (int)    $format_id,
				'langcode' => (string) $langcode,
				'cache'    => (bool)   $cache,
				'cache_id' => (string) $cache_id
                ), ArrayObject::ARRAY_AS_PROPS);
                
                Module::event('inputfilter', $textObj);
		
                $text = (is_string($textObj->text)) ? $textObj->text : $text;
		
                $text = Filter::process($textObj); //run all filters
		
                // Store in cache with a minimum expiration time of 1 day.
                if ($cache)
                {
                        Cache::instance('cache_filter')->set($cache_id, $text, null, time() + (60 * 60 * 24));
                }
                
                return $text;
        }
	
	/**
	 * HTML filter. Provides filtering of input into accepted HTML.
	 */
	public static function html($text, $format, $filter) {
		
		$text = (string) HTMLFilter::factory($text, $format, $filter)->render();
	
		if ($filter['settings']['html_nofollow'])
		{
			$html_dom = self::dom_load($text);
			$links = $html_dom->getElementsByTagName('a');
			foreach ($links as $link)
			{
				$link->setAttribute('rel', 'nofollow');
				
				//Shortens long URLs to http://www.example.com/long/url...
				if ($filter['settings']['url_length'])
				{
					$link->nodeValue = Text::limit_chars($link->nodeValue,
									     (int) $filter['settings']['url_length'], '....');
				}
			}
			$text = self::dom_serialize($html_dom);
		}
	
		return trim($text);
	}
	
	/**
	 * Automatically applies "p" and "br" markup to text.
	 *
	 *     echo Text::autop($text);
	 * 
	 * @see http://api.drupal.org/api/drupal/modules--filter--filter.module/function/_filter_autop
	 *
	 * @param   string   subject
	 * @return  string
	 */
	public static function autop($text, $format, $filter)
	{
		// Standardize newlines
		$text = str_replace(array("\r\n", "\r"), "\n", $text);

		// Trim whitespace on each line
		$text = preg_replace('~^[ \t]+~m', '', $text);
		$text = preg_replace('~[ \t]+$~m', '', $text);

		// All block level tags
		$block = 	'(?:table|thead|tfoot|caption|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|address|p|h[1-6]|hr)';

		// Split at opening and closing PRE, SCRIPT, STYLE, OBJECT, IFRAME tags
		// and comments. We don't apply any processing to the contents of these tags
		// to avoid messing up code. We look for matched pairs and allow basic
		// nesting. For example:
		// "processed <pre> ignored <script> ignored </script> ignored </pre> processed"
		$chunks = preg_split('@(<!--.*?-->|</?(?:pre|script|style|object|iframe|!--)[^>]*>)@i', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
		// Note: PHP ensures the array consists of alternating delimiters and literals
		// and begins and ends with a literal (inserting NULL as required).
		$ignore = FALSE;
		$ignoretag = '';
		$output = '';
	
		foreach ($chunks as $i => $chunk)
		{
			if ($i % 2)
			{
				$comment = (substr($chunk, 0, 4) == '<!--');
				if ($comment)
				{
					// Nothing to do, this is a comment.
					$output .= $chunk;
					continue;
				}
				// Opening or closing tag?
				$open = ($chunk[1] != '/');
				list($tag) = preg_split('/[ >]/', substr($chunk, 2 - $open), 2);
				if (!$ignore)
				{
					if ($open)
					{
						$ignore = TRUE;
						$ignoretag = $tag;
					}
				}
				// Only allow a matching tag to close it.
				elseif (!$open && $ignoretag == $tag)
				{
					$ignore = FALSE;
					$ignoretag = '';
				}
			}
			elseif (!$ignore)
			{
				// just to make things a little easier, pad the end
				$chunk = preg_replace('|\n*$|', '', $chunk) . "\n\n"; 
				$chunk = preg_replace('|<br />\s*<br />|', "\n\n", $chunk);
				$chunk = preg_replace('!(<' . $block . '[^>]*>)!', "\n$1", $chunk); // Space things out a little
				$chunk = preg_replace('!(</' . $block . '>)!', "$1\n\n", $chunk); // Space things out a little
				$chunk = preg_replace("/\n\n+/", "\n\n", $chunk); // take care of duplicates
				$chunk = preg_replace('/^\n|\n\s*\n$/', '', $chunk);
				// make paragraphs, including one at the end
				$chunk = '<p>' . preg_replace('/\n\s*\n\n?(.)/', "</p>\n<p>$1", $chunk) . "</p>\n"; 
				$chunk = preg_replace("|<p>(<li.+?)</p>|", "$1", $chunk); // problem with nested lists
				$chunk = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $chunk);
				$chunk = str_replace('</blockquote></p>', '</p></blockquote>', $chunk);
				// under certain strange conditions it could create a P of entirely whitespace
				$chunk = preg_replace('|<p>\s*</p>\n?|', '', $chunk); 
				$chunk = preg_replace('!<p>\s*(</?' . $block . '[^>]*>)!', "$1", $chunk);
				$chunk = preg_replace('!(</?' . $block . '[^>]*>)\s*</p>!', "$1", $chunk);
				$chunk = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $chunk); // make line breaks
				$chunk = preg_replace('!(</?' . $block . '[^>]*>)\s*<br />!', "$1", $chunk);
				$chunk = preg_replace('!<br />(\s*</?(?:p|li|div|th|pre|td|ul|ol)>)!', '$1', $chunk);
				$chunk = preg_replace('/&([^#])(?![A-Za-z0-9]{1,8};)/', '&amp;$1', $chunk);
			}
			$output .= $chunk;
		}
	
		return $output;
	}
	
	/*
	 * Move links to bottom of the text
	 *
	 * @param   string  text
	 * @param   bool    Convert URLs into links. default true
	 * @return  string
	 */
	public static function move_links_to_end($text, $auto_links = FALSE)
	{
		$search  = '/<a [^>]*href="([^"]+)"[^>]*>(.*?)<\/a>/ie';
		$replace = 'self::_links_list("\\1", "\\2")';
	
		if($auto_links)
		{
			$text = Text::auto_link($text);
		}
	
		$text = preg_replace($search, $replace, $text);
	
		// Add link list
		if ( !empty(self::$_link_list) )
		{
			$text .= __("\n\nLinks:\n") . self::$_link_list;
		}
	
		//reset these vars to defaults
		self::$_link_list  = '';
		self::$_link_count = 0;
	
		return $text;
	}
	
	/*
	 * Helper function called by preg_replace() on link replacement.
	 *
	 *  @param string $link URL of the link
	 *  @param string $display Part of the text to associate number with
	 *  @access private
	 *  @return string
	 */
	private static function _links_list( $link, $display )
	{
		if ( substr($link, 0, 7) == 'http://' OR substr($link, 0, 8) == 'https://' OR
		    substr($link, 0, 7) == 'mailto:' )
		{
			self::$_link_count++;
			self::$_link_list .= "[" . self::$_link_count . "] $link\n";
			$additional = ' <sup>[' . self::$_link_count . ']</sup>';
		}
		elseif ( substr($link, 0, 11) == 'javascript:' )
		{
			// Don't count the link; ignore it
			$additional = '';
			// what about href="#anchor" ?
		}
		else
		{
			self::$_link_count++;
			self::$_link_list .= "[" . self::$_link_count . "] " . URL::site(null, TRUE);
		
			if ( substr($link, 0, 1) != '/' )
			{
				self::$_link_list .= '/';
			}
		
			self::$_link_list .= "$link\n";
			$additional = ' <sup>[' . self::$_link_count . ']</sup>';
		}

		return $display . $additional;
	}
	
	/*
	 * Highlights search terms in a string.
	 *
	 * @param   string  string to highlight terms in
	 * @param   string  words to highlight
	 * @return  string
	*/ 
	public static function highlight($str, $keywords)
	{
		// Trim, strip tags, and replace multiple spaces with single spaces
		$keywords = preg_replace('/\s\s+/', ' ', strip_tags(trim($keywords)));

		// Highlight partial matches
		$var = '';

		foreach (explode(' ', $keywords) as $keyword)
		{
			$replacement = '<span class="highlight-partial">'.$keyword.'</span>';
			$var .= $replacement." ";

			$str = str_ireplace($keyword, $replacement, $str);
		}

		// Highlight full matches
		$str = str_ireplace(rtrim($var), '<span class="highlight">'.$keywords.'</span>', $str);

		return $str;
	}
	
	/**
	 * Reverts auto_p
	 *
	 * @static
	 * @param  string string to be processed
	 * @return string
	 */
	public static function auto_p_revert($str)
	{
	    $br = preg_match('`<br>[\\n\\r]`', $str) ? '<br>' : '<br />';
	    return preg_replace('`'.$br.'([\\n\\r])`', '$1', $str);
	}

	/**
	 * Adds <span class="ordinal"> tags around any ordinals (nd / st / th / rd)
	 *
	 * @static
	 * @see http://drupal.org/project/more_filters
	 * @param  string string to be processed
	 * @return string
	 */
	public static function ordinals($text)
	{
		// Adds <span class="ordinal"> tags around any ordinals (nd / st / th / rd).
		// One or more numbers in front ok, but ignore if ordinal is immediately followed by a number or letter.
		$processed_text = preg_replace('/([0-9]+)(nd|st|th|rd)([^a-zA-Z0-9]+)/', '$1<span class="ordinal">$2</span>$3', $text);
		return $processed_text;
	}
	
	/**
	 * Adds <span class="initial"> tag around the initial letter of each paragraph
	 *
	 * @static
	 * @see http://drupal.org/project/more_filters
	 * @param  string string to be processed
	 * @return string
	 */
	public static function initialcaps($text)
	{
		// Adds <span class="initial"> tag around the initial letter of each paragraph.
		// Only add after an opening <p> tag, ignoring any leading spaces. First letter must be a letter or number (no symbols).
		// Works with contractions.
		$processed_text = preg_replace('/(<p[^>]*>\s*)([A-Z0-9])([A-Z\'\s]{1})/i', '$1<span class="initial">$2</span>$3', $text);
		return $processed_text;
	}

	/**
	 * Converts fractions to their html equivalent (for example, 1/4 will become &frac14;)
	 *
	 * @static
	 * @see http://drupal.org/project/more_filters
	 * @param  string string to be processed
	 * @return string
	 */
	public static function fractions($text)
	{
		// Converts fractions to their html equivalent (for example, 1/4 will become &frac14;).
		$processed_text = $text;
		$processed_text = self::_replace_fraction('1/4', '&frac14;', $processed_text);
		$processed_text = self::_replace_fraction('3/4', '&frac34;', $processed_text);
		$processed_text = self::_replace_fraction('1/2', '&frac12;', $processed_text);
		$processed_text = self::_replace_fraction('1/3', '&#8531;', $processed_text);
		$processed_text = self::_replace_fraction('2/3', '&#8532;', $processed_text);
		$processed_text = self::_replace_fraction('1/8', '&#8539;', $processed_text);
		$processed_text = self::_replace_fraction('3/8', '&#8540;', $processed_text);
		$processed_text = self::_replace_fraction('5/8', '&#8541;', $processed_text);
		$processed_text = self::_replace_fraction('7/8', '&#8542;', $processed_text);
		
		return $processed_text;
	}
	
	/**
         * Returns a string with all spaces converted to underscores (by default), accented
         * characters converted to non-accented characters, and non word characters removed.
         *
         * @param string $string the string you want to slug
         * @param string $replacement will replace keys in map
         * @return string
         * @access public
         */
        public static function convert_accented_characters($string, $replacement = '-')
        {
            $string = strtolower($string);
            
            $foreign_characters = array(
                '/Ã¤|Ã¦|Ç½/' => 'ae',
                '/Ã¶|Å“/' => 'oe',
                '/Ã¼/' => 'ue',
                '/Ã„/' => 'Ae',
                '/Ãœ/' => 'Ue',
                '/Ã–/' => 'Oe',
                '/Ã€|Ã|Ã‚|Ãƒ|Ã„|Ã…|Çº|Ä€|Ä‚|Ä„|Ç/' => 'A',
                '/Ã |Ã¡|Ã¢|Ã£|Ã¥|Ç»|Ä|Äƒ|Ä…|ÇŽ|Âª/' => 'a',
                '/Ã‡|Ä†|Äˆ|ÄŠ|ÄŒ/' => 'C',
                '/Ã§|Ä‡|Ä‰|Ä‹|Ä/' => 'c',
                '/Ã|ÄŽ|Ä/' => 'D',
                '/Ã°|Ä|Ä‘/' => 'd',
                '/Ãˆ|Ã‰|ÃŠ|Ã‹|Ä’|Ä”|Ä–|Ä˜|Äš/' => 'E',
                '/Ã¨|Ã©|Ãª|Ã«|Ä“|Ä•|Ä—|Ä™|Ä›/' => 'e',
                '/Äœ|Äž|Ä |Ä¢/' => 'G',
                '/Ä|ÄŸ|Ä¡|Ä£/' => 'g',
                '/Ä¤|Ä¦/' => 'H',
                '/Ä¥|Ä§/' => 'h',
                '/ÃŒ|Ã|ÃŽ|Ã|Ä¨|Äª|Ä¬|Ç|Ä®|Ä°/' => 'I',
                '/Ã¬|Ã­|Ã®|Ã¯|Ä©|Ä«|Ä­|Ç|Ä¯|Ä±/' => 'i',
                '/Ä´/' => 'J',
                '/Äµ/' => 'j',
                '/Ä¶/' => 'K',
                '/Ä·/' => 'k',
                '/Ä¹|Ä»|Ä½|Ä¿|Å/' => 'L',
                '/Äº|Ä¼|Ä¾|Å€|Å‚/' => 'l',
                '/Ã‘|Åƒ|Å…|Å‡/' => 'N',
                '/Ã±|Å„|Å†|Åˆ|Å‰/' => 'n',
                '/Ã’|Ã“|Ã”|Ã•|ÅŒ|ÅŽ|Ç‘|Å|Æ |Ã˜|Ç¾/' => 'O',
                '/Ã²|Ã³|Ã´|Ãµ|Å|Å|Ç’|Å‘|Æ¡|Ã¸|Ç¿|Âº/' => 'o',
                '/Å”|Å–|Å˜/' => 'R',
                '/Å•|Å—|Å™/' => 'r',
                '/Åš|Åœ|Åž|Å /' => 'S',
                '/Å›|Å|ÅŸ|Å¡|Å¿/' => 's',
                '/Å¢|Å¤|Å¦/' => 'T',
                '/Å£|Å¥|Å§/' => 't',
                '/Ã™|Ãš|Ã›|Å¨|Åª|Å¬|Å®|Å°|Å²|Æ¯|Ç“|Ç•|Ç—|Ç™|Ç›/' => 'U',
                '/Ã¹|Ãº|Ã»|Å©|Å«|Å­|Å¯|Å±|Å³|Æ°|Ç”|Ç–|Ç˜|Çš|Çœ/' => 'u',
                '/Ã|Å¸|Å¶/' => 'Y',
                '/Ã½|Ã¿|Å·/' => 'y',
                '/Å´/' => 'W',
                '/Åµ/' => 'w',
                '/Å¹|Å»|Å½/' => 'Z',
                '/Åº|Å¼|Å¾/' => 'z',
                '/Ã†|Ç¼/' => 'AE',
                '/ÃŸ/' => 'ss',
                '/Ä²/' => 'IJ',
                '/Ä³/' => 'ij',
                '/Å’/' => 'OE',
                '/Æ’/' => 'f'
            );
        
            if (is_array($replacement))
            {
                $map         = $replacement;
                $replacement = '_';
            }
        
            $quotedReplacement = preg_quote($replacement, '/');
            
            $merge = array(
                '/[^\s\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]/mu' => ' ',
                '/\\s+/' => $replacement,
                sprintf('/^[%s]+|[%s]+$/', $quotedReplacement, $quotedReplacement) => ''
            );
        
            $map = $foreign_characters + $merge;
            return preg_replace(array_keys($map), array_values($map), $string);
        }
	
	private static function _replace_fraction($fraction, $html_fraction, $text)
	{
		// fraction can't be preceded or followed by a number or letter.
		$search = '/([^0-9A-Z]+)' . preg_quote($fraction, '/') . '([^0-9A-Z]+)/i';
		$replacement = '$1' . $html_fraction . '$2';
		return preg_replace($search, $replacement, $text);
	}
	
}