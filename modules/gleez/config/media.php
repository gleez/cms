<?php defined('SYSPATH') or die('No direct script access.');

return array(
        
        // The public accessible directory where the file will be copied
        //'public_dir' => DOCROOT.'media',
        'public_dir' => 'media',
        
        // Write the files to the public directory when in production
        'cache' => Kohana::$environment === Kohana::PRODUCTION,
        
        'compress'  => FALSE,
);