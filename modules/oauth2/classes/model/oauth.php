<?php
/**
 * Oauth2 Model
 *
 * @package    Gleez\oAuth2
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Model_Oauth extends Model_Database {

	public function checkConsent($client_id, $user_id)
	{
		$table = Config::get('oauth2.storage.token_table');

		$oatoken = DB::query(Database::SELECT, "SELECT * FROM $table WHERE client_id = :client_id AND user_id = :user_id LIMIT 1")
				->parameters(array(
					':client_id' => $client_id,
					':user_id' => $user_id,
				))
				->execute()->as_array();

		return empty($oatoken) ? FALSE : TRUE;
	}

	public function checkClientCredentials($client_id, $client_secret = NULL)
	{
		$client = $this->getClientDetails($client_id);

		return ($client && $client['client_secret'] == $client_secret) ? TRUE : FALSE;
	}

	public function getClientDetails($client_id)
	{
		$table = Config::get('oauth2.storage.client_table');

		$result = DB::query(Database::SELECT, "SELECT * FROM $table WHERE client_id = :client_id LIMIT 1;")
					->parameters(array(
						':client_id' => $client_id,
					))
					->execute()
					->as_array();

		return $result ? $result[0] : FALSE;
	}

	public function checkRestrictedGrantType($client_id, $grant_type)
	{
		$details = $this->getClientDetails($client_id);

		if ($details && ! empty($details['grant_types'])) {
			$grant_types = explode(' ', $details['grant_types']);

		    return in_array($grant_type, (array) $grant_types);
		}

		// if grant_types are not defined, then none are restricted
		return TRUE;
	}

	public function getAccessToken($token)
	{
		$table = Config::get('oauth2.storage.token_table');

		$result = DB::query(Database::SELECT, "SELECT * FROM $table WHERE access_token = :token LIMIT 1;")
					->parameters(array(
						':token' => $token,
					))
					->execute()
					->as_array();

		return $result ? $result[0] : FALSE;
	}

	public function setAccessToken($access_token, $client_id, $user_id, $expires, $scope = NULL)
	{
		$table = Config::get('oauth2.storage.token_table');

		if ($this->getAccessToken($access_token))
		{
			$result = DB::query(Database::UPDATE, "UPDATE $table SET client_id = :client_id, access_expires = :expires, user_id = :user_id, scope = :scope WHERE access_token = :access_token;");
		}
		else
		{
			$result = DB::query(Database::INSERT, "INSERT INTO $table(access_token, client_id, access_expires, user_id, scope) VALUES(:access_token, :client_id, :expires, :user_id, :scope);");
		}

		$result = $result->parameters(array(
					':client_id' => $client_id,
					':expires' => $expires,
					':user_id' => $user_id,
					':scope' => $scope,
					':access_token' => $access_token,
		        ))
		        ->execute();

		return $result;
	}

	public function getAuthorizationCode($code)
	{
		$table = Config::get('oauth2.storage.code_table');

		$result = DB::query(Database::SELECT, "SELECT * FROM $table WHERE code = :code LIMIT 1;")
					->parameters(array(
						':code' => $code,
					))
					->execute()
					->as_array();

		return $result ? $result[0] : FALSE;
	}

	public function setAuthorizationCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = NULL)
	{
		$table = Config::get('oauth2.storage.code_table');

		if ($this->getAuthorizationCode($code))
		{
			$result = DB::query(Database::UPDATE, "UPDATE $table SET client_id = :client_id, user_id = :user_id, redirect_uri = :redirect_uri, expires = :expires, scope = :scope WHERE code = :code;");
		}
		else
		{
			$result = DB::query(Database::INSERT, "INSERT INTO $table(code, client_id, user_id, redirect_uri, expires, scope) VALUES(:code, :client_id, :user_id, :redirect_uri, :expires, :scope);");
		}

		$result = $result->parameters(array(
					':client_id' => $client_id,
					':user_id' => $user_id,
					':redirect_uri' => $redirect_uri,
					':expires' => $expires,
					':scope' => $scope,
					':code' => $code,
		        ))
		        ->execute();

		return $result;
	}

	public function expireAuthorizationCode($code)
	{
		$table = Config::get('oauth2.storage.code_table');

		$result = DB::query(Database::DELETE, "DELETE FROM $table WHERE code = :code;")
					->parameters(array(
						':code' => $code,
					))
					->execute();

		return $result;
	}

	public function createAuthorizationCode($client_id, $user_id, $redirect_uri, $scope = null)
	{
		$table = Config::get('oauth2.storage.code_table');

		$code_exists  = DB::query(Database::SELECT, "SELECT * FROM $table WHERE client_id = :client_id AND user_id = :user_id LIMIT 1;")
					->parameters(array(
						':client_id' => $client_id,
						':user_id' => $user_id,
					))
					->execute()
					->as_array();
					
		$code    = Auth::instance()->hash( uniqid($client_id . mt_rand() . microtime() . $user_id, TRUE));
		$expires = time() + Config::get('oauth2.access_lifetime', 30);
	
		if ($code_exists)
		{
			$result = DB::query(Database::UPDATE, "UPDATE $table SET code = :code, redirect_uri = :redirect_uri, expires = :expires, scope = :scope WHERE client_id = :client_id AND user_id = :user_id;");
			$result = $result->parameters(array(
					':client_id' => $client_id,
					':user_id' => $user_id,
					':redirect_uri' => $redirect_uri,
					':expires' => $expires,
					':scope' => $scope,
					':code' => $code,
		        ))
		        ->execute();
		}
		else
		{
			$created = time();
			$result = DB::query(Database::INSERT, "INSERT INTO $table(code, client_id, user_id, redirect_uri, expires, scope, created) VALUES(:code, :client_id, :user_id, :redirect_uri, :expires, :scope, :created);");
			$result = $result->parameters(array(
					':client_id' => $client_id,
					':user_id' => $user_id,
					':redirect_uri' => $redirect_uri,
					':expires' => $expires,
					':scope' => $scope,
					':code' => $code,
					':created' => $created
		        ))
		        ->execute();
		}
		

		return $code;
	}
	
	public function checkUserCredentials($username, $password)
	{
		$table  = Config::get('oauth2.storage.user_table');
		$pass   = Auth::instance()->hash($password);
		$status = 1;
		
		$result = DB::query(Database::SELECT, "SELECT * FROM $table WHERE name = :name AND pass = :pass AND status = :status LIMIT 1;")
					->parameters(array(
						':name'   => $username,
						':pass'   => $pass,
						':status' => $status
					))
					->execute()
					->as_array();

		return $result ? array('user_id' => $result[0]['name'], 'id' => $result[0]['id']) : FALSE;
	}

	public function getRefreshToken($token)
	{
		$table = Config::get('oauth2.storage.token_table');

		$result = DB::query(Database::SELECT, "SELECT * FROM $table WHERE refresh_token = :token LIMIT 1;")
					->parameters(array(
						':token' => $token,
					))
					->execute()
					->as_array();
					

		return $result ? $result[0] : FALSE;
	}

	public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = NULL)
	{
		$table = Config::get('oauth2.storage.token_table');
		
		if ($this->getRefreshToken($refresh_token))
		{
			$result = DB::query(Database::UPDATE, "UPDATE $table SET refresh_token = :refresh_token, refresh_expires = :expires WHERE refresh_token = :refresh_token;");
			$result = $result->parameters(array(
					':refresh_token' => $refresh_token,
					':expires' => $expires,
		        ));
		}
		else
		{
			$result = DB::query(Database::UPDATE, "UPDATE $table SET refresh_token = :refresh_token, refresh_expires = :expires WHERE client_id = :client_id AND user_id = :user_id;");
			$result = $result->parameters(array(
					':client_id' => $client_id,
					':expires' => $expires,
					':user_id' => $user_id,
					':refresh_token' => $refresh_token,
		        ));
		}

		$result = $result->execute();

		return $result;
	}

	public function unsetRefreshToken($refresh_token)
	{
		$table = Config::get('oauth2.storage.token_table');
	}
	
	public function createAccessToken($client_id, $user_id, $scope = NULL, $includeRefreshToken = FALSE)
	{
		$refresh_token   = FALSE;
		
		/*
		 * It is optional to force a new refresh token when a refresh token is used.
		 * However, if a new refresh token is issued, the old one MUST be expired
		 * @see http://tools.ietf.org/html/rfc6749#section-6
		 */
		$issueNewRefreshToken = Config::get('oauth2.always_issue_new_refresh_token', false);
		
		$access_token    = Auth::instance()->hash( uniqid($client_id . mt_rand() . microtime() . $user_id, TRUE));
		$access_expires  = time() + Config::get('oauth2.access_token_ttl', 3600);
		
		//$this->setAccessToken($access_token, $client_id, $user_id, $access_expires, $scope);
		
		$table = Config::get('oauth2.storage.token_table');
		
		// Check for client user combination already exists
		$token_exists = DB::query(Database::SELECT, "SELECT * FROM $table WHERE client_id = :client_id AND user_id = :user_id LIMIT 1");
		$token_exists = $token_exists->parameters(array(
					':client_id' => $client_id,
					':user_id' => $user_id,
		))->execute()->as_array();
		
		// If token exists
		if ($token_exists)
		{
			// Need to generate refresh token every time or,
			// previously refresh is null, and need to include this time.
			if ( $issueNewRefreshToken || ($includeRefreshToken && $token_exists[0]['refresh_token'] == NULL) )
			{
				$refresh_token    = Auth::instance()->hash( uniqid($client_id . mt_rand() . microtime() . $user_id, TRUE));
				$refresh_expires  = time() + Config::get('oauth2.refresh_token_ttl', 1209600);
				
				$result = DB::query(Database::UPDATE, "UPDATE $table SET access_token = :access_token, access_expires = :access_expires, refresh_token = :refresh_token, refresh_expires = :refresh_expires, scope = :scope WHERE client_id = :client_id AND user_id = :user_id");
				$result = $result->parameters(array(
							':client_id'       => $client_id,
							':user_id'         => $user_id,
							':access_token'    => $access_token,
							':access_expires'  => $access_expires,
							':refresh_token'   => $refresh_token,
							':refresh_expires' => $refresh_expires,
							':scope'           => $scope,
				))->execute();
			}
			else
			{
				$result = DB::query(Database::UPDATE, "UPDATE $table SET access_token = :access_token, access_expires = :access_expires, scope = :scope WHERE client_id = :client_id AND user_id = :user_id");
				$result = $result->parameters(array(
							':client_id'      => $client_id,
							':user_id'        => $user_id,
							':access_token'   => $access_token,
							':access_expires' => $access_expires,
							':scope'          => $scope,
				))->execute();
			}
		}
		else
		{
			$created = time();
			// Need to generate refresh token every time or,
			// include refresh token
			if ($issueNewRefreshToken || $includeRefreshToken)
			{
				$refresh_token    = Auth::instance()->hash( uniqid($client_id . mt_rand() . microtime() . $user_id, TRUE));
				$refresh_expires  = time() + Config::get('oauth2.refresh_token_ttl', 1209600);
				
				$result = DB::query(Database::INSERT, "INSERT INTO $table(access_token, client_id, access_expires, user_id, refresh_token, refresh_expires, scope, created) VALUES(:access_token, :client_id, :access_expires, :user_id, :refresh_token, :refresh_expires, :scope, :created);");
				$result = $result->parameters(array(
							':client_id'    => $client_id,
							':user_id'      => $user_id,
							':access_token'   => $access_token,
							':access_expires' => $access_expires,
							':refresh_token'   => $refresh_token,
							':refresh_expires' => $refresh_expires,
							':scope'           => $scope,
							':created'         => $created,
				))->execute();
			}
			else
			{
				$result = DB::query(Database::INSERT, "INSERT INTO $table(access_token, client_id, access_expires, user_id, scope) VALUES(:access_token, :client_id, :access_expires, :user_id, :scope);");
				$result = $result->parameters(array(
							':client_id'    => $client_id,
							':user_id'      => $user_id,
							':access_token'   => $access_token,
							':access_expires' => $access_expires,
							':scope' 	  => $scope,
							':created'        => $created,
				))->execute();
			}
		}
		
		$expires_in = $access_expires - time();
		$token = array(
			'access_token'   => $access_token,
			'expires_in'     => $expires_in,
			"token_type"     => Config::get('oauth2.token_bearer_header_name', 'Bearer')
		);

		/*
		 * Issue a refresh token also, if we support them
		 */
		if ($includeRefreshToken)
		{
			$token['refresh_token'] = $refresh_token ? $refresh_token : $token_exists[0]['refresh_token'];
		}

		return $token;
	}
	
	public function isValidRevoke($token)
	{
		$table = Config::get('oauth2.storage.token_table');
		
		$result = DB::query(Database::SELECT, "SELECT * FROM $table WHERE access_token = :token OR refresh_token = :token LIMIT 1;")
				->parameters(array(
					':token'       => $token,
				))
				->execute()
				->as_array();

		return $result ? $result : FALSE;
	}
	
	public function revoke_access($token)
	{
		$table = Config::get('oauth2.storage.token_table');
		
		// Revoking access token
		$access_expires  = time() + Config::get('oauth2.access_token_ttl', 3600);
		
		$result = DB::query(Database::UPDATE, "UPDATE $table SET access_expires = :access_expires WHERE access_token = :token;");
				$result = $result->parameters(array(
							':token'       => $token,
							':access_expires'  => $access_expires,

				))->execute();
		return $result;
	}
	
	public function revoke_refresh($token)
	{
		$table = Config::get('oauth2.storage.token_table');
		
		// Revoking refresh token
		$refresh_expires  = time() + Config::get('oauth2.refresh_token_ttl', 1209600);
		
		$result = DB::query(Database::UPDATE, "UPDATE $table SET refresh_expires = :refresh_expires WHERE refresh_token = :token;");
				$result = $result->parameters(array(
							':token'       => $token,
							':refresh_expires'  => $refresh_expires,

				))->execute();
		return $result;
	}
	
	public function revoke_access_refresh($token)
	{
		$table = Config::get('oauth2.storage.token_table');
		
		// Revoking both access & refresh token
		$access_expires   = time() + Config::get('oauth2.access_token_ttl', 3600);
		$refresh_expires  = time() + Config::get('oauth2.refresh_token_ttl', 1209600);
		
		$result = DB::query(Database::UPDATE, "UPDATE $table SET access_expires = :access_expires, refresh_expires = :refresh_expires WHERE access_token = :token;");
				$result = $result->parameters(array(
							':token'       => $token,
							':access_expires'  => $access_expires,
							':refresh_expires'  => $refresh_expires,
				))->execute();
		
		return $result;
	}
}