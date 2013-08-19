<?php
/**
 * JSON helper class
 *
 * @package    Gleez\Helpers
 * @author     Gleez Team
 * @author     Igal Alkon <igal.alkon@gmail.com>
 * @version    1.2.2
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class JSON {
	
	/**
	 * Encodes the given value into a JSON string
	 *
	 * Converts a PHP variable into its Javascript equivalent  We use HTML-safe strings,
	 * i.e. with <, > and & escaped. For more details For more details please refer to
	 * [[http://www.php.net/manual/en/function.json-encode.php]]
	 *
	 * [!!] This function only works with UTF-8 encoded data
	 *
	 * @link    http://www.php.net/manual/en/json.constants.php JSON Predefined Constants
	 *
	 * @param   mixed    $value    The data to be encoded
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
	 * Decodes the given JSON string into a PHP data structure
	 *
	 * [!!] This function only works with UTF-8 encoded data
	 *
	 * For more details please refer to [[http://www.php.net/manual/en/function.json-decode.php]]
	 *
	 * Example:
	 * ~~~
	 * $j = JSON::decode('{"Organization": "Gleez"}');
	 * ~~~
	 *
	 * @param   string   $json     The JSON string to be decoded
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

		$error = '';

		switch(json_last_error())
		{
			case JSON_ERROR_NONE:
			break;
			case JSON_ERROR_DEPTH:
				$error = 'The maximum stack depth has been exceeded';
			break;
			case JSON_ERROR_CTRL_CHAR:
				$error = 'Control character error, possibly incorrectly encoded';
			break;
			case JSON_ERROR_STATE_MISMATCH:
				$error = 'Invalid or malformed JSON';
			break;
			case JSON_ERROR_SYNTAX:
				$error = 'Syntax error';
			break;
			case JSON_ERROR_UTF8:
				$error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
			break;
			default:
				$error = 'Unknown JSON decoding error';
		}

		if ( ! empty($error))
		{
			throw new Gleez_Exception('JSON DECODE: :error', array(':error' => __($error)));
		}

		return $result;
	}

	/**
	 * Encodes the given value into a Mongo-like JSON string
	 *
	 * [!!] This function only works with UTF-8 encoded data
	 *
	 * Example:
	 * ~~~
	 * $j = JSON::encodeMongo(array('$id' => 1234567890));
	 * ~~~
	 *
	 * @param   mixed  $value  The data to be encoded
	 *
	 * @return  string
	 */
	public static function encodeMongo($value)
	{
		$json = json_encode($value);
		$json = preg_replace('/{"\$id":"(\w+)"}/','ObjectId("$1")', $json);

		return $json;
	}

}