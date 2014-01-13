<?php
/**
 * Helper OAuth2 Password Grant Type
 *
 * @package    Gleez\oAuth2
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Oauth2_GrantType_UserCredentials implements Oauth2_GrantType_Interface
{
	protected $userInfo;
	protected $config;
	protected $request;
	protected $response;

	public function __construct(array $config = array())
	{
		$this->config = $config;
	}

	public function getQuerystringIdentifier()
	{
		return 'password';
	}

	public function validateRequest(Request $request, Response $response)
	{
		$this->request  = $request;
		$this->response = $response;

		if (!$request->post("password") || !$request->post("username")) {
			throw Oauth2_Exception::factory(400, 'invalid_request', 'Missing parameters: "username" and "password" required');

			return NULL;
		}

		if (! $userInfo = $this->checkUserCredentials($request->post("username"), $request->post("password"))) {
			throw Oauth2_Exception::factory(400, 'invalid_grant', 'Invalid username and password combination');

			return NULL;
		}

		if (empty($userInfo)) {
			throw Oauth2_Exception::factory(400, 'invalid_grant', 'Unable to retrieve user information');

			return NULL;
		}

		if (!isset($userInfo['id'])) {
			throw new Gleez_Exception("you must set the user_id on the array returned by checkUserCredentials");
			//$this->setError(500, 'server_error', 'you must set the user_id on the array returned by checkUserCredentials');
		}

		$this->userInfo = $userInfo;

		return TRUE;
	}

	public function getClientId()
	{
		return NULL;
	}

	public function getUserId()
	{
		//return isset($this->userInfo['user_id']) ? $this->userInfo['user_id'] : NULL;
		return isset($this->userInfo['id']) ? $this->userInfo['id'] : NULL;
	}

	public function getScope()
	{
		return isset($this->userInfo['scope']) ? $this->userInfo['scope'] : NULL;
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

	protected function checkUserCredentials($name, $pass)
	{
		return Model::factory('oauth')->checkUserCredentials($name, $pass);
	}
}