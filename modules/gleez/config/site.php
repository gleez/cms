<?php

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
	'site_logo' => '/media/images/logo.png',

	/**
	 * Site favicon
	 * @var string
	 */
	'site_favicon' => '/media/icons/favicon.ico',

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
	 * Keywords for search engines
	 * @var string
	 */
	'keywords' => 'cms, cmf, gleez, kohana, php framework, site building',

	/**
	 * Description for search engines
	 * @var string
	 */
	'description' => 'Light, Simple, Flexible Content Management System',

	/**
	 * Site title separator
	 * @var string
	 */
	'title_separator' => ' :: ',

	/**
	 * Default active site theme
	 * @var string
	 */
	'theme' => 'cerber',

	/**
	 * Default active admin theme
	 * @var string
	 */
	'admin_theme' => 'cerber',

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
	 * The module search paths. They are searched in the order given.
	 *
	 * @var array
	 */
	'module_paths' => array(MODPATH),

	/**
	 * The theme search paths. They are searched in the order given.
	 *
	 * @var array
	 */
	'theme_paths' => array(THEMEPATH),

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
	 * Number of minutes, which indicates how long the channel can be cached without updating
	 * @var integer
	 */
	'feed_ttl' => 60,

	/**
	 * Use Gravatar service?
	 * @var boolean
	 */
	'use_gravatars' => FALSE,

	/**
	 * Meta defaults
	 * @var array
	 */
	'meta' => array(
		'links' => array(
			URL::site('media/icons/favicon.ico', TRUE) => array(
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
			'charset'    => Kohana::$charset,
			'generator'  => 'Gleez '.Gleez::VERSION.' (http://gleezcms.org)',
			'author'     => 'Gleez Team',
			'copyright'  => 'Copyright (c) Gleez Technologies 2011-2014. All rights reserved.',
			'robots'     => 'index, follow, noodp',
			'viewport'   => 'width=device-width, initial-scale=1.0',
		),
	),

	/**
	 * Default locale.
	 * Default to 'en_US'
	 * @var string
	 */
	'locale' =>  'en',

	/**
	 * Allow locale override.
	 * Change the default locale, accepted values: FALSE|ALL|USER|CLIENT|URL|DOMAIN
	 * @var string
	 */
	'locale_override' =>  FALSE,

	/**
	 * Locale cookie key.
	 * Default to 'lang'
	 * @var string
	 */
	'locale_cookie' => 'lang',

	/**
	 * List of all supported languages. Array keys match language segment from the URI.
	 * A default fallback language can be set by I18n::$default.
	 *
	 * Options for each language:
	 *  i18n_code - The target language for the I18n class
	 *  locale    - Locale name(s) for setting all locale information (http://php.net/setlocale)
	 */
	'installed_locales' => array(
		'en' => array(
			'name'      => 'English',
			'i18n_code' => 'en',
			'locale'    => array('en_US.utf-8'),
		),
		'et' => array(
			'name'      => 'Estonian',
			'i18n_code' => 'et',
			'locale'    => array('et_EE.utf-8'),
		),
		'it' => array(
			'name'      => 'Italian',
			'i18n_code' => 'it',
			'locale'    => array('it_IT.utf-8'),
		),
		'ro' => array(
			'name'      => 'Romanian',
			'i18n_code' => 'ro',
			'locale'    => array('ro_RO.utf-8'),
		),
		'ru' => array(
			'name'      => 'Russian',
			'i18n_code' => 'ru',
			'locale'    => array('ru_RU.utf-8'),
		),
		'zh' => array(
			'name'      => 'Chinese (Simplified)',
			'i18n_code' => 'zh',
			'locale'    => array('zh_CN.utf-8'),
		),
	),

	/**
	 * Default timezone
	 * @var string
	 */
	'timezone' => 'Asia/Kolkata',

	/**
	 * Allow timezone override.
	 * Change the default timezone, accepted values: TRUE|FALSE
	 * @var boolean
	 */
	'timezone_override' => FALSE,

	/**
	 * Blocked ips.
	 * Default to null, comma separated ip-addresses to block
	 * @var string
	 */
	'blocked_ips' => NULL,

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
