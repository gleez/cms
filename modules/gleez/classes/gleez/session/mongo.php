<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Mongo-based session class.
 *
 * @package	Gleez
 * @category	Session/Mongo
 * @author	Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2012 Gleez Technologies
 * @license	http://gleezcms.org/license
 */
class Gleez_Session_Mongo extends Session {

	/**
	 * @var garbage collection requests
	 */
	protected $_gc = 500;
	
	/**
	 * @var string the current session
	 */
	protected $_session;

	// The old session id
	protected $_update_id;
        
        /**
	 * @var string the client user id
	 */
	protected $_user_id;
        
	/**
	 * Constructor
	 */
	public function __construct(array $config = NULL, $id = NULL)
	{
		// Load aditional config
		if ( isset($config['gc']) )
		{
			$this->_gc = (int) $config['gc'];
		}

                $this->_user_id   = 0;

		parent::__construct($config, $id);

		if ( mt_rand(0, $this->_gc) === $this->_gc )
		{
			// Collect
			$this->_gc();
		}
	}

	public function id()
	{
		return $this->_session;
	}
        
	/**
	 * Read session data
	 *
	 * @param integer $id
	 */
	protected function _read($id = NULL)
	{
		if ( $id OR $id = Cookie::get($this->_name))
		{
			$id = explode('-', $id);

			if ( count($id) === 2)
			{
				$criteria =  array(
                                                '_id'   => new MongoId($id[0]),
                                                'token' => new MongoId($id[1])
                                                );
                        
                                $result = MangoDB::instance()->find_one('sessions', $criteria);
	
                                if( $result != NULL )
                                {
                                        // Set the current session id
                                        $this->_session = $result['_id'];
                                
                                        return $result['contents'];
                                }
			}
		}

		return NULL;
	}

        /**
         * Create new session
         */
	protected function _regenerate()
	{
		// nothing here as the token is regenerated no matter what
	}

        /**
         * Write session data
         */
        protected function _write()
        {
		$data = array(
                                'last_active' => $this->_data['last_active'],
                                'contents'    => $this->__toString(),
                                'token'       => new MongoId, // regenerate against session fixation attacks
                                'hostname'    => Request::$client_ip,
                                'uid'         => $this->_user_id,
                        );

                try
		{
                        if( $this->_session == NULL OR ! isset($data['_id']) )
                        {
                                MangoDB::instance()->insert('sessions', $data);
                        }
                        else
                        {
                                $data['_id'] = new MongoId($this->_session);
                                $criteria    = array('_id' => $data['_id'] );
                                MangoDB::instance()->update('sessions', $criteria, $data);
                        }
		}
		catch ( MongoCursorException $e)
		{
			throw new Kohana_Exception('Unable to update sessions, database returned error :error',
					array(':error' => $e->getMessage()));
                }
        
                if( isset($data['_id']) )
                {
                        $this->_session = $data['_id'];
        
                        // Update cookie
                        Cookie::set($this->_name, $this->_session . '-' . $data['token'], $this->_lifetime);
                }

		return TRUE;
        }

        /**
         * Delete session
         */
        protected function _destroy()
        {
		if ( $this->_session !== NULL )
		{
			MangoDB::instance()->remove('sessions', array('_id'=> new MongoId($this->_session) ) );
			$this->_session = NULL;

			Cookie::delete($this->_name);
		}

		return TRUE;
	}
        
        /**
         * Garbage Collector to delete old sessions
        */
        protected function _gc()
        {
                if ( $this->_lifetime)
                {
                        $expires = $this->_lifetime;
                }
                else
                {
                        $expires = Date::MONTH;
                }

                // Delete old sessions
                MangoDB::instance()->remove('sessions', array('last_active' => array('$lt' => time() - $expires)));
        }

	/**
	 * @return  bool
	 */
	protected function _restart()
	{
		// Fire up a new session
		//$status = session_start();

		// Use the $_SESSION global for storing data
		//$this->_data =& $_SESSION;

		//return $status;
	}
}
