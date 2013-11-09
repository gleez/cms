<?php
/**
 * Controller User
 *
 * @package    Gleez\User
 * @author     Gleez Team
 * @version    1.1.2
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Controller_User extends Template {

	/**
	 * User object
	 * @var Model_User
	 */
	protected $_user;

	/**
	 * The before() method is called before controller action
	 *
	 * @uses  Assets::css
	 * @uses  Auth::get_user
	 * @uses  Request::uri
	 * @uses  Request::action
	 */
	public function before()
	{
		Assets::css('user', 'media/css/user.css', array('weight' => 2));

		parent::before();

		// Get the currently logged in user or set up a new one.
		// Note that get_user will also do an auto_login check.
		if (($this->_user = $this->_auth->get_user()) === FALSE)
		{
			$this->_user = ORM::factory('user');
		}

		if (strpos($this->request->uri(), 'user/reset/', 0) !== FALSE)
		{
			$this->request->action('reset_'.$this->request->action());
		}
	}

	/**
	 * Register a new user
	 *
	 * @uses    Auth::logged_in
	 * @uses    Auth::instance
	 * @uses    Auth::login
	 * @uses    Request::redirect
	 * @uses    Request::action
	 * @uses    Route::get
	 * @uses    Route::uri
	 * @uses    Config::load
	 * @uses    Config_Group::get
	 * @uses    Captcha::instance
	 * @uses    Message::success
	 *
	 * @throws  HTTP_Exception_403
	 */
	public function action_register()
	{
		// set the template title (see Template for implementation)
		$this->title = __('User Registration');

		// If user already signed-in
		if ($this->_auth->logged_in())
		{
			// redirect to the user account
			$this->request->redirect(Route::get('user')->uri(array('action' => 'profile')), 200);
		}

		/** @var $post Model_User */
		$post   = ORM::factory('user');
		$config = Config::load('auth');

		if ( ! $config->register)
		{
			// If user registration disabled, we return access denied.
			throw HTTP_Exception::factory(403, __('User registration not allowed'));
		}

		$action = Route::get('user')->uri(array('action' => $this->request->action()));

		$male   = (isset($post->gender) AND $post->gender == 1) ? TRUE : FALSE;
		$female = (isset($post->gender) AND $post->gender == 2) ? TRUE : FALSE;

		// Load the view
		$view = View::factory('user/register')
			->set('config',  $config)
			->set('action',  $action)
			->set('post',    $post)
			->bind('male',   $male)
			->bind('female', $female)
			->bind('errors', $this->_errors);

		if ($config->get('use_captcha', FALSE))
		{
			$captcha = Captcha::instance();
			$view->set('captcha', $captcha);
		}

		// If there is a post and $_POST is not empty
		if ($this->valid_post('register'))
		{
			try
			{
				// creating user, adding roles and sending verification mail
				$form = $this->request->post();
				$post->signup($form);

				// sign the user in
				Auth::instance()->login($post->name, $post->pass);

				Log::info('Account :title created successful.', array(':title' => $post->nick));
				Message::success(__('Account %title created successful!', array('%title' => $post->nick)));

				$this->request->redirect(Route::get('user')->uri(array('action' => 'profile')));
			}
			catch (ORM_Validation_Exception $e)
			{
				$this->_errors = $e->errors('models', TRUE);
			}
		}

		$this->response->body($view);
	}

	/**
	 * Sign In
	 *
	 * @uses  Request::redirect
	 * @uses  Request::post
	 * @uses  Route::get
	 * @uses  Message::success
	 * @uses  Log::add
	 */
	public function action_login()
	{
		// If user already signed-in
		if ($this->_auth->logged_in())
		{
			// redirect to the user account
			$this->request->redirect(Route::get('user')->uri(array('action' => 'profile')), 200);
		}

		$this->title = __('Sign In');
		$user        = ORM::factory('user');
		$config      = Kohana::$config->load('auth');

		// Create form action
		$destination = isset($_GET['destination']) ? $_GET['destination'] : Request::initial()->uri();
		$params      = array('action' => 'login');
		$action      = Route::get('user')->uri($params).URL::query(array('destination' => $destination));

		$view = View::factory('user/login')
			->set('register',     $config->get('register'))
			->set('use_username', $config->get('username'))
			->set('providers',    array_filter($config->get('providers')))
			->set('post',         $user)
			->set('action',       $action)
			->bind('errors',      $this->_errors);

		if ($this->valid_post('login'))
		{
			try
			{
				// Check Auth
				$user->login($this->request->post());

				// If the post data validates using the rules setup in the user model
				Message::success(__('Welcome, %title!', array('%title' => $user->nick)));
				Log::info('User :name logged in.', array(':name' => $user->name));

				// redirect to the user account
				$this->request->redirect(isset($_GET['destination']) ? $_GET['destination'] :'', 200);

			}
			catch (Validation_Exception $e)
			{
				$this->_errors = $e->array->errors('login', TRUE);
			}
		}

		$this->response->body($view);
	}

	/**
	 * Log Out
	 *
	 * @uses  Auth::logout
	 * @uses  Request::redirect
	 * @uses  Route::get
	 */
	public function action_logout()
	{
		// Sign out the user
		Auth::instance()->logout();

		// Redirect to the user account and then the signin page if logout worked as expected
		$this->request->redirect(Route::get('user')->uri(array('action' => 'profile')), 200);
	}

	/**
	 * View user account information
	 *
	 * @uses  Request::redirect
	 * @uses  Route::get
	 */
	public function action_profile()
	{
		if ( ! $this->_auth->logged_in())
		{
			// No user is currently logged in
			$this->request->redirect(Route::get('user')->uri(array('action' => 'login')), 401);
		}
		else
		{
			$this->request->redirect(Route::get('user')->uri(array('action' => 'view', 'id' => $this->_auth->get_user()->id)), 200);
		}
	}

	/**
	 * View user account information
	 *
	 * @throws  HTTP_Exception_403
	 *
	 * @uses    Auth::get_user
	 * @uses    ACL::check
	 * @uses    Text::ucfirst
	 * @uses    Assets::popup
	 */
	public function action_view()
	{
		$id       = (int) $this->request->param('id', 0);
		$user     = ORM::factory('user', $id);
		$account  = FALSE;
		$is_owner = FALSE;

		if ( ! $user->loaded())
		{
			Log::error('Attempt to access non-existent user.');

			// No user is currently logged in
			$this->request->redirect(Route::get('user')->uri(array('action' => 'login')), 401);
		}

		if ($this->_auth->logged_in() AND $user->id > 1)
		{
			$account = Auth::instance()->get_user();
		}

		if ($account AND $account->id == $user->id)
		{
			Assets::popup();

			$this->title = __('My Account');
		}
		elseif ($account AND ((ACL::check('access profiles') AND $user->status) OR ACL::check('administer users')))
		{
			$this->title = __('Profile %title', array('%title' => Text::ucfirst($user->nick)));
		}
		elseif (ACL::check('access profiles') AND $user->status AND $user->id > Model_User::GUEST_ID)
		{
			$this->title = __('Profile %title', array('%title' => Text::ucfirst($user->nick)));
		}
		else
		{
			throw HTTP_Exception::factory(403, 'Attempt to access without required privileges.');
		}

		if ($account AND ($user->id === $account->id))
		{
			$is_owner = TRUE;
		}

		$view = View::factory('user/profile')
				->set('user',     $user)
				->set('is_owner', $is_owner);

		$this->response->body($view);
	}

	/**
	 * User profile and account editor
	 *
	 * @uses  Request::redirect
	 * @uses  Route::get
	 * @uses  Route::uri
	 * @uses  Auth::get_user
	 * @uses  Message::success
	 * @uses  Arr::merge
	 * @uses  Arr::get
	 */
	public function action_edit()
	{
		// The user is not logged in
		if ( ! $this->_auth->logged_in())
		{
			$this->request->redirect(Route::get('user')->uri(array('action' => 'login')), 401);
		}

		$user = $this->_auth->get_user();
		$this->title = __('Edit Account');

		$male   = (isset($user->gender) AND $user->gender == 1) ? TRUE : FALSE;
		$female = (isset($user->gender) AND $user->gender == 2) ? TRUE : FALSE;
		$action = Route::get('user')->uri(array('action' => $this->request->action(), 'id' => $user->id));

		$view = View::factory('user/edit')
				->set('user',    $user)
				->set('male',    $male)
				->set('action',  $action)
				->set('female',  $female)
				->bind('errors', $this->_errors);

		// Form submitted
		if ($this->valid_post('user_edit'))
		{
			// Creating user age
			$dob = strtotime(Arr::get($_POST, 'days').'-'.Arr::get($_POST, 'month').'-'.Arr::get($_POST, 'years'));

			// Unset not needed field
			unset($_POST['years'], $_POST['month'], $_POST['days'], $_POST['name']);

			$post = Arr::merge($_POST, array('dob' => $dob));

			try
			{
				$user->values($post)->save();

				// If the post data validates using the rules setup in the user model
				Message::success(__("%title successfully updated!", array('%title' => $user->nick)));

				// redirect to the user account
				$this->request->redirect(Route::get('user')->uri(array('action' => 'profile')), 200);
			}
			catch (ORM_Validation_Exception $e)
			{
				$this->_errors = $e->errors('models', TRUE);
			}
		}

		$this->response->body($view);
	}

	/**
	 * Change password
	 *
	 * @uses  Request::redirect
	 * @uses  Route::get
	 * @uses  Auth::get_user
	 * @uses  Message::success
	 */
	public function action_password()
	{
		// The user is not logged in
		if ( ! $this->_auth->logged_in())
		{
			$this->request->redirect(Route::get('user')->uri(array('action' => 'login')), 200);
		}

		$user = Auth::instance()->get_user();
		$this->title =  __('Change Password');
		$destination = Request::initial()->uri();
		$params = array('action' => $this->request->action());

		$view = View::factory('user/password')
				->set('destination', $destination)
				->set('params',      $params)
				->bind('errors',     $this->_errors);

		// Form submitted
		if ($this->valid_post('change_pass'))
		{
			try
			{
				// Change password
				$user->change_pass($this->request->post());

				// If the post data validates using the rules setup in the user model
				Message::success(__('Password successfully changed! We hope you feel safer now.'));

				// Redirect to the user account
				$this->request->redirect(Route::get('user')->uri(array('action' => 'profile')), 200);

			}
			catch (ORM_Validation_Exception $e)
			{
				$this->_errors = $e->errors('models', TRUE);
			}
		}

		$this->response->body($view);
	}

	/**
	 * Upload photo
	 *
	 * @uses  Arr::merge
	 * @uses  Message::success
	 */
	public function action_photo()
	{
		// The user is not logged in
		if ( ! $this->_auth->logged_in())
		{
			$this->request->redirect(Route::get('user')->uri(array('action' => 'login')), 401);
		}

		$allowed_types = Config::get('media.supported_image_formats', array('jpg', 'png', 'gif'));
		$user = $this->_auth->get_user();
		$this->title =  __('Upload Photo');

		$view = View::factory('user/photo')
					->set('user',          $user)
					->set('allowed_types', $allowed_types)
					->bind('errors',       $this->_errors);

		// Form submitted
		if ($this->valid_post('user_edit'))
		{
			try
			{
				$post = Arr::merge($this->request->post(), $_FILES);
				$user->values($post)->save();

				// If the post data validates using the rules setup in the user model
				Message::success(__('Photo successfully uploaded!', array('%title' => $user->nick)));

				// Redirect to the user account
				$this->request->redirect(Route::get('user')->uri(array('action' => 'profile')), 200);
			}
			catch(ORM_Validation_Exception $e)
			{
				$this->_errors = $e->errors('models', TRUE);
			}
		}

		$this->response->body($view);
	}

	/**
	 * Confirm signup by email link validation
	 */
	public function action_confirm()
	{
		// Grab the user id and token from the confirmation link.
		// Note: Type casting is necessary!
		$id = (int) $this->request->param('id');
		$token = (string) $this->request->param('token');


		// Make sure nobody else is logged in
		$this->prevent_user_collision($id);

		// Confirm the user's sign-up
		if ($this->_user->confirm_signup($id, $token))
		{
			// @todo If logged in, redirect to profile page or something, otherwise to sign in form
			// @todo If account already confirmed, show Message::NOTICE
			Message::success(__('Congratulations! Your sign-up has been confirmed.'));
			$this->request->redirect('user/profile');
		}

		Message::error(__('Oh no! This confirmation link is invalid.'));
		$this->request->redirect('user/profile');
	}

	/**
	 * Reset user password
	 *
	 * @uses  Request::redirect
	 * @uses  Route::get
	 * @uses  Route::uri
	 * @uses  Message::success
	 */
	public function action_reset_password()
	{
		// The user is logged in, yet it is possible that he lost his password anyway
		if ($this->_auth->logged_in())
		{
			$this->request->redirect(Route::get('user')->uri(array('action' => 'password', 'id' => $this->_user->id)), 200);
		}

		// Show form
		$this->title = __('Reset password');

		$action = Route::get('user/reset')->uri(array('action' => 'password'));

		$view = View::factory('user/reset_pass')
				->set('action',  $action)
				->bind('post',   $post)
				->bind('errors', $this->_errors);

		// Form submitted
		if ($this->valid_post('reset_pass'))
		{
			// Try to reset the password
			if ($this->_user->reset_password($post = $this->request->post()))
			{
				Message::success(__('Instructions to reset your password are being sent to your email address %mail.', array('%mail' => $_POST['mail'])));
				Log::info('Password reset instructions mailed to :name at :mail.',
					array(':name' => $this->_user->name, ':mail' => $_POST['mail'])
				);
				$this->request->redirect(Route::get('user')->uri(array('action' => 'login')));
			}

			$this->_errors = $post->errors('models/mail', TRUE);
		}

		$this->response->body($view);
	}

	/**
	 * Reset confirm password
	 *
	 * Validates the confirmation link for a password reset and shows 'New password' form
	 *
	 * @uses  Request::redirect
	 * @uses  Request::current
	 * @uses  Request::post
	 * @uses  Route::get
	 * @uses  Route::uri
	 * @uses  URL::query
	 * @uses  Model_User::confirm_reset_password_link
	 * @uses  Message::error
	 * @uses  Message::success
	 */
	public function action_reset_confirm_password()
	{
		if ($this->_auth->logged_in())
		{
			// redirect to the user account
			$this->request->redirect('user/profile');
		}

		// Grab the user id, token and timestamp from the confirmation link.
		$id    = (int) $this->request->param('id');
		$token = (string) $this->request->param('token');
		$time  = (int) $this->request->param('time');

		// Make sure nobody else is logged in
		$this->prevent_user_collision($id);

		// Validate the confirmation link first
		if ( ! $this->_user->confirm_reset_password_link($id, $token, $time))
		{
			Message::error(__('The confirmation link to reset your password has expired or is invalid. Please request a new one using the form below.'));
			$this->request->redirect('user/reset/password');
		}

		// Show form
		$this->title = __('Choose a new password');
		$view = View::factory('user/confirm_reset_password')
			->set('action',  Request::current()->uri().URL::query())
			->bind('post',   $post)
			->bind('errors', $this->_errors);

		// Form submitted
		if ($this->valid_post('password_confirm'))
		{
			if ($this->_user->confirm_reset_password_form($post = $this->request->post()))
			{
				Message::success(__('You can now sign in with your new password.'));
				$this->request->redirect(Route::get('user')->uri(array('action' => 'login')));
			}

			$this->_errors = $post->errors('models', TRUE);
		}

		$this->response->body($view);
	}

	/**
	 * Prevent User Collision
	 *
	 * If a user is currently logged in, but his id does not match
	 * the one provided here, log that user out and reset the user object.
	 * This situation could arise, for example, when a user follows a
	 * confirmation links while another user was still logged in.
	 *
	 * @param   integer $id  User id of the current user
	 */
	protected function prevent_user_collision($id)
	{
		// Another user (on the same browser) is still logged in
		if ($this->_auth->logged_in() AND $id != $this->_user->id)
		{
			// Cover your ears, we're blowing up the whole session!
			$this->_auth->logout(TRUE);

			// Also, override the user object with a new one
			$this->_user = ORM::factory('user');
		}
	}
}
