<?php
/**
 * File log writer
 *
 * Writes out messages and stores them in a YYYY/MM directory.
 *
 * @package    Gleez\Logging
 * @author     Sandeep Sangamreddi - Gleez
 * @version    1.0.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Log_File extends Log_Writer {

	/**
	 * Directory to place log files in
	 * @var  string
	 */
	protected $_directory;

	/**
	 * Default format
	 * @var string
	 */
	public static $format_string = 'time - - level: body - - hostname - - url - - user_agent - - referer';

	/**
	 * Class constructor
	 *
	 * Creates a new file logger
	 *
	 * Example:<br>
	 * <code>
	 *   $writer = new Log_File($directory);
	 * </code>
	 *
	 * @param   string  $directory  Log directory
	 */
	public function __construct($directory)
	{
		$this->_checkDir($directory);

		// Determine the directory path
		$this->_directory = realpath($directory).DS;
	}

	/**
	 * Writes each of the messages into the log file
	 *
	 * The log file will be appended to the `YYYY/MM/DD.log.php` file,
	 * where YYYY is the current year, MM is the current month,
	 * and DD is the current day.
	 *
	 * Example:<br>
	 * <code>
	 *   $writer->write($messages);
	 * </code>
	 *
	 * @param   array  $messages  Log messages
	 *
	 * @uses    Arr::merge
	 * @uses    Request::$client_ip
	 * @uses    Request::$user_agent
	 * @uses    Request::uri
	 * @uses    Request::initial
	 * @uses    Text::plain
	 */
	public function write(array $messages)
	{
		// Set the yearly directory name
		$directory = $this->_directory.date('Y');

		// Add the month to the directory
		$directory .= DS.date('m');

		$this->_checkDir($directory);

		// Set the name of the log file
		$filename = $directory.DS.date('d').EXT;

		if ( ! file_exists($filename))
		{
			// Create the log file
			file_put_contents($filename, PHP_EOL);

			// Allow anyone to write to log files
			chmod($filename, 0666);
		}

		foreach ($messages as $message)
		{
			// Write each message into the log file
			file_put_contents($filename, PHP_EOL.$this->format_message($message, Log_File::$format_string), FILE_APPEND);
		}

	}

	/**
	 * Check that the directory exists and is writable
	 *
	 * @since   1.0.1
	 *
	 * @param   $directory
	 *
	 * @uses    System::mkdir
	 * @uses    Debug::path
	 *
	 * @throws  Gleez_Exception
	 */
	protected function _checkDir($directory)
	{
		if ( ! is_dir($directory))
		{
			try
			{
				// Create the yearly directory
				System::mkdir($directory);
			}
			catch (Exception $e)
			{
				throw new Gleez_Exception('Could not create log directory :dir',
					array(':dir' => Debug::path($directory)));
			}
		}
		if ( ! is_writable($directory))
		{
			throw new Gleez_Exception('Directory :dir must be writable',
				array(':dir' => Debug::path($directory)));
		}
	}

}
