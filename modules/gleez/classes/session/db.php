<?php
/**
 * Database-based session class.
 *
 * Sample schema:
 * ~~~
 * CREATE TABLE  `sessions` (
 *     `session_id` VARCHAR( 24 ) NOT NULL,
 *     `last_active` INT UNSIGNED NOT NULL,
 *     `contents` TEXT NOT NULL,
 *     `hostname` VARCHAR( 128 ) DEFAULT '',
 *     `user_id` int(11) DEFAULT '0',
 *     PRIMARY KEY ( `session_id` ),
 *     INDEX ( `last_active` )
 * ) ENGINE = MYISAM;
 * ~~~
 *
 * @package    Gleez\Session\Db
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Session_Db extends Session {

	/**
	 * Database instance
	 * @var Database
	 */
	protected $_db;

	/**
	 * Database table name
	 * @var string
	 */
	protected $_table = 'sessions';

	/**
	 * Database column names
	 * @var array
	 */
	protected $_columns = array(
		'session_id'  => 'session_id',
		'last_active' => 'last_active',
		'contents'    => 'contents',
		'hostname'    => 'hostname',
		'uid'         => 'user_id'
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
	protected $_user_id;

	/**
	 * Class constructor
	 *
	 * @param  array   $config  Configuration [Optional]
	 * @param  string  $id      Session id [Optional]
	 */
	public function __construct(array $config = NULL, $id = NULL)
	{
		if ( ! isset($config['group']))
		{
			// Use the default group
			$config['group'] = 'default';
		}

		// Load the database
		$this->_db = Database::instance($config['group']);

		if (isset($config['table']))
		{
			// Set the table name
			$this->_table = (string) $config['table'];
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

		$this->_user_id = 0;

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
			$result = DB::select(array($this->_columns['contents'], 'contents'))
				->from($this->_table)
				->where($this->_columns['session_id'], '=', ':id')
				->limit(1)
				->param(':id', $id)
				->execute($this->_db);

			if ($result->count())
			{
				// Set the current session id
				$this->_session_id = $this->_update_id = $id;

				// Return the contents
				return $result->get('contents');
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
	protected function _regenerate()
	{
		// Create the query to find an ID
		$query = DB::select($this->_columns['session_id'])
			->from($this->_table)
			->where($this->_columns['session_id'], '=', ':id')
			->limit(1)
			->bind(':id', $id);

		do
		{
			// Create a new session id
			$id = str_replace('.', '-', uniqid(NULL, TRUE));

			// Get the the id from the database
			$result = $query->execute($this->_db);
		}
		while ($result->count());

		return $this->_session_id = $id;
	}

	/**
	 * Writes the current session.
	 *
	 * @return  boolean
	 */
	protected function _write()
	{
		if ($this->_update_id === NULL)
		{
			// Insert a new row
			$query = DB::insert($this->_table, $this->_columns)
				->values(array(':new_id', ':active', ':contents', ':hostname', ':user_id'));
		}
		else
		{
			// Update the row
			$query = DB::update($this->_table )
				->value($this->_columns['last_active'], ':active')
				->value($this->_columns['contents'], ':contents')
				->value($this->_columns['hostname'], ':hostname')
				->value($this->_columns['user_id'], ':user_id')
				->where($this->_columns['session_id'], '=', ':old_id');

			if ($this->_update_id !== $this->_session_id)
			{
				// Also update the session id
				$query->value($this->_columns['session_id'], ':new_id');
			}
		}

		$query
			->param(':new_id',   $this->_session_id)
			->param(':old_id',   $this->_update_id)
			->param(':active',   $this->_data['last_active'])
			->param(':hostname', Request::$client_ip)
			->param(':user_id',  $this->_user_id)
			->param(':contents', $this->__toString());

		// Execute the query
		$query->execute($this->_db);

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
	 */
	protected function _destroy()
	{
		if ($this->_update_id === NULL)
		{
			// Session has not been created yet
			return TRUE;
		}

		// Delete the current session
		$query = DB::delete($this->_table)
			->where($this->_columns['session_id'], '=', ':id')
			->param(':id', $this->_update_id);

		try
		{
			// Execute the query
			$query->execute($this->_db);

			// Delete the cookie
			Cookie::delete($this->_name);
		}
		catch (Exception $e)
		{
			// An error occurred, the session has not been deleted
			return FALSE;
		}

		return TRUE;
	}

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

		// Delete all sessions that have expired
		DB::delete($this->_table)
			->where($this->_columns['last_active'], '<', ':time')
			->param(':time', time() - $expires)
			->execute($this->_db);
	}

	/**
	 * Restarts the current session.
	 *
	 * @return  boolean
	 */
	protected function _restart()
	{
		$this->_regenerate();

		return TRUE;
	}

}
