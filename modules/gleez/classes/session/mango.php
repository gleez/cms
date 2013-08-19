<?php
/**
 * MongoDB-based session class
 *
 * ### System Requirements
 *
 * - MongoDB 2.4 or higher
 * - PHP-extension MongoDB 1.4.0 or higher
 *
 * @package    Gleez\Session\Mango
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Session_Mango extends Session {

	/**
	 * Mango instance
	 * @var \Mango
	 */
	protected $_db;

	/**
	 * Mango_Collection instance
	 * @var \Mango_Collection
	 */
	protected $_collection = 'sessions';

	/**
	 * Collection column names
	 * @var array
	 */
	protected $_columns = array(
		'session_id'  => 'session_id',
		'last_active' => 'last_active',
		'contents'    => 'contents',
		'hostname'    => 'hostname',
		'user_id'     => 'user_id'
	);

	/**
	 * Garbage collection requests
	 * @var integer
	 */
	protected $_gc = 500;

	/**
	 * The current session id
	 * @var string
	 */
	protected $_session_id;

	/**
	 * The old session id
	 * @var string
	 */
	protected $_update_id;

	/**
	 * The client user id
	 * @var integer
	 */
	protected $_user_id = 0;

	/**
	 * Class constructor
	 *
	 * List of available options for `$config` array:
	 *
	 * * `group`:       Mango config group name
	 * * `collection`:  The name of the collection
	 * * `gc`:          Number of requests before gc is invoked
	 * * `fields`:      Custom field names, array of:
	 *   * `session_id`:   Session identifier
	 *   * `last_active`:  Timestamp of the last activity
	 *   * `contents`:     Serialized session data
	 *   * `hostname`:     Host name
	 *   * `user_id`:      The used ID
	 *
	 * [!!] Note: Sessions can only be created using the [Session::instance] method.
	 *
	 * @param  array   $config  Configuration array [Optional]
	 * @param  string  $id      Session id [Optional]
	 */
	public function __construct(array $config = NULL, $id = NULL)
	{
		if ( ! isset($config['group']))
		{
			// Use the default group
			$config['group'] = Mango::$default;
		}

		// Create Mango instance
		$this->_db = Mango::instance($config['group']);

		if (isset($config['collection']))
		{
			// Save the collection name for later use
			$this->_collection = (string)$config['collection'];
		}

		if (isset($config['gc']))
		{
			// Set the gc chance
			$this->_gc = (int) $config['gc'];
		}

		if (isset($config['columns']))
		{
			// Overload column names
			$this->_columns = $config['columns'];
		}

		// Load the collection
		$this->_collection = $this->_db->selectCollection($this->_collection);

		parent::__construct($config, $id);

		if (mt_rand(0, $this->_gc) === $this->_gc)
		{
			// Run garbage collection
			// This will average out to run once every X requests
			$this->_gc();
		}
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
	public function id()
	{
		return $this->_session_id;
	}

	/**
	 * Loads the raw session data string and returns it.
	 *
	 * @param   string  $id  Session id [Optional]
	 * @return  string
	 */
	protected function _read($id = NULL)
	{
		if ($id OR $id = Cookie::get($this->_name))
		{
			$retval = $this->_collection->findOne(
				array($this->_columns['session_id'] => $id)
			);

			if (count($retval))
			{
				// Set the current session id
				$this->_session_id = $this->_update_id = $id;

				// Return the contents
				return $retval['contents'];
			}
		}

		// Create a new session id
		$this->_regenerate();

		return NULL;
	}

	/**
	 * Generate a new session id and return it
	 *
	 * @return  string
	 */
	protected function _regenerate()
	{
		do
		{
			// Create a new session id
			$id = str_replace('.', '-', uniqid(NULL, TRUE));

			// Get the the id from the database
			$retval = $this->_collection->findOne(
				array($this->_columns['session_id'] => $id)
			);
		}
		while(count($retval));

		return $this->_session_id = $id;
	}

	/**
	 * Writes the current session
	 *
	 * @return  boolean
	 *
	 * @throws  Session_Exception
	 */
	protected function _write()
	{
		$data = array(
			$this->_columns['session_id']  => $this->_session_id,
			$this->_columns['last_active'] => $this->_data['last_active'],
			$this->_columns['contents']    => $this->__toString(),
			$this->_columns['hostname']    => Request::$client_ip,
			$this->_columns['user_id']     => $this->_user_id
		);

		if (is_null($this->_update_id))
		{
			if ( ! $this->_collection->insert($data))
			{
				throw new Session_Exception('Cannot create new session record :err', array(':err' => $this->_db->lastError()));
			}
		}
		else
		{
			if ($this->_update_id !== $this->_session_id)
			{
				// Also update the session id
				$data[$this->_columns['session_id']] = $this->_session_id;
			}

			try
			{
				// Update the row
				$this->_collection->safeUpdate(
					array($this->_columns['session_id'] => $this->_update_id),
					$data
				);
			}
			catch(MongoException $e)
			{
				throw new Session_Exception('Cannot update session record :err', array(':err' => $e->getMessage()));
			}
			catch(Exception $e)
			{
				throw new Session_Exception('Cannot update session record :err', array(':err' => $this->_db->lastError()));
			}
		}

		// The update and the session id are now the same
		$this->_update_id = $this->_session_id;

		// Update the cookie with the new session id
		Cookie::set($this->_name, $this->_session_id, $this->_lifetime);

		return TRUE;
	}

	/**
	 * Destroys the current session.
	 *
	 * @return  boolean
	 *
	 * @throws  Session_Exception
	 */
	protected function _destroy()
	{
		if (is_null($this->_update_id))
		{
			// Session has not been created yet
			return TRUE;
		}

		try
		{
			// Delete the current session
			$this->_collection->safeRemove(
				array($this->_columns['session_id'] => $this->_update_id),
				array('justOne' => TRUE)
			);

			// Delete the cookie
			Cookie::delete($this->_name);
		}
		catch (MongoException $e)
		{
			throw new Session_Exception('Cannot destroy session :err', array(':err' => $e->getMessage()));
		}
		catch (Exception $e)
		{
			throw new Session_Exception('Cannot destroy session :err', array(':err' => $this->_db->lastError()));
		}

		return TRUE;
	}

	/**
	 * Restarts the current session
	 *
	 * @return  boolean
	 */
	protected function _restart()
	{
		$this->_regenerate();

		return TRUE;
	}

	/**
	 * Garbage collection
	 *
	 * @throws Session_Exception
	 */
	protected function _gc()
	{
		if ($this->_lifetime)
		{
			// Expire sessions when their lifetime is up
			$expires = $this->_lifetime;
		}
		else
		{
			// Expire sessions after one month
			$expires = Date::MONTH;
		}

		$expired = __('this.:column < :time', array(':column' => $this->_columns['last_active'], ':time' => (time() - $expires)));

		try
		{
			$this->_collection->safeRemove(array('$where' => $expired));
		}
		catch (MongoException $e)
		{
			throw new Session_Exception('Cannot delete old sessions :err', array(':err' => $e->getMessage()));
		}
		catch (Exception $e)
		{
			throw new Session_Exception('Cannot delete old sessions :err', array(':err' => $this->_db->lastError()));
		}
	}
}