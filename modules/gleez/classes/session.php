<?php
/**
 * Base session class
 *
 * @package    Gleez\Session
 * @author     Gleez Team
 * @version    1.0.3
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
abstract class Session {

	/**
	 * Default session adapter
	 * @var string
	 */
	public static $default = 'native';

	/**
	 * Session instances
	 * @var array
	 */
	public static $instances = array();

	/**
	 * Cookie name
	 * @var string
	 */
	protected $_name = 'session';

	/**
	 * Cookie lifetime
	 * @var integer
	 */
	protected $_lifetime = 0;

	/**
	 * Encrypt session data?
	 * @var boolean
	 */
	protected $_encrypted = FALSE;

	/**
	 * Session data
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Session destroyed?
	 * @var boolean
	 */
	protected $_destroyed = FALSE;

	/**
	 * Creates a singleton session of the given type. Some session types
	 * (native, database) also support restarting a session by passing a
	 * session id as the second parameter.
	 *
	 * Example:
	 * ~~~
	 * $session = Session::instance();
	 * ~~~
	 *
	 * [!!] [Session::write] will automatically be called when the request ends.
	 *
	 * @param   string  $type   type of session (native, cookie, etc)
	 * @param   string  $id     session identifier
	 *
	 * @return  Session
	 *
	 * @uses    Config::get
	 */
	public static function instance($type = NULL, $id = NULL)
	{
		if ($type === NULL)
		{
			// Use the default type
			$type = Session::$default;
		}

		if ( ! isset(Session::$instances[$type]))
		{
			// Load the configuration for this type
			$config = Config::get("session.{$type}");

			// Set the session class name
			$class = 'Session_'.ucfirst($type);

			// Create a new session instance
			Session::$instances[$type] = $session = new $class($config, $id);

			// Write the session at shutdown
			register_shutdown_function(array($session, 'write'));
		}

		return Session::$instances[$type];
	}

	/**
	 * Overloads the name, lifetime, and encrypted session settings.
	 *
	 * [!!] Sessions can only be created using the [Session::instance] method.
	 *
	 * @param   array   $config configuration
	 * @param   string  $id     session id
	 */
	public function __construct(array $config = NULL, $id = NULL)
	{
		if (isset($config['name']))
		{
			// Cookie name to store the session id in
			$this->_name = (string) $config['name'];
		}

		if (isset($config['lifetime']))
		{
			// Cookie lifetime
			$this->_lifetime = (int) $config['lifetime'];
		}

		if (isset($config['encrypted']))
		{
			if ($config['encrypted'] === TRUE)
			{
				// Use the default Encrypt instance
				$config['encrypted'] = 'default';
			}

			// Enable or disable encryption of data
			$this->_encrypted = $config['encrypted'];
		}

		// Load the session
		$this->read($id);
	}

	/**
	 * Session object is rendered to a serialized string. If encryption is
	 * enabled, the session will be encrypted. If not, the output string will
	 * be encoded using [base64_encode].
	 *
	 * Example:
	 * ~~~
	 * echo $session;
	 * ~~~
	 *
	 * @return  string
	 *
	 * @uses    Encrypt::encode
	 */
	public function __toString()
	{
		// Serialize the data array
		$data = serialize($this->_data);

		if ($this->_encrypted)
		{
			// Encrypt the data using the default key
			$data = Encrypt::instance($this->_encrypted)->encode($data);
		}
		else
		{
			// Obfuscate the data with base64 encoding
			$data = base64_encode($data);
		}

		return $data;
	}

	/**
	 * Returns the current session array. The returned array can also be
	 * assigned by reference.
	 *
	 * Example:
	 * ~~~
	 * // Get a copy of the current session data
	 * $data = $session->as_array();
	 *
	 * // Assign by reference for modification
	 * $data =& $session->as_array();
	 * ~~~
	 *
	 * @return  array
	 */
	public function & as_array()
	{
		return $this->_data;
	}

	/**
	 * Get the current session id, if the session supports it.
	 *
	 * Example:
	 * ~~~
	 * $id = $session->id();
	 * ~~~
	 *
	 * [!!] Not all session types have ids.
	 *
	 * @return  string
	 */
	public function id()
	{
		return NULL;
	}

	/**
	 * Get the current session cookie name.
	 *
	 * Example:
	 * ~~~
	 * $name = $session->name();
	 * ~~~
	 *
	 * @return  string
	 */
	public function name()
	{
		return $this->_name;
	}

	/**
	 * Get a variable from the session array.
	 *
	 * Example:
	 * ~~~
	 * $foo = $session->get('foo');
	 * ~~~
	 *
	 * @param   string  $key      variable name
	 * @param   mixed   $default  default value to return
	 *
	 * @return  mixed
	 */
	public function get($key, $default = NULL)
	{
		return array_key_exists($key, $this->_data) ? $this->_data[$key] : $default;
	}

	/**
	 * Get and delete a variable from the session array.
	 *
	 * Example:
	 * ~~~
	 * $bar = $session->get_once('bar');
	 * ~~~
	 *
	 * @param   string  $key      variable name
	 * @param   mixed   $default  default value to return
	 *
	 * @return  mixed
	 */
	public function get_once($key, $default = NULL)
	{
		$value = $this->get($key, $default);

		unset($this->_data[$key]);

		return $value;
	}

	/**
	 * Set a variable in the session array.
	 *
	 * Example:
	 * ~~~
	 * $session->set('foo', 'bar');
	 * ~~~
	 *
	 * @param   string  $key    variable name
	 * @param   mixed   $value  value
	 *
	 * @return  $this
	 */
	public function set($key, $value)
	{
		$this->_data[$key] = $value;

		return $this;
	}

	/**
	 * Set a variable by reference.
	 *
	 * Example:
	 * ~~~
	 * $session->bind('foo', $foo);
	 * ~~~
	 *
	 * @param   string  $key    variable name
	 * @param   mixed   $value  referenced value
	 *
	 * @return  $this
	 */
	public function bind($key, & $value)
	{
		$this->_data[$key] =& $value;

		return $this;
	}

	/**
	 * Removes a variable in the session array.
	 *
	 * Example:
	 * ~~~
	 * $session->delete('foo');
	 * ~~~
	 *
	 * @param   string  $key,...    variable name
	 *
	 * @return  $this
	 */
	public function delete($key)
	{
		$args = func_get_args();

		foreach ($args as $key)
		{
			unset($this->_data[$key]);
		}

		return $this;
	}

	/**
	 * Overload catch exception with session destroy and log
	 *
	 * Loads existing session data.
	 *
	 * Example:
	 * ~~~
	 * $session->read();
	 * ~~~
	 *
	 * @param   string   session id
	 *
	 * @return  void
	 */
	public function read($id = NULL)
	{
		$data = NULL;

		try
		{
			if (is_string($data = $this->_read($id)))
			{
				if ($this->_encrypted)
				{
					// Decrypt the data using the default key
					$data = Encrypt::instance($this->_encrypted)->decode($data);
				}
				else
				{
					// Decode the base64 encoded data
					$data = base64_decode($data);
				}

				// Unserialize the data
				$data = unserialize($data);
			}
			else
			{
				// Ignore these, session is valid, likely no data though.
			}
		}
		catch (Exception $e)
		{
			// Destroy the session
			$this->destroy();

			// Log & ignore all errors when a read fails
			Log::error(Gleez_Exception::text($e))->write();

			return;
		}

		if (is_array($data))
		{
			// Load the data locally
			$this->_data = $data;
		}
	}

	/**
	 * Generates a new session id and returns it.
	 *
	 * Example:
	 * ~~~
	 * $id = $session->regenerate();
	 * ~~~
	 *
	 * @return  string
	 */
	public function regenerate()
	{
		return $this->_regenerate();
	}

	/**
	 * Sets the last_active timestamp and saves the session.
	 *
	 * Example:
	 * ~~~
	 * $session->write();
	 * ~~~
	 *
	 * [!!] Any errors that occur during session writing will be logged,
	 * but not displayed, because sessions are written after output has
	 * been sent.
	 *
	 * @return  boolean
	 *
	 * @uses    Log::add
	 */
	public function write()
	{
		if (headers_sent() OR $this->_destroyed)
		{
			// Session cannot be written when the headers are sent or when
			// the session has been destroyed
			return FALSE;
		}

		// Set the last active timestamp
		$this->_data['last_active'] = time();

		try
		{
			return $this->_write();
		}
		catch (Exception $e)
		{
			// Log & ignore all errors when a write fails
			Log::error(Kohana_Exception::text($e))->write();

			return FALSE;
		}
	}

	/**
	 * Completely destroy the current session.
	 *
	 * Example:
	 * ~~~
	 * $success = $session->destroy();
	 * ~~~
	 *
	 * @return  boolean
	 */
	public function destroy()
	{
		if ($this->_destroyed === FALSE)
		{
			if ($this->_destroyed = $this->_destroy())
			{
				// The session has been destroyed, clear all data
				$this->_data = array();
			}
		}

		return $this->_destroyed;
	}

	/**
	 * Restart the session.
	 *
	 * Example:
	 * ~~~
	 * $success = $session->restart();
	 * ~~~
	 *
	 * @return  boolean
	 */
	public function restart()
	{
		if ($this->_destroyed === FALSE)
		{
			// Wipe out the current session.
			$this->destroy();
		}

		// Allow the new session to be saved
		$this->_destroyed = FALSE;

		return $this->_restart();
	}

	/**
	 * Loads the raw session data string and returns it.
	 *
	 * @param   string  $id session id
	 *
	 * @return  string
	 */
	abstract protected function _read($id = NULL);

	/**
	 * Generate a new session id and return it.
	 *
	 * @return  string
	 */
	abstract protected function _regenerate();

	/**
	 * Writes the current session.
	 *
	 * @return  boolean
	 */
	abstract protected function _write();

	/**
	 * Destroys the current session.
	 *
	 * @return  boolean
	 */
	abstract protected function _destroy();

	/**
	 * Restarts the current session.
	 *
	 * @return  boolean
	 */
	abstract protected function _restart();
}