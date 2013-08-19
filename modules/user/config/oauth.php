<?php
/**
 * Configuration for OAuth providers.
 *
 * @link  http://en.wikipedia.org/wiki/OAuth  Wikipedia OAuth
 */
return array(
  /**
   * Twitter applications can be registered at https://dev.twitter.com/apps.
   * You will be given a "consumer key" and "consumer secret", which must
   * be provided when making OAuth requests.
   *
   * @link https://dev.twitter.com/docs/auth/oauth/faq OAuth FAQ
   */
  'twitter' => array(
    'key' => 'your consumer key',
    'secret' => 'your consumer secret'
  ),
  /**
   * Github applications can be registered at https://github.com/settings/applications/new.
   * You will be given a "client id" and "client secret", which must
   * be provided when making OAuth2 requests.
   *
   * @link http://developer.github.com/v3/oauth/ Github OAuth
   */
  'github' => array(
    'id' => 'your client id',
    'secret' => 'your client secret'
  ),
  /**
   * Facebook
   */
  'facebook' => array(
    'id' => 'your client id',
    'secret' => 'your client secret',
    'callback' => URL::site('/oauth/facebook/callback', 'http'),
    'scope' => array('scope' => 'email,read_stream,publish_stream'),
  ),
  /**
   * Google
   */
  'google' => array(
    'id' => 'your client id',
    'secret' => 'your client secret',
    'callback' => URL::site('/oauth/facebook/callback', 'http'),
    'scope' => array('scope' => 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email'),
  ),
  /**
   * Windows Live
   */
  'live' => array(
    'id' => 'your client id',
    'secret' => 'your client secret',
    'callback' => URL::site('/oauth/live/callback', 'http'),
    'scope' => array('scope' => 'wl.basic,wl.birthday,wl.emails,wl.offline_access,wl.signin'),
  ),
);
