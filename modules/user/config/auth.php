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
	 * Driver name file or orm
	 * @var  string
	 */
	'driver' => 'orm',

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
	 * Set the hash key that will be used to store the user password's salt
	 * @var  string
	 */
	'hash_key' => 'e41eb68d5605ebcc01424519da854c00cf52c342e81de4f88fd336b1d31ff430',

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
	 * Username/password combinations for the Auth File driver
	 * @var  string
	 */
	'users' => array(
		// 'admin' => 'b3154acf3a344170077d11bdb5fff31532f679a1919e716a02',
	),

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
	 * Use nickname for registration (TRUE) or use username (FALSE)?
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

	/**
	 * 3rd party providers supported/allowed.
	 */
	'providers' => array(
		/**
		 * Toggle Github support:
		 *  if set, then users can log in using Github.
		 *
		 * Setup:
		 * - You must register your app with Github and
		 *   add the information in /config/oauth.php (Oauth's config)
		 *
		 * @var  boolean
		 */
		'github' => FALSE,

		/**
		 * Toggle Facebook support:
		 *  if set, then users can log in using Facebook.
		 *
		 * Setup:
		 * - You must register your app with Facebook and
		 *   add the information in /config/oauth.php (Oauth's config)
		 *
		 * @var  boolean
		 */
		'facebook' => FALSE,

		/**
		 * Toggle Twitter support:
		 *  if set, users can log in using Twitter
		 *
		 * Setup:
		 * - You must register your app with Twitter and
		 *   add the information in /config/oauth.php (Oauth's config)
		 *
		 * @var  boolean
		 */
		'twitter' => FALSE,

		/**
		 * Toggle Google support:
		 *  if set, users can log in using their Google account.
		 *
		 * Setup:
		 * - You must register your app with Google and
		 *   add the information in /config/oauth.php (Oauth's config)
		 *
		 * @var  boolean
		 */
		'google' => FALSE,

		/**
		 * Toggle Windows Live support:
		 *  if set, users can log in using their Windows Live account.
		 *
		 * Setup:
		 * - You must register your app with Windows Live and
		 *   add the information in /config/oauth.php (Oauth's config)
		 *
		 * @var  boolean
		 */
		'live' => FALSE,
	),
);
