<?php

return array(

	/**
	 * The public accessible directory where the file will be copied
	 * @var string
	 */
	'public_dir' => 'media',

	/**
	 * Write the files to the public directory?
	 * @var boolean
	 */
	'cache' => Kohana::$environment === Kohana::PRODUCTION,

	/**
	 * Compress assets?
	 * @var boolean
	 */
	'compress' => Kohana::$environment === Kohana::PRODUCTION,

        /**
	 * Combine multiple css/js files into single file. Defaults to FALSE
	 * @var boolean
	 */
	'combine' => FALSE,
        
	/**
	 * Supported image formats
	 * @var array
	 */
	'supported_image_formats' => array(
		'jpe',
		'jpg',
		'jpeg',
		'gif',
		'png',
		'bmp'
	),

	/**
	 * Maximum size of POST data that PHP will accept (eg. '200K', '5MiB', '1M', '500B')
	 * @var string
	 */
	'post_max_size' => '8M',

	/**
	 * Image quality
	 * @var integer
	 */
	'quality' => 85,
);