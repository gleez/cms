<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Message is a class that lets you easily send messages
 * in your application (aka Flash Messages)
 *
 * @package	Gleez
 * @category	Message
 * @author	Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2012 Gleez Technologies
 * @license	http://gleezcms.org/license
 */
class Gleez_Message {
        
        /**
	 * Constants to use for the types of messages that can be set.
	 */
	const ERROR   	= 'error';
	const ALERT 	= 'alert';
	const CRITICAL 	= 'critical';
	const NOTICE  	= 'notice';
	const SUCCESS 	= 'success';
	const WARN    	= 'warning';
        const INFO    	= 'info';
	const ACCESS 	= 'access';
        const DEBUG   	= 'debug';

	/**
	 * @var  string  default session key used for storing messages
	 */
	public static $session_key = 'messages';
        
	/**
	 * @var string default view
	 */
	public static $default_view = 'message/basic';

	/**
	 * Adds a new message.
	 *
	 * @param   string  message type (e.g. Message::SUCCESS)
	 * @param   string  Array/String for the message(s)
	 * @param   array   any options for the message
	 * @return  void
	 */        
        public static function set($type, $message, array $options = NULL)
        {
                // Load existing messages
		$messages = (array) self::get();
                
                // initialize if necessary
                if (!is_array($messages))
                {
                        $messages = array();
                }

                // Add new message
                if (is_array($message))
                {
			foreach ($message as $_type => $_message)
                        {
				$messages[] = (object) array(
					'type'     => $type,
                                        'text'     => $_message,
                                        'options'  => (array) $options,
				);
			}
		}
                else
                {
                        $messages[] = (object) array(
                                'type'     => $type,
                                'text'     => $message,
                                'options'  => (array) $options,
                        );
                }
        
                // set messages
                Session::instance()->set(self::$session_key, $messages);
        }

	/**
	 * Sets an error message.
	 *
	 * @param	mixed	String/Array for the message(s)
	 * @return	void
	 */
	public static function error($message, array $options = NULL)
	{
		self::set(self::ERROR, $message, $options);
	}
	
	/**
	 * Sets a ALERT message.
	 *
	 * @param	mixed	String/Array for the message(s)
	 * @return	void
	 */
	public static function alert($message, array $options = NULL)
	{
		self::set(self::ALERT, $message, $options);
	}

	/**
	 * Sets a CRITICAL message.
	 *
	 * @param	mixed	String/Array for the message(s)
	 * @return	void
	 */
	public static function critical($message, array $options = NULL)
	{
		self::set(self::CRITICAL, $message, $options);
	}
	
	/**
	 * Sets a notice.
	 *
	 * @param	mixed	String/Array for the message(s)
	 * @return	void
	 */
	public static function notice($message, array $options = NULL)
	{
		self::set(self::NOTICE, $message, $options);
	}

	/**
	 * Sets a success message.
	 *
	 * @param	mixed	String/Array for the message(s)
	 * @return	void
	 */
	public static function success($message, array $options = NULL)
	{
		self::set(self::SUCCESS, $message, $options);
	}
        
	/**
	 * Sets a warning message.
	 *
	 * @param	mixed	String/Array for the message(s)
	 * @return	void
	 */
	public static function warn($message, array $options = NULL)
	{
		self::set(self::WARN, $message, $options);
	}

	/**
	 * Sets a info message.
	 *
	 * @param	mixed	String/Array for the message(s)
	 * @return	void
	 */
	public static function info($message, array $options = NULL)
	{
		self::set(self::INFO, $message, $options);
	}

	/**
	 * Sets a ACCESS message.
	 *
	 * @param	mixed	String/Array for the message(s)
	 * @return	void
	 */
	public static function access($message, array $options = NULL)
	{
		self::set(self::ACCESS, $message, $options);
	}

	/**
	 * Sets a debug message, not in production stage.
	 *
	 * @param	mixed	String/Array for the message(s)
	 * @return	void
	 */
	public static function debug($message, array $options = NULL)
	{
                if (Kohana::$environment !== Kohana::PRODUCTION)
                {
                        self::set(self::DEBUG, $message, $options);
                }
	}

	/**
	 * The same as display - used to mold to Kohana standards
	 *
	 * @return	string	HTML for message
	 */
	public static function render($type = NULL, $delete = TRUE, $view = NULL)
	{
		return self::display($type, $delete, $view);
	}
        
	/**
	 * Returns all messages.
	 *
	 *	$messages = Message::get();
	 *	
	 *  	//Get error messages only
	 *  	$error_messages = Message::get(Message::ERROR);
	 *  
	 *	// Get error and alert messages
	 *  	$messages = Message::get(array(Message::ERROR, Message::ALERT));
	 *
	 *	// Customize the default value
	 *  	$error_messages = Message::get(Message::ERROR, 'No error messages found');
	 * 
	 * @param 	mixed 	message type (e.g. Message::SUCCESS, array(Message::ERROR, Message::ALERT))
	 * @param 	mixed 	default value to return
	 * @param 	bool 	delete the messages?
	 * @return 	mixed 	array or NULL
	 */
	public static function get($type = NULL, $default = NULL, $delete = FALSE)
	{
		// Get the messages
		$messages = Session::instance()->get(self::$session_key, array());
	
		if ($messages === NULL)
		{
			// No messages to return
			return $default;
		}
        
	        if ($type !== NULL)
	        {
	                // Will hold the filtered set of messages to return
	                $return = array();
	                
	                // Store the remainder in case delete or get_once is called
	                $remainder = array();
	        
	                foreach ($messages as $message)
	                {
	                        if (($message['type'] === $type) OR (is_array($type) AND in_array($message['type'], $type)) OR (is_array($type) AND Arr::is_assoc($type) AND !in_array($message['type'], $type[1])))
	                        {
	                                $return[] = $message;
	                        }
	                        else
	                        {
	                                $remainder[] = $message;
	                        }
	                }
                
	                // No messages of '$type' found
	                if (empty($return))
	                        return $default;
	                
	                $messages = $return;
	        }
	
		if ($delete === TRUE)
		{
			if ($type === NULL OR empty($remainder))
			{
				// Nothing to save, delete the key from memory
				self::clear();
			}
			else
			{
				// Override the messages with the remainder to simulate a deletion
				Session::instance()->set(self::$session_key, $remainder);
			}
		}

		return $messages;
	}

	/**
	 * Delete messages.
	 *
	 * 	Message::clear();
	 *
	 * 	// Delete error messages
	 * 	Message::clear(Message::ERROR);
	 *
	 * 	// Delete error and alert messages
	 * 	Message::clear(array(Message::ERROR, Message::ALERT));
	 *
	 * @param   mixed  message type (e.g. Message::SUCCESS, array(Message::ERROR, Message::ALERT))
	 * @return  void
	 */
	public static function clear($type = NULL)
	{
		if ($type === NULL)
		{
			// Delete everything!
			Session::instance()->delete(self::$session_key);
		}
		else
		{
			// Deletion by type happens in get(), too weird?
			self::get($type, NULL, TRUE);
		}
	}

	/**
	 * Displays the message
	 *
	 * @return   string   Message to string
	 */
	public static function display($type = NULL, $delete = TRUE, $view = NULL)
	{
		if (($messages = self::get($type, NULL, $delete)) === NULL)
		{
			// No messages
			return '';
		}
        
                if ($view === NULL)
		{
			// Use the default view
			$view = self::$default_view;
		}
	
		if ( ! $view instanceof Kohana_View)
		{
			// Load the view file
			$view = new View($view);
		}
	
		return $view->set('messages', $messages)->render();
	}
	
}