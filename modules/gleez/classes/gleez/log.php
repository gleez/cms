<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Message logging with observer-based log writing.
 *
 * [!!] This class does not support extensions, only additional writers.
 *
 * Override add() with information hostname, referer, user, url, user_agent
 *
 * @package	Gleez
 * @category	Logging
 * @author	Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2012 Gleez Technologies
 * @license	http://gleezcms.org/license
 */
class Gleez_log extends Kohana_Log {

	/**
	 * Adds a message to the log. Replacement values must be passed in to be
	 * replaced using [strtr](http://php.net/strtr).
	 *
	 *     $log->add(Log::ERROR, 'Could not locate user: :user', array(
	 *         ':user' => $username,
	 *     ));
	 *
	 * @param   string  level of message
	 * @param   string  message body
	 * @param   array   values to replace in the message
	 * @return  Log
	 */
	public function add($level, $message, array $values = NULL)
	{
		if ($values)
		{
			// Insert the values into the message
			$message = strtr($message, $values);
		}

		// Create a new message and timestamp it
		$this->_messages[] = array
		(
			'time'       => Date::formatted_time('now', Log::$timestamp, Log::$timezone),
			'level'      => $level,
			'body'       => $message,
		);

		if (Log::$write_on_add)
		{
			// Write logs as they are added
			$this->write();
		}

		return $this;
	}
        
}