<?php defined('SYSPATH') OR die('No direct access allowed.');

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
