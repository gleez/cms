<?php
/**
 * Helper OAuth2 Code Response Type
 *
 * @package    Gleez\oAuth2
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Oauth2_ResponseType_AuthorizationCode
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

		$result["query"]["code"] = $this->createAuthorizationCode($params['client_id'], $user_id, $params['redirect_uri'], $params['scope']);

		if (isset($params['state'])) 
		{
			$result["query"]["state"] = $params['state'];
		}

		return array($params['redirect_uri'], $result);
	}

	/**
	 * Handle the creation of the authorization code.
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
	 * @see http://tools.ietf.org/html/rfc6749#section-4
	 * @ingroup oauth2_section_4
	 */
	protected function createAuthorizationCode($client_id, $user_id, $redirect_uri, $scope = null)
	{
		return Model::factory('oauth')->createAuthorizationCode($client_id, $user_id, $redirect_uri, $scope);
		
	}
}