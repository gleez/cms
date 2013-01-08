<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * The Gleez users auth configuration
 */
return array
(
  /**
   * Driver name file or orm
   */
  'driver' => 'orm',

  /**
   * Type of hash to use for passwords.
   * Any algorithm supported by the hash function can be used here.
   *
   * @link http://php.net/hash
   * @link http://php.net/hash_algos
   */
  'hash_method' => 'sha1',

  /**
   * Set the hash key that will be used to store the user password's salt.
   */
  'hash_key' => 'e41eb68d5605ebcc01424519da854c00cf52c342e81de4f88fd336b1d31ff430',

  /**
   * Set the auto-login (remember me) cookie lifetime, in seconds.
   * The default lifetime is two weeks.
   */
  'lifetime' => 1209600,

  /**
   * Set the session key that will be used to store the current user.
   */
  'session_key' => 'auth_user',

  /**
   * Username/password combinations for the Auth File driver
   */
  'users' => array(
    // 'admin' => 'b3154acf3a344170077d11bdb5fff31532f679a1919e716a02',
  ),

  /**
   * Use username for login and registration (TRUE) or use email as username (FALSE)
   */
  'username' => TRUE, // TRUE|FALSE

  /**
   * Allow user registration (TRUE)
   */
  'register' => TRUE, // TRUE|FALSE

  /**
   * Username rules for validation
   */
  'name' => array(
    'chars' => 'a-zA-Z0-9_\-\^\.',
    'length_min' => 4,
    'length_max' => 32,
  ),

  /**
   * Use confirm password field in registraion
   */
  'confirm_pass' => TRUE, // TRUE|FALSE

  /**
   * Use nickname for registration (TRUE) or use username (FALSE)
   */
  'use_nick' => TRUE, // TRUE|FALSE

  /**
   * Use nickname for registration (TRUE) or use username (FALSE)
   */
  'use_captcha' => TRUE, // TRUE|FALSE

  /**
   * The number of failed logins allowed can be specified here:
   * If the user mistypes their password X times,
   * then they will not be permitted to log in during the jail time.
   * This helps prevent brute-force attacks.
   */
  'auth' => array(
    /**
     * Define the maximum failed attempts to login
     * set 0 to disable the login jail
     */
    'max_failed_logins' => 5,

    /**
     * Define the time that user who archive the max_failed_logins
     * will need to wait before his next attempt
     */
    'login_jail_time' => "15 minutes",
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
     */
    'github' => FALSE,

    /**
     * Toggle Facebook support:
     *  if set, then users can log in using Facebook.
     *
     * Setup:
     * - You must register your app with Facebook and
     *   add the information in /config/oauth.php (Oauth's config)
     */
    'facebook' => FALSE,

    /**
     * Toggle Twitter support:
     *  if set, users can log in using Twitter
     *
     * Setup:
     * - You must register your app with Twitter and
     *   add the information in /config/oauth.php (Oauth's config)
     */
    'twitter' => FALSE,

    /**
     * Toggle Google support:
     *  if set, users can log in using their Google account.
     *
     * Setup:
     * - You must register your app with Google and
     *   add the information in /config/oauth.php (Oauth's config)
     */
    'google' => FALSE,

    /**
     * Toggle Windows Live support:
     *  if set, users can log in using their Windows Live account.
     *
     * Setup:
     * - You must register your app with Windows Live and
     *   add the information in /config/oauth.php (Oauth's config)
     */
    'live' => FALSE,
  ),
);
