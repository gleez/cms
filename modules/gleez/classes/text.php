<?php
/**
 * Text Class Helper
 *
 * Provides simple methods for working with text. Text helper for
 * formatting text for output for security Code taken from Drupal
 * filter module and text class
 *
 * @package    Gleez\Helpers
 * @author     Gleez Team
 * @version    1.3.2
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Text {

	// vars for moving href links to bottom filter
	protected static $_link_count = 0;
	protected static $_link_list  = '';

	/**
	 * Number units and text equivalents
	 * @var array
	 */
	public static $units = array(
		1000000000 => 'billion',
		1000000    => 'million',
		1000       => 'thousand',
		100        => 'hundred',
		90 => 'ninety',
		80 => 'eighty',
		70 => 'seventy',
		60 => 'sixty',
		50 => 'fifty',
		40 => 'fourty',
		30 => 'thirty',
		20 => 'twenty',
		19 => 'nineteen',
		18 => 'eighteen',
		17 => 'seventeen',
		16 => 'sixteen',
		15 => 'fifteen',
		14 => 'fourteen',
		13 => 'thirteen',
		12 => 'twelve',
		11 => 'eleven',
		10 => 'ten',
		9  => 'nine',
		8  => 'eight',
		7  => 'seven',
		6  => 'six',
		5  => 'five',
		4  => 'four',
		3  => 'three',
		2  => 'two',
		1  => 'one',
	);


	/**
	 * Limits a phrase to a given number of words
	 *
	 * Example:
	 * ~~~
	 * $text = Text::limit_words($text);
	 * ~~~
	 *
	 * @param   string  $str        Phrase to limit words of
	 * @param   integer $limit      Number of words to limit to [Optional]
	 * @param   string  $end_char   end character or entity [Optional]
	 *
	 * @return  string
	 */
	public static function limit_words($str, $limit = 100, $end_char = NULL)
	{
		$limit = (int) $limit;
		$end_char = ($end_char === NULL) ? '…' : $end_char;

		if (trim($str) === '')
			return $str;

		if ($limit <= 0)
			return $end_char;

		preg_match('/^\s*+(?:\S++\s*+){1,'.$limit.'}/u', $str, $matches);

		// Only attach the end character if the matched string is shorter
		// than the starting string.
		return rtrim($matches[0]).((strlen($matches[0]) === strlen($str)) ? '' : $end_char);
	}

	/**
	 * Limits a phrase to a given number of characters
	 *
	 * Example:
	 * ~~~
	 * $text = Text::limit_chars($text);
	 * ~~~
	 *
	 * @param   string  $str            phrase to limit characters of
	 * @param   integer $limit          number of characters to limit to
	 * @param   string  $end_char       end character or entity
	 * @param   boolean $preserve_words enable or disable the preservation of words while limiting
	 *
	 * @return  string
	 *
	 * @uses    UTF8::strlen
	 */
	public static function limit_chars($str, $limit = 100, $end_char = NULL, $preserve_words = FALSE)
	{
		$end_char = ($end_char === NULL) ? '…' : $end_char;

		$limit = (int) $limit;

		if (trim($str) === '' OR UTF8::strlen($str) <= $limit)
			return $str;

		if ($limit <= 0)
			return $end_char;

		if ($preserve_words === FALSE)
			return rtrim(UTF8::substr($str, 0, $limit)).$end_char;

		// Don't preserve words. The limit is considered the top limit.
		// No strings with a length longer than $limit should be returned.
		if ( ! preg_match('/^.{0,'.$limit.'}\s/us', $str, $matches))
			return $end_char;

		return rtrim($matches[0]).((strlen($matches[0]) === strlen($str)) ? '' : $end_char);
	}

	/**
	 * Alternates between two or more strings.
	 *
	 *     echo Text::alternate('one', 'two'); // "one"
	 *     echo Text::alternate('one', 'two'); // "two"
	 *     echo Text::alternate('one', 'two'); // "one"
	 *
	 * Note that using multiple iterations of different strings may produce
	 * unexpected results.
	 *
	 * @param   string  $str,...    strings to alternate between
	 *
	 * @return  string
	 */
	public static function alternate()
	{
		static $i;

		if (func_num_args() === 0)
		{
			$i = 0;
			return '';
		}

		$args = func_get_args();
		return $args[($i++ % count($args))];
	}

	/**
	 * Generates a random string of a given type and length
	 *
	 * Example:
	 * ~~~
	 * // 8 character random string
	 * $str = Text::random();
	 * ~~~
	 *
	 * The following types are supported:
	 * * alnum:     Upper and lower case a-z, 0-9 (default)
	 * * alpha:     Upper and lower case a-z
	 * * hexdec:    Hexadecimal characters a-f, 0-9
	 * * distinct:  Uppercase characters and numbers that cannot be confused
	 *
	 * You can also create a custom type by providing the "pool" of characters
	 * as the type.
	 *
	 * @param   string  $type    A type of pool, or a string of characters to use as the pool [Optional]
	 * @param   integer $length  Length of string to return [Optional]
	 *
	 * @return  string
	 *
	 * @uses    UTF8::split
	 * @uses    Valid::utf8
	 */
	public static function random($type = NULL, $length = 8)
	{
		if ($type === NULL)
		{
			// Default is to generate an alphanumeric string
			$type = 'alnum';
		}

		$utf8 = FALSE;

		switch ($type)
		{
			case 'alnum':
				$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
			case 'alpha':
				$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
			case 'hexdec':
				$pool = '0123456789abcdef';
			break;
			case 'numeric':
				$pool = '0123456789';
			break;
			case 'nozero':
				$pool = '123456789';
			break;
			case 'distinct':
				$pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
			break;
			default:
				$pool = (string) $type;
				$utf8 = Valid::utf8($pool);
			break;
		}

		// Split the pool into an array of characters
		$pool = ($utf8 === TRUE) ? UTF8::str_split($pool, 1) : str_split($pool, 1);

		// Largest pool key
		$max = count($pool) - 1;

		$str = '';
		for ($i = 0; $i < $length; $i++)
		{
			// Select a random character from the pool and add it to the string
			$str .= $pool[mt_rand(0, $max)];
		}

		// Make sure alnum strings contain at least one letter and one digit
		if ($type === 'alnum' AND $length > 1)
		{
			if (ctype_alpha($str))
			{
				// Add a random digit
				$str[mt_rand(0, $length - 1)] = chr(mt_rand(48, 57));
			}
			elseif (ctype_digit($str))
			{
				// Add a random letter
				$str[mt_rand(0, $length - 1)] = chr(mt_rand(65, 90));
			}
		}

		return $str;
	}

	/**
	 * Uppercase words that are not separated by spaces, using a custom
	 * delimiter or the default.
	 *
	 * Example:
	 * ~~~
	 * // returns "Content-Type"
	 * $str = Text::ucfirst('content-type');
	 * ~~~
	 *
	 * @param   string  $string     String to transform
	 * @param   string  $delimiter  Delimiter to use [Optional]
	 *
	 * @return  string
	 */
	public static function ucfirst($string, $delimiter = '-')
	{
		// Put the keys back the Case-Convention expected
		return implode($delimiter, array_map('ucfirst', explode($delimiter, $string)));
	}

	/**
	 * Reduces multiple slashes in a string to single slashes
	 *
	 * Example:
	 * ~~~
	 * // returns "foo/bar/baz"
	 * $str = Text::reduce_slashes('foo//bar/baz');
	 * ~~~
	 *
	 * @param   string  $str  String to reduce slashes of
	 *
	 * @return  string
	 */
	public static function reduce_slashes($str)
	{
		return preg_replace('#(?<!:)//+#', '/', $str);
	}

	/**
	 * Replaces the given words with a string
	 *
	 * Example:
	 * ~~~
	 * // Displays "What the #####, man!"
	 * echo Text::censor('What the frick, man!', array(
	 *     'frick' => '#####',
	 * ));
	 * ~~~
	 *
	 * @param   string  $str                    Phrase to replace words in
	 * @param   array   $badwords               Words to replace
	 * @param   string  $replacement            Replacement string [Optional]
	 * @param   boolean $replace_partial_words  Replace words across word boundaries (space, period, etc) [Optional]
	 *
	 * @return  string
	 *
	 * @uses    UTF8::strlen
	 */
	public static function censor($str, $badwords, $replacement = '#', $replace_partial_words = TRUE)
	{
		foreach ( (array) $badwords as $key => $badword)
		{
			$badwords[$key] = str_replace('\*', '\S*?', preg_quote( (string) $badword));
		}

		$regex = '('.implode('|', $badwords).')';

		if ($replace_partial_words === FALSE)
		{
			// Just using \b isn't sufficient when we need to replace a badword that already contains word boundaries itself
			$regex = '(?<=\b|\s|^)'.$regex.'(?=\b|\s|$)';
		}

		$regex = '!'.$regex.'!ui';

		if (UTF8::strlen($replacement) == 1)
		{
			$regex .= 'e';
			return preg_replace($regex, 'str_repeat($replacement, UTF8::strlen(\'$1\'))', $str);
		}

		return preg_replace($regex, $replacement, $str);
	}

	/**
	 * Finds the text that is similar between a set of words
	 *
	 * Example:
	 * ~~~
	 * // returns "fr"
	 * $match = Text::similar(array('fred', 'fran', 'free');
	 * ~~~
	 *
	 * @param   array   $words  words to find similar text of
	 *
	 * @return  string
	 */
	public static function similar(array $words)
	{
		// First word is the word to match against
		$word = current($words);

		for ($i = 0, $max = strlen($word); $i < $max; ++$i)
		{
			foreach ($words as $w)
			{
				// Once a difference is found, break out of the loops
				if ( ! isset($w[$i]) OR $w[$i] !== $word[$i])
					break 2;
			}
		}

		// Return the similar text
		return substr($word, 0, $i);
	}

	/**
	 * Converts text email addresses and anchors into links
	 *
	 * Example:
	 * ~~~
	 * echo Text::auto_link($text);
	 * ~~~
	 *
	 * @param   string  $text  Text to auto link
	 *
	 * @return  string
	 */
	public static function auto_link($text)
	{
		return Autolink::filter($text);
	}

	/**
	 * Converts text anchors into links. Existing links will not be altered.
	 *
	 * Example:
	 * ~~~
	 * echo Text::auto_link_urls($text);
	 * ~~~
	 *
	 * [!!] This method is not foolproof since it uses regex to parse HTML.
	 *
	 * @param   string  $text  Text to auto link
	 *
	 * @return  string
	 *
	 * @uses    HTML::anchor
	 */
	public static function auto_link_urls($text)
	{
		// Find and replace all http/https/ftp/ftps links that are not part of an existing html anchor
		$text = preg_replace_callback('~\b(?<!href="|">)(?:ht|f)tps?://[^<\s]+(?:/|\b)~i', 'Text::_auto_link_urls_callback1', $text);

		// Find and replace all naked www.links.com (without http://)
		return preg_replace_callback('~\b(?<!://|">)www(?:\.[a-z0-9][-a-z0-9]*+)+\.[a-z]{2,6}[^<\s]*\b~i', 'Text::_auto_link_urls_callback2', $text);
	}

	protected static function _auto_link_urls_callback1($matches)
	{
		return HTML::anchor($matches[0]);
	}

	protected static function _auto_link_urls_callback2($matches)
	{
		return HTML::anchor('http://'.$matches[0], $matches[0]);
	}

	/**
	 * Converts text email addresses into links. Existing links will not
	 * be altered.
	 *
	 *     echo Text::auto_link_emails($text);
	 *
	 * [!!] This method is not foolproof since it uses regex to parse HTML.
	 *
	 * @param   string  $text   text to auto link
	 *
	 * @return  string
	 *
	 * @uses    HTML::mailto
	 */
	public static function auto_link_emails($text)
	{
		// Find and replace all email addresses that are not part of an existing html mailto anchor
		// Note: The "58;" negative lookbehind prevents matching of existing encoded html mailto anchors
		//       The html entity for a colon (:) is &#58; or &#058; or &#0058; etc.
		return preg_replace_callback('~\b(?<!href="mailto:|58;)(?!\.)[-+_a-z0-9.]++(?<!\.)@(?![-.])[-a-z0-9.]+(?<!\.)\.[a-z]{2,6}\b(?!</a>)~i', 'Text::_auto_link_emails_callback', $text);
	}

	protected static function _auto_link_emails_callback($matches)
	{
		return HTML::mailto($matches[0]);
	}

	/**
	 * Automatically applies "p" and "br" markup to text.
	 * Basically [nl2br](http://php.net/nl2br) on steroids.
	 *
	 *     echo Text::auto_p($text);
	 *
	 * [!!] This method is not foolproof since it uses regex to parse HTML.
	 *
	 * @param   string  $str    subject
	 * @param   boolean $br     convert single linebreaks to <br />
	 * @return  string
	 */
	public static function auto_p($str, $br = TRUE)
	{
		// Trim whitespace
		if (($str = trim($str)) === '')
			return '';

		// Standardize newlines
		$str = str_replace(array("\r\n", "\r"), "\n", $str);

		// Trim whitespace on each line
		$str = preg_replace('~^[ \t]+~m', '', $str);
		$str = preg_replace('~[ \t]+$~m', '', $str);

		// The following regexes only need to be executed if the string contains html
		if ($html_found = (strpos($str, '<') !== FALSE))
		{
			// Elements that should not be surrounded by p tags
			$no_p = '(?:p|div|h[1-6r]|ul|ol|li|blockquote|d[dlt]|pre|t[dhr]|t(?:able|body|foot|head)|c(?:aption|olgroup)|form|s(?:elect|tyle)|a(?:ddress|rea)|ma(?:p|th))';

			// Put at least two linebreaks before and after $no_p elements
			$str = preg_replace('~^<'.$no_p.'[^>]*+>~im', "\n$0", $str);
			$str = preg_replace('~</'.$no_p.'\s*+>$~im', "$0\n", $str);
		}

		// Do the <p> magic!
		$str = '<p>'.trim($str).'</p>';
		$str = preg_replace('~\n{2,}~', "</p>\n\n<p>", $str);

		// The following regexes only need to be executed if the string contains html
		if ($html_found !== FALSE)
		{
			// Remove p tags around $no_p elements
			$str = preg_replace('~<p>(?=</?'.$no_p.'[^>]*+>)~i', '', $str);
			$str = preg_replace('~(</?'.$no_p.'[^>]*+>)</p>~i', '$1', $str);
		}

		// Convert single linebreaks to <br />
		if ($br === TRUE)
		{
			$str = preg_replace('~(?<!\n)\n(?!\n)~', "<br />\n", $str);
		}

		return $str;
	}

	/**
	 * Returns human readable sizes
	 *
	 * Based on original functions written by
	 * [Aidan Lister](http://aidanlister.com/repos/v/function.size_readable.php)
	 * and [Quentin Zervaas](http://www.phpriot.com/d/code/strings/filesize-format/).
	 *
	 * Example:
	 * ~~~
	 * echo Text::bytes(filesize($file));
	 * ~~~
	 *
	 * @param   integer  $bytes       Size in bytes
	 * @param   string   $force_unit  A definitive unit [Optional]
	 * @param   string   $format      The return string format [Optional]
	 * @param   boolean  $si          Whether to use SI prefixes or IEC [Optional]
	 *
	 * @return  string
	 */
	public static function bytes($bytes, $force_unit = NULL, $format = NULL, $si = TRUE)
	{
		// Format string
		$format = ($format === NULL) ? '%01.2f %s' : (string) $format;

		// IEC prefixes (binary)
		if ($si == FALSE OR strpos($force_unit, 'i') !== FALSE)
		{
			$units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
			$mod   = 1024;
		}
		// SI prefixes (decimal)
		else
		{
			$units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
			$mod   = 1000;
		}

		// Determine unit to use
		if (($power = array_search( (string) $force_unit, $units)) === FALSE)
		{
			$power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
		}

		return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
	}

	/**
	 * Format a number to human-readable text
	 *
	 * Examples:
	 * ~~~
	 * // Display: one thousand and twenty-four
	 * echo Text::number(1024);
	 *
	 * // Display: five million, six hundred and thirty-two
	 * echo Text::number(5000632);
	 * ~~~
	 *
	 * @param   integer  $number     Number to format
	 * @param   string   $separator  Seperation word [Optional]
	 *
	 * @return  string
	 */
	public static function number($number, $separator = ' and ')
	{
		// The number must always be an integer
		$number = (int) $number;

		// Uncompiled text version
		$text = array();

		// Last matched unit within the loop
		$last_unit = NULL;

		// The last matched item within the loop
		$last_item = '';

		foreach (self::$units as $unit => $name)
		{
			if ($number / $unit >= 1)
			{
				// $value = the number of times the number is divisible by unit
				$number -= $unit * ($value = (int) floor($number / $unit));
				// Temporary var for textifying the current unit
				$item = '';

				if ($unit < 100)
				{
					if ($last_unit < 100 AND $last_unit >= 20)
					{
						$last_item .= '-'.$name;
					}
					else
					{
						$item = $name;
					}
				}
				else
				{
					$item = self::number($value).' '.$name;
				}

				// In the situation that we need to make a composite number (i.e. twenty-three)
				// then we need to modify the previous entry
				if (empty($item))
				{
					array_pop($text);

					$item = $last_item;
				}

				$last_item = $text[] = $item;
				$last_unit = $unit;
			}
		}

		if (count($text) > 1)
		{
			$and = array_pop($text);
		}

		$text = implode(', ', $text);

		if (isset($and))
		{
			$text .= $separator . $and;
		}

		return $text;
	}

	/**
	 * Prevents [widow words](http://www.shauninman.com/archive/2006/08/22/widont_wordpress_plugin)
	 * by inserting a non-breaking space between the last two words.
	 *
	 *     echo Text::widont($text);
	 *
	 * @param   string  $str    text to remove widows from
	 *
	 * @return  string
	 */
	public static function widont($str)
	{
		$str = rtrim($str);
		$space = strrpos($str, ' ');

		if ($space !== FALSE)
		{
			$str = substr($str, 0, $space).'&nbsp;'.substr($str, $space + 1);
		}

		return $str;
	}

	/**
	 * Returns information about the client user agent.
	 *
	 *     // Returns "Chrome" when using Google Chrome
	 *     $browser = Text::user_agent('browser');
	 *
	 * Multiple values can be returned at once by using an array:
	 *
	 *     // Get the browser and platform with a single call
	 *     $info = Text::user_agent(array('browser', 'platform'));
	 *
	 * When using an array for the value, an associative array will be returned.
	 *
	 * @param   string  $agent
	 * @param   mixed   $value  array or string to return: browser, version, robot, mobile, platform
	 *
	 * @return  mixed
	 */
	public static function user_agent($agent, $value)
	{
		if (is_array($value))
		{
			$data = array();
			foreach ($value as $part)
			{
				// Add each part to the set
				$data[$part] = self::user_agent($agent, $part);
			}

			return $data;
		}

		if ($value === 'browser' OR $value == 'version')
		{
			// Extra data will be captured
			$info = array();

			// Load browsers
			$browsers = Config::get('user_agents.browser');

			foreach ($browsers as $search => $name)
			{
				if (stripos($agent, $search) !== FALSE)
				{
					// Set the browser name
					$info['browser'] = $name;

					if (preg_match('#'.preg_quote($search).'[^0-9.]*+([0-9.][0-9.a-z]*)#i', Request::$user_agent, $matches))
					{
						// Set the version number
						$info['version'] = $matches[1];
					}
					else
					{
						// No version number found
						$info['version'] = FALSE;
					}

					return $info[$value];
				}
			}
		}
		else
		{
			// Load the search group for this type
			$group = Config::get("user_agents.{$value}");

			foreach ($group as $search => $name)
			{
				if (stripos($agent, $search) !== FALSE)
				{
					// Set the value name
					return $name;
				}
			}
		}

		// The value requested could not be found
		return FALSE;
	}

	/**
	 * Turns an array of strings/ints into a readable, comma separated list
	 *
	 * @param   array    $words         An array of words
	 * @param   string   $conjunction   The conjunction term used (e.g. 'and', 'or' etc.) [Optional]
	 * @param   boolean  $serial_comma  Whether a serial comma should be used [Optional]
	 *
	 * @return  string
	 *
	 * @throws  InvalidArgumentException
	 */
	public static function readable_list(array $words, $conjunction = 'and', $serial_comma = TRUE)
	{
		// First, validate that the method parameters are suitable.
		foreach ($words as $word)
		{
			// Check that the word isn't an array itself
			if (is_array($word))
			{
				throw new InvalidArgumentException('The array must only have one dimension.');
			}
			// Check that the value of the word is appropriate
			elseif ( ! is_string($word) AND ! is_int($word) AND ! (is_object($word) AND method_exists($word, '__toString')))
			{
				throw new InvalidArgumentException('Array values must be either strings or integers.');
			}
		}

		// Build the 'readable list'
		$last_word = array_pop($words);
		$string = implode(', ', $words).($serial_comma ? ', ' : ' ').$conjunction.' '.$last_word;

		// Return the 'readable list'
		return $string;
	}

	/**
	 * Encode special characters in a plain-text string for display as HTML.
	 *
	 * Also validates strings as UTF-8 to prevent cross site scripting attacks
	 * on Internet Explorer 6.
	 *
	 * @param  string  $text  The text to be checked or processed.
	 *
	 * @return  string
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
	 * @param   string|array  $text  The text to be checked or processed
	 *
	 * @return  mixed
	 */
	public static function emptyparagraph($text)
	{
		return preg_replace('#<p[^>]*>(\s|&nbsp;?)*</p>#', '', $text);
	}

	/**
	 * Scan input and make sure that all HTML tags are properly closed and nested.
	 *
	 * @param   string   Text string to filter html
	 *
	 * @return  mixed
	 */
	public static function htmlcorrector($text)
	{
		return self::dom_serialize(self::dom_load($text));
	}

	/**
	 * Parses an HTML snippet and returns it as a DOM object
	 *
	 * This function loads the body part of a partial HTML document and returns
	 * a full DOMDocument object that represents this document.
	 *
	 * You can use [Text::dom_serialize] to serialize this DOMDocument
	 * back to a HTML snippet.
	 *
	 * @param   string       Text string to filter html
	 *
	 * @return  DOMDocument
	 */
	public static function dom_load($text)
	{
		$dom = new DOMDocument;

		// Ignore warnings during HTML soup loading.
		@$dom->loadHTML('<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>' . $text . '</body></html>');

		return $dom;
	}

	/**
	 * Converts a DOM object back to an HTML snippet
	 *
	 * The function serializes the body part of a DOMDocument
	 * back to an HTML snippet.
	 *
	 * The resulting HTML snippet will be properly formatted
	 * to be compatible with HTML user agents.
	 *
	 * @param   DOMDocument  $dom_document  A DOMDocument object to serialize
	 *
	 * @return  string
	 */
	private static function dom_serialize(DOMDocument $dom_document)
	{
		$body_node    = $dom_document->getElementsByTagName('body')->item(0);
		$body_content = '';

		foreach ($body_node->getElementsByTagName('script') as $node)
		{
			self::escape_cdata_element($dom_document, $node);
		}

		foreach ($body_node->getElementsByTagName('style') as $node)
		{
			self::escape_cdata_element($dom_document, $node, '/*', '*/');
		}

		foreach ($body_node->childNodes as $child_node)
		{
			$body_content .= $dom_document->saveXML($child_node);
		}

		return preg_replace('|<([^> ]*)/>|i', '<$1 />', $body_content);
	}

	/**
	 * Adds comments around the <!CDATA section in a dom element
	 *
	 * This function attempts to solve the problem by creating a DocumentFragment,
	 * commenting the CDATA tag.
	 *
	 * @param  DOMDocument  $dom_document   The DOMDocument containing the $dom_element
	 * @param  DOMElement   $dom_element    The element potentially containing a CDATA node
	 * @param  string       $comment_start  String to use as a comment start marker to escape the CDATA declaration [Optional]
	 * @param  string       $comment_end    String to use as a comment end marker to escape the CDATA declaration [Optional]
	*/
	private static function escape_cdata_element(DOMDocument $dom_document, DOMElement $dom_element, $comment_start = '//', $comment_end = '')
	{
		foreach ($dom_element->childNodes as $node)
		{
			if (get_class($node) == 'DOMCdataSection')
			{
				$embed_prefix = PHP_EOL."<!--{$comment_start}--><![CDATA[{$comment_start} ><!--{$comment_end}".PHP_EOL;
				$embed_suffix = PHP_EOL."{$comment_start}--><!]]>{$comment_end}".PHP_EOL;

				// Prevent invalid cdata escaping as this would throw a DOM error.
				// This is the same behavior as found in libxml2.
				// Related W3C standard: http://www.w3.org/TR/REC-xml/#dt-cdsection
				// Fix explanation: http://en.wikipedia.org/wiki/CDATA#Nesting
				$data = str_replace(']]>', ']]]]><![CDATA[>', $node->data);

				$fragment = $dom_document->createDocumentFragment();
				$fragment->appendXML($embed_prefix . $data . $embed_suffix);

				$dom_element->appendChild($fragment);
				$dom_element->removeChild($node);
			}
		}
	}


	/**
	 * Replace runs of multiple whitespace characters with a single space
	 *
	 * @param   string  $string  The string to normalize
	 *
	 * @return  string
	 *
	 * @uses    UTF8::trim
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
	 * Extract link URLs from HTML content
	 *
	 * @param	string	$html    The HTML [Optional]
	 * @param	boolean	$unique  Remove duplicate URLs? [Optional]
	 *
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
	 * @param	string	$value  The value
	 *
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
	 * @param   string   $text       The text to be filtered
	 * @param   integer  $format_id  The format id of the text to be filtered. If no format is assigned, the fallback format will be used [Optional]
	 * @param   string   $langcode   The language code of the text to be filtered, e.g. 'en' for English. This allows filters to be language aware so language specific text replacement can be implemented [Optional]
	 * @param   boolean  $cache      Boolean whether to cache the filtered output in the {cache_filter} table. The caller may set this to FALSE when the output is already cached elsewhere to avoid duplicate cache lookups and storage [Optional]
	 *
	 * @return  mixed
	 *
	 * @uses    Config::load
	 * @uses    Config_Group::get
	 * @uses    Cache::get
	 * @uses    Cache::set
	 * @uses    Module::event
	 * @uses    Filter::process
	 *
	 * @todo    Make @params description shorter
	 */
	public static function markup($text, $format_id = NULL, $langcode = NULL, $cache = FALSE)
	{
		// Save some cpu cycles if text is empty or null
		if(empty($text))
		{
			return $text;
		}

		$format_id = is_null($format_id) ? Config::get('inputfilter.default_format', 1) : $format_id;
		$langcode  = is_null($langcode) ? I18n::$lang : $langcode;

		// Check for a cached version of this piece of text.
		$cache_id = $format_id . ':' . $langcode . ':' . hash('sha256', $text);
		if ($cache AND $cached = Cache::instance('cache_filter')->get($cache_id))
		{
			return $cached;
		}

		// Convert all Windows and Mac newlines to a single newline, so filters
		// only need to deal with one possibility.
		$text = str_replace(array("\r\n", "\r"), "\n", $text);

		$textObj = new ArrayObject(array(
				'text' 	   => (string) $text,
				'format'   => (int)    $format_id,
				'langcode' => (string) $langcode,
				'cache'    => (bool)   $cache,
				'cache_id' => (string) $cache_id
		), ArrayObject::ARRAY_AS_PROPS);

		Module::event('inputfilter', $textObj);

		$text = (is_string($textObj->text)) ? $textObj->text : $text;

		$text = Filter::process($textObj); // run all filters

		// Store in cache with a minimum expiration time of 1 day.
		if ($cache)
		{
			Cache::instance('cache_filter')->set($cache_id, $text, null, time() + Date::DAY);
		}

		return $text;
	}

	/**
	 * HTML filter
	 *
	 * Provides filtering of input into accepted HTML.
	 *
	 * @param $text
	 * @param $format
	 * @param $filter
	 * @return string
	 */
	public static function html($text, $format, $filter)
	{
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
					$link->nodeValue = self::limit_chars($link->nodeValue,
										 (int) $filter['settings']['url_length'], '....');
				}
			}
			$text = self::dom_serialize($html_dom);
		}

		return trim($text);
	}

	/**
	 * Markdown filter. Allows content to be submitted using Markdown.
	 *
	 * @link http://michelf.ca/projects/php-markdown/
	 * @link http://littoral.michelf.ca/code/php-markdown/php-markdown-extra-1.2.6.zip
	 */
	public static function markdown($text, $format, $filter)
	{
		include_once Kohana::find_file('vendor/Markdown', 'markdown');

		return Markdown($text);
	}

	/**
	 * Automatically applies "p" and "br" markup to text.
	 *
	 *     echo Text::autop($text);
	 *
	 * @link http://api.drupal.org/api/drupal/modules--filter--filter.module/function/_filter_autop
	 *
	 * @param   string  $text  subject
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

	/**
	 * Move links to bottom of the text
	 *
	 * @param   string   $text        Text
	 * @param   boolean  $auto_links  Convert URLs into links [Optional]

	 * @return  string
	 */
	public static function move_links_to_end($text, $auto_links = FALSE)
	{
		$search  = '/<a [^>]*href="([^"]+)"[^>]*>(.*?)<\/a>/ie';
		$replace = 'self::_links_list("\\1", "\\2")';

		if($auto_links)
		{
			$text = self::auto_link($text);
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

	/**
	 * Helper function called by preg_replace() on link replacement.
	 *
	 * @param string $link URL of the link
	 * @param string $display Part of the text to associate number with
	 *
	 * @return string
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
	 *
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
	 * @param   string  $str  String to be processed
	 *
	 * @return  string
	 */
	public static function auto_p_revert($str)
	{
		$br = preg_match('`<br>[\\n\\r]`', $str) ? '<br>' : '<br />';
		return preg_replace('`'.$br.'([\\n\\r])`', '$1', $str);
	}

	/**
	 * Adds &lt;span class="ordinal"&gt; tags around any ordinals (nd / st / th / rd)
	 *
	 * @param   string  $text  String to be processed
	 *
	 * @return  string
	 *
	 * @link    http://drupal.org/project/more_filters
	 */
	public static function ordinals($text)
	{
		// Adds <span class="ordinal"> tags around any ordinals (nd / st / th / rd).
		// One or more numbers in front ok, but ignore if ordinal is immediately followed by a number or letter.
		$processed_text = preg_replace('/([0-9]+)(nd|st|th|rd)([^a-zA-Z0-9]+)/', '$1<span class="ordinal">$2</span>$3', $text);
		return $processed_text;
	}

	/**
	 * Adds &lt;span class="initial"&gt; tag around the initial letter of each paragraph
	 *
	 * @param   string  $text  String to be processed
	 *
	 * @return  string
	 *
	 * @link    http://drupal.org/project/more_filters
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
	 * @param   string  $text  String to be processed
	 *
	 * @return  string
	 *
	 * @link    http://drupal.org/project/more_filters
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
	 * @param   string  $string       The string you want to slug
	 * @param   string  $replacement  Will replace keys in map [Optional]
	 *
	 * @return  string
	 */
	public static function convert_accented_characters($string, $replacement = '-')
	{
		$string = mb_strtolower($string);

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

	/**
	 * Converts fractions to their html equivalent
	 *
	 * This is called automatically by [Text::fraction].
	 *
	 * @return  string
	 */
	private static function _replace_fraction($fraction, $html_fraction, $text)
	{
		// fraction can't be preceded or followed by a number or letter.
		$search = '/([^0-9A-Z]+)' . preg_quote($fraction, '/') . '([^0-9A-Z]+)/i';
		$replacement = '$1' . $html_fraction . '$2';

		return preg_replace($search, $replacement, $text);
	}

	/**
	 * Navigates through an array and removes slashes from the values
	 *
	 * If an array is passed, the array_map() function causes a callback to pass the
	 * value back to the function. The slashes from this value will removed.
	 *
	 * It is based on the WP function `stripslashes_deep()`.
	 *
	 * @since  1.1.0
	 *
	 * @param   mixed  $value  The value to be stripped
	 *
	 * @return  mixed
	 */
	public static function strip_slashes($value)
	{
		if (is_array($value))
		{
			$value = array_map('self::strip_slashes', $value);
		}
		elseif (is_object($value))
		{
			$vars = get_object_vars($value);
			foreach ($vars as $key => $data)
			{
				$value->{$key} = self::strip_slashes($data);
			}
		}
		elseif (is_string($value))
		{
			$value = stripslashes($value);
		}

		return $value;
	}

	/**
	 * Simple fast string encryption
	 *
	 * @since   1.1.1
	 *
	 * @param  string   $string  Text to encryption
	 * @param  boolean  $key     Key [Optional]
	 *
	 * @return  string
	 */
	public static function encode($string, $key = FALSE)
	{
		if ( ! $string)
		{
			return FALSE;
		}

		if ( ! $key)
		{
			$key = Config::get('site.gleez_private_key', sha1(uniqid(mt_rand(), true)) . md5(uniqid(mt_rand(), true)));
		}

		$crypttext = mcrypt_encrypt(MCRYPT_GOST, $key, $string, MCRYPT_MODE_ECB);

		return trim(self::safe_b64encode($crypttext));
	}

	/**
	 * Simple fast string decryption
	 *
	 * @since   1.1.1
	 *
	 * @param  string   $string  Text to decryption
	 * @param  boolean  $key     Key [Optional
	 *
	 * @return  string
	 */
	public static function decode($string, $key = FALSE)
	{
		if ( ! $string)
		{
			 return FALSE;
		}

		if ( ! $key)
		{
			$key = Config::get('site.gleez_private_key', sha1(uniqid(mt_rand(), true)) . md5(uniqid(mt_rand(), true)));
		}

		$crypttext = self::safe_b64decode($string);
		$decrypttext = mcrypt_decrypt(MCRYPT_GOST, $key, $crypttext, MCRYPT_MODE_ECB);

		return trim($decrypttext);
	}

	/**
	 * Url safe base64 encode
	 *
	 * @since   1.1.1
	 *
	 * @param   string   $string  Text to encode
	 *
	 * @return  string
	 */
	public static function safe_b64encode($string)
	{
		$data = base64_encode($string);
		$data = str_replace(array('+','/','='),array('-','_',''),$data);

		return $data;
	}

	/**
	 * Url safe base64 decode
	 *
	 * @since   1.1.1
	 *
	 * @param   string   $string  Text to decode
	 *
	 * @return  string
	 */
	public static function safe_b64decode($string)
	{
		$data = str_replace(array('-','_'),array('+','/'),$string);
		$mod4 = strlen($data) % 4;

		if ($mod4)
		{
			$data .= substr('====', $mod4);
		}

		return base64_decode($data);
	}
}
