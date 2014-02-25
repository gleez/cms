<?php
/**
 * Helper OAuth2 Client Credentials Grant Type
 *
 * @package    Gleez\oAuth2
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Oauth2_GrantType_ClientCredentials implements Oauth2_GrantType_Interface
{

	private $clientData;

	protected $config;
	protected $request;
	protected $response;

	public function __construct(array $config = array(), $is_grant = FALSE)
	{
		/** We use the same class for validating request for other grants
		 *	make sure this is true only if the request grant_type is 'client_credentials'
		 */
		if($is_grant == TRUE)
		{
			/**
			 * The client credentials grant type MUST only be used by confidential clients
			 *
			 * @see http://tools.ietf.org/html/rfc6749#section-4.4
			 */
			$config['allow_public_clients'] = false;
		}

		// Load the oauth2 config
		$this->config = $config;
	}

	public function getQuerystringIdentifier()
	{
		return 'client_credentials';
	}

	public function validateRequest(Request $request, Response $response)
	{
		$this->request  = $request;
		$this->response = $response;

		if (!$clientData = $this->getClientCredentials()) {
			throw Oauth2_Exception::factory(400, 'invalid_client', 'Client credentials are required');

			return FALSE;
		}

		if (!isset($clientData['client_id'])) 
		{
			throw Oauth2_Exception::factory(400, 'invalid_client', 'Missing parameter: "client_id" is required');

			return FALSE;
		}

		if (!isset($clientData['client_secret']) || empty($clientData['client_secret'])) 
		{
			if (!$this->config['allow_public_clients']) 
			{
				throw Oauth2_Exception::factory(400, 'invalid_client', 'Client credentials are required');

				return FALSE;
			}

			// Is this a public client?
			if ( ! $this->getClientDetails($clientData['client_id']))
			{
				throw Oauth2_Exception::factory(400, 'invalid_client', 'This client is invalid or must authenticate using a client secret');

				return FALSE;
			}
		}
		elseif ($this->checkClientCredentials($clientData['client_id'], $clientData['client_secret']) === false) {
			throw Oauth2_Exception::factory(400, 'invalid_client', 'The client credentials are invalid');

			return false;
		}

		if (! $this->checkRestrictedGrantType($clientData['client_id'], $this->request->post('grant_type'))) {
			throw Oauth2_Exception::factory(400, 'unauthorized_client', 'The grant type is unauthorized for this client_id');

			return false;
		}

		$this->clientData = $this->getClientDetails($clientData['client_id']);

		return true;
	}

	public function getClientId()
	{
		return $this->clientData['client_id'];
	}

	public function getUserId()
	{
		return isset($this->clientData['user_id']) ? $this->clientData['user_id'] : NULL;
	}

	public function getScope()
	{
		return isset($this->clientData['scope']) ? $this->clientData['scope'] : NULL;
	}

	public function createAccessToken($client_id, $user_id, $scope = NULL)
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

	/**
	 * Internal function used to get the client credentials from HTTP basic
	 * auth or POST data.
	 *
	 * According to the spec (draft 20), the client_id can be provided in
	 * the Basic Authorization header (recommended) or via GET/POST.
	 *
	 * @return
	 * A list containing the client identifier and password, for example
	 * @code
	 * return array(
	 *     "client_id"     => CLIENT_ID,        // REQUIRED the client id
	 *     "client_secret" => CLIENT_SECRET,    // OPTIONAL the client secret (may be omitted for public clients)
	 * );
	 * @endcode
	 *
	 * @see http://tools.ietf.org/html/rfc6749#section-2.3.1
	 *
	 * @ingroup oauth2_section_2
	 */
	protected function getClientCredentials()
	{
		if (isset($_SERVER['PHP_AUTH_USER']) && ! is_null($clientId = $_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) && ! is_null($clientSecret = $_SERVER['PHP_AUTH_PW'])) 
		{
			return array('client_id' => $clientId, 'client_secret' => $clientSecret);
		} 

		if ($this->config['allow_credentials_in_request_body']) {
			// Using POST for HttpBasic authorization is not recommended, but is supported by specification
			if (!is_null($this->request->post('client_id'))) {
				/**
				 * client_secret can be null if the client's password is an empty string
				 * @see http://tools.ietf.org/html/rfc6749#section-2.3.1
				 */
				return array('client_id' => $this->request->post('client_id'), 'client_secret' => $this->request->post('client_secret'));
			}
		}

	/*  if ($response) {
	        $message = $this->config['allow_credentials_in_request_body'] ? ' or body' : '';
	        $this->setError(400, 'invalid_client', 'Client credentials were not found in the headers'.$message);
	    }*/

	    return FALSE;
	}

	public function getClientDetails($id)
	{
		return Model::factory('oauth')->getClientDetails($id);
	}

	public function checkClientCredentials($id, $secret)
	{
		return Model::factory('oauth')->checkClientCredentials($id, $secret);
	}

	public function checkRestrictedGrantType($client_id, $grant_type)
	{
		return Model::factory('oauth')->checkRestrictedGrantType($client_id, $grant_type);
	}
}