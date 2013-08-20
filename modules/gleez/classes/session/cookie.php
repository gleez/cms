<?php
/**
 * Cookie-based session class
 *
 * @package    Gleez\Session\Cookie
 * @author     Gleez Team
 * @author     Kohana Team
 * @version    1.0.1
 * @copyright  (c) 2008-2012 Kohana Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://kohanaframework.org/license
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Session_Cookie extends Session {

	/**
	 * Loads the raw session data string and returns it
	 *
	 * @param   string  $id  session id
	 *
	 * @return  string
	 */
	protected function _read($id = NULL)
	{
		return Cookie::get($this->_name, NULL);
	}

	/**
	 * @return  null
	 */
	protected function _regenerate()
	{
		// Cookie sessions have no id
		return NULL;
	}

	/**
	 * Writes the current session
	 *
	 * @return  boolean
	 */
	protected function _write()
	{
		return Cookie::set($this->_name, $this->__toString(), $this->_lifetime);
	}

	/**
	 * Restarts the current session
	 *
	 * @return  boolean
	 */
	protected function _restart()
	{
		return TRUE;
	}

	/**
	 * Destroys the current session
	 *
	 * @return  boolean
	 */
	protected function _destroy()
	{
		return Cookie::delete($this->_name);
	}

}
