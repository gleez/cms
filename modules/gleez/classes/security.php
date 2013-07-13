<?php defined('SYSPATH') OR die('No direct script access allowed.');
/**
 * Security helper class
 *
 * @package    Gleez\Helpers
 * @author     Kohana Team
 * @author     Sergey Yakovlev - Gleez
 * @version    1.0.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @copyright  (c) 2007-2012 Kohana Team
 * @license    http://gleezcms.org/license  Gleez CMS License
 * @license    http://kohanaframework.org/license
 */
class Security {

	/**
	 * Key name used for token storage
	 * @var string
	 */
	public static $token_name = 'security_token';

	/**
	 * Generate and store a unique token which can be used to help prevent
	 * [CSRF](http://wikipedia.org/wiki/Cross_Site_Request_Forgery) attacks.
	 *
	 * Example:
	 * ~~~
	 * $token = Security::token();
	 * ~~~
	 *
	 * You can insert this token into your forms as a hidden field:
	 * ~~~
	 * echo Form::hidden('csrf', Security::token());
	 * ~~~
	 *
	 * And then check it when using [Validation]:
	 * ~~~
	 * $array->rules('csrf', array(
	 *     'not_empty'       => NULL,
	 *     'Security::check' => NULL,
	 * ));
	 * ~~~
	 *
	 * This provides a basic, but effective, method of preventing CSRF attacks.
	 *
	 * @param   boolean  $new  Force a new token to be generated?
	 *
	 * @return  string
	 *
	 * @uses    Session::instance
	 */
	public static function token($new = FALSE)
	{
		$session = Session::instance();

		// Get the current token
		$token = $session->get(Security::$token_name);

		if ($new === TRUE OR ! $token)
		{
			// Generate a new unique token
			$token = sha1(uniqid(NULL, TRUE));

			// Store the new token
			$session->set(Security::$token_name, $token);
		}

		return $token;
	}

	/**
	 * Check that the given token matches the currently stored security token.
	 *
	 * Example:
	 * ~~~
	 * if (Security::check($token))
	 * {
	 *     // Pass
	 * }
	 * ~~~
	 *
	 * @param   string  $token  Token to check
	 *
	 * @return  boolean
	 *
	 * @uses    Security::token
	 */
	public static function check($token)
	{
		return Security::token() === $token;
	}

	/**
	 * Remove image tags from a string
	 *
	 * Example:
	 * ~~~
	 * $str = Security::strip_image_tags($str);
	 * ~~~
	 *
	 * @param   string  $str  String to sanitize
	 *
	 * @return  string
	 */
	public static function strip_image_tags($str)
	{
		return preg_replace('#<img\s.*?(?:src\s*=\s*["\']?([^"\'<>\s]*)["\']?[^>]*)?>#is', '$1', $str);
	}

	/**
	 * Encodes PHP tags in a string
	 *
	 * Example:
	 * ~~~
	 * $str = Security::encode_php_tags($str);
	 * ~~~
	 *
	 * @param   string  $str  String to sanitize
	 *
	 * @return  string
	 */
	public static function encode_php_tags($str)
	{
		return str_replace(array('<?', '?>'), array('&lt;?', '?&gt;'), $str);
	}

}
