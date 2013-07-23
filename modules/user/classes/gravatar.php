<?php defined('SYSPATH') OR die('No direct script access allowed.');
/**
 * Gleez Gravatar
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
 * @author     Sergey Yakovlev - Gleez
 * @version    1.2.1
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
	 * Default size of the returned gravatar (Percentage)
	 * + String of the gravatar-recognized default image "type" to use
	 * + URL
	 * + FALSE if using the default gravatar default image
	 * @var string
	 */
	protected $_default_image = FALSE;

	/**
	 * Default size of the returned gravatar (Percentage)
	 * @var integer
	 */
	protected $_size = 100;

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
		'G'  => TRUE,
		'PG' => TRUE,
		'R'  => TRUE,
		'X'  => TRUE
	);

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
	 */
	public function buildURL()
	{
		$url = Gravatar::HTTP_URL;

		// Building the URL
		if ($this->useSecureURL() OR Request::current()->secure())
		{
			$url = Gravatar::HTTPS_URL;
		}

		$url .= $this->getEmailHash($this->_email);

		$url .= URL::query(
			array(
				's' => $this->getSize(),
				'r' => $this->getRating(),
			),
			FALSE
		);

		if ($this->getDefaultImage())
		{
			$url .= URL::query(array('d' => $this->getDefaultImage()), FALSE);
		}

		if ($this->isForceDefault())
		{
			$url .= URL::query(array('f' => 'y'), FALSE);
		}

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
		return $this->_rating;
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
	 * Set the avatar size to use
	 *
	 * By default, Gravatar return images at 80px by 80px
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

		if ($size > 600 OR $size < 0)
		{
			throw new Gleez_Exception('Avatar size must be within 0% and 600%');
		}

		$this->_size = $size;

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

		$image = strtolower($image);
		if ( ! isset(self::$_default_gravatar[$image]))
		{
			if ( ! Valid::url($image))
			{
				throw new Gleez_Exception('The default image specified is not a recognized gravatar "default" and is not a valid URL');
			}
			else
			{
				$this->_default_image = rawurlencode($image);
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
		$rating = strtoupper($rating);

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
	 * @param   boolean  $force  Force default?
	 *
	 * @return  Gravatar
	 */
	public function setForceDefault($force)
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

}