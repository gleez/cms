<?php defined('SYSPATH') OR die('No direct script access.');

return array(

        // The public accessible directory where the file will be copied
        'public_dir' => 'media',

        // Write the files to the public directory when in production
        'cache' => Kohana::$environment === Kohana::PRODUCTION,

        'compress' => Kohana::$environment === Kohana::PRODUCTION,

		// @todo
        'supported_image_formats' => array(
            'jpg',
            'jpeg',
            'gif',
            'png',
            'bmp'
        )
);