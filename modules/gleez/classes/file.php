<?php
/**
 * Gleez File Class
 *
 * @package    Gleez\Helpers
 * @author     Gleez Team
 * @version    1.1.2
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class File extends SplFileInfo {

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
	 * File instance
	 * @var File|NULL
	 */
	public static $instance = NULL;

	/**
	 * Creates a singleton of a File
	 *
	 * Example:
	 * ~~~
	 * $file = File::instance();
	 *
	 * // Access an instantiated file directly:
	 * $file = File::$instance;
	 * ~~~
	 *
	 * @param   string  $file_name  File name
	 *
	 * @return  File
	 *
	 * @throws  Gleez_Exception
	 */
	public static function instance($file_name)
	{
		if (is_null($file_name))
		{
			throw new Gleez_Exception('The file name must be specified', 500);
		}

		if (is_null(self::$instance))
		{
			new File($file_name);
		}

		// If initiated already return the current File instance
		return self::$instance;
	}

	/**
	 * Class constructor
	 *
	 * @param  string  $file_name  File name
	 */
	public function __construct($file_name)
	{
		// Construct a new SplFileInfo object
		$spl = new SplFileInfo($file_name);

		$this->_name = $spl->getBasename();
		$this->_path = $spl->getPath();

		self::$instance = $this;
	}

	/**
	 * Class destructor
	 */
	final public function __destruct()
	{
		try
		{
			self::$instance = NULL;
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
		return is_null(self::$instance) ? NULL : $this->_name;
	}

	/**
	 * Gets or sets the filename
	 *
	 * @param   string  $name  File name [Optional]
	 *
	 * @return  string
	 */
	public function name($name = NULL)
	{
		if ( ! is_null($name))
		{
			$this->_name = $name;
		}

		return $this->_name;
	}

	/**
	 * Gets or sets the path without filename
	 *
	 * @param   string  $path  File path [Optional]
	 *
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
		return $this->_path . DS . $this->_name;
	}

	/**
	 * Attempt to get the mime type from a file
	 *
	 * This method is horribly unreliable, due to PHP being horribly unreliable
	 * when it comes to determining the mime type of a file.
	 *
	 * Example:
	 * ~~~
	 * $mime = File::mime($file);
	 * ~~~
	 *
	 * @param   string  $filename  File name or path
	 *
	 * @return  string  Mime type on success
	 * @return  boolean FALSE on failure
	 */
	public static function mime($filename)
	{
		// Get the complete path to the file
		$filename = realpath($filename);

		// Get the extension from the filename
		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

		if (preg_match('/^(?:jpe?g|png|[gt]if|bmp|swf)$/', $extension))
		{
			// Use getimagesize() to find the mime type on images
			$file = getimagesize($filename);

			if (isset($file['mime']))
				return $file['mime'];
		}

		if (class_exists('finfo', FALSE))
		{
			if ($info = new finfo(defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME))
			{
				return $info->file($filename);
			}
		}

		if (ini_get('mime_magic.magicfile') AND function_exists('mime_content_type'))
		{
			// The mime_content_type function is only useful with a magic file
			return mime_content_type($filename);
		}

		if ( ! empty($extension))
		{
			return self::mime_by_ext($extension);
		}

		// Unable to find the mime-type
		return FALSE;
	}

	/**
	 * Return the mime type of an extension
	 *
	 * Example:
	 * ~~~
	 * $mime = File::mime_by_ext('png'); // "image/png"
	 * ~~~
	 *
	 * @param   string  $extension  php, pdf, txt, etc
	 *
	 * @return  string  mime type on success
	 * @return  boolean FALSE on failure
	 */
	public static function mime_by_ext($extension)
	{
		// Load all of the mime types
		$mimes = Config::load('mimes')->as_array();

		return isset($mimes[$extension]) ? $mimes[$extension][0] : FALSE;
	}

	/**
	 * Lookup MIME types for a file
	 *
	 * @param   string $extension  Extension to lookup
	 *
	 * @return  array  Array of MIMEs associated with the specified extension
	 */
	public static function mimes_by_ext($extension)
	{
		// Load all of the mime types
		$mimes = Config::load('mimes')->as_array();

		return isset($mimes[$extension]) ? ( (array) $mimes[$extension]) : array();
	}

	/**
	 * Lookup file extensions by MIME type
	 *
	 * @param   string  $type File MIME type
	 *
	 * @return  array   File extensions matching MIME type
	 */
	public static function exts_by_mime($type)
	{
		static $types = array();

		// Fill the static array
		if (empty($types))
		{
			foreach (Config::load('mimes')->as_array() as $ext => $mimes)
			{
				foreach ($mimes as $mime)
				{
					if ($mime == 'application/octet-stream')
					{
						// octet-stream is a generic binary
						continue;
					}

					if ( ! isset($types[$mime]))
					{
						$types[$mime] = array( (string) $ext);
					}
					elseif ( ! in_array($ext, $types[$mime]))
					{
						$types[$mime][] = (string) $ext;
					}
				}
			}
		}

		return isset($types[$type]) ? $types[$type] : FALSE;
	}

	/**
	 * Lookup a single file extension by MIME type
	 *
	 * @param   string  $type  MIME type to lookup
	 *
	 * @return  mixed          First file extension matching or false
	 */
	public static function ext_by_mime($type)
	{
		return current(self::exts_by_mime($type));
	}

	/**
	 * Split a file into pieces matching a specific size
	 *
	 * Used when you need to split large files into smaller pieces for easy transmission.
	 *
	 * Example:
	 * ~~~
	 * $count = File::split($file);
	 * ~~~
	 *
	 * @param   string   $filename    File to be split
	 * @param   integer  $piece_size  Size, in MB, for each piece to be [Optional]
	 * @return  integer  The number of pieces that were created
	 */
	public static function split($filename, $piece_size = 10)
	{
		// Open the input file
		$file = fopen($filename, 'rb');

		// Change the piece size to bytes
		$piece_size = floor($piece_size * 1024 * 1024);

		// Write files in 8k blocks
		$block_size = 1024 * 8;

		// Total number of pieces
		$pieces = 0;

		while ( ! feof($file))
		{
			// Create another piece
			$pieces += 1;

			// Create a new file piece
			$piece = str_pad($pieces, 3, '0', STR_PAD_LEFT);
			$piece = fopen($filename.'.'.$piece, 'wb+');

			// Number of bytes read
			$read = 0;

			do
			{
				// Transfer the data in blocks
				fwrite($piece, fread($file, $block_size));

				// Another block has been read
				$read += $block_size;
			}
			while ($read < $piece_size);

			// Close the piece
			fclose($piece);
		}

		// Close the file
		fclose($file);

		return $pieces;
	}

	/**
	 * Join a split file into a whole file
	 *
	 * Does the reverse of [File::split].
	 *
	 * Example:
	 * ~~~
	 * $count = File::join($file);
	 * ~~~
	 *
	 * @param   string  $filename   Split filename, without .000 extension
	 *
	 * @return  integer The number of pieces that were joined.
	 */
	public static function join($filename)
	{
		// Open the file
		$file = fopen($filename, 'wb+');

		// Read files in 8k blocks
		$block_size = 1024 * 8;

		// Total number of pieces
		$pieces = 0;

		while (is_file($piece = $filename.'.'.str_pad($pieces + 1, 3, '0', STR_PAD_LEFT)))
		{
			// Read another piece
			$pieces += 1;

			// Open the piece for reading
			$piece = fopen($piece, 'rb');

			while ( ! feof($piece))
			{
				// Transfer the data in blocks
				fwrite($file, fread($piece, $block_size));
			}

			// Close the pieces
			fclose($piece);
		}

		return $pieces;
	}

	/**
	 * Generate a unique filename to avoid conflicts
	 *
	 * @since   1.0.1
	 *
	 * @param   string   $name           Filename [Optional]
	 * @param   integer  $length         Length of filename to return [Optional]
	 * @param   boolean  $remove_spaces  Remove spaces from file name [Optional]
	 * @param   string   $replacement    Replacement for spaces [Optional]
	 *
	 * @return  string
	 *
	 * @uses    Text::random
	 * @uses    UTF8::strtolower
	 */
	public static function getUnique($name = NULL, $length = 20, $remove_spaces = TRUE, $replacement = '_')
	{
		if (is_null($name))
		{
			return UTF8::strtolower(uniqid().Text::random('alnum', (int)$length));
		}
		else
		{
			$retval = uniqid().($remove_spaces ? preg_replace('/\s+/u', $replacement, $name) : $name);
			$retval = is_null($length) ? $retval : substr($retval, (int)$length);

			return $retval;
		}
	}

	/**
	 * Get file extension from it name
	 *
	 * @since   1.1.0
	 *
	 * @param   string  $file  Filename
	 *
	 * @return  string
	 */
	public static function getExt($file)
	{
		return pathinfo($file, PATHINFO_EXTENSION);
	}
}
