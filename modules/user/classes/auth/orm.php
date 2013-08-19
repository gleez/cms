<?php
/**
 * ORM Auth driver
 *
 * @package    Gleez\User
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Auth_ORM extends Auth {

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
	 * Checks if a session is active.
	 *
	 * @param   mixed    $role Role name string, role ORM object, or array with role names
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
	 * Gets the currently logged in user from the session (with auto_login check).
	 * Returns FALSE if no user is currently logged in.
	 *
	 * @return  mixed
	 */
	public function get_user($default = NULL)
	{
		$user = parent::get_user($default);

		if ( !$user OR $user->id === 1 )
		{
			// check for "remembered" login
			$user = $this->auto_login();
		}

		return $user;
	}

	/**
	 * Log a user out and remove any autologin cookies.
	 *
	 * @param   boolean  completely destroy the session
	 * @param	boolean  remove all tokens for user
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

		return parent::logout($destroy);
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

		return $user->passw;
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

		return parent::complete_login($user);
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
			return FALSE;

		return ($this->hash($password) === $user['pass']);
	}

	/**
	 * Register a single user
	 * Method to register new user by Auth module, when you set the
	 * fields, be sure they must respect the driver rules
	 *
	 * @param 	array 	$fields An array witch contains the fields to be populate
	 * @return	boolean Operation final status
	 */
	public function register($fields)
	{
		if( ! is_object($fields) )
		{
			// Load the user
			$user = ORM::factory('user');
		}
		else
		{
			// Check for instanced model
			if( $fields instanceof Model_User )
			{
				$user = $fields;
			}
			else
			{
				throw new Gleez_Exception('Invalid user fields.');
			}
		}
		try
		{
			$user->create_user($fields, array(
				'name',
				'pass',
				'mail',
			));

			// Add the login role to the user (add a row to the db)
			$login_role = new Model_Role(array('name' =>'login'));
			$user->add('roles', $login_role);
		}
		catch (ORM_Validation_Exception $e)
		{
			throw $e;
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Unegister multiple users
	 * Method to unregister existing user by Auth module, when you set the
	 * Model_User reference for removing a user.
	 *
	 * @param 	mixed 	$users An array witch contains the Model_User or a array of Model_User
	 * @return 	void
	 */
	public function unregister ($users)
	{
		if( ! is_array($users))
			$users = array($users);

		foreach ($users as $user)
		{
			if($user instanceof Model_User)
			{
				try
				{
					$user->delete();
				}
				catch (ORM_Validation_Exception $e)
				{
					throw $e;
				}
			}
			elseif( ! is_null($user) )
			{
				throw new Gleez_Exception("Invalid argument, must be instance of Model_User or array() containing Model_User's");
			}
		}
	}

} // End Auth ORM