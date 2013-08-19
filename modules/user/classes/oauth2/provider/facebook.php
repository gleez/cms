<?php
/**
 * OAuth v2 Provider Facebook
 *
 * @package    Gleez\OAuth
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class OAuth2_Provider_Facebook extends OAuth2_Provider {

	public $name = 'facebook';

	public function url_authorize()
	{
		return 'https://www.facebook.com/dialog/oauth';
	}

	public function url_access_token()
	{
		return 'https://graph.facebook.com/oauth/access_token';
	}

	public function access_profile( $token )
	{
		$graph_url = "https://graph.facebook.com/me";

		$request = OAuth2_Request::factory('data', 'GET', $graph_url, array(
                                                'access_token'    => $token,
                                        ))->execute();

		return $response = JSON::decode($request);
	}

}
