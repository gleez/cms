<?php
/**
 * File Auth driver
 *
 *
 * @package    Gleez\User
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 *
 * @todo This Auth driver does not support roles nor autologin.
 */
class Auth_File extends Auth {

	// User list
	protected $_users;

	/**
	 * Constructor loads the user list into the class.
	 */
	public function __construct($config_name = 'auth')
	{
		$config  = Kohana::$config->load($config_name);
		parent::__construct($config_name);

		// Load user list
		$this->_users = Arr::get($config, 'users', array());
	}

	/**
	 * Logs a user in.
	 *
	 * @param   string   username
	 * @param   string   password
	 * @param   boolean  enable autologin (not supported)
	 * @return  boolean
	 */
	protected function _login($username, $password, $remember)
	{
		if (isset($this->_users[$username]) AND $this->_users[$username] === $password)
		{
			// Complete the login
			return $this->complete_login($username);
		}

		// Login failed
		return FALSE;
	}

	/**
	 * Forces a user to be logged in, without specifying a password.
	 *
	 * @param   mixed    username
	 * @return  boolean
	 */
	public function force_login($username)
	{
		// Complete the login
		return $this->complete_login($username);
	}

	/**
	 * Get the stored password for a username.
	 *
	 * @param   mixed   username
	 * @return  string
	 */
	public function password($username)
	{
		return Arr::get($this->_users, $username, FALSE);
	}

	/**
	 * Compare password with original (plain text). Works for current (logged in) user
	 *
	 * @param   string  $password
	 * @return  boolean
	 */
	public function check_password($password)
	{
		$username = $this->get_user();

		if ($username === FALSE)
		{
			return FALSE;
		}

		return ($password === $this->password($username));
	}

} // End Auth File