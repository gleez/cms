<?php
/**
 * OAuth v2 Provider GitHub
 *
 * @package    Gleez\OAuth
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class OAuth2_Provider_Github extends OAuth2_Provider {

	public $name = 'github';

	public function url_authorize()
	{
		return 'https://github.com/login/oauth/authorize';
	}

	public function url_access_token()
	{
		return 'https://github.com/login/oauth/access_token';
	}

}
