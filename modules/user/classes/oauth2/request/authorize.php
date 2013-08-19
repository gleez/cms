<?php
/**
 * OAuth v2 Request Credentials
 *
 * @package    Gleez\OAuth
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class OAuth2_Request_Authorize extends OAuth2_Request {

	protected $name = 'authorize';

	public function execute(array $options = NULL)
	{
		return Request::current()->redirect($this->as_url());
	}

}
