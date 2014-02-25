<?php
/**
 * Controller oAuth2 Token
 *
 * @package    Gleez\oAuth2
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Controller_Token extends Controller
{

	protected $accessToken;
	protected $grantTypes;
	protected $clientAssertionType;
	protected $scopeUtil;
	protected $config;

	private $client;
	private $client_id;
	private $clientData;

	/**
	 * The before() method is called before controller action
	 */
	public function before()
	{
		parent::before();

		$this->auto_render = FALSE;

		// Load the oauth2 config
		$this->config = Config::load('oauth2')->as_array();

		// create array of supported grant types
		$this->grantTypes = array(
			'authorization_code' => new Oauth2_GrantType_AuthorizationCode($this->config),
			'client_credentials' => new Oauth2_GrantType_ClientCredentials($this->config, TRUE),
			'refresh_token'		 => new Oauth2_GrantType_RefreshToken($this->config),
			'password' 			 => new Oauth2_GrantType_UserCredentials($this->config),
		);
	}

	public function action_index()
	{
		try
		{
			if ($token = $this->grantAccessToken()) {
				// @see http://tools.ietf.org/html/rfc6749#section-5.1
				// server MUST disable caching in headers when tokens are involved
				$this->response->status(200);
				$this->response->headers(array('Cache-Control' => 'no-store', 'Pragma' => 'no-cache'));
				$this->response->headers('content-type',  'application/json; charset='.Kohana::$charset);

				$this->response->body( JSON::encode($token));
				return;
			}
		}
		catch(Oauth2_Exception $e) 
		{
			// Throw an exception because there was a problem with the client's request
			$response = array(
				'error'				=> $e->getError(),
				'error_description' => $e->getMessage()
			);

			$this->response->status($e->getCode());
			$this->response->headers(array('Cache-Control' => 'no-store', 'Pragma' => 'no-cache'));
			$this->response->headers('content-type',  'application/json; charset='.Kohana::$charset);

			$this->response->body(json_encode($response));
			return;
		}
		catch (Exception $e) 
		{
			/**
			 * Something went wrong!
			 *
			 * Throw an error when a non-library specific exception has been thrown
			 *
			 * You should probably show a nice error page :)
			 *
			 * Do NOT redirect the user back to the client.
			 */
			throw HTTP_Exception::factory(500, $e->getMessage());
		}
	}

	/**
	 * Grant or deny a requested access token.
	 * This would be called from the "/token" endpoint as defined in the spec.
	 * You can call your endpoint whatever you want.
	 *
	 * @see http://tools.ietf.org/html/rfc6749#section-4
	 * @see http://tools.ietf.org/html/rfc6749#section-10.6
	 * @see http://tools.ietf.org/html/rfc6749#section-4.1.3
	 *
	 * @ingroup oauth2_section_4
	 */
	protected function grantAccessToken()
	{
		if (strtolower($this->request->method()) != 'post')
		{
			$this->response->headers(array('Allow' => 'POST'));
			throw Oauth2_Exception::factory(405, 'invalid_request', 'The request method must be POST when requesting an access token', NULL, '#section-3.2');

			return FALSE;
		}

		/* Determine grant type from request
		 * and validate the request for that grant type
		 */
		if (!$grantTypeIdentifier = $this->request->post('grant_type')) {
			throw Oauth2_Exception::factory(400, 'invalid_request', 'The grant type was not specified in the request');

			return FALSE;
		}

		if (!isset($this->grantTypes[$grantTypeIdentifier])) {
			/* TODO: If this is an OAuth2 supported grant type that we have chosen not to implement, throw a 501 Not Implemented instead */
			throw Oauth2_Exception::factory(400, 'unsupported_grant_type', sprintf('Grant type "%s" not supported', $grantTypeIdentifier));

			return null;
		}

		$grantType = $this->grantTypes[$grantTypeIdentifier];

		/* Retrieve the client information from the request
		 * ClientCredentials allow for grant types which also assert the client data
		 * in which case ClientCredentials is handled in the validateRequest method
		 *
		 * @see OAuth2\GrantType\ClientCredentials
		 */
		if (!$grantType instanceof Oauth2_GrantType_ClientCredentials) 
		{
			$check = new Oauth2_GrantType_ClientCredentials($this->config, FALSE);

			if ( ! $check->validateRequest($this->request, $this->response) ) {
				return null;
			}

			$clientId = $check->getClientId();
		}

		/* Retrieve the grant type information from the request
		 * The GrantTypeInterface object handles all validation
		 * If the object is an instance of Oauth2_GrantType_ClientCredentials,
		 * That logic is handled here as well
		 */
		if ( ! $grantType->validateRequest($this->request, $this->response) ) {
			return null;
		}

		if ($grantType instanceof Oauth2_GrantType_ClientCredentials) 
		{
			$clientId = $grantType->getClientId();
		}
		else
		{
			// validate the Client ID (if applicable)
			if (!is_null($storedClientId = $grantType->getClientId()) && $storedClientId != $clientId) {
				throw Oauth2_Exception::factory(400, 'invalid_grant', sprintf('%s doesn\'t exist or is invalid for the client', $grantTypeIdentifier));

				return null;
			}
		}

		$requestedScope = NULL;
		/*
		 * Validate the scope of the token
		 * If the grant type returns a value for the scope,
		 * as is the case with the "Authorization Code" grant type,
		 * this value must be verified with the scope being requested
		 */
	/*	$availableScope = $grantType->getScope();
		if (!$requestedScope = $this->getScopeFromRequest($request)) {
			if (!$availableScope) {
			    if (false === $defaultScope = $this->getDefaultScope($clientId)) {
					throw Oauth2_Exception::factory(400, 'invalid_scope', 'This application requires you specify a scope parameter');

					return null;
				}
			}

			$requestedScope = $availableScope ? $availableScope : $defaultScope;
		}

		if (($requestedScope && !$this->scopeExists($requestedScope, $clientId))
			|| ($availableScope && !$this->checkScope($requestedScope, $availableScope))) {
			throw Oauth2_Exception::factory(400, 'invalid_scope', 'An unsupported scope was requested');

			return null;
		}*/

		return $grantType->createAccessToken($clientId, $grantType->getUserId(), $requestedScope);
	}

}