<?php
/**
 * Helper OAuth2 Authorization Code Grant Type
 *
 * @package    Gleez\oAuth2
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Oauth2_GrantType_AuthorizationCode implements Oauth2_GrantType_Interface
{
	protected $authCode;
	protected $config;
	protected $request;
	protected $response;

	public function __construct(array $config = array())
	{
		$this->config = $config;
	}

	public function getQuerystringIdentifier()
	{
		return 'authorization_code';
	}

	public function validateRequest(Request $request, Response $response)
	{
		$this->request  = $request;
		$this->response = $response;

		if (!$request->post('code')) {
			throw Oauth2_Exception::factory(400, 'invalid_request', 'Missing parameter: "code" is required');

			return false;
		}

		$code = $request->post('code');

		if (!$authCode = $this->getAuthorizationCode($code)) {
			throw Oauth2_Exception::factory(400, 'invalid_grant', 'Authorization code doesn\'t exist or is invalid for the client');

			return false;
		}

		/*
		 * 4.1.3 - ensure that the "redirect_uri" parameter is present if the "redirect_uri" parameter was included in the initial authorization request
		 * @uri - http://tools.ietf.org/html/rfc6749#section-4.1.3
		 */
		if (isset($authCode['redirect_uri']) && $authCode['redirect_uri']) {
			if (!$request->post('redirect_uri') || urldecode($request->post('redirect_uri')) != $authCode['redirect_uri']) {
				throw Oauth2_Exception::factory(400, 'redirect_uri_mismatch', "The redirect URI is missing or do not match");

				return false;
			}
		}

		if ($authCode["expires"] < time()) {
			throw Oauth2_Exception::factory(400, 'invalid_grant', "The authorization code has expired");
			//throw new Oauth2_Exception(400, 'invalid_grant', "The authorization code has expired");

			return false;
		}

		if (!isset($authCode['code'])) {
			$authCode['code'] = $code; // used to expire the code after the access token is granted
		}

		$this->authCode = $authCode;

	    return true;
	}

	public function getClientId()
	{
		return $this->authCode['client_id'];
	}

	public function getUserId()
	{
		return isset($this->authCode['user_id']) ? $this->authCode['user_id'] : NULL;
	}

	public function getScope()
	{
		return isset($this->authCode['scope']) ? $this->authCode['scope'] : NULL;
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

	protected function getAuthorizationCode($code)
	{
		$result = Model::factory('oauth')->getAuthorizationCode($code);

		return $result;
	}

}