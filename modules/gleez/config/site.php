<?php defined('SYSPATH') or die('404 Not Found.');

return array(

	/**
	 * Site name
	 */
	'site_name'    => 'Gleez CMS',

	/**
	 * Site slogan
	 */
	'site_slogan' => 'Light, Simple, Flexible Content Management System',

	/**
	 * Site logo
	 */
	'site_logo' => '/media/logo.png',

	/**
	 * Site favicon
	 */
	'site_favicon' => 'favicon.ico',
        
        /**
	 * Site email
	 */
	'site_email' => 'webmaster@example.com',

        /**
	 * Site url used for background tasks, where sitename is not available
	 */
	'site_url' => 'www.example.com',

	/**
	 * Site mission
	 */
	'site_mission' => '',
        
	/**
	 * Site title seperator
	 */
        'title_separator' => ' :: ',
        
	/**
	 * Default active site theme
	 */
	'theme' => 'anytime',

	/**
	 * Default active admin theme
	 */
	'admin_theme' => 'anytime',

	/**
	 * Site Maintenance Mode, when true only site admin's can access
	 */
	'maintenance_mode' => FALSE,

	/**
	 * Site Maintenance Mode, offline message
	 */
	'offline_message' => FALSE,
        
	/**
	 * Date Time Format
	 */
	'date_time_format' => 'Y-M-d H:i:s',

	/**
	 * Date Format
	 */
	'date_format' => 'Y-M-d',

	/**
	 * Time Format
	 */
	'time_format' => 'H:i:s',

	/**
	 * Filter Default Format
	 */
	'filter_default_format' => '1',

	/**
	 * Default controller
	 */
	'front_page' => 'welcome',

	// Default headers
	'headers' => array
	(
		'X-Powered-By'	=> 'Gleez CMS (http://gleezcms.org)',
	),

        'xmlrpc'  => 'xmlrpc',
        
	// Meta defaults
	'meta' => array
	(
		'links' => array
		(
			URL::site('media/favicon.ico', TRUE) => array
			(
				'rel'  => 'shortcut icon',
                                'type' => 'image/x-icon'
			),
			URL::site('rss', TRUE) => array
			(
				'rel'   => 'alternate',
                                'type'  => 'application/rss+xml',
                                'title' => 'Gleez RSS 2.0'
			),
                        URL::site('', TRUE) => array
			(
				'rel'  => 'index',
                                'title' => 'Gleez'
			),
		),
		'tags' => array
		(
                        'charset'		=> 'text/html; charset=UTF-8',
			'generator'		=> 'Gleez '.GLEEZ::VERSION . ' (http://gleezcms.org)',
                        //'author'		=> 'Gleez',
			//'copyright'		=> 'Copyright Gleez 2011. All rights reserved.',
			'robots'		=> 'index, follow, noodp',
			'viewport'		=> 'width=device-width; initial-scale=1.0; maximum-scale=1.0;',
			'X-UA-Compatible'	=> array('http_equiv' => TRUE, 'value' => 'IE=edge,chrome=1'),
		),
	),
        
	/**
	 * Default locale
	 */
	'locale'	=> 'en-US',

	/**
	 * Installed locales
	 */
	'installed_locales'	=> 'en-US|',

	/**
	 * Default timezone
	 */
	'timezone'	=> 'Asia/Kolkata',

	/**
	 * Default date first day
	 */
	'date_first_day'	=> 1,

	/**
	 * Default seo
	 */
	'seo_url'	=> TRUE,
        
	/**
	 * Site Private Key
	 */
	'gleez_private_key' =>'e41eb68d5605ebcc01424519da854c00cf52c342e81de4f88fd336b1d31ff430',

	/**
	 * Number of seconds before password reset confirmation links expire
	 */
	'reset_password_expiration' => 86400 // 24 hour(s)
);