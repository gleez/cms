<?php
/**
 * Message is a class that lets you easily send messages
 * in your application (aka Flash Messages)
 *
 * @package    Gleez\Message
 * @author     Gleez Team
 * @version    1.0.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license	   http://gleezcms.org/license  Gleez CMS License
 */
class Message {

	// Constants to use for the types of messages that can be set.
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
	 * Default session key used for storing messages
	 * @var string
	 */
	public static $session_key = 'messages';

	/**
	 * Default view
	 * @var string
	 */
	public static $default_view = 'message/basic';

	/**
	 * Adds a new message.
	 *
	 * @param   string  $type     Message type (e.g. Message::SUCCESS)
	 * @param   string  $message  Array/String for the message(s)
	 * @param   array   $options  Any options for the message [Optional]
	 * @return  void
	 */
	public static function set($type, $message, array $options = NULL)
	{
		// Load existing messages
		$messages = (array) self::get();

		// initialize if necessary
		if ( ! is_array($messages))
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
	 * @param	mixed	$message  String/Array for the message(s)
	 * @param   array   $options  Any options for the message [Optional]
	 */
	public static function error($message, array $options = NULL)
	{
		self::set(self::ERROR, $message, $options);
	}

	/**
	 * Sets a ALERT message.
	 *
	 * @param	mixed	$message  String/Array for the message(s)
	 * @param   array   $options  Any options for the message [Optional]
	 */
	public static function alert($message, array $options = NULL)
	{
		self::set(self::ALERT, $message, $options);
	}

	/**
	 * Sets a CRITICAL message.
	 *
	 * @param	mixed	$message  String/Array for the message(s)
	 * @param   array   $options  Any options for the message [Optional]
	 */
	public static function critical($message, array $options = NULL)
	{
		self::set(self::CRITICAL, $message, $options);
	}

	/**
	 * Sets a notice.
	 *
	 * @param	mixed	$message  String/Array for the message(s)
	 * @param   array   $options  Any options for the message [Optional]
	 */
	public static function notice($message, array $options = NULL)
	{
		self::set(self::NOTICE, $message, $options);
	}

	/**
	 * Sets a success message.
	 *
	 * @param	mixed	$message  String/Array for the message(s)
	 * @param   array   $options  Any options for the message [Optional]
	 */
	public static function success($message, array $options = NULL)
	{
		self::set(self::SUCCESS, $message, $options);
	}

	/**
	 * Sets a warning message.
	 *
	 * @param	mixed	$message  String/Array for the message(s)
	 * @param   array   $options  Any options for the message [Optional]
	 */
	public static function warn($message, array $options = NULL)
	{
		self::set(self::WARN, $message, $options);
	}

	/**
	 * Sets a info message.
	 *
	 * @param	mixed	$message  String/Array for the message(s)
	 * @param   array   $options  Any options for the message [Optional]
	 */
	public static function info($message, array $options = NULL)
	{
		self::set(self::INFO, $message, $options);
	}

	/**
	 * Sets a ACCESS message.
	 *
	 * @param	mixed	$message  String/Array for the message(s)
	 * @param   array   $options  Any options for the message [Optional]
	 */
	public static function access($message, array $options = NULL)
	{
		self::set(self::ACCESS, $message, $options);
	}

	/**
	 * Sets a debug message, not in production stage.
	 *
	 * @param	mixed	$message  String/Array for the message(s)
	 * @param   array   $options  Any options for the message [Optional]
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
	 * @param 	mixed 	$type     Message type (e.g. Message::SUCCESS, array(Message::ERROR, Message::ALERT)) [Optional]
	 * @param 	bool 	$delete   Delete the messages? [Optional]
	 * @param 	mixed 	$view     View filename or View object [Optional]
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
	 * Example:
	 * ~~~
	 * $messages = Message::get();
	 *
	 * //Get error messages only
	 * $error_messages = Message::get(Message::ERROR);
	 *
	 * // Get error and alert messages
	 * $messages = Message::get(array(Message::ERROR, Message::ALERT));
	 *
	 * // Customize the default value
	 * $error_messages = Message::get(Message::ERROR, 'No error messages found');
	 * ~~~
	 *
	 * @param 	mixed 	$type     Message type (e.g. Message::SUCCESS, array(Message::ERROR, Message::ALERT))
	 * @param 	mixed 	$default  Default value to return [Optional]
	 * @param 	bool 	$delete   Delete the messages?
	 *
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
	 * Delete messages
	 *
	 * Example:
	 * ~~~
	 * Message::clear();
	 *
	 * // Delete error messages
	 * Message::clear(Message::ERROR);
	 *
	 * // Delete error and alert messages
	 * Message::clear(array(Message::ERROR, Message::ALERT));
	 * ~~~
	 *
	 * @param   mixed  message type (e.g. Message::SUCCESS, array(Message::ERROR, Message::ALERT))
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
	 * @param 	mixed 	$type     Message type (e.g. Message::SUCCESS, array(Message::ERROR, Message::ALERT)) [Optional]
	 * @param 	bool 	$delete   Delete the messages? [Optional]
	 * @param 	mixed 	$view     View filename or View object [Optional]
	 *
	 * @return   string   Message to string
	 */
	public static function display($type = NULL, $delete = TRUE, $view = NULL)
	{
		$messages = self::get($type, NULL, $delete);

		if (empty($messages))
		{
			// No messages
			return '';
		}

		if (is_null($view))
		{
			// Use the default view
			$view = self::$default_view;
		}

		if ( ! $view instanceof View)
		{
			// Load the view file
			$view = new View($view);
		}

		return $view->set('messages', $messages)->render();
	}

}