<?php
/**
 * AutoLink Class
 *
 * Automatically wraps unhyperlinked uri with html anchors.
 *
 * @package    Gleez\HTML
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Gleez_AutoLink {
	
	protected static $_escape_uri = array();
	
	/**
	 * filter()
	 *
	 * @param string $text
	 * @return string $text
	 **/
	public static function filter($text)
	{
		// Tags to skip and not recurse into.
		$ignore_tags = 'a|script|style|code|pre';
	
		$protocols = array('http', 'https', 'ftp', 'news', 'nntp',
							'telnet', 'mailto', 'irc', 'ssh', 'sftp', 'webcal', 'rtsp');
		$protocols = implode(':(?://)?|', $protocols) . ':(?://)?';
	
		// Prepare domain name pattern.
		// The ICANN seems to be on track towards accepting more diverse top level
		// domains, so this pattern has been "future-proofed" to allow for TLDs
		// of length 2-64.
		$domain = '(?:[A-Za-z0-9._+-]+\.)?[A-Za-z]{2,64}\b';
		$ip = '(?:[0-9]{1,3}\.){3}[0-9]{1,3}';
		$auth = '[a-zA-Z0-9:%_+*~#?&=.,/;-]+@';
		$trail = '[a-zA-Z0-9:%_+*~#&\[\]=/;?!\.,-]*[a-zA-Z0-9:%_+*~#&\[\]=/;-]';
	
		// Prepare pattern for optional trailing punctuation.
		// Even these characters could have a valid meaning for the URL, such usage is
		// rare compared to using a URL at the end of or within a sentence, so these
		// trailing characters are optionally excluded.
		$punctuation = '[\.,?!]*?';
	
		// Create an array which contains the regexps for each type of link.
		// The key to the regexp is the name of a function that is used as
		// callback function to process matches of the regexp. The callback function
		// is to return the replacement for the match. The array is used and
		// matching/replacement done below inside some loops.
		$tasks = array();
	
		// Match absolute URLs.
		$url_pattern = "(?:$auth)?(?:$domain|$ip)/?(?:$trail)?";
		$pattern = "`((?:$protocols)(?:$url_pattern))($punctuation)`";
		$tasks['Autolink::_parse_full_links'] = $pattern;
	
		// Match e-mail addresses.
		$url_pattern = "[A-Za-z0-9._-]+@(?:$domain)";
		$pattern = "`($url_pattern)`";
		$tasks['Autolink::_parse_email_links'] = $pattern;

		// Match www domains.
		$url_pattern = "www\.(?:$domain)/?(?:$trail)?";
		$pattern = "`($url_pattern)($punctuation)`";
		$tasks['Autolink::_parse_partial_links'] = $pattern;
	
		// Each type of URL needs to be processed separately. The text is joined and
		// re-split after each task, since all injected HTML tags must be correctly
		// protected before the next task.
		foreach ($tasks as $task => $pattern)
		{
			//HTML comments need to be handled separately, as they may contain HTML
			$text = preg_replace_callback('`<!--(.*?)-->`s', 'Autolink::escape', $text);

			// Split at all tags; ensures that no tags or attributes are processed.
			$chunks = preg_split('/(<.+?>)/is', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
			$chunk_type = 'text';
			$open_tag = '';

			for ($i = 0; $i < count($chunks); $i++)
			{
				if ($chunk_type == 'text')
				{
					// Only process this text if there are no unclosed $ignore_tags.
					if ($open_tag == '')
					{
						// If there is a match, inject a link into this chunk via the callback
						// function contained in $task.
						$chunks[$i] = preg_replace_callback($pattern, $task, $chunks[$i]);
					}
			
					// Text chunk is done, so next chunk must be a tag.
					$chunk_type = 'tag';
				}
				else
				{
					// Only process this tag if there are no unclosed $ignore_tags.
					if ($open_tag == '')
					{
						// Check whether this tag is contained in $ignore_tags.
						if (preg_match("`<($ignore_tags)(?:\s|>)`i", $chunks[$i], $matches)) 
							$open_tag = $matches[1];
					}
					// Otherwise, check whether this is the closing tag for $open_tag.
					else
					{
						if (preg_match("`<\/$open_tag>`i", $chunks[$i], $matches)) 
							$open_tag = '';
					}
					
					// Tag chunk is done, so next chunk must be text.
					$chunk_type = 'text';
				}
			}

			$text = implode($chunks);
			$text = preg_replace_callback('`<!--(.*?)-->`', 'Autolink::unescape', $text);
                        //$text = Autolink::unescape($text);
		}

		return $text;
	}
	
	/**
	 * url_callback()
	 *
	 * @param array $match
	 * @return string $text
	 * preg_replace callback to make links out of absolute URLs.
	*/
	protected static function _parse_full_links($match)
	{
		/*
		// The $i:th parenthesis in the regexp contains the URL.
		$i = 1;

		$match[$i] = HTML::entities($match[$i]);
		$caption = Text::plain(Autolink::_url_trim($match[$i]));
		$match[$i] = Text::plain($match[$i]);
		return '<a href="' . $match[$i] . '">' . $caption . '</a>' . $match[$i + 1];
		*/
		return HTML::anchor($match[1]);
	}

	/**
	 * preg_replace callback to make links out of domain names starting with "www."
	 */
	protected static function _parse_partial_links($match)
	{
		/*
		// The $i:th parenthesis in the regexp contains the URL.
		$i = 1;

		$match[$i] = HTML::entities($match[$i]);
		$caption = Text::plain(Autolink::_url_trim($match[$i]));
		$match[$i] = Text::plain($match[$i]);
		//return '<a href="http://' . $match[$i] . '">' . $caption . '</a>' . $match[$i + 1];
		*/
		return HTML::anchor('http://'.$match[1], $match[1]);
	}

	/**
	 * preg_replace callback to make links out of e-mail addresses.
	*/
	protected static function _parse_email_links($match)
	{
		/*
		// The $i:th parenthesis in the regexp contains the URL.
		$i = 0;

		$match[$i] = HTML::entities($match[$i]);
		$caption = Text::plain(Autolink::_url_trim($match[$i]));
		$match[$i] = Text::plain($match[$i]);
		return '<a href="mailto:' . $match[$i] . '">' . $caption . '</a>';
		*/
		return HTML::mailto($match[0]);
	}
	
	/**
	 * Shortens long URLs to http://www.example.com/long/url...
	*/
	protected static function _url_trim($text, $length = NULL)
	{
		static $_length;
		if ($length !== NULL) {
			$_length = $length;
		}

		// Use +3 for '...' string length.
		if ($_length && strlen($text) > $_length + 3) {
			$text = substr($text, 0, $_length) . '...';
		}

		return $text;
	}

	/**
	 * escape()
	 *
	 * @param array $match
	 * @return string $tag_id
	 **/
	protected static function escape($match)
	{
		//$tag_id                       = "----escape_autolink:" . md5($match[0]) . "----";
                $tag_id = md5($match[1]);
		self::$_escape_uri[$tag_id] = $match[1];
		
		return "<!--$tag_id-->";
	}
	
	/**
	 * unescape()
	 *
	 * @param string $text
	 * @return string $text
	 **/
	protected static function unescape($text)
	{
		if (!self::$_escape_uri) return $text;
	
		//$unescape = array_reverse(self::$_escape_uri);
		//return str_replace(array_keys($unescape), array_values($unescape), $text);
        
                $hash = trim($text[1]);
                $content = self::$_escape_uri[$hash];
                return "<!--$content-->";
	}
	
}
