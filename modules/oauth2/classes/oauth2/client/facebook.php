<?php
/**
 * @package    Gleez\OAuth\Client\Facebook
 * @author     Gleez Team
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 *
 */
class OAuth2_Client_Facebook extends OAuth2_Client {

	/**
	 * Return the authorization endpoint
	 *
	 * @return  string
	 */
	public function get_authorization_endpoint()
	{
		return 'https://graph.facebook.com/oauth/authorize';
	}

	/**
	 * Return the access token endpoint
	 *
	 * @return  string
	 */
	public function get_access_token_endpoint()
	{
		return 'https://graph.facebook.com/oauth/access_token';
	}

	/**
	 * Return the user profile service url
	 *
	 * @return  string
	 */
	public function get_user_profile_service_url()
	{
		return 'https://graph.facebook.com/me';
	}

	/**
	 * Get user data
	 *
	 * @return  array
	 * @throws  OAuth2_Exception
	 */
	public function get_user_data()
	{
		$url = $this->get_user_profile_service_url();
		$response = $this->fetch($url);

		return $this->parseResponse($response['result']);
	}

	protected function parseResponse($response)
	{
		$data     = array();

		if( isset($response['email']) )
		{
			$data['id']     = $response['id'];
			$data['email']  = $response['email'];
			$data['nick']   = $response['name'];
			$data['link']   = (isset($response['link']) && $response['link'] != NULL) ? $response['link'] : '';
			$data['gender'] = (isset($response['gender']) && $response['gender'] != NULL) ? $response['gender'] : '';
		}

		return $data;
	}
}