<?php
/**
 * OAuth v2 Provider Live
 *
 * @package    Gleez\OAuth
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class OAuth2_Provider_Live extends OAuth2_Provider {

	public $name = 'live';

	public function url_authorize()
	{
		return 'https://oauth.live.com/authorize';
	}

	public function url_access_token()
	{
		return 'https://oauth.live.com/token';
	}

	public function access_token(OAuth2_Client $client, $code, array $params = NULL)
	{
		$request = OAuth2_Request::factory('token', 'POST', $this->url_access_token(), array(
			'grant_type'    => 'authorization_code',
			'code'          => $code,
			'client_id'     => $client->id,
			'client_secret' => $client->secret,
		));

		if ($client->callback)
		{
			$request->param('redirect_uri', $client->callback);
		}

		if ($params)
		{
			// Load user parameters
			$request->params($params);
		}
		$request->format('json');

		$response = $request->execute();

		Session::instance()->set('refresh_token', $response->param('refresh_token') );
		return OAuth2_Token::factory('access', array(
			'token' => $response->param('access_token')
		));
	}

	public function get_tokens(OAuth2_Client $client, $code, array $params = NULL)
	{
		$request = OAuth2_Request::factory('token', 'POST', $this->url_access_token(), array(
			'grant_type'    => 'authorization_code',
			'code'          => $code,
			'client_id'     => $client->id,
			'client_secret' => $client->secret,
		));

		if ($client->callback)
		{
			$request->param('redirect_uri', $client->callback);
		}

		if ($params)
		{
			// Load user parameters
			$request->params($params);
		}
		$request->format('json');

		return $request->execute();
	}

	public function access_profile( $token )
	{
		$graph_url = "https://apis.live.net/v5.0/me";

		$request = OAuth2_Request::factory('data', 'GET', $graph_url, array(
                                                'access_token'    => $token,
                                        ))->execute();

		return $response = JSON::decode($request);
	}

}
