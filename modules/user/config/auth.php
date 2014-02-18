<?php
/**
 * The Gleez users auth configuration
 *
 * @package    Gleez\User\Config
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
return array
(
	/**
	 * Type of hash to use for passwords.
	 * Any algorithm supported by the hash function can be used here.
	 *
	 * @var  string
	 * @link http://php.net/hash
	 * @link http://php.net/hash_algos
	 */
	'hash_method' => 'sha1',

	/**
	 * Set the auto-login (remember me) cookie lifetime, in seconds.
	 * The default lifetime is two weeks.
	 * @var  integer
	 */
	'lifetime' => 1209600,

	/**
	 * Set the session key that will be used to store the current user.
	 * @var  string
	 */
	'session_key' => 'auth_user',

	/**
	 * Use username for login and registration (TRUE) or use email as username (FALSE)?
	 * @var  boolean
	 */
	'username' => TRUE,

	/**
	 * Allow user registration?
	 * @var  boolean
	 */
	'register' => TRUE,

	/**
	 * Username rules for validation
	 * @var  array
	 */
	'name' => array(
		'chars' => 'a-zA-Z0-9_\-\^\.',
		'length_min' => 4,
		'length_max' => 32,
	),

	/**
	 * Password rules for validation
	 * @var  array
	 */
	'password' => array(
		'length_min' => 4,
	),

	/**
	 * Use confirm password field in registration?
	 * @var  boolean
	 */
	'confirm_pass' => TRUE,

	/**
	 * Use nickname for registration (TRUE) or use username (FALSE)?
	 * @var  boolean
	 */
	'use_nick' => TRUE,

	/**
	 * Use captcha for registration (TRUE)?
	 * @var  boolean
	 */
	'use_captcha' => TRUE,

	/**
	 * The number of failed logins allowed can be specified here:
	 * If the user mistypes their password X times,
	 * then they will not be permitted to log in during the jail time.
	 *
	 * This helps prevent brute-force attacks.
	 * * @var  array
	 */
	'auth' => array(
		/**
		 * Define the maximum failed attempts to login
		 * set 0 to disable the login jail
		 * @var  integer
		 */
		'max_failed_logins' => 5,

		/**
		 * Define the time that user who archive the max_failed_logins
		 * will need to wait before his next attempt
		 * @var  string
		 */
		'login_jail_time' => "15 minutes",
	),

	/**
	 * Gravatar config
	 * @var array
	 */
	'gravatar' => array(

		/**
		 * Should we use the secure (HTTPS) URL base?
		 * @var boolean
		 */
		'secure_url' => FALSE,

		/**
		 * The size of the returned gravatar
		 * @var integer
		 */
		'size' => 250,

		/**
		 * The maximum rating to allow for the avatar
		 * Possible values: G, PG, R, X
		 * @var string
		 */
		'rating' => 'G',

		/**
		 * The default image if Gravatar is not found, FALSE uses Gravatar default.
		 * Possible values:  404, mm, identicon, monsterid, wavatar, retro, blank
		 * @var string
		 */
		'default_image' => FALSE,

		/**
		 * If for some reason you wanted to force the default image to always load
		 * set it to TRUE
		 * @var boolean
		 */
		'force_default' => FALSE,

		/**
		 * Valid picture formats for downloading
		 * @var array
		 */
		'valid_formats' => array(
			'jpe',
			'jpg',
			'jpeg',
			'gif',
			'png',
			'bmp'
		),

		/**
		 * Default store location for downloading pictures
		 * @var string
		 */
		'store_location' => APPPATH . 'media/pictures',
	),

);
