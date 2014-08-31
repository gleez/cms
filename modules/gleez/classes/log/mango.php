<?php
/**
 * Gleez CMS (http://gleezcms.org)
 *
 * @link https://github.com/cleez/cms Canonical source repository
 * @copyright Copyright (c) 2011-2014 Gleez Technologies
 * @license http://gleezcms.org/license Gleez CMS License
 */

use \Gleez\Mango\Client;

/**
 * MongoDB log writer
 *
 * @package Gleez\Logging
 * @author  Gleez Team
 * @version 0.2.5
 */
class Log_Mango extends Log_Writer {

	/**
	 * Collection for log
	 * @var string
	 */
	private $collection;

	/**
	 * Database instance name
	 * @var string
	 */
	private $name;

	/**
	 * MongoDB object
	 * @var MongoDB
	 */
	private $db;

	/**
	 * Class constructor
	 *
	 * Creates a new MongoDB logger using [\Gleez\Mango\Client]
	 *
	 * Example:<br>
	 * <code>
	 * $writer = new Log_Mango($collection);
	 * </code>
	 *
	 * @param   string  $collection  Collection name [Optional]
	 * @param   string  $name        Database instance name [Optional]
	 *
	 * @throws  \Gleez\Mango\Exception
	 */
	public function __construct($collection = 'logs', $name = 'default')
	{
		$this->collection = $collection;
		$this->name       = $name;

		// Getting Mango instance
		$this->db = Client::instance($this->name);
	}

	/**
	 * Writes each of the messages into the MongoDB collection
	 *
	 * Example:<br>
	 * <code>
	 * $writer->write($messages);
	 * </code>
	 *
	 * @param   array  $messages  An array of log messages
	 *
	 * @uses    Log_Writer::_log_levels
	 * @uses    \Gleez\Mango\Client::selectCollection
	 * @uses    \Gleez\Mango\Collection::batchInsert
	 */
	public function write(array $messages)
	{
		$logs      = array();
		$exception = null;

		foreach ($messages as $message) {
			$exception = isset($message['additional']['exception']) ? $message['additional']['exception'] : null;
			$message['level'] = $this->_log_levels[$message['level']];

			unset($message['additional'], $message['trace']);

			// FIX: $message should consist of an array of strings
			$message = array_filter($message, 'is_string');

			$message['time'] = new MongoDate(strtotime($message['time']));

			if ($exception && method_exists($exception, 'getTraceAsString')) {
				// Re-use as much as possible, just resetting the body to the trace
				$message['body']  = $exception->getTraceAsString();
				$message['level'] = $this->_log_levels[Log_Writer::$strace_level];
			}

			// Merging descriptive array and the current message
			$logs[] = $message;
		}

		// Write messages
		$this->db->{$this->collection}->batchInsert($logs);
	}
}
