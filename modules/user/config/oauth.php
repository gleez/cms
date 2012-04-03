<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Configuration for OAuth providers.
 */
return array(
	/**
	 * Twitter applications can be registered at https://twitter.com/apps.
	 * You will be given a "consumer key" and "consumer secret", which must
	 * be provided when making OAuth requests.
	 */
	// 'twitter' => array(
	// 	'key' => 'your consumer key',
	// 	'secret' => 'your consumer secret'
	// ),
	/**
	 * Github applications can be registered at https://github.com/account/applications/new.
	 * You will be given a "client id" and "client secret", which must
	 * be provided when making OAuth2 requests.
	 */
	// 'github' => array(
	// 	'id' => 'your client id',
	// 	'secret' => 'your client secret'
	// ),
        
	'facebook' => array(
		'id' => 'your client id',
		'secret' => 'your client secret',
                'callback' => URL::site('/oauth/facebook/callback', 'http'),
                'scope' => array('scope' => 'email,read_stream,publish_stream'),
	),
	'google' => array(
		'id' => 'your client id',
		'secret' => 'your client secret',
                'callback' => URL::site('/oauth/facebook/callback', 'http'),
                'scope' => array('scope' => 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email'),
	),
        'live' => array(
		'id' => 'your client id',
		'secret' => 'your client secret',
                'callback' => URL::site('/oauth/live/callback', 'http'),
                'scope' => array('scope' => 'wl.basic,wl.birthday,wl.emails,wl.offline_access,wl.signin'),
	),
);