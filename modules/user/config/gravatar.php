<?php
/**
 * Gravatar config
 */
return array(

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
);
