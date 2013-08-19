<?php
/**
 * Cookie Helper
 *
 * @package    Gleez\Helpers
 * @author     Kohana Team
 * @author     Sergey Yakovlev - Gleez
 * @author     Sandeep Sangamreddi - Gleez
 * @version    1.0.2
 * @copyright  (c) 2008-2012 Kohana Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 * @license    http://kohanaframework.org/license
 */
class Cookie {

	/**
	 * Magic salt to add to the cookie
	 * @var string
	 */
	public static $salt = NULL;

	/**
	 * Number of seconds before the cookie expires
	 * @var integer
	 */
	public static $expiration = 0;

	/**
	 * Restrict the path that the cookie is available to
	 * @var string
	 */
	public static $path = '/';

	/**
	 * Restrict the domain that the cookie is available to
	 * @var string
	 */
	public static $domain = NULL;

	/**
	 * Only transmit cookies over secure connections
	 * @var boolean
	 */
	public static $secure = FALSE;

	/**
	 * Only transmit cookies over HTTP, disabling Javascript access
	 * @var boolean
	 */
	public static $httponly = FALSE;

	/**
	 * Gets the value of a signed cookie
	 *
	 * Cookies without signatures will not be returned.
	 * If the cookie signature is present, but invalid, the cookie will be deleted.
	 *
	 * Example:
	 * ~~~
	 * // Get the "theme" cookie, or use "blue" if the cookie does not exist
	 * $theme = Cookie::get('theme', 'blue');
	 * ~~~
	 *
	 * @param   string  $key      Cookie name
	 * @param   mixed   $default  Default value to return [Optional]
	 *
	 * @return  string
	 */
	public static function get($key, $default = NULL)
	{
		if ( ! isset($_COOKIE[$key]))
		{
			// The cookie does not exist
			return $default;
		}

		// Get the cookie value
		$cookie = $_COOKIE[$key];

		// Find the position of the split between salt and contents
		$split = strlen(Cookie::salt($key, NULL));

		if (isset($cookie[$split]) AND $cookie[$split] === '~')
		{
			// Separate the salt and the value
			list ($hash, $value) = explode('~', $cookie, 2);

			if (Cookie::salt($key, $value) === $hash)
			{
				// Cookie signature is valid
				return $value;
			}

			// The cookie signature is invalid, delete it
			Cookie::delete($key);
		}

		return $default;
	}

	/**
	 * Sets a signed cookie.
	 *
	 * [!!] Note that all cookie values must be strings and no automatic serialization will be performed!
	 *
	 * Example:
	 * ~~~
	 * // Set the "theme" cookie
	 * Cookie::set('theme', 'red');
	 * ~~~
	 *
	 * @param   string   $name        Name of cookie
	 * @param   string   $value       Value of cookie
	 * @param   integer  $expiration  Lifetime in seconds [Optional]
	 *
	 * @return  boolean
	 */
	public static function set($name, $value, $expiration = NULL)
	{
		if (is_null($expiration))
		{
			// Use the default expiration
			$expiration = Cookie::$expiration;
		}

		if ($expiration !== 0)
		{
			// The expiration is expected to be a UNIX timestamp
			$expiration += time();
		}

		// Add the salt to the cookie value
		$value = Cookie::salt($name, $value).'~'.$value;

		return setcookie($name, $value, $expiration, Cookie::$path, Cookie::$domain, Cookie::$secure, Cookie::$httponly);
	}

	/**
	 * Deletes a cookie by making the value NULL and expiring it
	 *
	 *     Cookie::delete('theme');
	 *
	 * @param   string  $name  Cookie name
	 *
	 * @return  boolean
	 */
	public static function delete($name)
	{
		// Remove the cookie
		unset($_COOKIE[$name]);

		// Nullify the cookie and make it expire
		return setcookie($name, NULL, time() - 1, Cookie::$path, Cookie::$domain, Cookie::$secure, Cookie::$httponly);
	}

	/**
	 * Generates a salt string for a cookie based on the name and value
	 *
	 * Example:
	 * ~~~
	 *   $salt = Cookie::salt('theme', 'red');
	 * ~~~
	 *
	 * Thanks to [rjd22](https://github.com/rjd22) and [birkir](https://github.com/birkir)
	 *
	 * @param   string  $name   Name of cookie
	 * @param   string  $value  Value of cookie
	 *
	 * @return  string
	 *
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
