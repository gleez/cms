<?php defined('SYSPATH') or die('No direct script access.');
/**
 * JSON helper class
 *
 *     Examples:
 *     $j = JSON::decode('{"Organization": "Kohana"}'); // Good
 *     $j = JSON::decode("{'Organization': 'Kohana'}"); // Invalid
 *     $j = JSON::decode('{"Organization": "Kohana"}', NULL, 1); // depth stack exceeded
 *
 * @package    Kohana
 * @category   Helpers
 * @author     Igal Alkon <igal.alkon@gmail.com>
 */
class JSON {
	
	/**
	 * Returns a string containing the JSON representation of value. 
	 * Converts a PHP variable into its Javascript equivalent
	 * We use HTML-safe strings, i.e. with <, > and & escaped.
	 *
	 * Please note:
	 * PHP 5.3.0 - The options parameter was added.
	 * PHP 5.2.1 - Added support for JSON encoding of basic types.
	 *
	 * @static
	 * @param  mixed  $value  This function only works with UTF-8 encoded data
	 * @return string
	 */
	public static function encode($value)
	{
		// json_encode() does not escape <, > and &, so we do it with str_replace().
		return str_replace(array('<', '>', '&'), array('\u003c', '\u003e', '\u0026'), json_encode($value));
	}

	/**
	 * Takes a JSON encoded string and converts it into a PHP variable
	 * Converts an HTML-safe JSON string into its PHP equivalent.
	 *
	 * Please note:
	 * PHP future - The options parameter was added
	 * PHP 5.3.0  - Added the optional depth. The default recursion depth was increased from 128 to 512
	 * PHP 5.2.3  - The nesting limit was increased from 20 to 128
	 *
	 * @static
	 * @throws Kohana_Exception
	 * @param  string  $json      This function only works with UTF-8 encoded data
	 * @param  bool    $to_assoc  When TRUE, returned objects will be converted into associative arrays
	 * @param  int     $depth     User specified recursion depth
	 * @return mixed
	 */
	public static function decode($json, $assoc = TRUE, $depth = 512)
	{
		$result = json_decode($json, $assoc, $depth);

		switch(json_last_error())
		{
			case JSON_ERROR_DEPTH:
				$error = 'Maximum stack depth exceeded';
				break;
			case JSON_ERROR_CTRL_CHAR:
				$error = 'Unexpected control character found';
				break;
			case JSON_ERROR_STATE_MISMATCH:
				$error = 'Invalid or malformed JSON';
				break;
			case JSON_ERROR_SYNTAX:
				$error = 'Syntax error';
				break;
			case JSON_ERROR_NONE:
			default:
				$error = '';
		}

		if ( ! empty($error))
		{
			throw new Kohana_Exception('JSON DECODE: :error', array(':error' => $error));
		}

		return $result;
	}

}