<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Gleez File Class
 *
 * @package    Gleez\SPL
 * @author     Sergey Yakovlev - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 *
 * @link       http://www.php.net/manual/en/class.splfileinfo.php SplFileInfo
 */
class Gleez_File extends SplFileInfo {

	/**
	 * File name
	 * @var string
	 */
	protected $_name;

	/**
	 * File path
	 * @var string
	 */
	protected $_path;

	/**
	 * Gleez_File instance
	 * @var Gleez_File|NULL
	 */
	public static $instance = NULL;

	/**
	 * Creates a singleton of a Gleez_File
	 *
	 * Example:<br>
	 * <code>
	 *   $file = File::instance();
	 * </code>
	 *
	 * Access an instantiated file directly:<br>
	 * <code>
	 *   $file = File::$instance;
	 * </code>
	 *
	 * @param   string  $file_name  File name
	 * @return  Gleez_File
	 * @throws  Gleez_Exception
	 */
	public static function instance($file_name)
	{
		if (is_null($file_name))
		{
			throw new Gleez_Exception('The file name must be specified', 500);
		}

		if (is_null(File::$instance))
		{
			new File($file_name);
		}

		// If initiated already return the current Gleez_File instance
		return File::$instance;
	}

	/**
	 * Class constructor
	 *
	 * @param  string  $file_name  File name
	 * @link   http://php.net/manual/en/splfileinfo.construct.php  SplFileInfo::__construct
	 */
	public function __construct($file_name)
	{
		// Construct a new SplFileInfo object
		$spl = new SplFileInfo($file_name);

		$this->_name = $spl->getBasename();
		$this->_path = $spl->getPath();

		File::$instance = $this;
	}

	/**
	 * Class destructor
	 */
	final public function __destruct()
	{
		try
		{
			Gleez_File::$instance = NULL;
		}
		catch(Exception $e)
		{
			// can't throw exceptions in __destruct
		}
	}

	/**
	 * Returns the path to the file as a string
	 *
	 * @return  NULL|string
	 */
	final public function __toString()
	{
		return is_null(File::$instance) ? NULL : '';
	}

	/**
	 * Gets or sets the filename
	 *
	 * @param   string  $name  File name [Optional]
	 * @return  string
	 */
	public function name($name = NULL)
	{
		if ( ! is_null($name))
		{
			$this->_path = $name;
		}

		return $this->_name;
	}

	/**
	 * Gets or sets the path without filename
	 *
	 * @param   string  $path  File path [Optional]
	 * @return  string  File path
	 */
	public function path($path = NULL)
	{
		if ( ! is_null($path))
		{
			$this->_path = $path;
		}

		return $this->_path;
	}

	/**
	 * Gets full file name
	 *
	 * @return string
	 */
	public function get_full_name()
	{
		return $this->_path . DIRECTORY_SEPARATOR . $this->_name;
	}
}