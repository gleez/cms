<?php defined('SYSPATH') or die('No direct script access.');
/**
 * MongoDB log writer
 *
 * @package    Gleez
 * @category   Log
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Gleez_Log_Mongo extends Log_Writer {

	/*
	 * Collection to write log data to
	 * Make this is a capped collection for better performance
	 * http://www.mongodb.org/display/DOCS/Capped+Collections
	*/
	protected $_collection;

	/*
	 * Name of MangoDB configuration
	 */
	protected $_name;
	
	/**
	 * Creates a new mongo logger. 
	 *
	 *     $writer = new Log_Mongo($collection);
	 *
	 * @param   string  log collection
	 * @return  void
	 */
	public function __construct($collection = 'Logs', $name = 'default')
	{
		$this->_collection = $collection;
		$this->_name = $name;
	}
	
	/**
	 * Writes each message to the MongoDB Collection
	 *
	 * @param   array   messages
	 * @return  void
	 */
	public function write(array $messages)
	{
		$info = array(
				'hostname'   => Request::$client_ip,
				'user_agent' => Request::$user_agent,
				'url'        => Text::plain( Request::initial()->uri() ),
				'user'	     => User::active_user()->id,
				'referer'    => isset(Request::$referrer) ? Text::plain( Request::$referrer ) : '',
			);
	
		$logs = array();
		foreach ($messages as $message)
		{
			if(isset($message))
			{
				$message['type'] = $this->_log_levels[$message['level']];
				$message['time'] =  new MongoDate( strtotime($message['time']) );
				unset($message['level']);
				$logs[] = array_merge($info, $message); //merge info with message
			}
		}
	
		if( !empty($logs) )
		{
			// Write message into the collection
			MangoDB::instance($this->_name)->batch_insert( $this->_collection, $logs );
		}
	}

}