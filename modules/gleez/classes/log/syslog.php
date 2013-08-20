<?php
/**
 * Syslog log writer
 *
 * Writes out messages to syslog.
 *
 * @package    Gleez\Logging
 * @author     Sergey Yakovlev - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Log_Syslog extends Log_Writer {

	/**
	 * The syslog identifier
	 * @var string
	 */
	protected $_ident;

	/**
	 * Default format
	 * @var string
	 */
	public static $format_string = 'body';

	/**
	 * Class constructor
	 *
	 * - Creates a new syslog logger
	 *
	 * @param  string   $ident     Syslog identifier [Optional]
	 * @param  integer  $facility  Facility to log to [Optional]
	 *
	 * @link   http://www.php.net/manual/function.openlog openlog()
	 */
	public function __construct($ident = 'Gleez CMS', $facility = LOG_USER)
	{
		$this->_ident = $ident;

		// Open the connection to syslog
		openlog($this->_ident, LOG_CONS, $facility);
	}

	public function __destruct()
	{
		// Close connection to syslog
		closelog();
	}

	public function write(array $messages)
	{
		foreach ($messages as $message)
		{
			syslog($message['level'], $message['body']);
		}
	}
}