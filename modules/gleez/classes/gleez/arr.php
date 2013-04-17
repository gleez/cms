<?php defined("SYSPATH") or die("No direct script access.");
/**
 * @package	Gleez
 * @category	Helpers
 * @author	Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2012 Gleez Technologies
 * @license	http://gleezcms.org/license
 */
class Gleez_Arr extends Kohana_Arr {

	public static function validElement($element)
	{
		return !empty($element);
	}

	/**
	 * Implode multidimensional array to string
	 */
	public static function multi_implode($glue, $pieces)
	{
		$string='';
   
		if(is_array($pieces))
		{
			reset($pieces);
			while(list($key,$value)=each($pieces))
			{
				$string.=$glue.self::multi_implode($glue, $value);
			}
		}
		else
		{
			return $pieces;
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
         * Simple method to sort an array by a specific key.
         *      Maintains index association.
         *
         * @param array      The array to sort
         * @param string     The array key to sort
         * @param constant   SORT_ASC|SORT_DESC
         * @return array
         */
        public static function array_sort($array, $on, $order = SORT_ASC)
        {
                $new_array = $sortable_array = array();
            
                if (count($array) > 0)
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
                                else{
                                        $sortable_array[$k] = $v;
                                }
                        }

                        switch ($order)
                        {
                                case SORT_ASC:
                                        asort($sortable_array);
                                break;
                                case SORT_DESC:
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
        
	public static function search_in_array($needle, $haystack)
	{
		# Settings
		$path = array ();
   
		# Loop
		foreach ($haystack as $key => $value )
		{
			# Check for val
			if ($key == $needle)
			{
				# Add to path
				$path[$key] = $key;
			}
			else if (is_array ($value))
			{
				# Fetch subs
				$sub = self::search_in_array ($needle, $value);
           
				# Check if there are subs
				if (count ($sub) > 0)
				{
					# Add to path
					$path[$key] = $sub;
				}
			}//Message::success( Kohana::debug( $key ));
		}
		
		return $path;
	}

	/**
	 * Unpack string from serialized array
	 *
	 * Gets an array, for example stored in the database as a serialized string
	 * unserialize it into normal array and arranges the key values into separate strings
	 *
	 * @param   mixed  $string  Serialized array
	 * @param   string $sep     Separator [Optional]
	 * @return  string
	 */
	public static function unpack_string($string, $sep = PHP_EOL)
	{
		return implode($sep, unserialize($string));
	}

	/**
	 * Pack string to serialized array
	 *
	 * Gets a string divide the string based by using the symbol `$sep` creates an array,
	 * where each substring - a single element of the array and serialize this array to a string
	 *
	 * @param   string    $string  String
	 * @param   string    $sep     Separator [Optional]
	 * @param   int|NULL  $maxlen  Max length of substrings for trimming their [Optional]
	 * @return  string    Serialized array
	 */
	public static function pack_string($string, $sep = PHP_EOL, $maxlen = NULL)
	{
		$options = explode($sep, $string);

		$result = array();

		foreach ($options as $option)
		{
			if( ! $option = trim($option))
			{
				continue;
			}

			if ( ! is_null($maxlen))
			{
				$result[] = Text::limit_chars($option, $maxlen);
			}
		}

		return serialize($result);
	}
        
}