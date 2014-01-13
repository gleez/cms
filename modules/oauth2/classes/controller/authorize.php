<?php
/**
 * Controller oAuth2 Authorize
 *
 * @package    Gleez\oAuth2
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Controller_Authorize extends Template {
	/**
	 * List of possible authentication response types.
	 * The "authorization_code" mechanism exclusively supports 'code'
	 * and the "implicit" mechanism exclusively supports 'token'.
	 *
	 * @var string
	 * @see http://tools.ietf.org/html/rfc6749#section-4.1.1
	 * @see http://tools.ietf.org/html/rfc6749#section-4.2.1
	 */
	const RESPONSE_TYPE_AUTHORIZATION_CODE = 'code';
	const RESPONSE_TYPE_ACCESS_TOKEN       = 'token';

	private $scope;
	private $state;
	private $client_id;
	private $redirect_uri;
	private $response_type;
	private $is_authorized   = FALSE;

	// These 2 vars are not part of oauth2
	private $approval_prompt = 'auto';
	private $access_type 	 = 'online';

	protected $client;
	protected $responseTypes;
	protected $config;

	/**
	 * The before() method is called before controller action
	 */
	public function before()
	{
		parent::before();

		// Load the oauth2 config
		$this->config = Config::load('oauth2')->as_array();

		// create array of supported response types
		$this->responseTypes = array(
			'code'	=> new Oauth2_ResponseType_AuthorizationCode($this->config),
			'token'	=> new Oauth2_ResponseType_AccessToken($this->config),
		);

		/**
		 * Indicates if the user should be re-prompted for consent.
		 * The default is auto, so a given user should only see the consent page for a given set of scopes 
		 * the first time through the sequence. If the value is force, then the user sees a 
		 * consent page even if they previously gave consent to your application for a given set of scopes.
		 */
		$this->approval_prompt	= $this->request->query('approval_prompt');

		// @todo not implemented so far these 4 vars
		$this->access_type		= $this->request->query('access_type');

		/** email address or sub identifier
		 * Passing this hint will either pre-fill the email box on the sign-in form or 
		 * select the proper multi-login session, thereby simplifying the login flow.
		 */
		$this->login_hint		= $this->request->query('login_hint');

		//Optional. A market string that determines how the consent UI is localized.
		$this->locale			= $this->request->query('locale');

		/** The display type to be used for the authorization page. 
		 *	Valid values are "popup", "touch", "page", or "none".
		 */
		$this->display			= $this->request->query('display');

		// Disable sidebars on oauth2
		$this->_sidebars = FALSE;
	}

	/**
	 * Redirect the user appropriately after approval.
	 *
	 * After the user has approved or denied the resource request the
	 * authorization server should call this function to redirect the user
	 * appropriately.
	 *
	 * $request
	 * The request should have the follow parameters set in the querystring:
	 * - response_type: The requested response: an access token, an
	 * authorization code, or both.
	 * - client_id: The client identifier as described in Section 2.
	 * - redirect_uri: An absolute URI to which the authorization server
	 * will redirect the user-agent to when the end-user authorization
	 * step is completed.
	 * - scope: (optional) The scope of the resource request expressed as a
	 * list of space-delimited strings.
	 * - state: (optional) An opaque value used by the client to maintain
	 * state between the request and callback.
	 *
	 * @see http://tools.ietf.org/html/rfc6749#section-4
	 *
	 * The "authorization_code" mechanism
	 * @see http://tools.ietf.org/html/rfc6749#section-4.1.1
	 *
	 * The "implicit" mechanism
	 * @see http://tools.ietf.org/html/rfc6749#section-4.2.1
	 */
	public function action_index()
	{
		try
		{
			// We repeat this, because we need to re-validate. The request could be POSTed
			// by a 3rd-party (because we are not internally enforcing NONCEs, etc)
			$this->validateAuthorizeRequest();

			// If no redirect_uri is passed in the request, use client's registered one
			if (empty($this->redirect_uri)) {
				$this->redirect_uri = $this->client['redirect_uri'];
			}

			$params = array(
				'scope'				=> $this->scope,
				'state'				=> $this->state,
				'client_id'			=> $this->client['client_id'],
				'redirect_uri'		=> $this->redirect_uri,
				'response_type'		=> $this->response_type,
				'approval_prompt'	=> $this->approval_prompt,
				'access_type'		=> $this->access_type
			);

			if( $this->request->post('oauth2') )
			{
				// check the form data to see if the user authorized the request
				$authorized = (bool) $this->request->post('authorize');
			}
			elseif( ! $authorized = (bool) $this->showAuthorizeForm($params)) 
			{
				//return to show consent approval form
				return;
			}

			if ($authorized === false) {
				$this->setRedirect($this->config['redirect_status_code'], $this->redirect_uri, $this->state, 'access_denied', "The user denied access to your application");
			}

			return $this->authorizeFinish($params, $this->redirect_uri);
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
			$this->response->body(json_encode($response));
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

	protected function authorizeFinish($params, $registered_redirect_uri)
	{
		// Complete the authorization and return the code
		$user_id 	= Auth::instance()->get_user()->id;
		$authResult = $this->responseTypes[$this->response_type]->getAuthorizeResponse($params, $user_id);

		list($redirect_uri, $uri_params) = $authResult;

		if (empty($redirect_uri) && !empty($registered_redirect_uri)) {
			$redirect_uri = $registered_redirect_uri;
		}

		$uri = $this->buildUri($redirect_uri, $uri_params);

		// return redirect response
		$this->setRedirect($this->config['redirect_status_code'], $uri);
	}

	protected function showAuthorizeForm($params)
	{
		$url = Route::get('oauth2/auth')->uri().URL::query($params);

		if ( ! Auth::instance()->logged_in())
		{
			$this->request->redirect( 
				Route::get('user')->uri(array('action' => 'login')) . URL::query( array('destination' => $url) ) 
			);
		}

		$user  = Auth::instance()->get_user();

		// Checks whether user already approved client access or not
		$consent = $this->checkConsent($params['client_id'], $user->id);

		// Check if the client should be automatically approved
		//$autoApprove = ($params['auto_approve'] === '1') ? TRUE : FALSE;
		$autoApprove = ($params['approval_prompt'] === 'force') ? FALSE : TRUE;

		/*
		 * Dispaly the "do you want to authorize?" form if previously not approved,
		 * or, if approval_promt parameter is 'force'
		 */ 
		if ( $consent === FALSE || $autoApprove === FALSE )
		{
			$view   = View::factory('oauth2/authorize')->set('client', (object) $this->client)->set('action', $url);

			$this->title = __('Welcome to the OAuth2.0 Server!');
			$this->response->body($view);

			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Pull the authorization request data out of the HTTP request.
	 * - The redirect_uri is OPTIONAL as per draft 20. But your implementation can enforce it
	 * by setting $config['enforce_redirect'] to true.
	 * - The state is OPTIONAL but recommended to enforce CSRF. Draft 21 states, however, that
	 * CSRF protection is MANDATORY. You can enforce this by setting the $config['enforce_state'] to true.
	 *
	 * The draft specifies that the parameters should be retrieved from GET, override the Response
	 * object to change this
	 *
	 * @return
	 * The authorization parameters so the authorization server can prompt
	 * the user for approval if valid.
	 *
	 * @see http://tools.ietf.org/html/rfc6749#section-4.1.1
	 * @see http://tools.ietf.org/html/rfc6749#section-10.12
	 *
	 */
	protected function validateAuthorizeRequest()
	{
		// Make sure a valid client id was supplied (we can not redirect because we were unable to verify the URI)
		if (!$client_id = $this->request->query("client_id")) {
		    // We don't have a good URI to use
		   	throw Oauth2_Exception::factory(400, 'invalid_client', "No client id supplied");

		    return false;
		}

		// Get client details
		if (!$clientData = $this->getClientDetails($client_id)) {
		    throw Oauth2_Exception::factory(400, 'invalid_client', 'The client id supplied is invalid');

		    return false;
		}

		$this->client 			 = $clientData;
		$registered_redirect_uri = isset($clientData['redirect_uri']) ? $clientData['redirect_uri'] : '';

		// Make sure a valid redirect_uri was supplied. If specified, it must match the clientData URI.
		// @see http://tools.ietf.org/html/rfc6749#section-3.1.2
		// @see http://tools.ietf.org/html/rfc6749#section-4.1.2.1
		// @see http://tools.ietf.org/html/rfc6749#section-4.2.2.1
		if ($supplied_redirect_uri = $this->request->query('redirect_uri')) {
			// validate there is no fragment supplied
			$parts = parse_url($supplied_redirect_uri);
			if (isset($parts['fragment']) && $parts['fragment']) {
			    throw Oauth2_Exception::factory(400, 'invalid_uri', 'The redirect URI must not contain a fragment');

			    return false;
			}

			// validate against the registered redirect uri(s) if available
			if ($registered_redirect_uri && !$this->validateRedirectUri($supplied_redirect_uri, $registered_redirect_uri)) {
			    throw Oauth2_Exception::factory(400, 'redirect_uri_mismatch', 'The redirect URI provided is missing or does not match');

			    return false;
			}

			$redirect_uri = $supplied_redirect_uri;
		} else {
			// use the registered redirect_uri if none has been supplied, if possible
			if (!$registered_redirect_uri) {
			    throw Oauth2_Exception::factory(400, 'invalid_uri', 'No redirect URI was supplied or stored');

			    return false;
			}

			if (count(explode(' ', $registered_redirect_uri)) > 1) {
			    throw Oauth2_Exception::factory(400, 'invalid_uri', 'A redirect URI must be supplied when multiple redirect URIs are registered');

			    return false;
			}

			$redirect_uri = $registered_redirect_uri;
		}

		// Select the redirect URI
		$response_type = $this->request->query('response_type');
		$state = $this->request->query('state');
		//if (!$scope = $this->getScopeFromRequest($request)) {
		//    $scope = $this->getDefaultScope($client_id);
		//}

		// type and client_id are required
		if (!$response_type || !in_array($response_type, array(self::RESPONSE_TYPE_AUTHORIZATION_CODE, self::RESPONSE_TYPE_ACCESS_TOKEN))) {
		    $this->setRedirect($this->config['redirect_status_code'], $redirect_uri, $state, 'invalid_request', 'Invalid or missing response type');

		    return false;
		}

		if ($response_type == self::RESPONSE_TYPE_AUTHORIZATION_CODE) {
		    if (!isset($this->responseTypes['code'])) {
		        $this->setRedirect($this->config['redirect_status_code'], $redirect_uri, $state, 'unsupported_response_type', 'authorization code grant type not supported');

		        return false;
		    }
		    if (!$this->checkRestrictedGrantType($client_id, 'authorization_code')) {
		        $this->setRedirect($this->config['redirect_status_code'], $redirect_uri, $state, 'unauthorized_client', 'The grant type is unauthorized for this client_id');

		        return false;
		    }
		    if ($this->config['enforce_redirect'] && !$redirect_uri) {
		        throw Oauth2_Exception::factory(400, 'redirect_uri_mismatch', 'The redirect URI is mandatory and was not supplied');

		        return false;
		    }
		}

		if ($response_type == self::RESPONSE_TYPE_ACCESS_TOKEN) {
		    if (!$this->config['allow_implicit']) {
		        $this->setRedirect($this->config['redirect_status_code'], $redirect_uri, $state, 'unsupported_response_type', 'implicit grant type not supported');

		        return false;
		    }
		    if (!$this->checkRestrictedGrantType($client_id, 'implicit')) {
		        $this->setRedirect($this->config['redirect_status_code'], $redirect_uri, $state, 'unauthorized_client', 'The grant type is unauthorized for this client_id');

		        return false;
		    }
		}

		// Validate that the requested scope is supported
/*		if (false === $scope) {
		    $this->setRedirect($this->config['redirect_status_code'], $redirect_uri, $state, 'invalid_client', 'This application requires you specify a scope parameter');

		    return false;
		}

		if (!is_null($scope) && !$this->scopeExists($scope, $client_id)) {
		    $this->setRedirect($this->config['redirect_status_code'], $redirect_uri, $state, 'invalid_scope', 'An unsupported scope was requested');

		    return false;
		}*/

		// Validate state parameter exists (if configured to enforce this)
		if ($this->config['enforce_state'] && !$state) {
		    $this->setRedirect($this->config['redirect_status_code'], $redirect_uri, null, 'invalid_request', 'The state parameter is required');

		    return false;
		}

		// save the input data and return true
		//$this->scope         = $scope;
		$this->state         = $state;
		$this->client_id     = $client_id;
		// Only save the SUPPLIED redirect URI (@see http://tools.ietf.org/html/rfc6749#section-4.1.3)
		$this->redirect_uri  = $supplied_redirect_uri;
		$this->response_type = $response_type;

		return true;
	}

	/**
	 * Internal method for validating redirect URI supplied
	 *
	 * @param string $inputUri
	 * The submitted URI to be validated
	 * @param string $registeredUriString
	 * The allowed URI(s) to validate against.  Can be a space-delimited string of URIs to
	 * allow for multiple URIs
	 *
	 * @see http://tools.ietf.org/html/rfc6749#section-3.1.2
	 */
	private function validateRedirectUri($inputUri, $registeredUriString)
	{
		if (!$inputUri || !$registeredUriString) {
		    return false; // if either one is missing, assume INVALID
		}

		$registered_uris = explode(' ', $registeredUriString);
		foreach ($registered_uris as $registered_uri) {
			if ($this->config['require_exact_redirect_uri']) {
				// the input uri is validated against the registered uri using exact match
				if (strcmp($inputUri, $registered_uri) === 0) {
				    return true;
				}
			} else {
				// the input uri is validated against the registered uri using case-insensitive match of the initial string
				// i.e. additional query parameters may be applied
				if (strcasecmp(substr($inputUri, 0, strlen($registered_uri)), $registered_uri) === 0) {
				    return true;
				}
			}
		}

	    return false;
	}

	protected function setRedirect($statusCode = 302, $url, $state = null, $error = null, $errorDescription = null)
	{
		$parameters = array();

		if (!is_null($error)) {
			$parameters = array(
				'error' => $error,
				'error_description' => $errorDescription,
			);
		}

		if (!is_null($state)) {
			$parameters['state'] = $state;
		}

		if (count($parameters) > 0) {
		    // add parameters to URL redirection
		    $parts = parse_url($url);
		    $sep = isset($parts['query']) && count($parts['query']) > 0 ? '&' : '?';
		    $url .= $sep . http_build_query($parameters);
		}

		$this->request->redirect($url, $statusCode);
	}

	protected function getClientDetails($id)
	{
		$client = ORM::factory('oaclient')->where('client_id', '=', $id)->find();

		if( $client->loaded() ) return $client->as_array();

		return FALSE;
	}

	public function checkRestrictedGrantType($client_id, $grant_type)
	{
		$details = $this->getClientDetails($client_id);

		if (! empty($details['grant_types'])) {
		    $grant_types = explode(' ', $details['grant_types']);

		    return in_array($grant_type, (array) $grant_types);
		}

		// if grant_types are not defined, then none are restricted
		return true;
	}

	// Checks whether user already approved client access or not
	public function checkConsent($client_id, $user_id)
	{
		$oatoken = Model::factory('oauth')->checkConsent($client_id, $user_id);

		return empty($oatoken) ? FALSE : TRUE;
	}

	/**
	 * Build the absolute URI based on supplied URI and parameters.
	 *
	 * @param $uri
	 * An absolute URI.
	 * @param $params
	 * Parameters to be append as GET.
	 *
	 * @return
	 * An absolute URI with supplied parameters.
	 *
	 * @ingroup oauth2_section_4
	 */
	private function buildUri($uri, $params)
	{
		$parse_url = parse_url($uri);

		// Add our params to the parsed uri
		foreach ($params as $k => $v) {
			if (isset($parse_url[$k])) {
				$parse_url[$k] .= "&" . http_build_query($v);
			} else {
				$parse_url[$k] = http_build_query($v);
			}
		}

		// Put humpty dumpty back together
		return
			((isset($parse_url["scheme"])) ? $parse_url["scheme"] . "://" : "")
			. ((isset($parse_url["user"])) ? $parse_url["user"]
			. ((isset($parse_url["pass"])) ? ":" . $parse_url["pass"] : "") . "@" : "")
			. ((isset($parse_url["host"])) ? $parse_url["host"] : "")
			. ((isset($parse_url["port"])) ? ":" . $parse_url["port"] : "")
			. ((isset($parse_url["path"])) ? $parse_url["path"] : "")
			. ((isset($parse_url["query"]) && !empty($parse_url['query'])) ? "?" . $parse_url["query"] : "")
			. ((isset($parse_url["fragment"])) ? "#" . $parse_url["fragment"] : "");
	}
}