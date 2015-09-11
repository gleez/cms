<?php
/**
 * Redis-based session class.
 *
 *
 * @package    Gleez\Session\Redis
 * @author     Gleez Team
 * @version    1.1.0
 * @copyright  (c) 2011-2015 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */

class Session_Redis extends Session {

	/**
	 * Database instance
	 * @var Database
	 */
	protected $_redis;

	/**
	 * The current session id
	 * @var string
	 */
	protected $_session_id;

	/**
	 * Class constructor
	 *
	 * @param  array   $config  Configuration [Optional]
	 * @param  string  $id      Session id [Optional]
	 */
	public function __construct(array $config = NULL, $id = NULL) {

		// Check that the PhpRedis extension is loaded.
		if (!extension_loaded('redis')) {
			throw new Gleez_Exception('You must have PhpRedis installed and enabled to use.');
		}

		$host = 'localhost';
		$port = 6379;

		if (isset($config['host'])) {
			$host = $config['host'];
		}

		if (isset($config['port'])) {
			$port = (int) $config['port'];
		}

		try {
			// Create a new Redis instance and start a connection using the settings provided.
			$this->_redis = new Redis;
			$this->_redis->connect($host, $port);
		} catch (Exception $e) {
			Log::error('An error occurred connecting Redis Session: [:error]', [':error' => $e->getMessage()]);
		}

		parent::__construct($config, $id);
	}

	/**
	 * Get the current session id
	 *
	 * Example:
	 * ~~~
	 * $id = $session->id();
	 * ~~~
	 *
	 * @return  string
	 */
	public function id() {
		return $this->_session_id;
	}

	/**
	 * Loads the raw session data string and returns it.
	 *
	 * @param   string  $id  Session id [Optional]
	 * @return  string
	 */
	protected function _read($id = NULL) {
		if ($id OR $id = Cookie::get($this->_name)) {
			$result = $this->_redis->get($id);

			if ($result) {
				// Set the current session id
				$this->_session_id = $id;

				// Return the contents
				return $result;
			}
		}

		// Create a new session id
		$this->_regenerate();

		return NULL;
	}

	/**
	 * Generate a new session id and return it.
	 *
	 * @return  string
	 */
	protected function _regenerate() {
		// Create a new session id
		$id = str_replace('.', '-', uniqid(NULL, TRUE));

		return $this->_session_id = $id;
	}

	/**
	 * Writes the current session.
	 *
	 * @return  boolean
	 */
	protected function _write() {
		// Save to Redis
		$this->_redis->set($this->_session_id, $this->__toString(), $this->_lifetime);

		// Update the cookie with the new session id
		Cookie::set($this->_name, $this->_session_id, $this->_lifetime);

		return TRUE;
	}

	/**
	 * Destroys the current session.
	 *
	 * @return  boolean
	 */
	protected function _destroy() {

		try
		{
			// Execute the query
			$this->_redis->delete($this->_session_id);

			// Delete the cookie
			Cookie::delete($this->_name);
		} catch (Exception $e) {
			// An error occurred, the session has not been deleted
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Restarts the current session.
	 *
	 * @return  boolean
	 */
	protected function _restart() {
		$this->_regenerate();

		return TRUE;
	}

}
