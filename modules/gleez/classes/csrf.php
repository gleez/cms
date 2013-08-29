<?php
/**
 * Cross-Site Request Forgery helper
 *
 * @package    Gleez\Helpers
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class CSRF {

	/**
	 * Token time to live in seconds, 30 minutes
	 * @var integer
	 */
	public static $csrf_ttl = 1800;

	/**
	 * Get CSRF token
	 *
	 * @param   string   $id      Custom token id, e.g. uid [Optional]
	 * @param   string   $action  Optional action
	 * @param   integer  $time    Used only internally [Optional]
	 *
	 * @return  string
	 */
	public static function token($id = '', $action, $time = 0)
	{
		// Get id string for token, could be uid or ip etc
		if (empty($id)) $id =  sha1(Request::$user_agent);

		// Get time to live
		if (!$time) $time = ceil(time() / self::$csrf_ttl);

		return sha1($time . self::key() . $id . $action);
	}

	/**
	 * Validate CSRF token
	 *
	 * @param   string   $token   Token [Optional]
	 * @param   string   $action  Optional action [Optional]
	 * @param   string   $id      Custom token id, e.g. uid [Optional]
	 *
	 * @return  boolean
	 */
	public static function valid($token = NULL, $action = '', $id = '')
	{
		// get token and action from Form POST
		if (empty($token))  $token  = Arr::get($_REQUEST, '_token');
		if (empty($action)) $action = Arr::get($_REQUEST, '_action');
		if (empty($id))     $id     = sha1(Request::$user_agent);

		// Get time to live
		$time = ceil(time() / self::$csrf_ttl);

		// Check token validity
		return ($token === self::token($id, $action, $time) || $token === self::token($id, $action, $time - 1));
	}

	/**
	 * User specific key used to generate unique tokens.
	 *
	 * @return string  The user specific private key.
	 */
	public static function key()
	{
		$token  = Session::instance()->id();
		$secret = self::_private_key();
		return sha1($secret . $token);
	}

	/**
	 * Ensure the private key variable used to generate tokens is set.
	 *
	 * @return  string  The private key.
	 *
	 * @uses    Config::load
	 */
	private static function _private_key()
	{
		$config = Config::load('site');

		if ( !($key = $config->get('gleez_private_key')) )
		{
			$key = sha1(uniqid(mt_rand(), TRUE)) . md5(uniqid(mt_rand(), TRUE));
			$config->set('gleez_private_key', $key);
		}

		return $key;
	}

}