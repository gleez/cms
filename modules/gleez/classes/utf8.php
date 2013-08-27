<?php
/**
 * A port of [phputf8](http://phputf8.sourceforge.net/) to a unified set of files.
 *
 * Provides multi-byte aware replacement string functions.
 *
 * For UTF-8 support to work correctly, the following requirements must be met:
 *
 * - PCRE needs to be compiled with UTF-8 support (--enable-utf8)
 * - Support for [Unicode properties](http://php.net/manual/reference.pcre.pattern.modifiers.php)
 *   is highly recommended (--enable-unicode-properties)
 * - UTF-8 conversion will be much more reliable if the
 *   [iconv extension](http://php.net/iconv) is loaded
 * - The [mbstring extension](http://php.net/mbstring) is highly recommended,
 *   but must not be overloading string functions
 *
 * [!!] This file is licensed differently from the rest of Gleez. As a port of
 *      [phputf8](http://phputf8.sourceforge.net/), this file is released under the LGPL.
 *
 * @package    Gleez\Base
 * @author     Kohana Team
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @copyright  (c) 2007-2012 Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
class UTF8 {

	/**
	 * Does the server support UTF-8 natively?
	 * @var boolean
	 */
	public static $server_utf8 = NULL;

	/**
	 * List of called methods that have had their required file included
	 * @var array
	 */
	public static $called = array();

	/**
	 * Recursively cleans arrays, objects, and strings
	 *
	 * Removes ASCII control codes and converts to the requested charset
	 * while silently discarding incompatible characters.
	 *
	 * Example:
	 * ~~~
	 * // Clean GET data
	 * UTF8::clean($_GET);
	 * ~~~
	 *
	 * [!!] This method requires [Iconv](http://php.net/iconv)
	 *
	 * @param   mixed   $var      Variable to clean
	 * @param   string  $charset  Character set [Optional]
	 *
	 * @return  mixed
	 *
	 * @uses    UTF8::strip_ascii_ctrl
	 * @uses    UTF8::is_ascii
	 * @uses    Kohana::$charset
	 */
	public static function clean($var, $charset = NULL)
	{
		if ( ! $charset)
		{
			// Use the application character set
			$charset = Kohana::$charset;
		}

		if (is_array($var) OR is_object($var))
		{
			foreach ($var as $key => $val)
			{
				// Recursion!
				$var[self::clean($key)] = self::clean($val);
			}
		}
		elseif (is_string($var) AND $var !== '')
		{
			// Remove control characters
			$var = self::strip_ascii_ctrl($var);

			if ( ! self::is_ascii($var))
			{
				// Disable notices
				$error_reporting = error_reporting(~E_NOTICE);

				// iconv is expensive, so it is only used when needed
				$var = iconv($charset, $charset.'//IGNORE', $var);

				// Turn notices back on
				error_reporting($error_reporting);
			}
		}

		return $var;
	}

	/**
	 * Tests whether a string contains only 7-bit ASCII bytes
	 *
	 * This is used to determine when to use native functions or UTF-8 functions.
	 *
	 * Example:
	 * ~~~
	 * $ascii = UTF8::is_ascii($str);
	 * ~~~
	 *
	 * @param   mixed  $str  String or array of strings to check
	 *
	 * @return  boolean
	 */
	public static function is_ascii($str)
	{
		if (is_array($str))
		{
			$str = implode($str);
		}

		return ! preg_match('/[^\x00-\x7F]/S', $str);
	}

	/**
	 * Strips out device control codes in the ASCII range
	 *
	 * Example:
	 * ~~~
	 * $str = UTF8::strip_ascii_ctrl($str);
	 * ~~~
	 *
	 * @param   string  $str  String to clean
	 *
	 * @return  string
	 */
	public static function strip_ascii_ctrl($str)
	{
		return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $str);
	}

	/**
	 * Strips out all non-7bit ASCII bytes
	 *
	 * Example:
	 * ~~~
	 * $str = UTF8::strip_non_ascii($str);
	 * ~~~
	 *
	 * @param   string  $str  String to clean
	 *
	 * @return  string
	 */
	public static function strip_non_ascii($str)
	{
		return preg_replace('/[^\x00-\x7F]+/S', '', $str);
	}

	/**
	 * Replaces special/accented UTF-8 characters by ASCII-7 "equivalents"
	 *
	 * Example:
	 * ~~~
	 * $ascii = UTF8::transliterate_to_ascii($utf8);
	 * ~~~
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 *
	 * @param   string   $str   string to transliterate
	 * @param   integer  $case  -1 lowercase only, +1 uppercase only, 0 both cases [Optional]
	 *
	 * @return  string
	 *
	 * @uses    Kohana::find_file
	 */
	public static function transliterate_to_ascii($str, $case = 0)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require Kohana::find_file('utf8', __FUNCTION__);

			// Function has been called
			self::$called[__FUNCTION__] = TRUE;
		}

		return _transliterate_to_ascii($str, $case);
	}

	/**
	 * Returns the length of the given string
	 *
	 * This is a UTF8-aware version of [strlen](http://php.net/strlen).
	 *
	 * Example:
	 * ~~~
	 * $length = UTF8::strlen($str);
	 * ~~~
	 *
	 * @param   string  $str  String being measured for length
	 *
	 * @return  integer
	 *
	 * @uses    UTF8::$server_utf8
	 * @uses    Kohana::$charset
	 * @uses    Kohana::find_file
	 */
	public static function strlen($str)
	{
		if (self::$server_utf8)
		{
			return mb_strlen($str, Kohana::$charset);
		}

		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require Kohana::find_file('utf8', __FUNCTION__);

			// Function has been called
			self::$called[__FUNCTION__] = TRUE;
		}

		return _strlen($str);
	}

	/**
	 * Finds position of first occurrence of a UTF-8 string
	 *
	 * This is a UTF8-aware version of [strpos](http://php.net/strpos).
	 *
	 * Example:
	 * ~~~
	 * $position = UTF8::strpos($str, $search);
	 * ~~~
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string   $str     Haystack
	 * @param   string   $search  Needle
	 * @param   integer  $offset  Offset from which character in haystack to start searching [Optional]
	 *
	 * @return  mixed
	 *
	 * @uses    UTF8::$server_utf8
	 * @uses    Kohana::find_file
	 */
	public static function strpos($str, $search, $offset = 0)
	{
		if (self::$server_utf8)
		{
			return mb_strpos($str, $search, $offset, Kohana::$charset);
		}

		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require Kohana::find_file('utf8', __FUNCTION__);

			// Function has been called
			self::$called[__FUNCTION__] = TRUE;
		}

		return _strpos($str, $search, $offset);
	}

	/**
	 * Finds position of last occurrence of a char in a UTF-8 string
	 *
	 * This is a UTF8-aware version of [strrpos](http://php.net/strrpos).
	 *
	 * Example:
	 * ~~~
	 * $position = UTF8::strrpos($str, $search);
	 * ~~~
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string   $str     Haystack
	 * @param   string   $search  Needle
	 * @param   integer  $offset  Offset from which character in haystack to start searching [Optional]
	 *
	 * @return  mixed
	 *
	 * @uses    UTF8::$server_utf8
	 * @uses    Kohana::$charset
	 * @uses    Kohana::find_file
	 */
	public static function strrpos($str, $search, $offset = 0)
	{
		if (self::$server_utf8)
		{
			return mb_strrpos($str, $search, $offset, Kohana::$charset);
		}

		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require Kohana::find_file('utf8', __FUNCTION__);

			// Function has been called
			self::$called[__FUNCTION__] = TRUE;
		}

		return _strrpos($str, $search, $offset);
	}

	/**
	 * Returns part of a UTF-8 string
	 *
	 * This is a UTF8-aware version of [substr](http://php.net/substr).
	 *
	 * Example:
	 * ~~~
	 * $sub = UTF8::substr($str, $offset);
	 * ~~~
	 *
	 * @author  Chris Smith <chris@jalakai.co.uk>
	 *
	 * @param   string   $str     Input string
	 * @param   integer  $offset  Offset
	 * @param   integer  $length  Length limit [Optional]
	 *
	 * @return  string
	 *
	 * @uses    UTF8::$server_utf8
	 * @uses    Kohana::$charset
	 * @uses    Kohana::find_file
	 */
	public static function substr($str, $offset, $length = NULL)
	{
		if (self::$server_utf8)
		{
			return (is_null($length))
				? mb_substr($str, $offset, mb_strlen($str), Kohana::$charset)
				: mb_substr($str, $offset, $length, Kohana::$charset);
		}

		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require Kohana::find_file('utf8', __FUNCTION__);

			// Function has been called
			self::$called[__FUNCTION__] = TRUE;
		}

		return _substr($str, $offset, $length);
	}

	/**
	 * Replaces text within a portion of a UTF-8 string
	 *
	 * This is a UTF8-aware version of [substr_replace](http://php.net/substr_replace).
	 *
	 * If `$length` given and is positive, it represents the length of the portion of `$str`
	 * which is to be replaced. If it is negative, it represents the number of characters
	 * from the end of `$str` at which to stop replacing. If it is not given, then it will default
	 * to strlen(`$str`); i.e. end the replacing at the end of `$str`. Of course, if `$length` is zero
	 * then this function will have the effect of inserting `$replacement` into `$str` at the given start `$offset`.
	 *
	 * Example:
	 * ~~~
	 * $str = UTF8::substr_replace($str, $replacement, $offset);
	 * ~~~
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string   $str          Input string
	 * @param   string   $replacement  Replacement string
	 * @param   integer  $offset       Offset
	 * @param   mixed    $length       The length of the portion of $string which is to be replaced [Optional]
	 *
	 * @return  string
	 *
	 * @uses    Kohana::find_file
	 */
	public static function substr_replace($str, $replacement, $offset, $length = NULL)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require Kohana::find_file('utf8', __FUNCTION__);

			// Function has been called
			self::$called[__FUNCTION__] = TRUE;
		}

		return _substr_replace($str, $replacement, $offset, $length);
	}

	/**
	 * Makes a UTF-8 string lowercase
	 *
	 * This is a UTF8-aware version of [strtolower](http://php.net/strtolower).
	 *
	 * Example:
	 * ~~~
	 * $str = UTF8::strtolower($str);
	 * ~~~
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 *
	 * @param   string  $str  Mixed case string
	 *
	 * @return  string
	 *
	 * @uses    UTF8::$server_utf8
	 * @uses    Kohana::$charset
	 * @uses    Kohana::find_file
	 */
	public static function strtolower($str)
	{
		if (self::$server_utf8)
		{
			return mb_strtolower($str, Kohana::$charset);
		}

		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require Kohana::find_file('utf8', __FUNCTION__);

			// Function has been called
			self::$called[__FUNCTION__] = TRUE;
		}

		return _strtolower($str);
	}

	/**
	 * Makes a UTF-8 string uppercase
	 *
	 * This is a UTF8-aware version of [strtoupper](http://php.net/strtoupper).
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 *
	 * @param   string  $str  Mixed case string
	 *
	 * @return  string
	 *
	 * @uses    UTF8::$server_utf8
	 * @uses    Kohana::$charset
	 * @uses    Kohana::find_file
	 */
	public static function strtoupper($str)
	{
		if (self::$server_utf8)
		{
			return mb_strtoupper($str, Kohana::$charset);
		}

		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require Kohana::find_file('utf8', __FUNCTION__);

			// Function has been called
			self::$called[__FUNCTION__] = TRUE;
		}

		return _strtoupper($str);
	}

	/**
	 * Makes a UTF-8 string's first character uppercase
	 *
	 * This is a UTF8-aware version of [ucfirst](http://php.net/ucfirst).
	 *
	 * Example:
	 * ~~~
	 * $str = UTF8::ucfirst($str);
	 * ~~~
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string  $str  Mixed case string
	 *
	 * @return  string
	 *
	 * @uses    Kohana::find_file
	 */
	public static function ucfirst($str)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require Kohana::find_file('utf8', __FUNCTION__);

			// Function has been called
			self::$called[__FUNCTION__] = TRUE;
		}

		return _ucfirst($str);
	}

	/**
	 * Makes the first character of every word in a UTF-8 string uppercase
	 *
	 * This is a UTF8-aware version of [ucwords](http://php.net/ucwords).
	 *
	 * Example:
	 * ~~~
	 * $str = UTF8::ucwords($str);
	 * ~~~
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string  $str  Mixed case string
	 *
	 * @return  string
	 *
	 * @uses    UTF8::$server_utf8
	 * @uses    Kohana::find_file
	 */
	public static function ucwords($str)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require Kohana::find_file('utf8', __FUNCTION__);

			// Function has been called
			self::$called[__FUNCTION__] = TRUE;
		}

		return _ucwords($str);
	}

	/**
	 * Case-insensitive UTF-8 string comparison
	 *
	 * This is a UTF8-aware version of [strcasecmp](http://php.net/strcasecmp).
	 *
	 * Example:
	 * ~~~
	 * $compare = UTF8::strcasecmp($str1, $str2);
	 * ~~~
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string  $str1  String to compare
	 * @param   string  $str2  String to compare
	 *
	 * @return  integer
	 *
	 * @uses    Kohana::find_file
	 */
	public static function strcasecmp($str1, $str2)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require Kohana::find_file('utf8', __FUNCTION__);

			// Function has been called
			self::$called[__FUNCTION__] = TRUE;
		}

		return _strcasecmp($str1, $str2);
	}

	/**
	 * Returns a string or an array with all occurrences of search in subject
	 * (ignoring case) and replaced with the given replace value
	 *
	 * This is a UTF8-aware version of [str_ireplace](http://php.net/str_ireplace).
	 *
	 * [!!] Note: This function is very slow compared to the native version.
	 *      Avoid using it when possible.
	 *
	 * @todo    PHP 5.5 issue
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com
	 *
	 * @param   mixed    $search   Text to replace
	 * @param   mixed    $replace  Replacement text
	 * @param   mixed    $str      Subject text
	 * @param   integer  $count    Number of matched and replaced needles will be returned via this parameter which is passed by reference [Optional]
	 *
	 * @return  mixed
	 *
	 * @uses    Kohana::find_file
	 */
	public static function str_ireplace($search, $replace, $str, & $count = NULL)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require Kohana::find_file('utf8', __FUNCTION__);

			// Function has been called
			self::$called[__FUNCTION__] = TRUE;
		}

		return _str_ireplace($search, $replace, $str, $count);
	}

	/**
	 * Case-insensitive UTF-8 version of strstr
	 *
	 * Returns all of input string from the first occurrence of needle to the end.
	 * This is a UTF8-aware version of [stristr](http://php.net/stristr).
	 *
	 * Example:
	 * ~~~
	 * $found = UTF8::stristr($str, $search);
	 * ~~~
	 *
	 * @author Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string  $str     Input string
	 * @param   string  $search  Needle
	 *
	 * @return  mixed
	 *
	 * @uses    Kohana::find_file
	 */
	public static function stristr($str, $search)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require Kohana::find_file('utf8', __FUNCTION__);

			// Function has been called
			self::$called[__FUNCTION__] = TRUE;
		}

		return _stristr($str, $search);
	}

	/**
	 * Finds the length of the initial segment matching mask
	 *
	 * This is a UTF8-aware version of [strspn](http://php.net/strspn).
	 *
	 * Example:
	 * ~~~
	 * $found = UTF8::strspn($str, $mask);
	 * ~~~
	 *
	 * @author Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string   $str     Input string
	 * @param   string   $mask    Mask for search
	 * @param   integer  $offset  Start position of the string to examine [Optional]
	 * @param   integer  $length  Length of the string to examine [Optional]
	 *
	 * @return  integer
	 *
	 * @uses    Kohana::find_file
	 */
	public static function strspn($str, $mask, $offset = NULL, $length = NULL)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require Kohana::find_file('utf8', __FUNCTION__);

			// Function has been called
			self::$called[__FUNCTION__] = TRUE;
		}

		return _strspn($str, $mask, $offset, $length);
	}

	/**
	 * Finds the length of the initial segment not matching mask
	 *
	 * This is a UTF8-aware version of [strcspn](http://php.net/strcspn).
	 *
	 * Example:
	 * ~~~
	 * $found = UTF8::strcspn($str, $mask);
	 * ~~~
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string   $str     Input string
	 * @param   string   $mask    Mask for search
	 * @param   integer  $offset  Start position of the string to examine [Optional]
	 * @param   integer  $length  Length of the string to examine [Optional]
	 *
	 * @return  integer
	 *
	 * @uses    Kohana::find_file
	 */
	public static function strcspn($str, $mask, $offset = NULL, $length = NULL)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require Kohana::find_file('utf8', __FUNCTION__);

			// Function has been called
			self::$called[__FUNCTION__] = TRUE;
		}

		return _strcspn($str, $mask, $offset, $length);
	}

	/**
	 * Pads a UTF-8 string to a certain length with another string
	 *
	 * This is a UTF8-aware version of [str_pad](http://php.net/str_pad).
	 *
	 * Example:
	 * ~~~
	 * $str = UTF8::str_pad($str, $length);
	 * ~~~
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string   $str            Input string
	 * @param   integer  $final_str_len  Desired string length after padding
	 * @param   string   $pad_str        String to use as padding [Optional]
	 * @param   integer  $pad_type       Padding type: STR_PAD_RIGHT, STR_PAD_LEFT, or STR_PAD_BOTH [Optional]
	 *
	 * @return  string
	 *
	 * @uses    Kohana::find_file
	 */
	public static function str_pad($str, $final_str_len, $pad_str = ' ', $pad_type = STR_PAD_RIGHT)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require Kohana::find_file('utf8', __FUNCTION__);

			// Function has been called
			self::$called[__FUNCTION__] = TRUE;
		}

		return _str_pad($str, $final_str_len, $pad_str, $pad_type);
	}

	/**
	 * Converts a UTF-8 string to an array
	 *
	 * This is a UTF8-aware version of [str_split](http://php.net/str_split).
	 *
	 * Example:
	 * ~~~
	 * $array = UTF8::str_split($str);
	 * ~~~
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string   $str           Input string
	 * @param   integer  $split_length  Maximum length of each chunk [Optional]
	 *
	 * @return  array
	 *
	 * @uses    Kohana::find_file
	 */
	public static function str_split($str, $split_length = 1)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require Kohana::find_file('utf8', __FUNCTION__);

			// Function has been called
			self::$called[__FUNCTION__] = TRUE;
		}

		return _str_split($str, $split_length);
	}

	/**
	 * Reverses a UTF-8 string
	 *
	 * This is a UTF8-aware version of [strrev](http://php.net/strrev).
	 *
	 * Example:
	 * ~~~
	 * $str = UTF8::strrev($str);
	 * ~~~
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string  $str  String to be reversed
	 *
	 * @return  string
	 *
	 * @uses    Kohana::find_file
	 */
	public static function strrev($str)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require Kohana::find_file('utf8', __FUNCTION__);

			// Function has been called
			self::$called[__FUNCTION__] = TRUE;
		}

		return _strrev($str);
	}

	/**
	 * Strips whitespace (or other UTF-8 characters) from the beginning and
	 * end of a string
	 *
	 * This is a UTF8-aware version of [trim](http://php.net/trim).
	 *
	 * Example:
	 * ~~~
	 * $str = UTF8::trim($str);
	 * ~~~
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 *
	 * @param   string  $str       Input string
	 * @param   string  $charlist  String of characters to remove [Optional]
	 *
	 * @return  string
	 *
	 * @uses    Kohana::find_file
	 */
	public static function trim($str, $charlist = NULL)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require Kohana::find_file('utf8', __FUNCTION__);

			// Function has been called
			self::$called[__FUNCTION__] = TRUE;
		}

		return _trim($str, $charlist);
	}

	/**
	 * Strips whitespace (or other UTF-8 characters) from the beginning of
	 * a string
	 *
	 * This is a UTF8-aware version of [ltrim](http://php.net/ltrim).
	 *
	 * Example:
	 * ~~~
	 * $str = UTF8::ltrim($str);
	 * ~~~
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 *
	 * @param   string  $str       Input string
	 * @param   string  $charlist  String of characters to remove [Optional]
	 *
	 * @return  string
	 *
	 * @uses    Kohana::find_file
	 */
	public static function ltrim($str, $charlist = NULL)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require Kohana::find_file('utf8', __FUNCTION__);

			// Function has been called
			self::$called[__FUNCTION__] = TRUE;
		}

		return _ltrim($str, $charlist);
	}

	/**
	 * Strips whitespace (or other UTF-8 characters) from the end of a string
	 *
	 * This is a UTF8-aware version of [rtrim](http://php.net/rtrim).
	 *
	 * Example:
	 * ~~~
	 * $str = UTF8::rtrim($str);
	 * ~~~
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 *
	 * @param   string  $str       Input string
	 * @param   string  $charlist  String of characters to remove [Optional]
	 *
	 * @return  string
	 *
	 * @uses    Kohana::find_file
	 */
	public static function rtrim($str, $charlist = NULL)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require Kohana::find_file('utf8', __FUNCTION__);

			// Function has been called
			self::$called[__FUNCTION__] = TRUE;
		}

		return _rtrim($str, $charlist);
	}

	/**
	 * Returns the unicode ordinal for a character
	 *
	 * This is a UTF8-aware version of [ord](http://php.net/ord).
	 *
	 * Example:
	 * ~~~
	 * $digit = UTF8::ord($character);
	 * ~~~
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string  $chr  UTF-8 encoded character
	 *
	 * @return  integer
	 *
	 * @uses    Kohana::find_file
	 */
	public static function ord($chr)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require Kohana::find_file('utf8', __FUNCTION__);

			// Function has been called
			self::$called[__FUNCTION__] = TRUE;
		}

		return _ord($chr);
	}

	/**
	 * Takes an UTF-8 string and returns an array of ints representing the Unicode characters
	 *
	 * Astral planes are supported i.e. the ints in the output can be > 0xFFFF.
	 * Occurrences of the BOM are ignored. Surrogates are not allowed.
	 *
	 * Example:
	 * ~~~
	 * $array = UTF8::to_unicode($str);
	 * ~~~
	 *
	 * The Original Code is Mozilla Communicator client code.
	 * The Initial Developer of the Original Code is Netscape Communications Corporation.
	 * Portions created by the Initial Developer are Copyright (C) 1998 the Initial Developer.
	 * Ported to PHP by Henri Sivonen <hsivonen@iki.fi>, see <http://hsivonen.iki.fi/php-utf8/>
	 * Slight modifications to fit with phputf8 library by Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string  $str  UTF-8 encoded string
	 *
	 * @return  array    mixed
	 *
	 * @uses    Kohana::find_file
	 */
	public static function to_unicode($str)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require Kohana::find_file('utf8', __FUNCTION__);

			// Function has been called
			self::$called[__FUNCTION__] = TRUE;
		}

		return _to_unicode($str);
	}

	/**
	 * Takes an array of ints representing the Unicode characters and returns a UTF-8 string
	 *
	 * Astral planes are supported i.e. the ints in the input can be > 0xFFFF.
	 * Occurrences of the BOM are ignored. Surrogates are not allowed.
	 *
	 * Example:
	 * ~~~
	 * $str = UTF8::from_unicode($array);
	 * ~~~
	 *
	 * The Original Code is Mozilla Communicator client code.
	 * The Initial Developer of the Original Code is Netscape Communications Corporation.
	 * Portions created by the Initial Developer are Copyright (C) 1998 the Initial Developer.
	 * Ported to PHP by Henri Sivonen <hsivonen@iki.fi>, see http://hsivonen.iki.fi/php-utf8/
	 * Slight modifications to fit with phputf8 library by Harry Fuecks <hfuecks@gmail.com>.
	 *
	 * @param   array  $arr  Unicode code points representing a string
	 *
	 * @return  mixed
	 *
	 * @uses    Kohana::find_file
	 */
	public static function from_unicode($arr)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require Kohana::find_file('utf8', __FUNCTION__);

			// Function has been called
			self::$called[__FUNCTION__] = TRUE;
		}

		return _from_unicode($arr);
	}

}

if (is_null(UTF8::$server_utf8))
{
	// Determine if this server supports UTF-8 natively
	UTF8::$server_utf8 = extension_loaded('mbstring');
}
