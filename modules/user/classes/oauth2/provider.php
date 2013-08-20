<?php
/**
 * OAuth v2  Provider
 *
 * @package    Gleez\OAuth
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
abstract class OAuth2_Provider {

	public static function factory($name, array $options = NULL)
	{
		$class = 'OAuth2_Provider_'.$name;

		return new $class($options);
	}

	abstract public function url_authorize();

	abstract public function url_access_token();

	public $name;

	public function url_refresh_token()
	{
		// By default its the same as access token URL
		return $this->url_access_token();
	}

	public function authorize_url(OAuth2_Client $client, array $params = NULL)
	{
		// Create a new GET request for a request token with the required parameters
		$request = OAuth2_Request::factory('authorize', 'GET', $this->url_authorize(), array(
			'response_type' => 'code',
			'client_id'     => $client->id,
			'redirect_uri'  => $client->callback,
		));

		if ($params)
		{
			// Load user parameters
			$request->params($params);
		}

		return $request->as_url();
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

		$response = $request->execute();

		//Session::instance()->set('refresh_token', $response->param('refresh_token') );

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

		return $request->execute();
	}

}
