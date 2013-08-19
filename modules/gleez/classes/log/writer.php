<?php
/**
 * Log writer abstract class
 *
 * All [Log] writers must extend this class.
 *
 * @package    Gleez\Logging
 * @author     Sergey Yakovlev - Gleez
 * @author     Kohana Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://gleezcms.org/license  Gleez CMS License
 * @license    http://kohanaframework.org/license
 */
abstract class Log_Writer {

	/**
	 * Timestamp format for log entries.
	 * Defaults to [Date::$timestamp_format]
	 * @var string
	 */
	public static $timestamp;

	/**
	 * Timezone for log entries.
	 * Defaults to [Date::$timezone], which defaults to date_default_timezone_get()
	 * @var string
	 */
	public static $timezone;

	/**
	 * Numeric log level to string lookup table
	 * @var array
	 */
	protected $_log_levels = array(
		LOG_EMERG   => 'EMERGENCY',
		LOG_ALERT   => 'ALERT',
		LOG_CRIT    => 'CRITICAL',
		LOG_ERR     => 'ERROR',
		LOG_WARNING => 'WARNING',
		LOG_NOTICE  => 'NOTICE',
		LOG_INFO    => 'INFO',
		LOG_DEBUG   => 'DEBUG',
	);

	/**
	 * Level to use for stack traces
	 * @var integer
	 */
	public static $strace_level = LOG_DEBUG;

	/**
	 * Default format
	 * @var string
	 */
	public static $format_string = 'time --- level: body in file:line';

	/**
	 * Write an array of messages
	 *
	 * Example:
	 * ~~~
	 * $writer->write($messages);
	 * ~~~
	 *
	 * @param  array  $messages  Array of messages
	 */
	abstract public function write(array $messages);

	/**
	 * Allows the writer to have a unique key when stored
	 *
	 * Example:
	 * ~~~
	 * echo $writer;
	 * ~~~
	 *
	 * @return  string  Returns the hash of the unique identifier for the object
	 *
	 * @link    http://php.net/manual/en/function.spl-object-hash.php spl_object_hash()
	 */
	final public function __toString()
	{
		return spl_object_hash($this);
	}

	/**
	 * Formats a log entry
	 *
	 * @param   array   $message  Message
	 * @param   string  $format   Message format [Optional]
	 * @return  string
	 *
	 * @uses    Date::formatted_time
	 */
	public function format_message(array $message, $format = NULL)
	{
		if (is_null($format))
		{
			$format = Log_Writer::$format_string;
		}

		$exception = isset($message['additional']['exception']) ? $message['additional']['exception'] : NULL;
		$message['time']  = Date::formatted_time($message['time'], Log_Writer::$timestamp, Log_Writer::$timezone);
		$message['level'] = $this->_log_levels[$message['level']];

		unset($message['additional'], $message['trace']);

		// FIX: $message should consist of an array of strings
		$message = array_filter($message, 'is_string');

		$string = strtr($format,$message);

		if ($exception)
		{
			// Re-use as much as possible, just resetting the body to the trace
			$message['body'] = $exception->getTraceAsString();
			$message['level'] = $this->_log_levels[Log_Writer::$strace_level];

			$string .= PHP_EOL.strtr($format, $message);
		}

		return $string;
	}
}