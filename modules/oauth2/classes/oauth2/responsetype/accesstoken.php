<?php
/**
 * Helper OAuth2 Token Response Type (implicit grant type)
 *
 * @package    Gleez\oAuth2
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Oauth2_ResponseType_AccessToken
{
	protected $config;
	protected $request;
	protected $response;

	public function __construct(array $config = array())
	{
		$this->config = $config;
	}

	public function getAuthorizeResponse($params, $user_id = null)
	{
		// build the URL to redirect to
		$result = array('query' => array());

		$params += array('scope' => null, 'state' => null);

		$result["fragment"] = $this->createAccessToken($params['client_id'], $user_id, $params['redirect_uri'], $params['scope']);

		if (isset($params['state'])) 
		{
			$result["fragment"]["state"] = $params['state'];
		}

		return array($params['redirect_uri'], $result);
	}

	/**
	 * Handle the creation of access token, also issue refresh token if supported / desirable.
	 *
	 * @param $client_id
	 * Client identifier related to the authorization code
	 * @param $user_id
	 * User ID associated with the authorization code
	 * @param $redirect_uri
	 * An absolute URI to which the authorization server will redirect the
	 * user-agent to when the end-user authorization step is completed.
	 * @param $scope
	 * (optional) Scopes to be stored in space-separated string.
	 *
	 * @see http://tools.ietf.org/html/rfc6749#section-5
	 * @ingroup oauth2_section_5
	 */
	protected function createAccessToken($client_id, $user_id, $redirect_uri, $scope = NULL)
	{
		try
		{
			/**
			 * Client Credentials Grant does NOT include a refresh token
			 *
			 * @see http://tools.ietf.org/html/rfc6749#section-4.4.3
			 */
			$includeRefreshToken = false;
			return Model::factory('oauth')->createAccessToken($client_id, $user_id, $scope, $includeRefreshToken);
		}
		catch (Exception $e)
		{
			throw Oauth2_Exception::factory(500, 'server_error', 'The Token server encountered an unexpected condition which prevented it from fulfilling the request.');
		}
	}
}