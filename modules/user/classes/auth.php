<?php
/**
 * User authorization library
 *
 * Handles user login and logout, as well as secure
 * password hashing.
 *
 * @package    Gleez\Auth\Base
 * @author     Gleez Team
 * @version    1.1.2
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Auth {

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
	 * Get enabled oAuth2 providers
	 * @return array
	 *
	 * @uses   Module::is_active
	 */
	public static function providers()
	{
		if ( ! Module::is_active('oauth2'))
			return array();

		$config    = Config::get('oauth2.providers', array());
		$providers = array();

		foreach($config as $name => $provider)
		{
			if ($provider['enable'] === TRUE)
			{
				$providers[$name] = array(
					'name' => $name,
					'url'  => Route::get('oauth2/provider')->uri(array('provider' => $name, 'action' => 'login')),
					'icon' => isset($provider['icon']) ? $provider['icon'] : 'facebook',
				);
			}
		}

		return $providers;
	}

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
			$config = Config::load('auth');

			// Create a new session instance
			Auth::$_instance = new Auth($config);
		}

		return Auth::$_instance;
	}

	public function __construct($config = array())
	{
		// Save the config in the object
		$this->_config = $config;

		$this->_session = Session::instance();
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
		$user = $this->_session->get($this->_config['session_key'], $default);

		if ( !$user OR $user->id === 1 )
		{
			// check for "remembered" login
			$user = $this->auto_login();
		}

		return $user;
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
	 * @return  boolean
	 */
	public function logged_in($role = NULL)
	{
		static $roles;

		// Get the user from the session
		$user = $this->get_user();
		if ( ! $user) return FALSE;

		if ($user instanceof Model_User AND $user->loaded() AND $user->id !== 1)
		{
			// If we don't have a roll no further checking is needed
			if ( ! $role) return TRUE;

			if ( ! is_array($role) ) $role = array($role);
			if ( ! isset($roles) ) $roles = $user->roles->find_all()->as_array('id', 'name');

			foreach ($role as $role_item)
			{
				if (is_int($role_item))
				{
					if ( !isset($roles[$role_item]) ) return FALSE;
				}
				elseif (is_object($role_item))
				{
					if ( !isset($roles[$role_item->pk()]) ) return FALSE;
				}
				else
				{
					if ( !in_array($role_item, $roles) ) return FALSE;
				}
			}

			return TRUE;
		}
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
	 * @return  string
	 */
	public function hash($str)
	{
		$key = self::hashKey();

		return hash_hmac($this->_config['hash_method'], $str, $key);
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
		{
			return FALSE;
		}

		return $this->_login($username, $password, $remember);
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

	/**
	 * Get the stored password for a username.
	 *
	 * @param   mixed   username string, or user ORM object
	 * @return  string
	 */
	public function password($user)
	{
		if ( ! is_object($user))
		{
			$username = $user;

			// Load the user
			$user = ORM::factory('user');
			$user->where($user->unique_key($username), '=', $username)->find();
		}

		return $user->pass;
	}

	/**
	 * Compare password with original (hashed). Works for current (logged in) user
	 *
	 * @param   string  $password
	 * @return  boolean
	 */
	public function check_password($password)
	{
		$user_model = $this->get_user();
		$user = $user_model->original_values();

		if ( ! $user)
		{
			return FALSE;
		}

		//Avoid Timing attacks
		return Auth::hashEquals($user['pass'], $this->hash($password));
	}

	/**
	 * Logs a user in, based on the authautologin cookie.
	 *
	 * @return  mixed
	 */
	public function auto_login()
	{
		if ($token = Cookie::get('authautologin'))
		{
			// Load the token and user
			$token = ORM::factory('user_token', array('token' => $token));

			if ($token->loaded() AND $token->user->loaded()  AND $token->user->id != 1)
			{
				if ($token->user_agent === sha1(Request::$user_agent))
				{
					// Save the token to create a new unique token
					$token->save();

					// Set the new token
					Cookie::set('authautologin', $token->token, $token->expires - time());

					// Complete the login with the found data
					$this->complete_login($token->user);

					// Automatic login was successful
					return $token->user;
				}

				// Token is invalid
				$token->delete();
			}
		}

		return FALSE;
	}

	/**
	 * Forces a user to be logged in, without specifying a password.
	 *
	 * @param   mixed    username string, or user ORM object
	 * @param   boolean  mark the session as forced
	 * @return  boolean
	 */
	public function force_login($user, $mark_session_as_forced = FALSE)
	{
		if ( ! is_object($user))
		{
			$username = $user;

			// Load the user
			$user = ORM::factory('user');
			$user->where($user->unique_key($username), '=', $username)->find();
		}

		if ($mark_session_as_forced === TRUE)
		{
			// Mark the session as forced, to prevent users from changing account information
			$this->_session->set('auth_forced', TRUE);
		}

		// Run the standard completion
		$this->complete_login($user);
	}

	/**
	 * Forces a user to be logged in when using SSO, without specifying a password.
	 *
	 * @param   ORM      $user
	 * @param   boolean  $mark_session_as_forced
	 * @return  boolean
	 */
	public function force_sso_login(ORM $user, $mark_session_as_forced = FALSE)
	{
		if ($mark_session_as_forced === TRUE)
		{
			// Mark the session as forced, to prevent users from changing account information
			$this->_session->set('auth_forced', TRUE);
		}

		// Token data
		$data = array(
			'user_id'    => $user->id,
			'expires'    => time() + $this->_config['lifetime'],
			'user_agent' => sha1(Request::$user_agent),
		);

		// Create a new autologin token
		$token = ORM::factory('user_token')
			->values($data)
			->create();

		// Set the autologin cookie
		Cookie::set('authautologin', $token->token, $this->_config['lifetime']);

		// Run the standard completion
		$this->complete_login($user);
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
		// Set by force_login()
		$this->_session->delete('auth_forced');

		if ($token = Cookie::get('authautologin'))
		{
			// Delete the autologin cookie to prevent re-login
			Cookie::delete('authautologin');

			// Clear the autologin token from the database
			$token = ORM::factory('user_token', array('token' => $token));

			if ($token->loaded() AND $logout_all)
			{
				ORM::factory('user_token')->where('user_id', '=', $token->user_id)->delete_all();
			}
			elseif ($token->loaded())
			{
				$token->delete();
			}
		}

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

	/**
	 * Logs a user in.
	 *
	 * @param   string   username
	 * @param   string   password
	 * @param   boolean  enable autologin
	 * @return  boolean
	 */
	protected function _login($user, $password, $remember)
	{
		if ( ! is_object($user))
		{
			$username = $user;

			// Load the user
			$user = ORM::factory('user');
			$user->where($user->unique_key($username), '=', $username)->find();
		}

		// If the passwords match, perform a login! role id: 2
		if ($user->has('roles', 2) AND User::check_pass($user, $password) AND $user->id !== 1)
		{
			if ($remember === TRUE)
			{
				// Token data
				$data = array(
					'user_id'    => $user->id,
					'expires'    => time() + $this->_config['lifetime'],
					'user_agent' => sha1(Request::$user_agent),
					'type'	     => 'autologin',
					'created'    => time(),
				);

				// Create a new autologin token
				$token = ORM::factory('user_token')
					->values($data)
					->create();

				// Set the autologin cookie
				Cookie::set('authautologin', $token->token, $this->_config['lifetime']);
			}

			// Finish the login
			$this->complete_login($user);

			return TRUE;
		}

		// Login failed
		return FALSE;
	}

	/**
	 * Complete the login for a user by incrementing the logins and setting
	 * session data: user_id, username, roles.
	 *
	 * @param   object  user ORM object
	 * @return  void
	 */
	protected function complete_login($user)
	{
		$user->complete_login();

		// Regenerate session_id
		$this->_session->regenerate();

		// Store username in session
		$this->_session->set($this->_config['session_key'], $user);

		return TRUE;
	}

	/**
	 * Ensure the hash key variable used for Auth.
	 *
	 * @return  string  The hash key.
	 *
	 * @uses    Config::load
	 */
	public static function hashKey()
	{
		$config = Config::load('site');

		if ( !($key = $config->get('auth_hash_key')) )
		{
			$key = sha1(uniqid(mt_rand(), TRUE)) . md5(uniqid(mt_rand(), TRUE));
			$config->set('auth_hash_key', $key);
		}

		return $key;
	}

	/**
	 * Timing attack safe string comparison
	 *
	 * @param   string  known_string
	 * @param   string  user_string
	 * @return  bool
	 */
	public static function hashEquals($known_string, $user_string)
	{
		// Available only in php >= 5.6.0
		if ( function_exists('hash_equals') )
		{
			return hash_equals($known_string, $user_string);
		}

		return System::equalsHashes($known_string, $user_string);
	}
}
