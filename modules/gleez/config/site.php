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
  'site_email' => 'webmaster@example.com',

  /** @var string Site url used for background tasks */
  'site_url' => 'www.example.com',

  /** @var string Site mission */
  'site_mission' => 'Innovate IT',

  /** @var string Site title seperator */
  'title_separator' => ' :: ',

  /** @var string Default active site theme */
  'theme' => 'fluid',

  /** @var string Default active admin theme */
  'admin_theme' => 'fluid',

  /** @var boolean Maintenance Mode */
  'maintenance_mode' => FALSE,

  /** @var boolean Offline message in Maintenance Mode */
  'offline_message' => FALSE,

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
  'xmlrpc'  => 'xmlrpc',

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
        'title' => 'Gleez'
      ),
    ),
    'tags' => array(
      'X-UA-Compatible' => array(
        'http_equiv' => TRUE,
        'value' => 'IE=edge,chrome=1'
      ),
      'charset'         => 'text/html; charset=UTF-8',
      'generator'       => 'Gleez '.Gleez::VERSION.' (http://gleezcms.org)',
      'author'          => 'Gleez',
      'copyright'       => 'Copyright Gleez 2011-2013. All rights reserved.',
      'robots'          => 'index, follow, noodp',
      'viewport'        => 'width=device-width, initial-scale=1.0, maximum-scale=1.0',
    ),
  ),

  /** @var array Installed locales */
  'installed_locales' => array(
    'en',
    'ru',
    'zh',
  ),

  /** @var string Default timezone */
  'timezone' => 'Asia/Kolkata',

  /** @var integer Default date first day */
  'date_first_day' => 1,

  /** @var string Site Private Key */
  'gleez_private_key' => NULL, //default to null, generate a random key on installation

  /** @var string Number of seconds before password reset confirmation links expire */
  'reset_password_expiration' => 86400,

  /** @var string Default session type */
  'session_type' => 'db'

);
