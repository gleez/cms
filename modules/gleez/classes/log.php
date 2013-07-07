<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Message logging with observer-based log writing.
 *
 * [!!] This class does not support extensions, only additional writers.
 * [!!] __NOTE__: For log messages levels Windows users see PHP Bug #18090
 *
 * @package    Gleez\Logging
 * @author     Kohana Team
 * @author     Sergey Yakovlev - Gleez
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2008-2012 Kohana Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 * @license    http://kohanaframework.org/license
 */
class Log {

	/* System is unusable. 0 */
	const EMERGENCY = LOG_EMERG;

	/* Action must be taken immediately. 1 */
	const ALERT = LOG_ALERT;

	/* Critical conditions. 2 */
	const CRITICAL = LOG_CRIT;

	/* Error conditions. 3 */
	const ERROR = LOG_ERR;

	/* Warning conditions. 4 */
	const WARNING = LOG_WARNING;

	/* Normal, but significant, condition. 5 */
	const NOTICE = LOG_NOTICE;

	/* Informational message. 6 */
	const INFO = LOG_INFO;

	/* Debug-level message. 7 */
	const DEBUG = LOG_DEBUG;

	/**
	 * Immediately write when logs are added
	 * @var boolean
	 */
	public static $write_on_add = FALSE;

	/**
	 * Singleton instance container
	 * @var Log
	 */
	protected static $_instance;

	/**
	 * List of added messages
	 * @var array
	 */
	protected $_messages = array();

	/**
	 * List of log writers
	 * @var array
	 */
	protected $_writers = array();

	/**
	 * Get the singleton instance of this class and enable writing at shutdown
	 *
	 * Usage:<br>
	 * <code>
	 *   $log = Log::instance();
	 * </code>
	 *
	 * @return  Log
	 */
	public static function instance()
	{
		if (is_null(Log::$_instance))
		{
			// Create a new instance
			Log::$_instance = new Log;

			// Write the logs at shutdown
			register_shutdown_function(array(Log::$_instance, 'write'));
		}

		return Log::$_instance;
	}

	/**
	 * Attaches a log writer
	 *
	 * Optionally limits the levels of messages that will be written by the writer.
	 *
	 * Example:<br>
	 * <code>
	 *   $log->attach($writer);
	 * </code>
	 *
	 * @param   Log_Writer  $writer     Instance
	 * @param   array       $levels     Array of messages levels to write OR max level to write [Optional]
	 * @param   integer     $min_level  Min level to write if `$levels` is not an array [Optional]
	 * @return  Log
	 */
	public function attach(Log_Writer $writer, $levels = array(), $min_level = 0)
	{
		if ( ! is_array($levels))
		{
			$levels = range($min_level, $levels);
		}

		$this->_writers["{$writer}"] = array(
			'object' => $writer,
			'levels' => $levels
		);

		return $this;
	}

	/**
	 * Detaches a log writer
	 *
	 * The same writer object must be used.
	 *
	 * Example:<br>
	 * <code>
	 *   $log->detach($writer);
	 * </code>
	 *
	 * @param   Log_Writer  $writer  Instance
	 * @return  Log
	 */
	public function detach(Log_Writer $writer)
	{
		// Remove the writer
		unset($this->_writers["{$writer}"]);

		return $this;
	}

	/**
	 * Adds a message to the log
	 *
	 * Replacement values must be passed in to be
	 * replaced using [strtr](http://php.net/strtr).
	 *
	 * Usage:<br>
	 * <code>
	 *   $log->add(Log::ERROR, 'Could not locate user: :user', array(':user' => $user->name));
	 * </code>
	 *
	 * @param   string  $level       Level of message
	 * @param   string  $message     Message body
	 * @param   array   $values      Values to replace in the message [Optional]
	 * @param   array   $additional  Additional custom parameters to supply to the log writer [Optional]
	 * @return  Log
	 *
	 * @uses    Date::formatted_time
	 */
	public function add($level, $message, array $values = NULL, array $additional = NULL)
	{
		if ($values)
		{
			// Insert the values into the message
			$message = strtr($message, $values);
		}

		if (isset($additional['exception']))
		{
			$trace = $additional['exception']->getTrace();
		}
		else
		{
			// Older PHP version don't have 'DEBUG_BACKTRACE_IGNORE_ARGS',
			// so manually remove the args from the backtrace
			if ( ! defined('DEBUG_BACKTRACE_IGNORE_ARGS'))
			{
				$trace = array_map(function ($item) {
					unset($item['args']);
					return $item;
				}, array_slice(debug_backtrace(FALSE), 1));
			}
			else
			{
				$trace = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 1);
			}
		}

		is_null($additional) OR ($additional = array());

		$request = Request::current();
		$uri = '';
		
		if($request instanceof Request)
		{
			$uri = Request::initial()->uri();
		}
		elseif(!Kohana::$is_cli)
		{
			$uri = Request::detect_uri();
		}
		
		// Create a new message and timestamp it
		$this->_messages[] = array
		(
			'time'       => Date::formatted_time('now', Date::$timestamp_format, Date::$timezone),
			'level'      => $level,
			'body'       => $message,
			'trace'      => $trace,
			'file'       => isset($trace[0]['file']) ? $trace[0]['file'] : NULL,
			'line'       => isset($trace[0]['line']) ? $trace[0]['line'] : NULL,
			'class'      => isset($trace[0]['class']) ? $trace[0]['class'] : NULL,
			'function'   => isset($trace[0]['function']) ? $trace[0]['function'] : NULL,
			'additional' => $additional,
			'hostname'   => Request::$client_ip,
			'user_agent' => Request::$user_agent,
			'referer'    => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
			'url'        => Text::plain($uri),
		);

		if (Log::$write_on_add)
		{
			// Write logs as they are added
			$this->write();
		}

		return $this;
	}

	/**
	 * Write and clear all of the messages
	 *
	 * Example:<br>
	 * <code>
	 *   $log->write();
	 * </code>
	 */
	public function write()
	{
		if (empty($this->_messages))
		{
			// There is nothing to write, move along
			return;
		}

		// Import all messages locally
		$messages = $this->_messages;

		// Reset the messages array
		$this->_messages = array();

		foreach ($this->_writers as $writer)
		{
			if (empty($writer['levels']))
			{
				// Write all of the messages
				$writer['object']->write($messages);
			}
			else
			{
				// Filtered messages
				$filtered = array();

				foreach ($messages as $message)
				{
					if (in_array($message['level'], $writer['levels']))
					{
						// Writer accepts this kind of message
						$filtered[] = $message;
					}
				}

				// Write the filtered messages
				$writer['object']->write($filtered);
			}
		}
	}

}