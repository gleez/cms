<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Extend the Kohana Date helper
 *
 * @package   Gleez
 * @category  Helper
 * @author    Sandeep Sangamreddi - Gleez
 * @copyright (c) 2013 Gleez Technologies
 * @license   http://gleezcms.org/license
 * @link      http://php.net/manual/en/function.date.php
 */
class Gleez_Date extends Kohana_Date {

	/**
	 * Return month start timestamp
	 *
	 * @param integer Month
	 * @param integer Year
	 * @return integer Unix timestamp
	 */
	public static function firstOfMonth($month, $year)
	{
		return strtotime($month . '/01/' . $year . ' 00:00:00');
	}

	/**
	 * Return month end timestamp
	 *
	 * @param integer Month
	 * @param integer Year
	 * @return integer Unix timestamp
	 */
	public static function lastOfMonth($month, $year)
	{
		return strtotime('-1 second', strtotime('+1 month', strtotime($month . '/01/' . $year . ' 00:00:00')));
	}

	/**
	 * Return week days
	 *
	 * @return array
	 */
	static function weeekdays()
	{
		return array(
			0 => __('Sunday'),
			1 => __('Monday'),
			2 => __('Tuesday'),
			3 => __('Wednesday'),
			4 => __('Thursday'),
			5 => __('Friday'),
			6 => __('Saturday')
		);
	}

	/**
	 * Number of months in a year. Value will hold month name
	 *
	 * @param   boolean     Long (TRUE) or short (FALSE) months names [Optional]
	 * @uses    Date::hours
	 * @return  array  Array from 1-12 with month names
	 */
	public static function months_with_name($long = FALSE)
	{
		// Default values
		$long = (bool) $long;
		$months = Date::hours();

		for ($i = 1; $i <= 12; $i++)
		{
			$timestamp  = mktime(0, 0, 0, $i);
			$months[$i] = date(($long) ? "F" : "M", $timestamp);
		}

		return $months;
	}

	/**
	 * Checks whether a string is a date
	 *
	 * @param  string date
	 * @return bool
	 */
	public static function is_date($str)
	{
		return (boolean) strtotime($str);
	}

	/**
	 * Return a list of timezones
	 *
	 * @return array
	 */
	public static function timezones()
	{
		$continents = array(
			'Africa',
			'America',
			'Antarctica',
			'Arctic',
			'Asia',
			'Atlantic',
			'Australia',
			'Europe',
			'Indian',
			'Pacific'
		);

		$zones = DateTimeZone::listIdentifiers();

		$locations = array();

		foreach ($zones as $zone)
		{
			$zone = explode('/', $zone); // 0 => Continent, 1 => City

			if (!in_array($zone[0], $continents))
			{
				continue;
			}

			if (isset($zone[1]) != '')
			{
				// Creates array(DateTimeZone => 'Friendly name')
				$locations[$zone[0]][__($zone[0] . '/' . $zone[1])] = __(str_replace('_', ' ', $zone[1]));
			}
		}

		$offset_range = array(
			-12,
			-11.5,
			-11,
			-10.5,
			-10,
			-9.5,
			-9,
			-8.5,
			-8,
			-7.5,
			-7,
			-6.5,
			-6,
			-5.5,
			-5,
			-4.5,
			-4,
			-3.5,
			-3,
			-2.5,
			-2,
			-1.5,
			-1,
			-0.5,
			0,
			0.5,
			1,
			1.5,
			2,
			2.5,
			3,
			3.5,
			4,
			4.5,
			5,
			5.5,
			5.75,
			6,
			6.5,
			7,
			7.5,
			8,
			8.5,
			8.75,
			9,
			9.5,
			10,
			10.5,
			11,
			11.5,
			12,
			12.75,
			13,
			13.75,
			14
		);

		foreach ($offset_range as $offset)
		{
			if (0 <= $offset)
			{
				$offset_name = '+' . $offset;
			}
			else
			{
				$offset_name = (string) $offset;
			}

			$offset_value = $offset_name;
			$offset_name  = str_replace(array(
				'.25',
				'.5',
				'.75'
			), array(
				':15',
				':30',
				':45'
			), $offset_name);

			$locations[__('Manual Offsets')]['UTC' . $offset_value] = __('UTC :value', array(
				':value' => $offset_name
			));
		}

		return $locations;
	}

