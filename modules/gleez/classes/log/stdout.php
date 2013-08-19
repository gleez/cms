<?php
/**
 * STDOUT log writer
 *
 * Writes out messages to STDOUT.
 *
 * @package    Gleez\Logging
 * @author     Sergey Yakovlev - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Log_StdOut extends Log_Writer {

	/**
	 * Default format
	 * @var string
	 */
	public static $format_string = 'time --- type: body';

	/**
	 * Writes each of the messages to STDOUT
	 *
	 * Example:<br>
	 * <code>
	 *   $writer->write($messages);
	 * </code>
	 *
	 * @param  array  $messages  Log messages
	 */
	public function write(array $messages)
	{
		foreach ($messages as $message)
		{
			// Binary-safe writes out each message to the file stream
			fwrite(STDOUT, PHP_EOL.$this->format_message($message, Log_StdOut::$format_string));
		}
	}
}