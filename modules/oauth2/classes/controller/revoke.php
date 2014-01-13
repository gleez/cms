<?php
/**
 * Controller oAuth2 Revoke
 *
 * @package    Gleez\oAuth2
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Controller_Revoke extends Template {
	
	protected $token_info;
	protected $token;
	
	/**
	 * The before() method is called before controller action
	 */
	public function before()
	{
		parent::before();

		$this->auto_render = FALSE;

		// Load the oauth2 config
		$this->config = Config::load('oauth2')->as_array();
	}
	
	public function action_index()
	{
		try
		{
			// Validating
			$this->validateRevokeRequest();
			
			if ($this->token_info['access_token'] == $this->token && ! empty($this->token_info['refresh_token']))
			{
				$result = Model::factory('oauth')->revoke_access_refresh($this->token);
			}
			elseif ($this->token_info['access_token'] == $this->token && empty($this->token_info['refresh_token']))
			{
				$result = Model::factory('oauth')->revoke_access($this->token);
			}
			elseif ($this->token_info['refresh_token'] == $this->token)
			{
				$result = Model::factory('oauth')->revoke_refresh($this->token);
			}

			$this->response->body( json_encode(array('Response' => "Status Code: 200")) );
			return;
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
		catch(Exception $e) 
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
	
	protected function validateRevokeRequest()
	{
		if (!$token = $this->request->query("token")) {
			// We don't have a good URI to use
			throw Oauth2_Exception::factory(400, 'invalid_request', "No Token supplied");

			return false;
		}
		
		$this->token = $token;
		
		if (!$result = Model::factory('oauth')->isValidRevoke($token)) {
			throw Oauth2_Exception::factory(400, 'invalid_grant', "Token invalid");

			return false;
		}
		
		$this->token_info = $result[0];
		return TRUE;
	}
}