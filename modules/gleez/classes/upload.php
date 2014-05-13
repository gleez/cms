<?php
/**
 * Upload helper class for working with uploaded files and [Validation].
 *
 * Example:
 * ~~~
 * $array = Validation::factory($_FILES);
 * ~~~
 *
 * [!!] Note: Remember to define your form with "enctype=multipart/form-data"
 *      or file uploading will not work!
 *
 * The following configuration properties can be set:
 *
 * - [Upload::$remove_spaces]
 * - [Upload::$default_directory]
 *
 * @package    Gleez\Helpers
 * @author     Gleez Team
 * @version    1.2.1
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Upload {

	/**
	 * Remove spaces in uploaded files
	 * @var boolean
	 */
	public static $remove_spaces = TRUE;

	/**
	 * Default upload directory
	 * @var string
	 */
	public static $default_directory = 'upload';

	/**
	 * Save an uploaded file to a new location
	 *
	 * If no filename is provided, the original filename will be used,
	 * with a unique prefix added.
	 *
	 * This method should be used after validating the $_FILES array:
	 * ~~~
	 * if ($array->check())
	 * {
	 *     // Upload is valid, save it
	 *     Upload::save($array['file']);
	 * }
	 * ~~~
	 *
	 * @param   array    $file       Uploaded file data
	 * @param   string   $filename   New filename [Optional]
	 * @param   string   $directory  New directory [Optional]
	 * @param   integer  $chmod      Chmod mask [Optional]
	 *
	 * @return  string   On success, full path to new file
	 * @return  boolean  FALSE on failure
	 *
	 * @throws  Gleez_Exception
	 *
	 * @uses    File::getUnique
	 * @uses    System::mkdir
	 * @uses    Debug::path
	 */
	public static function save(array $file, $filename = NULL, $directory = NULL, $chmod = 0644)
	{
		if ( ! isset($file['tmp_name']) OR ! is_uploaded_file($file['tmp_name']))
		{
			// Ignore corrupted uploads
			return FALSE;
		}

		if (is_null($filename))
		{
			// Generate a unique filename
			$filename = File::getUnique($file['name'], NULL, Upload::$remove_spaces);
		}

		if (is_null($directory))
		{
			// Use the pre-configured upload directory
			$directory = Upload::$default_directory;
		}

		if ( ! is_dir($directory))
		{
			try
			{
				System::mkdir($directory, 0777, TRUE);
			}
			catch (Exception $e)
			{
				throw new Gleez_Exception('Could not create directory :dir',
					array(':dir' => Debug::path($directory)));
			}
		}

		if ( ! is_writable(realpath($directory)))
		{
			throw new Gleez_Exception('Directory :dir must be writable',
				array(':dir' => Debug::path($directory)));
		}

		// Make the filename into a complete path
		$filename = realpath($directory).DS.$filename;

		if (move_uploaded_file($file['tmp_name'], $filename))
		{
			if ($chmod !== FALSE)
			{
				// Set permissions on filename
				chmod($filename, $chmod);
			}

			// Return new file path
			return $filename;
		}

		return FALSE;
	}

	/**
	 * Tests if upload data is valid, even if no file was uploaded
	 *
	 * If you _do_ require a file to be uploaded, add the [Upload::not_empty]
	 * rule before this rule.
	 *
	 * Example:
	 * ~~~
	 * $array->rule('file', 'Upload::valid')
	 * ~~~
	 *
	 * @param   array  $file  $_FILES item
	 * @return  boolean
	 */
	public static function valid($file)
	{
		return (isset($file['error'])
			AND isset($file['name'])
			AND isset($file['type'])
			AND isset($file['tmp_name'])
			AND isset($file['size']));
	}

	/**
	 * Tests if a successful upload has been made
	 *
	 * Example:
	 * ~~~
	 * $array->rule('file', 'Upload::not_empty');
	 * ~~~
	 *
	 * @param   array  $file  $_FILES item
	 * @return  boolean
	 */
	public static function not_empty(array $file)
	{
		return (isset($file['error'])
			AND isset($file['tmp_name'])
			AND $file['error'] === UPLOAD_ERR_OK
			AND is_uploaded_file($file['tmp_name']));
	}

	/**
	 * Test if an uploaded file is an allowed file type, by extension
	 *
	 * Example:
	 * ~~~
	 * $array->rule('file', 'Upload::type', array(':value', array('jpg', 'png', 'gif')));
	 * ~~~
	 *
	 * @param   array  $file     $_FILES item
	 * @param   array  $allowed  Allowed file extensions
	 *
	 * @return  boolean
	 *
	 * @uses    File::getExt
	 */
	public static function type(array $file, array $allowed)
	{
		if ($file['error'] !== UPLOAD_ERR_OK)
		{
			return TRUE;
		}

		$ext = File::getExt($file['name']);

		return in_array($ext, $allowed);
	}

	/**
	 * Validation rule to test if an uploaded file is allowed by file size
	 *
	 * File sizes are defined as: SB, where S is the size (1, 8.5, 300, etc.)
	 * and B is the byte unit (K, MiB, GB, etc.). All valid byte units are
	 * defined in Num::$byte_units
	 *
	 * Example:
	 * ~~~
	 * $array->rule('file', 'Upload::size', array(':value', '1M'))
	 * $array->rule('file', 'Upload::size', array(':value', '2.5KiB'))
	 * ~~~
	 *
	 * @param   array   $file  $_FILES item
	 * @param   string  $size  Maximum file size allowed
	 *
	 * @return  boolean
	 *
	 * @uses    Num::bytes
	 */
	public static function size(array $file, $size)
	{
		if ($file['error'] === UPLOAD_ERR_INI_SIZE)
		{
			// Upload is larger than PHP allowed size (upload_max_filesize)
			return FALSE;
		}

		if ($file['error'] !== UPLOAD_ERR_OK)
		{
			// The upload failed, no size to check
			return TRUE;
		}

		// Convert the provided size to bytes for comparison
		$size = Num::bytes($size);

		// Test that the file is under or equal to the max size
		return ($file['size'] <= $size);
	}

	/**
	 * Validation rule to test if an upload is an image and, optionally, is the correct size
	 *
	 * Example:
	 * ~~~
	 * // The "image" file must be an image
	 * $array->rule('image', 'Upload::image')
	 *
	 * // The "photo" file has a maximum size of 640x480 pixels
	 * $array->rule('photo', 'Upload::image', array(640, 480));
	 *
	 * // The "image" file must be exactly 100x100 pixels
	 * $array->rule('image', 'Upload::image', array(100, 100, TRUE));
	 * ~~~
	 *
	 * @param   array   $file        $_FILES item
	 * @param   integer $max_width   Maximum width of image [Optional]
	 * @param   integer $max_height  Maximum height of image [Optional]
	 * @param   boolean $exact       Match width and height exactly? [Optional]
	 *
	 * @return  boolean
	 */
	public static function image(array $file, $max_width = NULL, $max_height = NULL, $exact = FALSE)
	{
		if (Upload::not_empty($file))
		{
			try
			{
				// Get the width and height from the uploaded image
				list($width, $height) = getimagesize($file['tmp_name']);
			}
			catch (ErrorException $e)
			{
				// Ignore read errors
			}

			if (empty($width) OR empty($height))
			{
				// Cannot get image size, cannot validate
				return FALSE;
			}

			if ( ! $max_width)
			{
				// No limit, use the image width
				$max_width = $width;
			}

			if ( ! $max_height)
			{
				// No limit, use the image height
				$max_height = $height;
			}

			if ($exact)
			{
				// Check if dimensions match exactly
				return ($width === $max_width AND $height === $max_height);
			}
			else
			{
				// Check if size is within maximum dimensions
				return ($width <= $max_width AND $height <= $max_height);
			}
		}

		return FALSE;
	}

	/**
	 * Get PHP upload_max_filesize
	 *
	 * @return  integer
	 */
	public static function getUploadMaxFilesize()
	{
		$max_size = ini_get('upload_max_filesize');
		$mul = substr($max_size, -1);
		$mul = ($mul == 'M' ? 1048576 : ($mul == 'K' ? 1024 : ($mul == 'G' ? 1073741824 : 1)));

		return $mul * (int) $max_size;
	}

    /**
     * Picture validation for image upload
     *
     * @param   array   $file        $_FILES item
     * @param   string  $upload_dir  Relative upload dir [Optional]
     *
     * Example:
     * ~~~
     * $filepath = Upload::uploadImage($_FILES);
     * ~~~
     *
     * @since   1.2.0
     *
     * @return  NULL|string          NULL when filed, otherwise file path
     *
     * @uses    System::mkdir
     * @uses    Message::error
     * @uses    Log::error
     * @uses    Upload::valid
     * @uses    Upload::save
     * @uses    Config::get
     * @uses    File::getUnique
     */
    public static function uploadImage($file, $upload_dir = NULL)
    {
    	if (is_null($upload_dir))
    	{
    		$upload_dir = Config::get('media.upload_dir', 'media/pictures');
    	}

        $picture_path  = APPPATH . $upload_dir;
        $valid_formats = Config::get('media.supported_image_formats', array('jpg', 'gif', 'png'));
        $save          = TRUE;

        if ( ! is_dir($picture_path))
        {
            if ( ! System::mkdir($picture_path))
            {
                Message::error(__('Failed to create directory %dir for uploading picture.'));

                Log::error('Failed to create directory :dir for uploading picture.',
                    array(':dir' => $picture_path)
                );

                $save = FALSE;
            }
        }
        // Check if there is an uploaded file and valid type
        if ($save AND self::valid($file) AND self::type($file, $valid_formats) and self::size($file, self::getUploadMaxFilesize()))
        {
            $filename = File::getUnique($file['name']).'.'.pathinfo($file['name'], PATHINFO_EXTENSION);
            $path     = self::save($file, $filename, $picture_path);

            if ($path)
            {
                return $upload_dir.DS.$filename;
            }
        }

        return NULL;
    }
}
