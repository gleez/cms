<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Controller User
 *
 * @package    Gleez\User
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Controller_User extends Template {

	/** @var User object */
	protected $user;

	public function before()
	{
		Assets::css('user', 'media/css/user.css', array('weight' => 2));

		parent::before();

		// Get the currently logged in user or set up a new one.
		// Note that get_user will also do an auto_login check.
		if( ! ($this->user = $this->_auth->get_user()))
		{
			$this->user = ORM::factory('user');
		}

		if(strpos($this->request->uri(), 'user/reset/', 0))
		{
			$this->request->action('reset_'.$this->request->action());
		}
	}

	/**
	 * Register a new user
	 */
	public function action_register()
	{
		// set the template title (see Template for implementation)
		$this->title = __('User Registration');

		// If user already signed-in
		if($this->_auth->logged_in())
		{
			// redirect to the user account
			$this->request->redirect(Route::get('user')->uri(array('action' => 'profile')), 200);
		}

		// Instantiate a new user
		$post = ORM::factory('user');
		$config = Kohana::$config->load('auth');

		if( ! $config->register)
		{
			// If user registration disabled, we return access denied.
			throw new HTTP_Exception_404('User registration not allowed');
		}

		// Load the view
		$view = View::factory('user/register')
				->set('errors', array())
				->set('config', $config)
				->bind('post', $post);

		if($config->get('use_captcha', FALSE))
		{
			$captcha = Captcha::instance();
			$view->set('captcha', $captcha);
		}

		// If there is a post and $_POST is not empty
		if($this->valid_post('register'))
		{
			try
			{
				// creating user, adding roles and sending verification mail
				$form = $this->request->post();
				$post->signup($form);

				// sign the user in
				Auth::instance()->login($post->name, $post->pass);

				Message::success(__("Account created: %title Successful", array('%title' => $post->nick)));

				if( ! $this->_internal)
				{
					$this->request->redirect(Route::get('user')->uri(array('action' => 'profile')));
				}

			}
			catch (ORM_Validation_Exception $e)
			{
				$view->errors =  $e->errors('models');
			}
		}

		$this->response->body($view);
	}

	/**
	 * Sign In
	 */
	public function action_login()
	{
		// If user already signed-in
		if($this->_auth->logged_in())
		{
			// redirect to the user account
			$this->request->redirect(Route::get('user')->uri(array('action' => 'profile')), 200);
		}

		$this->title = __('Login');
		$config = Kohana::$config->load('auth');

		$view = View::factory('user/login')
					->set('errors', array())
					->set('use_username', $config->get('username'))
					->set('providers', array_filter($config->get('providers')) )
					->bind('post', $user);

		$user = ORM::factory('user')->values($this->request->post());

		if($this->valid_post('login'))
		{
			try
			{
				// Check Auth
				$user->login($this->request->post());

				// If the post data validates using the rules setup in the user model
				Message::success(__('Login: %title Successful!', array('%title' => $user->name)));
				Kohana::$log->add(LOG::INFO, 'User :name logged in.', array(':name' => $user->name) );

				// redirect to the user account
				$this->request->redirect(isset($_GET['destination']) ? $_GET['destination'] :'');

			}
			catch (Validation_Exception $e)
			{
				$view->errors =  $e->array->errors('login', TRUE);
			}
		}

		$this->response->body($view);
	}

	public function action_logout()
	{
		// Sign out the user
		Auth::instance()->logout();

		// redirect to the user account and then the signin page if logout worked as expected
		$this->request->redirect('user/profile');
	}

	/**
	 * View: User account information
	 */
	public function action_profile()
	{
		if( ! $this->_auth->logged_in())
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
	 * View: User account information
	 */
	public function action_view()
	{
		$id = (int) $this->request->param('id', 0);
		$user = ORM::factory('user', $id);
		$account = FALSE;

		if( ! $user->loaded())
		{
			Kohana::$log->add(LOG::ERROR, 'Attempt to access non-existent user');

			// No user is currently logged in
			$this->request->redirect(Route::get('user')->uri(array('action' => 'login')), 401);
		}

		if($this->_auth->logged_in() AND $user->id > 1)
		{
			$account = Auth::instance()->get_user();
		}

		if($account AND $account->id == $user->id)
		{
			$this->title = __('My Account');
		}
		elseif($account AND ((ACL::check('access profiles') AND $user->status) OR ACL::check('administer users')))
		{
			$this->title = __('Profile %title', array('%title' => Text::ucfirst($user->nick)));
		}
		else
		{
			Kohana::$log->add(LOG::ALERT, 'Attempt to access without required privileges. Username :name',
				array(':name' => $account->name ));

			throw new HTTP_Exception_403('Attempt to access without required privileges.');
		}

		$view = View::factory('user/profile')
					->set('account', $user)
					->set('user', $account);

		$this->response->body($view);
	}

	public function action_edit()
	{
		// The user is not logged in
		if( ! $this->_auth->logged_in())
		{
			$this->request->redirect(Route::get('user')->uri(array('action' => 'login')), 401);
		}

		$user = $this->_auth->get_user();
		$this->title =  __('Edit Account');
		$errors = FALSE;

		$view = View::factory('user/edit')->set('user', $user);

		// Form submitted
		if( $this->valid_post('user_edit') )
		{
			// Creating user age
			$dob = strtotime(Arr::get($_POST, 'years').'-'.Arr::get($_POST, 'month').'-'.Arr::get($_POST, 'days'));
			unset($_POST['years'], $_POST['month'], $_POST['days']);
			$post = Arr::merge($_POST, array('dob' => $dob), $_FILES);

			try
			{
				$user->values($post)->save();

				// If the post data validates using the rules setup in the user model
				Message::success(__("%title successfully updated!", array('%title' => $user->nick)));

				// redirect to the user account
				$this->request->redirect( Route::get('user')->uri(array('action' => 'profile')), 200 );
			}
			catch(ORM_Validation_Exception $e)
			{
				$view->errors = $e->errors('models');
			}
		}

		$this->response->body( $view );
	}

	public function action_password()
	{
		// The user is not logged in
		if( ! $this->_auth->logged_in())
		{
			$this->request->redirect( Route::get('user')->uri(array('action' => 'login')) );
		}

		$user = Auth::instance()->get_user();
		$this->title =  __('Change Password');
		$errors = FALSE;

		// Form submitted
		if( $this->valid_post('change_pass') )
		{
			try
			{
				// Change password
				$user->change_pass($this->request->post());

				// If the post data validates using the rules setup in the user model
				Message::success(__("%title successfully changed your password.
					We hope you feel safer now.", array('%title' => $user->name)));

				// redirect to the user account
				$this->request->redirect('user/profile');

			}
			catch (ORM_Validation_Exception $e)
			{
				$errors =  $e->errors('models');
			}
		}

		$this->response->body( View::factory('user/password')->set('errors', $errors)  );
	}

	public function action_photo()
	{
		// The user is not logged in
		if( ! $this->_auth->logged_in())
		{
			$this->request->redirect( Route::get('user')->uri(array('action' => 'login')) );
		}

		$user = $this->_auth->get_user();
		$this->title =  __('Upload Photo');
		$errors = FALSE;
		$view = View::factory('user/photo')->set('user', $user);

		// Form submitted
		if( $this->valid_post('user_edit'))
		{
			try
			{
				$post = array_merge($this->request->post(), $_FILES);
				$user->values($post)->save();

				// If the post data validates using the rules setup in the user model
				Message::success(__("%title successfully updated!", array('%title' => $user->nick)));

				// redirect to the user account
				$this->request->redirect('user/profile');
			}
			catch(ORM_Validation_Exception $e)
			{
				$view->errors = $e->errors('models');
			}
		}

		$this->response->body( $view );
	}

	/*
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
		if ($this->user->confirm_signup($id, $token))
		{
			// @todo If logged in, redirect to profile page or something, otherwise to sign in form
			// @todo If account already confirmed, show Message::NOTICE
			Message::success('Rejoice. Your sign-up has been confirmed.');
			$this->request->redirect('user/profile');
		}

		Message::error('Oh no! This confirmation link is invalid.');
		$this->request->redirect('user/profile');
	}

	public function action_reset_password()
	{
		// The user is logged in, yet it is possible that he lost his password anyway
		if($this->_auth->logged_in())
		{
			// @todo Add real link to the message
			Message::info('Remember your current password? [Go to the change password form](link).');
		}

		// Show form
		$this->title =  __('Reset password');
		$view = View::factory('user/reset_pass')->bind('post', $post)->bind('errors', $errors);

		// Form submitted
		if($this->valid_post('reset_pass'))
		{
			// Try to reset the password
			if($this->user->reset_password($post = $this->request->post()))
			{
				Message::success('Instructions to reset your password are being sent to your email address.');
				Kohana::$log->add(LOG::INFO, 'Password reset instructions mailed to :name at :email.');
				$this->request->redirect('');
			}

			$errors = $post->errors('models/mail');
		}

		$this->response->body($view);
	}

	public function action_reset_confirm_password()
	{
		if($this->_auth->logged_in())
		{
			// redirect to the user account
			$this->request->redirect('user/profile');
		}

		// Grab the user id, token and timestamp from the confirmation link.
		$id = (int) $this->request->param('id');
		$token = (string) $this->request->param('token');
		$time = (int) $this->request->param('time');

		// Make sure nobody else is logged in
		$this->prevent_user_collision($id);

		// Validate the confirmation link first
		if( ! $this->user->confirm_reset_password_link($id, $token, $time))
		{
			Message::error('The confirmation link to reset your password has expired or is invalid. Please request a new one using the form below.');
			$this->request->redirect('user/reset/password');
		}

		// Show form
		$this->title = __('Choose a new password');
		$view = View::factory('user/confirm_reset_password')
					->bind('post', $post)
					->bind('errors', $errors);

		// Form submitted
		if( $this->valid_post('password_confirm'))
		{
			if($this->user->confirm_reset_password_form($post = $this->request->post()))
			{
				Message::success('You can now sign in with your new password.');
				$this->request->redirect(Route::get('user')->uri(array('action' => 'login')));
			}

			$errors = $post->errors('models');
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
		if($this->_auth->logged_in() AND $id != $this->user->id)
		{
			// Cover your ears, we're blowing up the whole session!
			$this->_auth->logout(TRUE);

			// Also, override the user object with a new one
			$this->user = ORM::factory('user');
		}
	}
}
