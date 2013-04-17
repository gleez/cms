<?php defined("SYSPATH") OR die("No direct script access.");
/**
 * @package    Gleez\Helpers
 * @author     Sandeep Sangamreddi - Gleez
 * @author     Sergey Yakovlev - Gleez
 * @copyright  (c) 2012 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Gleez_Arr extends Kohana_Arr {

	/**
	 * Implode multidimensional array to string
	 */
	public static function multi_implode($glue, $pieces)
	{
		if( ! is_array($pieces))
		{
			return $pieces;
		}

		$string = '';

		foreach ($pieces as $key => $value)
		{
			$string.=$glue.self::multi_implode($glue, $value);
		}

		return trim($string, $glue);
	}

	/**
	 * Function used by uasort to sort structured arrays by weight,
	 * without the property weight prefix.
	 */
	public static function sort_weight($a, $b)
	{
		$a_weight = (is_array($a) AND isset($a['weight'])) ? $a['weight'] : 0;
		$b_weight = (is_array($b) AND isset($b['weight'])) ? $b['weight'] : 0;

		if ($a_weight == $b_weight)
		{
			return 0;
		}

		return ($a_weight < $b_weight) ? -1 : 1;
	}

	/**
	 * Simple method to sort an array by a specific key
	 *
	 * Maintains index association.
	 *
	 * @param  array     $array  The array to sort
	 * @param  string    $on     The array key to sort
	 * @param  integer   $order  Sort order (SORT_ASC|SORT_DESC) [Optional]
	 * @return array
	 */
	public static function array_sort($array, $on, $order = SORT_ASC)
	{
		$new_array = $sortable_array = array();

		if ($array)
		{
			foreach ($array as $k => $v)
			{
				if (is_array($v))
				{
					foreach ($v as $k2 => $v2)
					{
						if ($k2 == $on)
						{
							$sortable_array[$k] = $v2;
						}
					}
				}
				else
				{
					$sortable_array[$k] = $v;
				}
			}

			switch ($order)
			{
				case SORT_ASC:
					// Sort an array and maintain index association
					asort($sortable_array);
					break;
				case SORT_DESC:
					// Sort an array in reverse order and maintain index association
					arsort($sortable_array);
					break;
			}

			foreach ($sortable_array as $k => $v)
			{
				$new_array[$k] = $array[$k];
			}

		}

		return $new_array;
	}

	/**
	 * Search value in an array and gets array of values
	 *
	 * @param   $needle    The searched value
	 * @param   $haystack  The array
	 * @return  array
	 */
	public static function search_in_array($needle, $haystack)
	{
		// Settings
		$path = array ();

		// Loop
		foreach ($haystack as $key => $value)
		{
			// Check for val
			if ($key == $needle)
			{
				// Add to path
				$path[$key] = $key;
			}
			else if (is_array($value))
			{
				// Fetch subs
				$sub = self::search_in_array($needle, $value);

				// Check if there are subs
				if (count ($sub) > 0)
				{
					// Add to path
					$path[$key] = $sub;
				}
			}
		}

		return $path;
	}

	/**
	 * Unpack string from array
	 *
	 * Gets an array, (if `$serialize` is TRUE  unserialize it into normal array)
	 * and arranges the key values into string separated by `$sep`
	 *
	 * @param   mixed    $array      Array
	 * @param   boolean  $serialize  Serialize string? [Optional]
	 * @param   string   $sep        Separator [Optional]
	 * @return  string
	 */
	public static function unpack_string($array, $serialize = FALSE, $sep = PHP_EOL)
	{
		if ($serialize)
		{
			$array = unserialize($array);
		}

		return implode($sep, $array);
	}

	/**
	 * Pack string to array
	 *
	 * Gets a string divide the string based by using the symbol `$sep` creates an array,
	 * where each substring - a single element of the array and serialize this array to a string
	 *
	 * @param   string    $string     String
	 * @param   boolean   $serialize  Serialize array? [Optional]
	 * @param   string    $sep        Separator [Optional]
	 * @param   int|NULL  $maxlen     Max length of substring for trimming [Optional]
	 * @return  string    Serialized array
	 *
	 * @uses    Text::limit_chars
	 */
	public static function pack_string($string, $serialize = FALSE, $sep = PHP_EOL, $maxlen = 0)
	{
		$options = explode($sep, $string);

		$result = array();

		foreach ($options as $option)
		{
			if($option = trim($option))
			{
				$result[] = $maxlen ? Text::limit_chars($option, $maxlen) : $option;
			}
		}

		return $serialize ? serialize($result) : $result;
	}

}