<?php
/**
 * Helper OAuth2 Refresh Token Grant Type
 *
 * @package    Gleez\oAuth2
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Oauth2_GrantType_RefreshToken implements Oauth2_GrantType_Interface
{
	protected $refreshToken;
	protected $config;
	protected $request;
	protected $response;

	public function __construct(array $config = array())
	{
		$this->config = $config;
	}

	public function getQuerystringIdentifier()
	{
		return 'refresh_token';
	}

	public function validateRequest(Request $request, Response $response)
	{
		$this->request  = $request;
		$this->response = $response;

		if (!$request->post('refresh_token')) {
			throw Oauth2_Exception::factory(400, 'invalid_request', 'Missing parameter: "refresh_token" is required');

			return false;
		}

		$token = $request->post('refresh_token');

		if (!$refreshToken = $this->getRefreshToken($token)) {
			throw Oauth2_Exception::factory(400, 'invalid_grant', 'Invalid refresh token');

			return false;
		}

		if ($refreshToken["refresh_expires"] < time()) {
			throw Oauth2_Exception::factory(400, 'invalid_grant', 'Refresh token has expired');

		    return false;
		}

		// store the refresh token locally so we can delete it when a new refresh token is generated
		$this->refreshToken = $refreshToken;

	    return true;
	}

	public function getClientId()
	{
		return $this->refreshToken['client_id'];
	}

	public function getUserId()
	{
		return isset($this->refreshToken['user_id']) ? $this->refreshToken['user_id'] : NULL;
	}

	public function getScope()
	{
		return isset($this->refreshToken['scope']) ? $this->refreshToken['scope'] : NULL;
	}

	public function createAccessToken($client_id, $user_id, $scope = NULL)
	{
		try
		{
			$issueRefreshToken = Config::get('oauth2.includeRefreshToken', true);
			return Model::factory('oauth')->createAccessToken($client_id, $user_id, $scope, $issueRefreshToken);
		}
		catch (Exception $e)
		{
			throw Oauth2_Exception::factory(500, 'server_error', 'The Token server encountered an unexpected condition which prevented it from fulfilling the request.');
		}
	}

	protected function getRefreshToken($token)
	{
		return Model::factory('oauth')->getRefreshToken($token);
	}

}