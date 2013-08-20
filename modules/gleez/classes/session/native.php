<?php
/**
 * Native PHP session class
 *
 * @package    Gleez\Session\Native
 * @author     Gleez Team
 * @author     Kohana Team
 * @version    1.0.1
 * @copyright  (c) 2008-2012 Kohana Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://kohanaframework.org/license
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Session_Native extends Session {

	/**
	 * Get set the current session id
	 *
	 * @return  string
	 */
	public function id()
	{
		return session_id();
	}

	/**
	 * Loads the raw session data string
	 *
	 * @param   string  $id  session id
	 *
	 * @return  null
	 */
	protected function _read($id = NULL)
	{
		// Sync up the session cookie with Cookie parameters
		session_set_cookie_params($this->_lifetime, Cookie::$path, Cookie::$domain, Cookie::$secure, Cookie::$httponly);

		// Do not allow PHP to send Cache-Control headers
		session_cache_limiter(FALSE);

		// Set the session cookie name
		session_name($this->_name);

		if ($id)
		{
			// Set the session id
			session_id($id);
		}

		// Start the session
		session_start();

		// Use the $_SESSION global for storing data
		// @todo PHP 5.5 issue
		$this->_data =& $_SESSION;

		return NULL;
	}

	/**
	 * Generate a new session id and return it
	 *
	 * @return  string
	 */
	protected function _regenerate()
	{
		// Regenerate the session id
		session_regenerate_id();

		return session_id();
	}

	/**
	 * Writes the current session
	 *
	 * @return  boolean
	 */
	protected function _write()
	{
		// Write and close the session
		session_write_close();

		return TRUE;
	}

	/**
	 * Restarts the current session
	 *
	 * @return  boolean
	 */
	protected function _restart()
	{
		// Fire up a new session
		$status = session_start();

		// Use the $_SESSION global for storing data
		$this->_data =& $_SESSION;

		return $status;
	}

	/**
	 * Destroys the current session
	 *
	 * @return  boolean
	 */
	protected function _destroy()
	{
		// Destroy the current session
		session_destroy();

		// Did destruction work?
		$status = ! session_id();

		if ($status)
		{
			// Make sure the session cannot be restarted
			Cookie::delete($this->_name);
		}

		return $status;
	}
}
