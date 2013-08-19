<?php
/**
 * OAuth v2 Request Token
 *
 * @package    Gleez\OAuth
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class OAuth2_Request_Token extends OAuth2_Request {

	protected $name = 'token';
	protected $format = FALSE;

	public function execute(array $options = NULL)
	{
		$body = parent::execute($options);

		if($this->format == 'json')
		{
			$body = JSON::decode($body);
		}

		return OAuth2_Response::factory($body);
	}

}
