<?php
/**
 * MongoDB log writer
 *
 * ### System Requirements
 *
 * - MondoDB 2.4 or higher
 * - PHP-extension MongoDB 1.4.0 or higher
 *
 * @package    Gleez\Logging
 * @author     Gleez Team
 * @version    0.2.3
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Log_Mango extends Log_Writer {

	/**
	 * Collection for log
	 * Use [Capped Collection](http://docs.mongodb.org/manual/core/capped-collections/)
	 * to support high-bandwidth inserts
	 * @var string
	 */
	protected $_collection;

	/**
	 * Database instance name
	 * @var string
	 */
	protected $_name;

	/**
	 * MongoDB object
	 * @var MongoDB
	 */
	protected $_db;

	/**
	 * Class constructor
	 *
	 * Creates a new MongoDB logger using Gleez [Mango]
	 *
	 * Example:
	 * ~~~
	 * $writer = new Log_Mango($collection);
	 * ~~~
	 *
	 * @param   string  $collection  Collection Name [Optional]
	 * @param   string  $name        Database instance name [Optional]
	 *
	 * @throws  Mango_Exception
	 */
	public function __construct($collection = 'logs', $name = 'default')
	{
		$this->_collection  = $collection;
		$this->_name        = $name;

		// Getting Mango instance
		$this->_db = Mango::instance($this->_name);
	}

	/**
	 * Writes each of the messages into the MongoDB collection
	 *
	 * Example:
	 * ~~~
	 * $writer->write($messages);
	 * ~~~
	 *
	 * @param   array  $messages  An array of log messages
	 *
	 * @uses    Arr::merge
	 * @uses    Request::$client_ip
	 * @uses    Request::$user_agent
	 * @uses    Request::uri
	 * @uses    Request::initial
	 * @uses    Text::plain
	 * @uses    Log_Writer::$strace_level
	 */
	public function write(array $messages)
	{
		$logs      = array();
		$exception = NULL;

		foreach ($messages as $message)
		{
			$exception = isset($message['additional']['exception']) ? $message['additional']['exception'] : NULL;
			$message['level'] = $this->_log_levels[$message['level']];

			unset($message['additional'], $message['trace']);

			// FIX: $message should consist of an array of strings
			$message = array_filter($message, 'is_string');

			// See MongoDate::__toString
			$message['time'] = new MongoDate(strtotime($message['time']));

			if ($exception)
			{
				// Re-use as much as possible, just resetting the body to the trace
				$message['body']  = $exception->getTraceAsString();
				$message['level'] = $this->_log_levels[Log_Writer::$strace_level];
			}

			// Merging descriptive array and the current message
			$logs[] = $message;
		}

		// Write messages
		$this->_db->{$this->_collection}->batchInsert($logs);
	}
}