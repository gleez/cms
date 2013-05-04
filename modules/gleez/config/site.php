<?php defined('SYSPATH') or die('404 Not Found.');

return array(

  /** @var string Site name */
  'site_name' => 'Gleez CMS',

  /** @var string Site slogan */
  'site_slogan' => 'Light, Simple, Flexible Content Management System',

  /** @var string Site logo */
  'site_logo' => '/media/logo.png',

  /** @var string Site favicon */
  'site_favicon' => 'favicon.ico',

  /** @var string Site email */
  'site_email' => 'webmaster@gleezcms.org',

  /** @var string Site url used for background tasks */
  'site_url' => 'www.gleezcms.org',

  /** @var string Site mission */
  'site_mission' => '',

  /** @var string Site title separator */
  'title_separator' => ' :: ',

  /** @var string Default active site theme */
  'theme' => 'fluid',

  /** @var string Default active admin theme */
  'admin_theme' => 'fluid',

  /** @var boolean Maintenance Mode */
  'maintenance_mode' => FALSE,

  /** @var string Offline message in Maintenance Mode */
  'offline_message' => '',

  /** @var string Date Time Format */
  'date_time_format' => 'Y-M-d H:i:s',

  /** @var string Date Format */
  'date_format' => 'Y-M-d',

  /** @var string Time Format */
  'time_format' => 'H:i:s',

  /** @var string Filter Default Format */
  'filter_default_format' => '1',

  /** @var string Default controller */
  'front_page' => 'welcome',

  /** @var array Default headers */
  'headers' => array(
    'X-Powered-By' => 'Gleez CMS (http://gleezcms.org)',
  ),

  /** @var string XMLRPC */
  'xmlrpc' => 'xmlrpc',

  /** @var array Meta defaults */
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
      'X-UA-Compatible' => array(
        'http_equiv' => TRUE,
        'value' => 'IE=edge,chrome=1'
      ),
	      'charset'         => 'utf-8',
	      'generator'       => 'Gleez '.Gleez::VERSION.' (http://gleezcms.org)',
	      'author'          => 'Gleez Team',
	      'copyright'       => 'Copyright (c) Gleez Technologies 2011-2013. All rights reserved.',
	      'robots'          => 'index, follow, noodp',
	      'keywords'        => 'cms, cmf, gleez, kohana, php framework, site building',
	      'description'     => 'Light, Simple, Flexible Content Management System',
	      'viewport'        => 'width=device-width, initial-scale=1.0, maximum-scale=1.0',
    ),
  ),

	/**
	 * Installed locales
	 *@var array
	 */
	'installed_locales' => array(
		'en' => array(
			'LINUX'   => 'en_US.utf-8',
			'WINDOWS' => 'english',
			'OTHER'   => 'en_US.utf-8',
		),
		'it' => array(
			'LINUX'   => 'it_IT.utf-8',
			'WINDOWS' => 'italian',
			'OTHER'   => 'it_IT.utf-8',
		),
		'ru' => array(
			'LINUX'   => 'ru_RU.utf-8',
			'WINDOWS' => 'russian',
			'OTHER'   => 'ru_RU.utf-8',
		),
		'zh' => array(
			'LINUX'   => 'zh_CN.utf-8',
			'WINDOWS' => 'chinese-simplified',
			'OTHER'   => 'ru_RU.utf-8',
		),
	),

  /** @var string Blocked ips */
  'blocked_ips' =>  NULL, //default to null, comma separated ip-addresses to block
  
  /** @var string Default timezone */
  'timezone' => 'Asia/Kolkata',

  /** @var integer Default date first day */
  'date_first_day' => 1,

  /** @var string Site Private Key */
  'gleez_private_key' => NULL, //default to null, generate a random key on installation

  /** @var string Number of seconds before password reset confirmation links expire */
  'reset_password_expiration' => 86400,

  /** @var string Default session type */
  'session_type' => 'db',

  /** @var string Define Google Analytics ID */
  'google_ua' => NULL
);
