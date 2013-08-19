<?php
/**
 * The Abstract OAuth Class
 *
 * @package    Gleez\OAuth\Controller
 * @author     Gleez Team
 * @version    1.0.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
abstract class Controller_OAuth_Base extends Template {

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

		// If loggedin redirect to profile
		if(Auth::instance()->logged_in())
		{
			$this->request->redirect(Route::get('user')->uri(array('action' => 'profile')), 200);
		}

		// Load the session
		$this->session = Session::instance();

		// Set the provider controller
		$provider = strtolower($this->request->controller());
		$providers = Kohana::$config->load('auth.providers');

		// Throw exception if the provider is disabled
		if( ! array_key_exists($provider, array_filter($providers)))
		{
			throw new Http_Exception_404('Unsupported provider', NULL);
		}

		$this->route = $this->request->route();

		// Load the provider
		$this->provider = OAuth2_Provider::factory($provider);

		// Load the client
		$this->client = OAuth2_Client::factory(Kohana::$config->load("oauth.{$provider}"));

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
		if (Auth::instance()->logged_in_oauth($this->provider->name))
		{
			echo __('You signed in via :provider. Yay!', array(':provider' => $this->provider->name));
		}
		else
		{
			$url = $this->route->uri(array(
				'controller' => $this->provider->name,
				'action' => 'login'
			));

			$this->content = HTML::anchor($url, __('Sign in with :provider',
				array(
					':provider' => $this->provider->name
				))
			);
		}
	}

	public function action_login()
	{
		try
		{
			// We will need a callback URL for the user to return to
			$callback = URL::site($this->route->uri(
				array(
					'controller' => $this->provider->name,
					'action' => 'callback'
				)),
			'http');

			// Add the callback URL to the consumer
			$this->client->callback($callback);

			// Get the login URL from the provider
			$url = $this->provider->authorize_url($this->client, $this->client->scope);

			// Redirect to the provider's login page
			$this->request->redirect($url);
		}
		catch( Exception $e)
		{
			Log::error($e->getMessage());
		}
	}

	public function action_callback()
	{
		try
		{
			// Attempt to complete signin
			if ($code = Arr::get($_REQUEST, 'code'))
			{
				// We will need a callback URL for the user to return to
				$callback = URL::site($this->route->uri(
					array(
						'controller' => $this->provider->name,
						'action' => 'callback'
					)),
				'http');

				// Add the callback URL to the consumer
				$this->client->callback($callback);

				// Exchange the authorization code for an access token
				$tokens = $this->provider->get_tokens($this->client, $code);
				$token = $tokens->param('access_token');
				$r_token = $tokens->param('refresh_token');

				// Store the access token
				$this->session->set($this->key('access'), $token);
				$this->session->set($this->key('refresh'), $r_token);

				// Refresh the page to prevent errors
				$this->request->redirect($this->request->uri());
			}

			if ($this->token)
			{
				// Redirect to the provider's index page
				$this->request->redirect( $this->route->uri(
					array(
						'controller' => $this->provider->name,
						'action' => 'complete'
					))
				);
			}

			Log::error('Error retrieving code/tokens.');
			Message::info(__('Coudn\'t login. Either you deny or network error!'));

			// Redirect to the provider's index page
			$this->request->redirect($this->route->uri(
				array(
					'controller' => $this->provider->name
				))
			);
		}
		catch( Exception $e)
		{
			Log::error($e->getMessage());

			// Redirect to the provider's index page
			$this->request->redirect( $this->route->uri(
					array('controller' => $this->provider->name, 'action' => 'index')));
		}
	}

	public function action_complete()
	{
		try
		{
			// Login succesful
			$response = $this->provider->access_profile($this->token);

			//make sure the response is valid by checking id
			if (isset($response['id']))
			{
				// Check whether that id exists in our identities table (provider_id field)
				$user = User::check_identity( $response['id'], $this->provider->name);

				//inisiate the provider specefic process to login
				$data = $this->response_process($response);

				if(isset($data['email']))
				{
					// @see Controller_OAuth_Base::sso_signup
					$this->sso_signup( $data, $user );
				}

				//$this->content = Debug::vars( "{$this->provider->name} Data:", $response );
				$this->request->redirect('user/profile');
			}
		}
		catch( Exception $e )
		{
			Log::error($e->getMessage());

			// Redirect to the provider's index page
			$this->request->redirect( $this->route->uri(
				array(
					'controller' => $this->provider->name,
					'action' => 'index'
				))
			);

		}
	}

	protected function sso_signup($data, $user = FALSE)
	{
		// If not, store the new provider_id (as a new user) or attach to existing user
		try
		{
			//vars for processing stuff
			$signup = $creation = FALSE;

			$provider = array();
			$provider['provider'] = $this->provider->name;
			$provider['provider_id'] = $data['id'];
			$provider['refresh_token'] = $this->session->get($this->key('refresh'));

			if($user instanceof Model_User)
			{
				// If they're loaded, they're a member. Login if not logged
				if($user->loaded() AND ! Auth::instance()->logged_in())
				{
					// Log in as this user
					Auth::instance()->force_login($user);

					Message::success(__('Welcome back, :nick logged in via (:provider).',
						array(
							':nick' => $user->nick,
							':provider' => $this->provider->name
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
						array(
							':nick' => $user->nick,
							':provider' =>  $this->provider->name
						))
					);
				}
				else
				{
					Message::success(__('Attached identity :nick (:provider) to your account.',
						array(
							':nick' => $user->nick,
							':provider' => $this->provider->name
						))
					);
				}
			}

		}
		catch(Exception $e)
		{
			Log::error($e->getMessage());

			// Redirect to the provider's index page
			$this->request->redirect( $this->route->uri(
				array(
					'controller' => $this->provider->name,
					'action' => 'index'
				))
			);
		}

		// If yes, log the user in and give him a normal auth session.
		Auth::instance()->force_login($user);
	}

	public function key($name)
	{
		return "api_{$this->provider->name}_{$name}";
	}

}