	/**
	 * Return available date time formats
	 *
	 * @param boolean $timestamp Unix timestamp [Optional]
	 * @return array
	 */
	public static function date_time_formats($timestamp = FALSE)
	{
		$date_time_format = array(
			'l, F j, Y - H:i',
			'l, j F, Y - H:i',
			'l, Y, F j - H:i',
			'l, F j, Y - g:ia',
			'l, j F Y - g:ia',
			'l, Y, F j - g:ia',
			'l, j. F Y - G:i',
			'D, Y-m-d H:i',
			'D, m/d/Y - H:i',
			'D, d/m/Y - H:i',
			'D, Y/m/d - H:i',
			'F j, Y - H:i',
			'j F, Y - H:i',
			'Y, F j - H:i',
			'D, m/d/Y - g:ia',
			'D, d/m/Y - g:ia',
			'D, Y/m/d - g:ia',
			'F j, Y - g:ia',
			'j F Y - g:ia',
			'Y, F j - g:ia',
			'j. F Y - G:i',
			'Y-m-d H:i',
			'm/d/Y - H:i',
			'd/m/Y - H:i',
			'Y/m/d - H:i',
			'd.m.Y - H:i',
			'm/d/Y - g:ia',
			'd/m/Y - g:ia',
			'Y/m/d - g:ia',
			'M j Y - H:i',
			'j M Y - H:i',
			'Y M j - H:i',
			'M j Y - g:ia',
			'j M Y - g:ia',
			'Y M j - g:ia'
		);

		if ($timestamp)
		{
			foreach ($date_time_format as $f)
			{
				$date_choices[$f] = date($f, time());
			}

			return $date_choices;
		}

		return $date_time_format;
	}

	/**
	 * Return available date formats
	 *
	 * @param  boolean $timestamp Unix timestamp [Optional]
	 * @return array
	 */
	public static function date_formats($timestamp = FALSE)
	{
		$date_format = array(
			'l, F j, Y',
			'l, j F, Y',
			'l, Y, F j',
			'l, F j, Y',
			'l, j F Y',
			'l, Y, F j',
			'l, j. F Y',
			'D, Y-m-d',
			'D, m/d/Y',
			'D, d/m/Y',
			'D, Y/m/d',
			'F j, Y',
			'j F, Y',
			'Y, F j',
			'j. F Y',
			'Y-m-d',
			'm/d/Y',
			'd/m/Y',
			'Y/m/d',
			'd.m.Y',
			'M j Y',
			'M j, Y',
			'j M Y',
			'Y M j'
		);

		if ($timestamp)
		{
			foreach ($date_format as $f)
			{
				$date_choices[$f] = date($f, time());
			}

			return $date_choices;
		}

		return $date_format;
	}

	/**
	 * Return available time formats
	 *
	 * @param  boolean $timestamp Unix timestamp [Optional]
	 * @return array
	 */
	public static function time_formats($timestamp = FALSE)
	{
		$time_format = array(
			'g:i:s a',
			'g:i:s A',
			'g:i a',
			'g:i A',
			'H:i:s',
			'G:i'
		);

		if ($timestamp)
		{
			foreach ($time_format as $f)
			{
				$time_choices[$f] = date($f, time());
			}

			return $time_choices;
		}

		return $time_format;
	}

	/**
	 * Return a unix timestamp in a user specified format including date and time
	 *
	 * @param  integer $timestamp Unix timestamp
	 * @param  string  $config    The configuration file [Optional]
	 * @param  string  $key       The key with the value of the configuration file [Optional]
	 * @return string
	 */
	public static function date_time($timestamp, $config = 'site', $key = 'date_time_format')
	{
		return date(Kohana::$config->load($config)->get($key), $timestamp);
	}

	/**
	 * Return a unix timestamp in a user specified format that's just the date
	 *
	 * @param  integer $timestamp Unix timestamp
	 * @param  string  $config    The configuration file [Optional]
	 * @param  string  $key       The key with the value of the configuration file [Optional]
	 * @return string
	 */
	public static function date($timestamp, $config = 'site', $key = 'date_format')
	{
		return date(Kohana::$config->load($config)->get($key), $timestamp);
	}

	/**
	 * Return a unix timestamp in a user specified format that's just the time
	 *
	 * @param  integer $timestamp Unix timestamp
	 * @param  string  $config    The configuration file [Optional]
	 * @param  string  $key       The key with the value of the configuration file [Optional]
	 * @return string
	 */
	public static function time($timestamp, $config = 'site', $key = 'time_format')
	{
		return date(Kohana::$config->load($config)->get($key), $timestamp);
	}

}
