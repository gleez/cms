<?php
/**
 * [Gleez Gravatar](gleez/gravatar)
 *
 * [Gravatar's](http://en.gravatar.com) are universal avatars available
 * to all web sites and services. Users must register their email addresses
 * with Gravatar before their avatars will be usable in Gleez.
 *
 * Users with gravatars can have a default image of your selection.
 *
 * @see        http://en.gravatar.com
 *
 * @package    Gleez\Gravatar
 * @author     Gleez Team
 * @version    1.4.3
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Gravatar {

	/** The gravatar service URLs */
	const HTTP_URL  = 'http://www.gravatar.com/avatar/';
	const HTTPS_URL = 'https://secure.gravatar.com/avatar/';

	/**
	 * Static instances
	 * @var  array
	 */
	protected static $_instances = array();

	/**
	 * Configuration array
	 * @var array
	 */
	protected $_config;

	/**
	 * The default image to use:
	 * String of the gravatar-recognized default image "type" to use,
	 * URL or FALSE if using the default gravatar default image.
	 * @var string
	 */
	protected $_default_image = FALSE;

	/**
	 * Default size of the returned gravatar
	 * @var integer
	 */
	protected $_size = 250;

	/**
	 * The maximum rating to allow for the avatar
	 * @var string
	 */
	protected $_rating = 'G';

	/**
	 * Should we use the secure (HTTPS) URL base?
	 * @var boolean
	 */
	protected $_secure_url = FALSE;

	/**
	 * The email address of the user
	 * @var string
	 */
	protected $_email;

	/**
	 * If default image shall be shown even if user the has an gravatar profile.
	 * @var boolean
	 */
	protected $_default_force = FALSE;

	/**
	 * List of valid picture formats for downloading
	 * @var array
	 */
	protected $_valid_formats = array(
		'jpe',
		'jpg',
		'jpeg',
		'gif',
		'png',
		'bmp'
	);

	/**
	 * Gravatar defaults
	 * @var array
	 */
	protected static $_default_gravatar = array(
		'404'       => TRUE,
		'mm'        => TRUE,
		'identicon' => TRUE,
		'monsterid' => TRUE,
		'wavatar'   => TRUE,
		'retro'     => TRUE,
		'blank'     => TRUE
	);

	/**
	 * Gravatar rating
	 * @var array
	 */
	protected static $_ratings = array(
		'g'  => TRUE,
		'pg' => TRUE,
		'r'  => TRUE,
		'x'  => TRUE
	);

	/**
	 * Current store location for downloading user pictures
	 * @var string
	 */
	protected $_store_location;

	/**
	 * Get a singleton Gravatar instance
	 *
	 * @param   string  $email   User email
	 * @param   array   $config  Gravatar config [Optional]
	 *
	 * @return  Gravatar
	 *
	 * @uses    Config::get
	 */
	public static function instance($email, $config = NULL)
	{
		if ( ! isset(self::$_instances[$email]))
		{
			if (is_null($config))
			{
				// Load the configuration
				$config = Config::get('auth.gravatar', array());
			}

			// Create the Gravatar instance
			self::$_instances[$email] = new self($email, $config);
		}

		return self::$_instances[$email];
	}

	/**
	 * Gravatar class constructor
	 *
	 * [!!] This method cannot be accessed directly, you must use [Gravatar::instance].
	 *
	 * @param   string  $email   User email
	 * @param   array   $config  Gravatar config
	 */
	protected function __construct($email, array $config)
	{
		// Set the email address
		$this->setEmail($email);

		// Set default picture location for downloading
		$this->setStoreLocation();

		// Store the config locally
		$this->_config = $this->_prepareConfig($config);
	}

	/**
	 * Returns the Gravatar URL
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return (string) $this->buildURL();
	}

	/**
	 * Creates the Gravatar URL based on the configuration and email
	 *
	 * [!!] This is called automatically by [Gravatar::__toString].
	 *
	 * @return  string  The resulting Gravatar URL
	 *
	 * @uses    URL::query
	 * @uses    Request::current
	 * @uses    Request::secure
	 * @uses    Arr::merge
	 */
	public function buildURL()
	{
		$url = self::HTTP_URL;

		// Building the URL
		if ($this->useSecureURL() OR Request::current()->secure())
		{
			$url = self::HTTPS_URL;
		}

		$url .= $this->getEmailHash($this->_email);

		$query = array(
			's' => $this->getSize(),
			'r' => $this->getRating(),
		);

		if ($this->getDefaultImage())
		{
			$query = Arr::merge($query, array('d' => $this->getDefaultImage()));
		}

		if ($this->isForceDefault())
		{
			$query = Arr::merge($query, array('f' => 'y'));
		}

		$url .= URL::query($query, FALSE);

		return $url;
	}

	/**
	 * Get the currently set avatar size
	 *
	 * The current avatar size in use.
	 *
	 * @since   1.1.0
	 *
	 * @return  integer
	 */
	public function getSize()
	{
		return $this->_size;
	}

	/**
	 * Get the current maximum allowed rating for avatars
	 *
	 * The string representing the current maximum allowed rating ('g', 'pg', 'r', 'x').
	 *
	 * @since   1.1.0
	 *
	 * @return  integer
	 */
	public function getRating()
	{
		return strtolower($this->_rating);
	}

	/**
	 * Get the email of currently user
	 *
	 * @since   1.1.0
	 *
	 * @return string
	 */
	public function getEmail()
	{
		return $this->_email;
	}

	/**
	 * Get the email hash to use
	 *
	 * @since   1.1.0
	 *
	 * @return string
	 */
	public function getEmailHash()
	{
		return hash('md5', $this->_email);
	}

	/**
	 * Get the current default image
	 *
	 * @since   1.2.0
	 *
	 * @return  string   If one is set
	 * @return  boolean  FALSE if no default image set
	 */
	public function getDefaultImage()
	{
		return $this->_default_image;
	}

	/**
	 * Creates a image link
	 *
	 * Example:
	 * ~~~
	 * echo Gravatar::instance('username@site.com')->getImage();
	 * ~~~
	 *
	 * @since   1.3.0
	 *
	 * @param   array    $attrs     Default attributes [Optional]
	 * @param   mixed    $protocol  Protocol string, [Request], or boolean [Optional]
	 * @param   boolean  $index     Add index file to URL? [Optional]
	 *
	 * @return  string
	 *
	 * @uses    Arr::merge
	 * @uses    HTML::resize
	 */
	public function getImage(array $attrs = NULL, $protocol = NULL, $index = FALSE)
	{
		// Set auto attributes
		$attributes = array(
			'width'    => $this->_size,
			'height'   => $this->_size,
			'itemprop' => 'image'
		);

		// Merge attributes
		$attrs = Arr::merge($attributes, (array) $attrs);

		// Return html
		return HTML::resize($this, $attrs, $protocol, $index);
	}

	/**
	 * Get list of valid picture formats for downloading
	 *
	 * @since   1.4.0
	 *
	 * @return  array
	 */
	public function getValidFormats()
	{
		return $this->_valid_formats;
	}

	/**
	 * Get list of valid picture mime types for downloading
	 *
	 * @since   1.4.0
	 *
	 * @return  array
	 *
	 * @uses    Config::get
	 */
	public function getValidTypes()
	{
		$valid_formats = array();

		foreach($this->_valid_formats as $format)
		{
			$valid_formats[$format] = Config::get("mimes.{$format}");
		}

		$valid_types   = array();

		foreach($valid_formats as $format => $types)
		{
			foreach ($types as $type)
			{
				$valid_types[] = $type;
			}
		}

		return $valid_types;
	}

	/**
	 * Get current store location for downloading pictures
	 *
	 * Example:
	 * ~~~
	 * echo Gravatar::instance('username@site.com')->getStoreLocation();
	 * // For example /srv/http/public_html/site.com/application/media/pictures/
	 *
	 * echo Gravatar::instance('username@site.com')->getStoreLocation('filename');
	 * // For example /srv/http/public_html/site.com/application/media/pictures/filename
	 * ~~~
	 *
	 * @since   1.4.0
	 *
	 * @param   string  $filename File name [Optional]
	 *
	 * @return  string
	 */
	public function getStoreLocation($filename = NULL)
	{
		return $this->_store_location . $filename;
	}

	/**
	 * Set the avatar size to use
	 *
	 * [!!] Note: By default, images from Gravatar.com will be returned as 80x80 px
	 *
	 * @since   1.1.0
	 *
	 * @param   integer  $size  The avatar size to use, must be less than 512 and greater than 0
	 *
	 * @return  Gravatar
	 *
	 * @throws Gleez_Exception
	 */
	public function setSize($size)
	{
		if ( ! is_int($size) AND ! ctype_digit($size))
		{
			throw new Gleez_Exception('Avatar size specified must be an integer');
		}

		if ($size > 2048 OR $size < 0)
		{
			throw new Gleez_Exception('Avatar size must be within 0 pixels and 2048 pixels');
		}

		$this->_size = $size;

		return $this;
	}

	/**
	 * Set list of valid picture formats for downloading
	 *
	 * @since   1.4.0
	 *
	 * @param   array  $formats  Array of valid picture formats (eg. array('jpg', 'gif', 'png', ...))
	 *
	 * @return  Gravatar
	 */
	public function setValidFormats(array $formats)
	{
		$this->_valid_formats = $formats;

		return $this;
	}

	/**
	 * Set store location for downloading pictures
	 *
	 * [!!] Note: If `$location` is NULL, by default used `APPPATH . 'media/pictures'`.
	 *      If dir not exists and fails create it used sys_get_temp_dir()
	 *
	 * @since   1.4.0
	 *
	 * @link    http://www.php.net/manual/en/function.sys-get-temp-dir.php sys_get_temp_dir()
	 *
	 * @param   string  $location  Store location [Optional]
	 *
	 * @return  Gravatar
	 *
	 * @throws  Gleez_Exception
	 *
	 * @uses    Text::reduce_slashes
	 * @uses    System::mkdir
	 */
	public function setStoreLocation($location = NULL)
	{
		$location = Text::reduce_slashes(trim($location));
		// Set default picture location for downloading
		$this->_store_location = empty($location) ?  APPPATH . 'media/pictures' : $location;

		// Make sure destination is a directory
		if ( ! is_dir($this->_store_location))
		{
			if ( ! System::mkdir($this->_store_location))
			{
				Log::warning("Can't create location :loc1 for picture downloading. Current location: :loc2",
					array(':loc1' => $this->_store_location, ':loc2' => sys_get_temp_dir())
				);
				$this->_store_location = sys_get_temp_dir();
			}
		}

		// Make sure destination is writable
		if ( ! is_writable($this->_store_location))
		{
			throw new Gleez_Exception('Gravatar download destination is not writable!', array(), 105);
		}

		$this->_store_location = $this->_store_location . DS;

		return $this;
	}

	/**
	 * Set the email address for current user
	 *
	 * @since   1.2.0
	 *
	 * @param   string  $email  Email address of the user
	 *
	 * @return  Gravatar
	 *
	 * @throws  Gleez_Exception
	 *
	 * @uses    Valid::email
	 */
	public function setEmail($email)
	{
		// trim leading/trailing white spaces
		$email = trim($email);

		// make sure passed email address is valid
		if ( ! Valid::email($email))
		{
			throw new Gleez_Exception('E-mail must be a valid email address');
		}

		// force lowercase and set property
		$this->_email = strtolower($email);

		return $this;
	}

	/**
	 * Set the default image to use for avatars
	 *
	 * Possible $image formats:
	 * + boolean FALSE for the gravatar default
	 * + string containing a valid image URL
	 * + a string specifying a recognized gravatar "default"
	 *
	 * @since   1.2.0
	 *
	 * @param   mixed  $image  The default image to use
	 *
	 * @return  Gravatar
	 *
	 * @throws  Gleez_Exception
	 *
	 * @uses    Valid::url
	 */
	public function setDefaultImage($image)
	{
		if($image === FALSE)
		{
			$this->default_image = FALSE;

			return $this;
		}

		$image = strtolower(trim($image));
		if ( ! isset(self::$_default_gravatar[$image]))
		{
			if ( ! Valid::url($image))
			{
				throw new Gleez_Exception('The default image specified is not a recognized gravatar "default" and is not a valid URL');
			}
			else
			{
				$this->_default_image = $image;
			}
		}
		else
		{
			$this->_default_image = $image;
		}

		return $this;
	}

	/**
	 * Set the maximum allowed rating for avatars
	 *
	 * @since   1.2.0
	 *
	 * @param   string  $rating   The maximum rating to use for avatars ('G', 'PG', 'R', 'X')
	 *
	 * @return  Gravatar
	 *
	 * @throws Gleez_Exception
	 */
	public function setRating($rating)
	{
		$rating = strtolower($rating);

		if ( ! isset(self::$_ratings[$rating]))
		{
			throw new Gleez_Exception('Invalid rating :rating specified, only "G", "PG", "R", or "X" are allowed to be used.',
				array(':rating' => $rating)
			);
		}

		$this->_rating = $rating;

		return $this;
	}

	/**
	 * Forces gravatar to display default image
	 *
	 * @since   1.2.0
	 *
	 * @param   boolean  $force  Force default? [Optional]
	 *
	 * @return  Gravatar
	 */
	public function setForceDefault($force = TRUE)
	{
		$this->_default_force = (bool)$force;

		return $this;
	}

	/**
	 * Prepare Gravatar config
	 *
	 * [!!] This is called automatically by [Gravatar::__construct].
	 *
	 * @param   array  $config  Gravatar config
	 *
	 * @return  array
	 */
	protected function _prepareConfig(array $config)
	{
		if (isset($config['secure_url']) AND $config['secure_url'])
		{
			$this->enableSecureURL();
		}

		if (isset($config['size']))
		{
			$this->setSize($config['size']);
		}

		if (isset($config['rating']))
		{
			$this->setRating($config['rating']);
		}

		if (isset($config['default_image']))
		{
			$this->setDefaultImage($config['default_image']);
		}

		if (isset($config['force_default']))
		{
			$this->setForceDefault($config['force_default']);
		}

		if (isset($config['valid_formats']) and is_array($config['valid_formats']))
		{
			$this->setValidFormats($config['valid_formats']);
		}

		if (isset($config['store_location']) and is_string($config['store_location']))
		{
			$this->setStoreLocation($config['store_location']);
		}

		return $config;
	}

	/**
	 * Check if we are using the secure protocol for the image URLs
	 *
	 * @since   1.2.0
	 *
	 * @return  boolean
	 */
	public function useSecureURL()
	{
		return $this->_secure_url;
	}

	/**
	 * Enable the use of the secure protocol for image URLs
	 *
	 * @since   1.2.0
	 *
	 * @return  Gravatar
	 */
	public function enableSecureURL()
	{
		$this->_secure_url = TRUE;

		return $this;
	}

	/**
	 * Disable the use of the secure protocol for image URLs
	 *
	 * @since   1.2.0
	 *
	 * @return  Gravatar
	 */
	public function disableSecureURL()
	{
		$this->_secure_url = FALSE;

		return $this;
	}

	/**
	 * Check if need to force the default image to always load
	 *
	 * @since   1.2.0
	 *
	 * @return boolean
	 */
	public function isForceDefault()
	{
		return $this->_default_force;
	}

	/**
	 * Downloads gravatar to location on server
	 *
	 * [!!] Note: If location is not set, by default use server tmp directory.
	 *
	 * Example:
	 * ~~~
	 * // get an image specific to a user
	 * $avatar = Gravatar::instance('username@site.com');
	 *
	 * // download gravatar
	 * $result = $avatar->download();
	 *
	 * // print result
	 * echo __('Gravatar saved to :loc, file size: :len', array(
	 *     ':loc' => $result->location,
	 *     ':len' => $result->length
	 * ));
	 * ~~~
	 *
	 * @since   1.4.0
	 *
	 * @return  stdClass
	 *
	 * @throws  Gleez_Exception
	 *
	 * @uses    File::ext_by_mime
	 */
	public function download()
	{
		try
		{
			$headers = get_headers($this, 1);
		}
		catch (ErrorException $e)
		{
			if ($e->getCode() === 2)
			{
				throw new Gleez_Exception('URL does not seem to exist', array(), 403);
			}
			else
			{
				throw new Gleez_Exception($e->getMessage(), array(), $e->getCode());
			}
		}

		// Make sure content type exists
		if ( ! isset($headers['Content-Type']))
		{
			throw new Gleez_Exception('Content-Type not found', array(), 300);
		}

		// Make sure content type is valid
		if ( ! in_array($headers['Content-Type'], $this->getValidTypes()))
		{
			throw new Gleez_Exception('Content-Type :type is invalid', array(':type' => $headers['Content-Type']), 305);
		}

		// Set file name
		$filename = $this->getEmailHash() . '.' . File::ext_by_mime($headers['Content-Type']);

		// Try to download
		try
		{
			file_put_contents($this->getStoreLocation($filename), file_get_contents($this));
		}
		catch (ErrorException $e)
		{
			throw new Gleez_Exception('File could not been downloaded: :msg', array(':msg' => $e->getMessage()), 400);
		}

		$result = new stdClass;

		$result->filename  = $filename;
		$result->extension = File::ext_by_mime($headers['Content-Type']);
		$result->type      = $headers['Content-Type'];
		$result->length    = $headers['Content-Length'];
		$result->location  = $this->getStoreLocation($filename);

		return $result;
	}

}
