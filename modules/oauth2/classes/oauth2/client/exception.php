<?php
/**
 * @package    Gleez\OAuth\Client\Exception
 * @author     Gleez Team
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 *
 */
class OAuth2_Client_Exception extends Gleez_Exception {

	/**
	 * Error codes
	 */
	const E_NO_CURL_INSTALLED = 1;
	const E_CURL_ERROR = 2;
	const E_CERTIFICATE_FILE_INVALID = 3;
	const E_NO_GRANT_TYPE_SPECIFIED = 4;
	const E_MISSING_PARAMETER = 5;
	const E_INCORRECT_PARAMETER = 6;
	const E_UNKNOWN_AUTH_TYPE = 7;
	const E_UNKNOWN_ACCESS_TOKEN_TYPE = 8;
	const E_CANT_GET_ACCESS_TOKEN = 9;
	const E_FETCH_UNSUCCESSFUL = 10;
}