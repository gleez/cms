<?php
/**
 * Interface for all OAuth2 Grant Types
 *
 * @package    Gleez\oAuth2
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
interface Oauth2_GrantType_Interface
{
	public function validateRequest(Request $request, Response $response);
	public function getClientId();
	public function getUserId();
	public function getScope();
	public function createAccessToken($client_id, $user_id, $scope);
}