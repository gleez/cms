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
 * @version    1.1.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Gravatar {

	/** Gravatar rating */
	const RATING_G  = 'G';
	const RATING_PG = 'PG';
	const RATING_R  = 'R';
	const RATING_X  = 'X';

	/** The gravatar service URL */
	const SERVICE   = 'http://www.gravatar.com/avatar.php';

	/** Default size of the returned gravatar (Percentage) */
	const SIZE      = 100;

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
	 * The email address of the user
	 * @var string
	 */
	public $email;

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
			new self($email, $config);
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
		$this->email = $email;

		// Store the config locally
		$this->_config = $this->_prepareConfig($config);

		// Store the database instance
		self::$_instances[$email] = $this;
	}

	/**
	 * Returns the Gravatar URL
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return (string) $this->getURL();
	}

	/**
	 * Creates the Gravatar URL based on the configuration and email
	 *
	 * [!!] This is called automatically by [Gravatar::__toString].
	 *
	 * @return  string  The resulting Gravatar URL
	 */
	public function getURL()
	{
		return $this->_config['service'].
			"?gravatar_id={$this->getEmail()}&s={$this->getSize()}&r={$this->getRating()}";
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
		return (int)$this->_config['size'];
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
		return (int)$this->_config['rating'];
	}

	/**
	 * Get the email hash to use (after cleaning the string)
	 *
	 * @since   1.1.0
	 *
	 * @return string
	 */
	public function getEmail()
	{
		return hash('md5', strtolower(trim($this->email)));
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

		if ((int)$size > 600 OR (int)$size < 0)
		{
			throw new Gleez_Exception('Avatar size must be within 0 pixels and 600 pixels');
		}

		$this->_config['size'] = (int) $size;

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
		if ( ! isset($config['service']) OR ! $config['service'])
		{
			$config['service'] = Gravatar::SERVICE;
		}

		if ( ! isset($config['size']) OR ! $config['size'])
		{
			$config['size'] = Gravatar::SIZE;
		}

		if ( ! isset($config['rating']) OR ! $config['rating'])
		{
			$config['rating'] = Gravatar::RATING_G;
		}

		return $config;
	}

}