<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Cookie Helper
 *
 * @package    Gleez\Helpers
 * @author     Sergey Yakovlev - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Gleez_Cookie extends Kohana_Cookie {

	/**
	 * Generates a salt string for a cookie based on the name and value
	 *
	 * Example:<br>
	 * <code>
	 *   $salt = Cookie::salt('theme', 'red');
	 * </code>
	 *
	 * Thanks to [rjd22](https://github.com/rjd22) and [birkir](https://github.com/birkir)
	 *
	 * @param   string  $name   Name of cookie
	 * @param   string  $value  Value of cookie
	 * @return  string
	 * @throws  Gleez_Exception
	 */
	public static function salt($name, $value)
	{
		// Require a valid salt
		if ( ! Cookie::$salt)
		{
			throw new Gleez_Exception('A valid cookie salt is required. Please set Cookie::$salt in your bootstrap.php. For more information check the documentation.');
		}

		// Determine the user agent
		$agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : 'unknown';

		return hash_hmac('sha1', $agent.$name.$value.Cookie::$salt, Cookie::$salt);
	}
}
