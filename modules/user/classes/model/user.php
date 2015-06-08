<?php
/**
 * User Model
 *
 * @package    Gleez\User
 * @author     Gleez Team
 * @version    1.5.0
 * @copyright  (c) 2011-2015 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Model_User extends ORM {

	/**
	 * Table columns
	 * @var array
	 */
	protected $_table_columns = array(
		'id'                => array( 'type' => 'int' ),
		'name'              => array( 'type' => 'string' ),
		'pass'              => array( 'type' => 'string' ),
		'mail'              => array( 'type' => 'string' ),
		'homepage'          => array( 'type' => 'string' ),
		'bio'               => array( 'type' => 'string' ),
		'nick'              => array( 'type' => 'string' ),
		'gender'            => array( 'type' => 'int' ),
		'dob'               => array( 'type' => 'int' ),
		'theme'             => array( 'type' => 'string' ),
		'signature'         => array( 'type' => 'string' ),
		'signature_format'  => array( 'type' => 'int' ),
		'access'            => array( 'type' => 'int' ),
		'logins'            => array( 'type' => 'int' ),
		'created'           => array( 'type' => 'int' ),
		'updated'           => array( 'type' => 'int' ),
		'login'             => array( 'type' => 'int' ),
		'status'            => array( 'type' => 'int' ),
		'timezone'          => array( 'type' => 'string' ),
		'language'          => array( 'type' => 'string' ),
		'picture'           => array( 'type' => 'string' ),
		'init'              => array( 'type' => 'string' ),
		'hash'              => array( 'type' => 'string' ),
		'data'              => array( 'type' => 'string' ),
	);

	/**
	 * Auto fill create column
	 * @var array
	 */
	protected $_created_column = array('column' => 'created', 'format' => TRUE);

	/**
	 * Auto fill update column
	 * @var array
	 */
	protected $_updated_column = array('column' => 'updated', 'format' => TRUE);

	/**
	 * A user has many tokens and roles
	 * @var array Relationships
	 */
	protected $_has_many = array(
		'user_tokens' => array('model' => 'user_token'),
		'roles'       => array('model' => 'role', 'through' => 'roles_users'),
		'identities'  => array('model' => 'identity'),
		'buddies'     => array('model' => 'user', 'through' => 'buddies', 'foreign_key' => 'request_from', 'far_key' => 'request_to'),
	);

	protected $_ignored_columns = array('password', 'old_pass');

	/**
	 * Rules for the user model
	 *
	 * Because the password is _always_ a hash  when it's set,you need to run
	 * an additional not_empty rule in your controller to make sure you didn't
	 * hash an empty string.
	 *
	 * The password rules should be enforced outside the model or with a model helper method.
	 *
	 * @return array Rules
	 *
	 * @uses  Config::get
	 */
	public function rules()
	{
		return array(
			'name' => array(
				array('not_empty'),
				array('min_length', array(':value', Config::get('auth.name.length_min', 4))),
				array('max_length', array(':value', Config::get('auth.name.length_max', 32))),
				array('regex', array(':value', '/^[' . Config::get('auth.name.chars', 'a-zA-Z0-9_\-\^\.') . ']+$/ui') ),
				array(array($this, 'unique'), array('name', ':value')),
			),
			'pass' => array(
				array('not_empty'),
				array('min_length', array(':value', Config::get('auth.password.length_min', 4))),
			),
			'mail' => array(
				array('not_empty'),
				array('min_length', array(':value', 4)),
				array('max_length', array(':value', 254)),
				array('email'),
				array(array($this, 'unique'), array('mail', ':value')),
			),
			'homepage' => array(
				array('url'),
			),
			'bio' => array(
				array('max_length', array(':value', 800)),
			),
		);
	}

	/**
	 * Filters to run when data is set in this model
	 *
	 * The password filter automatically hashes the password when
	 * it's set in the model.
	 *
	 * @return array Filters
	 */
	public function filters()
	{
		return array(
			'pass' => array(
				array(array(Auth::instance(), 'hash'))
			),
			'picture' => array(
				array(array($this, 'uploadPhoto'))
			)
		);
	}

	/**
	 * Labels for fields in this model
	 *
	 * @return array Labels
	 */
	public function labels()
	{
		return array(
			'name'         => __('Username'),
			'mail'         => __('Email'),
			'homepage'     => __('Home Page'),
			'bio'          => __('Bio'),
			'pass'         => __('Password'),
			'pass_confirm' => __('Password Confirm'),
			'nick'         => __('Display Name'),
			'old_pass'     => __('Current password'),
			'gender'       => __('Gender'),
			'dob'          => __('Birthday'),
		);
	}

	/**
	 * Reading data from inaccessible properties
	 *
	 * @param   string  $field
	 * @return  mixed
	 *
	 * @uses  HTML::chars
	 * @uses  Route::get
	 * @uses  Route::uri
	 * @uses  Path::load
	 */
	public function __get($field)
	{
		$nick = parent::__get('nick');

		switch ($field)
		{
			case 'name':
			case 'mail':
				return HTML::chars(parent::__get($field));
				break;
			case 'nick':
				// Return the best version of the user's name.
				// Either their specified nick name, or fall back to the user name.
				return empty($nick) ? HTML::chars($this->name) : HTML::chars($nick);
				break;
			case 'rawurl':
				return Route::get('user')->uri(array('id' => $this->id));
				break;
			case 'url':
				// Model specific links; view, edit, delete url's.
				return ($path = Path::load($this->rawurl)) ? $path['alias'] : $this->rawurl;
				break;
			case 'edit_url':
				// Model specific links; view, edit, delete url's.
				return Route::get('user')->uri(array('id' => $this->id, 'action' => 'edit'));
				break;
			case 'delete_url':
				// Model specific links; view, edit, delete url's.
				return Route::get('admin/user')->uri(array('id' => $this->id, 'action' => 'delete'));
				break;
		}

		return parent::__get($field);
	}

	/**
	 * Gets all permissions
	 *
	 * @return array
	 */
	public function perms()
	{
		if (empty($this->data))
		{
			return array();
		}

		$data = unserialize($this->data);

		return isset($data['permissions']) ? $data['permissions'] : array();
	}

	/**
	 * Gets all roles
	 *
	 * @return array
	 */
	public function roles()
	{
		return $this->_roles();
	}

	/**
	 * Override the create method with defaults
	 *
	 * @param   Validation $validation  Validation object [Optional]
	 *
	 * @return  ORM
	 *
	 * @throws  Gleez_Exception
	 */
	public function create(Validation $validation = NULL)
	{
		if ($this->_loaded)
		{
			throw new Gleez_Exception('Cannot create :model model because it is already loaded.', array(':model' => $this->_object_name));
		}

		$this->init = $this->mail;
		$this->status = 1;

		return parent::create($validation);
	}

	/**
	 * Take actions before the user is deleted
	 *
	 * @since   1.0.1
	 * @since   1.1.0  Used GUEST_ID & ADMIN_ID constants
	 *
	 * @param   integer  $id  User ID
	 *
	 * @throws  Gleez_Exception
	 *
	 * @uses    Log::error
	 */
	protected function before_delete($id, $soft = FALSE)
	{
		// If it is an internal request (eg. popup dialog) and id < 3
		if ($id == User::GUEST_ID OR $id == User::ADMIN_ID)
		{
			Log::error('Attempt to delete system user.');
			throw new Gleez_Exception("You can't delete system users!");
		}

		parent::before_delete($id, $soft = FALSE);
	}

	/**
	 * Override the create method with defaults
	 *
	 * @throws  Gleez_Exception
	 */
	public function update(Validation $validation = NULL)
	{
		if ( ! $this->_loaded)
		{
			throw new Gleez_Exception('Cannot Update :model model because it is not loaded.', array(':model' => $this->_object_name));
		}

		$this->data = $this->_data();

		return parent::update($validation);
	}

	/**
	 * Override the relation add method to reset user roles
	 */
	public function add($alias, $far_keys, $data = NULL)
	{
		parent::add($alias, $far_keys, $data);

		// update data roles
		$this->_set_roles();

		return $this;
	}

	/**
	 * Override the relation remove method to reset user roles
	 */
	public function remove($alias, $far_keys = NULL)
	{
		parent::remove($alias, $far_keys);

		// update data roles
		$this->_set_roles();

		return $this;
	}

	/**
	 * Override the find_all method
	 *
	 * @see  Gleez_ORM_Core::find_all
	 */
	public function find_all($id = NULL)
	{
		$this->where($this->_object_name.'.id', '!=', User::GUEST_ID);

		return parent::find_all($id);
	}

	/**
	 * Override the count_all method
	 *
	 * @see  Gleez_ORM_Core::count_all
	 */
	public function count_all()
	{
		$this->where($this->_object_name.'.id', '!=', User::GUEST_ID);

		return parent::count_all();
	}

	/**
	 * Complete the login for a user by incrementing the logins and saving login timestamp
	 *
	 * @return void
	 */
	public function complete_login()
	{
		if ($this->_loaded)
		{
			// Update the number of logins
			$this->logins = DB::expr('logins + 1');

			// Set the last login date
			$this->login = time();

			// Save the user
			$this->update();
		}
	}

	/**
	 * Does the reverse of unique_key_exists() by triggering error if username exists
	 *
	 * Validation callback.
	 *
	 * @param   Validation  $validation And Validation object
	 * @param   string      $field      Field name
	 */
	public function username_available(Validation $validation, $field)
	{
		if ($this->unique_key_exists($validation[$field], 'name'))
		{
			$validation->error($field, 'username_available', array($validation[$field]));
		}
	}

	/**
	 * Does the reverse of unique_key_exists() by triggering error if email exists
	 *
	 * Validation callback.
	 *
	 * @param   Validation  $validation An validation object
	 * @param   string      $field      Field name
	 */
	public function email_available(Validation $validation, $field)
	{
		if ($this->unique_key_exists($validation[$field], 'mail'))
		{
			$validation->error($field, 'email_available', array($validation[$field]));
		}
	}

	/**
	 * Triggers an error if the email does not exist
	 *
	 * Validation callback.
	 *
	 * @param   Validation  $validation And Validation object
	 * @param   string      $field      Field name
	 */
	public function email_not_available(Validation $validation, $field)
	{
		if ( ! $this->unique_key_exists($validation[$field], 'mail'))
		{
			$validation->error($field, 'email_not_available', array($validation[$field]));
		}
	}

	/**
	 * Tests if a unique key value exists in the database.
	 *
	 * @param   mixed   $value  The value to test
	 * @param   string  $field  Field name [Optional]
	 * @return  boolean
	 */
	public function unique_key_exists($value, $field = NULL)
	{
		if ($field === NULL)
		{
			// Automatically determine field by looking at the value
			$field = $this->unique_key($value);
		}

		$result = DB::select(array(DB::expr('COUNT(*)'), 'total_count'))
			->from($this->_table_name)
			->where($field, '=', $value)
			->where($this->_primary_key, '!=', $this->pk())
			->execute($this->_db)
			->get('total_count');

		return (bool) $result;
	}

	/**
	 * Allows a model use both email and username as unique identifiers for login
	 *
	 * @param   string  $value  Unique value
	 * @return  boolean
	 *
	 * @uses    Valid::email
	 */
	public function unique_key($value)
	{
		return Valid::email($value) ? 'mail' : 'name';
	}

	/**
	 * Password validation for plain passwords.
	 *
	 * @param   array  $values  Array of 'pass' 'pass_confirm' and to use for validation
	 *
	 * @return  Validation
	 *
	 * @uses    Config::get
	 * @uses    Validation::factory
	 * @uses    Validation::rule
	 */
	public static function get_password_validation($values)
	{
		return Validation::factory($values)
			->rule('pass', 'min_length', array(':value', Config::get('auth.password.length_min', 4)))
			->rule('pass_confirm', 'matches', array(':validation', ':field', 'pass'));
	}

	/**
	 * Upload photo and return file path
	 *
	 * @param   string  $file Uploaded file
	 * @return  NULL|string   NULL when filed, otherwise file path
	 */
	public function uploadPhoto($file)
	{
		if (isset($file['tmp_name']) AND ! empty($file['tmp_name']))
		{
			return Upload::uploadImage($file);
		}

		return $this->picture;
	}

	/**
	 * Validates login information from an array, and optionally redirects
	 * after a successful login.
	 *
	 * @param   array          $array    Values to check
	 * @param   boolean|string $redirect URI or URL to redirect to
	 *
	 * @throws  Validation_Exception
	 *
	 * @return  Model_User
	 *
	 * @uses    Log::error
	 * @uses    Module::event
	 * @uses    Request::initial
	 * @uses    Request::redirect
	 */
	public function login(array $array, $redirect = FALSE)
	{
		$labels = $this->labels();
		$rules  = $this->rules();

		$array = Validation::factory($array);

		// important to check isset to avoid unnecessary routing
		if (isset( $array['name']))
		{
			$login_name = $this->unique_key($array['name']);

			// be sure remove the name/email_available rule during login
			if (isset($rules[$login_name][4]))
			{
				unset($rules[$login_name][4]);
			}

			$array->rules('name', $rules[$login_name]);
			$array->label('name', $labels[$login_name]);

			$array->label('password', $labels['pass']);
			$array->rules('password', $rules['pass']);
		}

		// Get the remember login option
		$remember = isset($array['remember']);
		Module::event('user_login_validate', $array);

		if ($array->check())
		{
			// Attempt to load the user
			$this->where($login_name, '=', $array['name'])->find();

			if ($this->loaded() AND $this->status != 1)
			{
				$array->error('name', 'blocked');
				Module::event('user_blocked', $array);

				Log::error('User: :name account blocked.', array(':name' => $array['name']));
				throw new Validation_Exception($array, 'Account Blocked');
			}
			elseif ($this->loaded() AND Auth::instance()->login($this, $array['password'], $remember))
			{
				// Redirect after a successful login
				if (is_string($redirect))
				{
					Request::initial()->redirect($redirect);
				}

				return $this;
			}
			else
			{
				$array->error('name', 'invalid');
				Module::event('user_auth_failed', $array);

				Log::error('User: :name failed login.', array(':name' => $array['name']));
				throw new Validation_Exception($array, 'Validation has failed for login');
			}
		}
		else
		{
			Log::error('User Login error.');
			throw new Validation_Exception($array, 'Validation has failed for login');
		}
	}

	public function change_pass($values, $expected = NULL )
	{
		// Validation for passwords
		$extra_validation = self::get_password_validation($values)
			->rule('old_pass', 'not_empty')
			->rule('pass_confirm', 'not_empty')
			->rule('pass', 'not_empty')
			->rule('old_pass', array(Auth::instance(), 'check_password') );

		return $this->values($values, $expected)->save($extra_validation);
	}

	/**
	 * Finds SSO user based on supplied data.
	 *
	 * @param   string  $provider_field
	 * @param   array   $data
	 *
	 * @return  Model_User
	 */
	public function find_sso_user($provider_field, $data)
	{
		return $this->where($provider_field, '=', $data['id'])
			->or_where('mail', '=', $data['mail'])
			->find();
	}

	/**
	 * Sign-up using data from OAuth provider.
	 *
	 * Override this method to add your own sign up process.
	 *
	 * @param   array   $data
	 * @param   array  $provider
	 *
	 * @return  Model_User
	 */
	public function sso_signup(array $data, array $provider)
	{
		if ( ! $this->_loaded)
		{
			// Add user
			$this->name 	 = $provider['provider'].'_'.$data['id'];
			$this->pass 	 = $data['id']; //set id as pass( we can't save without password)
			$this->nick 	 = $data['nick'];
			$this->homepage  = $data['link'];
			$this->status  	 = 1;

			// Set email if it's available via OAuth provider
			if (isset($data['email']))
			{
				$this->mail = $data['email'];
			}

			// Set gender if it's available via OAuth provider
			if (isset($data['gender']))
			{
				$this->gender = ($data['gender'] === 'male') ? 1 : 2;
			}

			// Save user
			$this->save();

			// give "login" role as it is verified
			$this->add('roles', User::LOGIN_ROLE_ID);

			$identity = ORM::factory('identity');
			$identity->user_id = $this->id;
			$identity->values($provider);
			$identity->save();

			//send welcome mail
			$this->welcome_mail();
		}
		elseif ($this->_loaded)
		{
			// If user is found, but provider id is missing add it to details.
			// We can do this merge, because this means user is found by email address,
			// that is already confirmed by this OAuth provider, so it's considered trusted.
			$identity = ORM::factory('identity');
			$identity->user_id = $this->id;
			$identity->values($provider);
			$identity->save();

			// Set email if it's available via OAuth provider and save
			if (isset($data['email']))
			{
				$this->status  = 1;
				//$this->mail = $data['email'];
				$this->save();
			}
		}

		if ( ! $this->has('roles', User::USER_ROLE_ID))
		{
			// Give the user the "user" role
			$this->add('roles', User::USER_ROLE_ID);
		}

		// Return user
		return $this;
	}

	/**
	 * Sign-up: step 1
	 *
	 * Validates sign-up information and creates a new user with the "login" role only.
	 *
	 * @param   array  $data  Values to check
	 *
	 * @return  boolean
	 *
	 * @uses    Auth::instance
	 * @uses    Auth::hash
	 * @uses    URL::site
	 * @uses    Route::get
	 * @uses    Route::uri
	 * @uses    Email::factory
	 * @uses    Email::subject
	 * @uses    Email::to
	 * @uses    Email::message
	 * @uses    Email::send
	 */
	public function signup(array $data)
	{
		// Add user
		$this->values($data)->save();

		// Give user the "login" role
		if ( ! $this->has('roles', User::LOGIN_ROLE_ID))
		{
			// Give the user the "user" role
			$this->add('roles', User::LOGIN_ROLE_ID);
		}

		// Create e-mail body with reset password link
		// Token consists of email and the last_login field.
		// So as soon as the user logs in again, the reset link expires automatically
		$token = Auth::instance()->hash($this->mail.'+'.$this->pass.'+'.(int)$this->login);

		$body = View::factory('email/confirm_signup', $this->as_array())
			->set('url', URL::site(
				Route::get('user')->uri(array('action' => 'confirm',
					'id' => $this->id,
					'token' => $token,
				)),
				TRUE // Add protocol to URL
			));

		// Create an email message
		$email = Email::factory()
			->subject(__(':site - Validate account details for :name', array(
				':site' => Config::get('site.site_name', 'Gleez CMS'),
				':name' => ($this->nick ? $this->nick : $this->name)
			)))
			->to($this->mail, $this->nick)
			->message($body);

		// Send the message
		$email->send();

		return TRUE;
	}

	/**
	 * Sign-up: step 2
	 *
	 * Confirms a user sign-up by validating the confirmation link.
	 * Adds the "user" role to the user.
	 *
	 * @param   integer  $id     User id
	 * @param   string   $token  Confirmation token
	 *
	 * @return  boolean
	 *
	 * @uses    Auth::instance
	 * @uses    Auth::hash
	 */
	public function confirm_signup($id, $token)
	{
		// Don't even bother, save us the user lookup query
		if (empty($id) OR empty($token))
			return FALSE;

		// Load user by id and status is active
		$this->where('id', '=', $id)->where('status', '=', 1)->find();

		// Invalid user id or account blocked
		if ( ! $this->loaded())
			return FALSE;

		// Invalid confirmation token
		if ($token !== Auth::instance()->hash($this->mail.'+'.$this->pass.'+'.(int)$this->login))
			return FALSE;

		//send welcome mail
		$this->welcome_mail();

		// User is already confirmed.
		// We're not showing an error message.
		if ($this->has('roles', ORM::factory('role', array('name' => 'user'))))
			return TRUE;

		// Give the user the "user" role
		$this->add('roles', ORM::factory('role', array('name' => 'user')));

		return TRUE;
	}

	/**
	 * Welcome email to confirmed users/oauth users
	 *
	 * @return  boolean
	 *
	 * @uses    Email::factory
	 * @uses    Email::subject
	 * @uses    Email::to
	 * @uses    Email::message
	 * @uses    Email::send
	 */
	public function welcome_mail()
	{
		if ($this->_loaded)
		{
			$body = View::factory('email/welcome_signup', $this->as_array())
				->set('url', URL::site('', TRUE ))
				->set('uri_brief', URL::site(
					Route::get('user')->uri(array(
						'action' => 'login'
					)),
					TRUE // Protocol
				));

			// Create an email message
			$email = Email::factory()
				->subject(__(':site - Account details for :name (approved)', array(
					':site' => Config::get('site.site_name', 'Gleez CMS'),
					':name' => ($this->nick ? $this->nick : $this->name)
				)))
				->to($this->mail, $this->nick)
				->message($body);

			// Send the message
			$email->send();
		}

		return TRUE;
	}

	/**
	 * ## Reset password: step 1
	 *
	 * The form where a user enters the email address he signed up with.
	 *
	 * @param   array  $data  Values to check
	 * @return  boolean
	 *
	 * @uses    Config::load
	 * @uses    Validation::factory
	 * @uses    Validation::rule
	 * @uses    Auth::instance
	 * @uses    Auth::hash
	 * @uses    URL::site
	 * @uses    Email::factory
	 * @uses    Email::subject
	 * @uses    Email::to
	 * @uses    Email::message
	 * @uses    Email::send
	 */
	public function reset_password(array & $data)
	{
		$labels = $this->labels();
		$rules  = $this->rules();

		$config = Config::load('site');

		$data = Validation::factory($data)
			->rule('mail', 'not_empty')
			->rule('mail', 'min_length', array(':value', 4))
			->rule('mail', 'max_length', array(':value', 254))
			->rule('mail', 'email')
			->rule('mail', array($this, 'email_not_available'), array(':validation', ':field'));

		if ( ! $data->check())
		{
			throw new Validation_Exception($data, 'Validation has failed for reset password');
		}

		// Load user data
		$this->where('mail', '=', $data['mail'])->find();

		// Invalid user
		if ( ! $this->_loaded)
		{
			throw new Validation_Exception($data, 'Email not found');
		}

		// Token consists of email and the last_login field.
		// So as soon as the user logs in again, the reset link expires automatically
		$time = time();
		$token = Auth::instance()->hash($this->mail.'+'.$this->pass.'+'.$time.'+'.(int)$this->login);
		$url = URL::site(
			Route::get('user/reset')->uri(
				array(
					'action' => 'confirm_password',
					'id'     => $this->id,
					'token'  => $token,
					'time'   => $time)
			),
			TRUE // Protocol
		);

		// Create e-mail body with reset password link
		$body = View::factory('email/confirm_reset_password', $this->as_array())
			->set('time',     $time)
			->set('url',      $url)
			->set('config',   $config);

		// Create an email message
		$email = Email::factory()
			->subject(__(':site - Reset password for :name',
				array(
					':name' => $this->nick,
					':site' => Template::getSiteName()
				)
			))
			->to($this->mail, $this->nick)
			->message($body);

		// Send the message
		$email->send();

		return TRUE;
	}

	/**
	 * Reset password: step 2a.
	 * Validates the confirmation link for a password reset.
	 *
	 * @param   integer  $id     User id
	 * @param   string   $token  Confirmation token
	 * @param   integer  $time   UNIX timestamp
	 *
	 * @return  boolean
	 *
	 * @uses    Auth::instance
	 * @uses    Auth::hash
	 * @uses    Config::get
	 */
	public function confirm_reset_password_link($id, $token, $time)
	{
		// Don't even bother, save us the user lookup query
		if (empty($id) OR empty($token) OR empty($time))
			return FALSE;

		// Confirmation link expired
		if ($time + Config::get('site.reset_password_expiration', 86400) < time())
			return FALSE;

		//clear any loaded object in memory
		$this->clear();

		// Load user by id and status is active
		$this->where('id', '=', $id)->where('status', '=', 1)->find();

		// Invalid user id
		if ( ! $this->loaded())
			return FALSE;

		// Used onetime login link
		if ( $time < $this->login ) return FALSE;

		// Invalid confirmation token
		if ($token !== Auth::instance()->hash($this->mail.'+'.$this->pass.'+'.$time.'+'.(int)$this->login))
			return FALSE;

		return TRUE;
	}


	/**
	 * Reset password: step 2b
	 *
	 * Validates and saves a new password.
	 * Also adds the "user" role to the user, in case his sign-up wasn't confirmed yet.
	 *
	 * @param   array  $data  Values to check
	 *
	 * @return  boolean
	 *
	 * @uses    Log::info
	 * @uses    Validation::factory
	 * @uses    Validation::rule
	 * @uses    Validation::label
	 */
	public function confirm_reset_password_form(array & $data)
	{
		$data = Validation::factory($data)
			->label('pass', __('Password'))
			->label('pass_confirm', __('Password Confirm'))
			->rule('pass', 'not_empty' )
			->rule('pass', 'min_length', array(':value', 4) )
			->rule('pass_confirm', 'matches', array(':validation', ':field', 'pass'));

		if ( ! $data->check())
		{
			throw new Validation_Exception($data, 'Reset form failed');
		}

		// Store the new password
		$this->pass = $data['pass'];
		$this->save();

		Log::info('User %name used one-time login link.', array('%name' => $this->name));

		// It could be that the user resets his password before he confirmed his sign-up,
		// or a the reset password form could be used in case the original sign-up confirmation mail got lost.
		// Since the user could only come to this point if he supplied a valid email address,
		// we confirm his account right here.
		if ( ! $this->has('roles', User::USER_ROLE_ID))
		{
			// Give the user the "user" role
			$this->add('roles', User::USER_ROLE_ID);
		}

		return TRUE;
	}

	/**
	 * update user data field in the $user->data
	 */
	protected function _data()
	{
		$data = $this->_original_values['data'];
		$olddata =  unserialize($data);
		$newdata = is_array($this->data) ? $this->data : array();

		if (empty($data) OR ! $olddata)
		{
			return empty($this->data) ? NULL : serialize($newdata);
		}

		foreach ($newdata AS $key => $value)
		{
			if ($value === NULL)
			{
				unset($olddata[$key]);
			}
			elseif (!empty($key))
			{
				$olddata[$key] = $value;
			}
		}

		return empty($olddata) ? NULL : serialize($olddata);
	}

	/**
	 * Gets or sets all roles
	 *
	 * This simplifies the caching the roles in data column
	 * to improve performance
	 *
	 */
	protected function _roles()
	{
		if ($this->_loaded)
		{
			$data = empty($this->data) ? array() : unserialize($this->data);
			if (isset($data['roles']) AND !empty($data['roles']))
			{
				return $data['roles'];
			}

			if (empty($data['roles']))
			{
				return $this->_set_roles();
			}
		}

		return $this->roles->find_all()->as_array('id', 'name');
	}

	/**
	 * Update user data roles array in the $user->data
	 *
	 * @return array
	 */
	protected function _set_roles()
	{
		if ($this->_loaded)
		{
			$roles = $this->roles->find_all()->as_array('id', 'name');

			// save to data field for performance
			$this->data = array('roles' => $roles);
			$this->update();

			return $roles;
		}

		return array();
	}
}
