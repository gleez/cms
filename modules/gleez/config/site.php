<?php defined('SYSPATH') or die('404 Not Found.');

return array(

	/**
	 * Site name
	 * @var string
	 */
	'site_name' => 'Gleez CMS',

	/**
	 * Site slogan
	 * @var string
	 */
	'site_slogan' => 'Light, Simple, Flexible Content Management System',

	/**
	 * Site logo
	 * @var string
	 */
	'site_logo' => '/media/logo.png',

	/**
	 * Site favicon
	 * @var string
	 */
	'site_favicon' => 'favicon.ico',

	/**
	 * Site email
	 * @var string
	 */
	'site_email' => 'webmaster@gleezcms.org',

	/**
	 * Site url used for background tasks
	 * @var string
	 */
	'site_url' => 'www.gleezcms.org',

	/**
	 * Site mission
	 * @var string
	 */
	'site_mission' => '',

	/**
	 * Site title separator
	 * @var string
	 */
	'title_separator' => ' :: ',

	/**
	 * Default active site theme
	 * @var string
	 */
	'theme' => 'fluid',

	/**
	 * Default active admin theme
	 * @var string
	 */
	'admin_theme' => 'fluid',

	/**
	 * Mobile Theme or false
	 * @var mixed
	 */
	'mobile_theme' => FALSE,

	/**
	 * Maintenance Mode
	 * @var boolean
	 */
	'maintenance_mode' => FALSE,

	/**
	 * Offline message in Maintenance Mode
	 * @var string
	 */
	'offline_message' => '',

	/**
	 * Date Time Format
	 * @var string
	 */
	'date_time_format' => 'Y-M-d H:i:s',

	/**
	 * Date Format
	 * @var string
	 */
	'date_format' => 'Y-M-d',

	/**
	 * Time Format
	 * @var string
	 */
	'time_format' => 'H:i:s',

	/**
	 * Filter Default Format
	 * @var string
	 */
	'filter_default_format' => '1',

	/**
	 * Default controller
	 * @var string
	 */
	'front_page' => 'welcome',

	/**
	 * Default headers
	 * @var array
	 */
	'headers' => array(
		'X-Powered-By' => 'Gleez CMS (http://gleezcms.org)',
	),

	/**
	 * XMLRPC
	 * @var string
	 */
	'xmlrpc' => 'xmlrpc',

	/**
	 * Meta defaults
	 * @var array
	 */
	'meta' => array(
		'links' => array(
			URL::site('media/favicon.ico', TRUE) => array(
				'rel'  => 'shortcut icon',
				'type' => 'image/x-icon'
			),
			URL::site('rss', TRUE) => array(
				'rel'   => 'alternate',
				'type'  => 'application/rss+xml',
				'title' => 'Gleez RSS 2.0'
			),
			URL::site('', TRUE) => array(
				'rel'   => 'index',
				'title' => 'Gleez CMS'
			),
		),
		'tags' => array(
			'X-UA-Compatible'  => array(
				'http_equiv' => TRUE,
				'value'      => 'IE=edge,chrome=1'
			),
			'charset'          => 'utf-8',
			'generator'        => 'Gleez '.Gleez::VERSION.' (http://gleezcms.org)',
			'author'           => 'Gleez Team',
			'copyright'        => 'Copyright (c) Gleez Technologies 2011-2013. All rights reserved.',
			'robots'           => 'index, follow, noodp',
			'keywords'         => 'cms, cmf, gleez, kohana, php framework, site building',
			'description'      => 'Light, Simple, Flexible Content Management System',
			'viewport'         => 'width=device-width, initial-scale=1.0, maximum-scale=1.0',
		),
	),

	/**
	 * Installed locales
	 * @var array
	 */
	'installed_locales' => array(
		'en' => array(
			'LINUX'   => 'en_US.utf-8',
			'WINDOWS' => 'english',
		),
		'it' => array(
			'LINUX'   => 'it_IT.utf-8',
			'WINDOWS' => 'italian',
		),
		'ru' => array(
			'LINUX'   => 'ru_RU.utf-8',
			'WINDOWS' => 'russian',
		),
		'zh' => array(
			'LINUX'   => 'zh_CN.utf-8',
			'WINDOWS' => 'chinese-simplified',
		),
	),

	/**
	 * Blocked ips.
	 * Default to null, comma separated ip-addresses to block
	 * @var string
	 */
	'blocked_ips' =>  NULL,

	/**
	 * Default timezone
	 * @var string
	 */
	'timezone' => 'Asia/Kolkata',

	/**
	 * Default date first day
	 * @var integer
	 */
	'date_first_day' => 1,

	/**
	 * Site Private Key
	 * Default to null, generate a random key on installation
	 * @var string
	 */
	'gleez_private_key' => NULL,

	/**
	 * Number of seconds before password reset confirmation links expire
	 * @var string
	 */
	'reset_password_expiration' => 86400,

	/**
	 * Default session type
	 * @var string
	 */
	'session_type' => 'db',

	/**
	 * Define Google Analytics ID
	 * @var string
	 */
	'google_ua' => NULL
);
