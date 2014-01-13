<?php
/**
 * The OAuth Client Controller
 *
 * @package    Gleez\OAuth\Controller
 * @author     Gleez Team
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Provider extends Template {

	/**
	 * Demo content
	 * @var string
	 */
	protected $content;

	/**
	 * Demo source code
	 * @var string
	 */
	protected $code;

	/**
	 * OAuth2_Provider
	 * @var object
	 */
	protected $provider;
	
	/**
	 * OAuth2_Provider
	 * @var object
	 */
	protected $provider_config;

	/**
	 * OAuth2_Client
	 * @var object
	 */
	protected $client;

	/**
	 * OAuth2_Token
	 * @var object
	 */
	protected $token;

	/**
	 * Demo source route
	 * @var object
	 */
	protected $route;

	/**
	 * Demo source route
	 * @var object
	 */
	protected $session;

	/**
	 * The before() method is called before controller action.
	 *
	 * @throws Http_Exception_404 If the provider is disabled
	 *
	 * @uses  Auth::logged_in
	 * @uses  Route::get
	 * @uses  Route::uri
	 * @uses  Config::load
	 * @uses  Session::get
	 */
	public function before()
	{
		parent::before();

		// Disable sidebars on user pages
		$this->_sidebars = FALSE;

		// If loggedin redirect to profile
		if(Auth::instance()->logged_in())
		{
			//$this->request->redirect(Route::get('user')->uri(array('action' => 'profile')), 200);
		}

		// Load the session
		$this->session = Session::instance();

		// Set the provider controller
		$this->provider = strtolower($this->request->param('provider'));
		$providers = Kohana::$config->load('auth.providers');

		// Throw exception if the provider is disabled
		if( ! array_key_exists($this->provider, array_filter($providers)))
		{
			throw new Http_Exception_404('Unsupported provider', NULL);
		}

		$this->route = $this->request->route();

		// Load the client config
		$this->provider_config = Config::load("oauth2.providers.{$this->provider}");
		$this->client = OAuth2_Client::factory($this->provider, $this->provider_config['id'], $this->provider_config['secret']);

		if ($token = $this->session->get($this->key('access')))
		{
			// Make the access token available
			$this->token = $token;
		}
	}

	/**
	 * The after() method is called after controller action
	 */
	public function after()
	{
		$this->response->body($this->content);

		return parent::after();
	}

	public function action_index()
	{
		$this->response->body("we r in provider controller");
	}

	public function action_login()
	{
		try
		{
			$dest = Route::get('user')->uri(array('action' => 'profile'));
			
			if ( ! is_null($this->request->query('destination')) )
			{
				$dest = $this->request->query('destination');
			}
			
			Session::instance()->set('destination', $dest);
			
			// Get the login URL from the provider
			$url = $this->client->get_authentication_url($this->provider_config['callback'], array(
				'scope' => $this->provider_config['scope'],
				'state' => time(),
			));
			
			// Redirect to the provider's login page
			$this->request->redirect($url);
		}
		catch( Exception $e)
		{
			Kohana::$log->add(LOG::ERROR, (string) $e);
		}
	}

	public function action_accessProfile()
	{
		try
		{
			$tokens = $this->refreshToken();
			$token = $tokens->param('access_token');
			
			// Store the access token
			$this->session->set($this->key('access'), $token);

			// Redirect to the provider's index page
			$this->oauthComplete($token);
			return;
		}
		catch( Exception $e)
		{
			Kohana::$log->add(LOG::ERROR, (string) $e);
			Message::error("Error :".(string) $e);
		}
	}

	public function action_callback()
	{
		try
		{ 
			// Attempt to complete signin
			if ($code = Arr::get($_REQUEST, 'code'))
			{
				$params['code']           = $code;
				$params['grant_type']     = 'authorization_code';
				$params['client_id']      = $this->provider_config['id'];
				$params['client_secret']  = $this->provider_config['secret'];
				$params['scope']          = $this->provider_config['scope'];
				$params['redirect_uri']   = $this->provider_config['callback'];
				
				$access_token = $this->client->get_access_token(OAuth2_Client::GRANT_TYPE_AUTHORIZATION_CODE, $params);
				$this->client->set_access_token($access_token);
				
				// Store the access token
				$this->session->set($this->key('access'), $access_token);
				//$this->session->set($this->key('refresh'), $r_token);

				// Redirect to the provider's index page
				$this->oauthComplete($access_token);
			}
			
			if ($this->token)
			{
				// Redirect to the provider's index page
				$this->oauthComplete($this->token);
			}

			// Redirect to the provider's index page
			$this->request->redirect($this->route->uri(
				array(
					'provider'   => $this->provider,
					'action'     => 'index'
				))
			);
		}
		catch (ORM_Validation_Exception $e)
		{
			Message::info(__("Coudn't login. Contact administer for error!"));
			//$this->_errors = $e->errors('models', TRUE);
			Kohana::$log->add(LOG::ERROR, (string) $e);

			// Redirect to the provider's index page
			$this->request->redirect( Session::instance()->get('destination', Route::get('user')->uri(array('action' => 'profile'))));
		}
		catch( Exception $e)
		{
			if(Auth::instance()->logged_in())
			{
				Message::info(__("Identity associated with different user"));
			}
			else
			{
				Message::info(__("Coudn't login. Contact administer for error!"));	
			}
			
			Kohana::$log->add(LOG::ERROR, (string) $e);
		
			// Redirect to the provider's index page
			$this->request->redirect( Session::instance()->get('destination', Route::get('user')->uri(array('action' => 'profile'))));
		}
	}

	protected function oauthComplete($token)
	{
		// Login succesful
		$response = $this->client->get_user_data();
		
		//make sure the response is valid by checking id
		if (isset($response['id']))
		{
			// Check whether that id exists in our identities table (provider_id field)
			$user = User::check_identity( $response['id'], $this->provider);

			if(isset($response['email']))
			{
				// @see Controller_Provider::sso_signup
				$this->sso_signup( $response, $user );
			}
		}
		
		return $response;
	}

	/**
	* If not, store the new provider_id (as a new user) or attach to existing user
	*/
	protected function sso_signup($data, $user = FALSE)
	{
		//vars for processing stuff
		$signup = $creation = FALSE;

		$provider = array();
		$provider['provider']      = $this->provider;
		$provider['provider_id']   = $data['id'];
		$provider['refresh_token'] = $this->session->get($this->key('refresh'));

		if($user instanceof Model_User AND ! Auth::instance()->logged_in())
		{
			// If they're loaded, they're a member. Login if not logged
			if($user->loaded())
			{
				// Log in as this user
				Auth::instance()->force_login($user);

				Message::success(__('Welcome back, :nick logged in via (:provider).',
					array(
						':nick' => $user->nick,
						':provider' => $this->provider
					))
				);
			}
		}
		else
		{
			$signup = TRUE;

			// Otherwise, if we're here, this identity isn't associated with any one yet.
			// Are they currently logged in?
			if (Auth::instance()->logged_in())
			{
				// Associate their new oAuth with their current account.
				$user = Auth::instance()->get_user();
			}
			else
			{
				// Check whether the email exists or Otherwise, they need a new account
				$user = ORM::factory('user')->where('mail', '=', $data['email'])->find();

				if(! $user->loaded())
				{
					$creation = TRUE;
				}
			}
		}

		if($signup)
		{
			// @see Model_Auth_User::sso_signup for create new account/associate this OAuth
			$user->sso_signup($data, $provider);

			if($creation)
			{
				Message::success(__('Thank you :nick for registering via (:provider).',
					array(':nick' => $user->nick, ':provider' =>  $this->provider))
				);
			}
			else
			{
				Message::success(__('Attached identity :nick (:provider) to your account.',
					array(':nick' => $user->nick, ':provider' => $this->provider))
				);
			}
		}

		// If yes, log the user in and give him a normal auth session.
		//Auth::instance()->force_login($user);
		return;
	}

	public function key($name)
	{
		return "api_{$this->provider}_{$name}";
	}

}