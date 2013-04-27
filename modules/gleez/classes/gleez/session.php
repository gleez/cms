<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Base session class
 *
 * @package    Gleez\Session
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
abstract class Gleez_Session extends Kohana_Session {
        
        /**
         * Overload catch exception with session destory and log
         * 
	 * Loads existing session data.
	 *
	 *     $session->read();
	 *
	 * @param   string   session id
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
                        
			// Error reading the session, usually
			// a corrupt session.
			//throw new Session_Exception('Error reading session data.', NULL, Session_Exception::SESSION_CORRUPT);
                        
                        // Log & ignore all errors when a read fails
                        Kohana::$log->add(Log::ERROR, Kohana_Exception::text($e))->write();
                        
                        return;
		}

		if (is_array($data))
		{
			// Load the data locally
			$this->_data = $data;
		}
	}
}