<?php defined('SYSPATH') OR die('No direct script access allowed.');
/**
 * JSON helper class
 *
 * @package    Gleez\Helpers
 * @author     Sergey Yakovlev - Gleez
 * @author     Igal Alkon <igal.alkon@gmail.com>
 * @version    1.1.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class JSON {
	
	/**
	 * Returns a string containing the JSON representation of value
	 *
	 * Converts a PHP variable into its Javascript equivalent
	 * We use HTML-safe strings, i.e. with <, > and & escaped.
	 *
	 * @link    http://www.php.net/manual/en/json.constants.php SON Predefined Constants
	 *
	 * @param   mixed    $value    This function only works with UTF-8 encoded data
	 * @param   integer  $options  Bitmask consisting of JSON Predefined Constants [Optional]
	 * @param   integer  $depth    PHP 5.5 or higher [Optional]
	 * @return  string
	 */
	public static function encode($value, $options = 0, $depth = 512)
	{
		if (version_compare(PHP_VERSION, '5.5.0', '>='))
		{
			$raw = json_encode($value, $options, $depth);
		}
		else
		{
			$raw = json_encode($value, $options);
		}

		// json_encode() does not escape <, > and &, so we do it with str_replace().
		return str_replace(array('<', '>', '&'), array('\u003c', '\u003e', '\u0026'), $raw);
	}

	/**
	 * Takes a JSON encoded string and converts it into a PHP variable
	 *
	 * Converts an HTML-safe JSON string into its PHP equivalent.
	 *
	 * Example:<br>
	 * <code>
	 *   $j = JSON::decode('{"Organization": "Gleez"}');
	 * </code>
	 *
	 * @param   string   $json     This function only works with UTF-8 encoded data
	 * @param   boolean  $assoc    When TRUE, returned objects will be converted into associative arrays [Optional]
	 * @param   integer  $depth    User specified recursion depth [Optional]
	 * @param   integer  $options  Bitmask of JSON decode options. PHP 5.4 or higher [Optional]
	 *
	 * @return  mixed
	 *
	 * @throws Gleez_Exception
	 */
	public static function decode($json, $assoc = TRUE, $depth = 512, $options = 0)
	{
		if (version_compare(PHP_VERSION, '5.4.0', '>='))
		{
			$result = json_decode($json, $assoc, $depth, $options);
		}
		else
		{
			$result = json_decode($json, $assoc, $depth);
		}


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
			throw new Gleez_Exception('JSON DECODE: :error', array(':error' => __($error)));
		}

		return $result;
	}

}