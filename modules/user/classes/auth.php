<?php
/**
 * User authorization library
 *
 * Handles user login and logout, as well as secure
 * password hashing.
 *
 * @package    Gleez\Auth\Base
 * @author     Gleez Team
 * @version    1.0.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
abstract class Auth {

	/**
	 * Auth instances
	 * @var string
	 */
	protected static $_instance;

	/**
	 * Kohana session object
	 * @var object
	 */
	protected $_session;

	/**
	 * Kohana config object
	 * @var object
	 */
	protected $_config;

	/**
	 * Singleton pattern
	 *
	 * @return Auth
	 */
	public static function instance()
	{
		if ( ! isset(Auth::$_instance))
		{
			// Load the configuration for this type
			$config = Kohana::$config->load('auth');

			if ( ! $type = $config->get('driver'))
			{
				$type = 'file';
			}

			// Set the auth class name
			$class = 'Auth_'.ucfirst($type);

			// Create a new session instance
			Auth::$_instance = new $class($config);
		}

		return Auth::$_instance;
	}

	public function __construct($config = array())
	{
		// Save the config in the object
		$this->_config = $config;

		$this->_session = Session::instance();

		if (Kohana::DEVELOPMENT === Kohana::$environment)
		{
			Log::debug('Auth Library loaded.');
		}
	}

	/**
	 * Checks if a user logged in via an OAuth provider.
	 *
	 * @param   string   $provider  Provider name (e.g. 'twitter', 'google', etc.) [Optional]
	 * @return  boolean
	 */
	public function logged_in_oauth($provider = NULL)
	{
		// For starters, the user needs to be logged in
		if ( ! parent::logged_in())
			return FALSE;

		// Get the user from the session.
		// Because parent::logged_in returned TRUE, we know this is a valid user ORM object.
		$user = $this->get_user();

		if ($provider !== NULL)
		{
			// Check for one specific OAuth provider
			$provider = $provider.'_id';
			//return ! empty($user->$provider);
		}

		// Otherwise, just check the password field.
		// We don't store passwords for OAuth users.
		//return empty($user->pass);
	}

	/**
	 * Gets the currently logged in user from the session
	 *
	 * Returns NULL if no user is currently logged in.
	 *
	 * @param   mixed  $default  Default value to return [Optional]
	 * @return  mixed
	 */
	public function get_user($default = NULL)
	{
		return $this->_session->get($this->_config['session_key'], $default);
	}

	/**
	 * Get 3rd party provider used to sign in
	 *
	 * @return  string
	 */
	public function get_provider()
	{
		return $this->_session->get($this->_config['session_key'] . '_provider', NULL);
	}

	/**
	 * Check if there is an active session. Optionally allows checking for a
	 * specific role.
	 *
	 * @param   string  $role  Role name [Optional]
	 * @return  mixed
	 */
	public function logged_in($role = NULL)
	{
		return ($this->get_user() !== NULL);
	}

	/**
	 * Creates a hashed hmac password from a plaintext password. This
	 * method is deprecated, [Auth::hash] should be used instead.
	 *
	 * @deprecated
	 * @param   string  $password  Plaintext password
	 * @return  string
	 */
	public function hash_password($password)
	{
		return $this->hash($password);
	}

	/**
	 * Perform a hmac hash, using the configured method.
	 *
	 * @param   string  $str  String to hash
	 * @throws  Gleez_Exception
	 * @return  string
	 */
	public function hash($str)
	{
		if ( ! $this->_config['hash_key'])
		{
			throw new Gleez_Exception('A valid hash key must be set in your auth config.');
		}

		return hash_hmac($this->_config['hash_method'], $str, $this->_config['hash_key']);
	}

	/**
	 * Attempt to log in a user by using an ORM object and plain-text password.
	 *
	 * @param   string   $username  Username to log in
	 * @param   string   $password  Password to check against
	 * @param   boolean  $remember  Enable autologin [Optional]
	 * @return  boolean
	 */
	public function login($username, $password, $remember = FALSE)
	{
		if (empty($password))
			return FALSE;

		if (is_string($password))
		{
			// Create a hashed password
			//$password = $this->hash($password); //Support for old (Drupal md5 password sum)
		}

		return $this->_login($username, $password, $remember);
	}

	/**
	 * Log out a user by removing the related session variables.
	 *
	 * @param   boolean  $destroy     Completely destroy the session [Optional]
	 * @param   boolean  $logout_all  Remove all tokens for user [Optional]
	 * @return  boolean
	 */
	public function logout($destroy = FALSE, $logout_all = FALSE)
	{
		if ($destroy === TRUE)
		{
			// Destroy the session completely
			$this->_session->destroy();
		}
		else
		{
			// Remove the user from the session
			$this->_session->delete($this->_config['session_key']);

			// Regenerate session_id
			$this->_session->regenerate();
		}

		// Double check
		return ! $this->logged_in();
	}

	protected function complete_login($user)
	{
		// Regenerate session_id
		$this->_session->regenerate();

		// Store username in session
		$this->_session->set($this->_config['session_key'], $user);

		return TRUE;
	}

	/**
	 * Allows a model use email, username and OAuth provider id as unique identifiers for login
	 *
	 * @param   string  $value           Unique value
	 * @param   string  $oauth_provider  OAuth provider name [Optional]
	 * @return  string  field name
	 */
	public function unique_key($value, $oauth_provider = NULL)
	{
		if ($oauth_provider)
		{
			return $oauth_provider.'_id';
		}

		return Valid::email($value) ? 'mail' : 'name';
	}

	abstract protected function _login($username, $password, $remember);

	abstract public function password($username);

	abstract public function check_password($password);

}