<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Default auth user
 *
 * @package    Gleez\User
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Model_Auth_User extends ORM {

	protected $_table_columns = array(
					'id' => array( 'type' => 'int' ),
					'name' => array( 'type' => 'string' ),
					'pass' => array( 'type' => 'string' ),
					'mail' => array( 'type' => 'string' ),
					'nick' => array( 'type' => 'string' ),
					'gender' => array( 'type' => 'int' ),
					'dob' => array( 'type' => 'int' ),
					'url' => array( 'type' => 'string', "column_default" => NULL ),
					'theme' => array( 'type' => 'string', "column_default" => NULL ),
					'signature' => array( 'type' => 'string', "column_default" => NULL ),
					'signature_format' => array( 'type' => 'int' ),
					'logins' => array( 'type' => 'int' ),
					'created' => array( 'type' => 'int' ),
					'updated' => array( 'type' => 'int' ),
					'login' => array( 'type' => 'int' ),
					'status' => array( 'type' => 'int' ),
					'timezone' => array( 'type' => 'string' ),
					'language' => array( 'type' => 'string' ),
					'picture' => array( 'type' => 'string' ),
					'init' => array( 'type' => 'string' ),
					'hash' => array( 'type' => 'string' ),
					'data' => array( 'type' => 'string' ),
					);

	/**
	 * Auto fill create and update columns
	*/
	protected $_created_column = array('column' => 'created', 'format' => TRUE);
	protected $_updated_column = array('column' => 'updated', 'format' => TRUE);

	/**
	 * A user has many tokens and roles
	 *
	 * @var array Relationhips
	 */
	protected $_has_many = array(
		'user_tokens' => array('model' => 'user_token'),
		'roles'       => array('model' => 'role', 'through' => 'roles_users'),
		'identities'  => array('model' => 'identity'),
		// @see http://ygamretuta.me/2011/11/27/kohana-3-2-creating-a-basic-friends-system/
		'friends'     => array('model' => 'user', 'through' => 'buddies', 'foreign_key' => 'buddy_id', 'far_key' => 'user_id'),
		'requests'    => array('model' => 'user', 'through' => 'buddy_requests', 'foreign_key' => 'request_from', 'far_key' => 'request_to'),
	);

	protected $_ignored_columns = array('password', 'old_pass');

	/**
	 * Rules for the user model. Because the password is _always_ a hash
	 * when it's set,you need to run an additional not_empty rule in your controller
	 * to make sure you didn't hash an empty string. The password rules
	 * should be enforced outside the model or with a model helper method.
	 *
	 * @return array Rules
	 */
	public function rules()
	{
		$config = Kohana::$config->load('auth.name');
		return array(
			'name' => array(
				array('not_empty'),
				array('min_length', array(':value', max((int)$config['length_min'], 4)) ),
				array('max_length', array(':value', min((int)$config['length_max'], 32)) ),
				array('regex', array(':value', '/^[' . $config['chars'] . ']+$/ui') ),
				array(array($this, 'unique'), array('name', ':value')),
			),
			'pass' => array(
				array('not_empty'),
				array('min_length', array(':value', 4)),
			),
			'mail' => array(
				array('not_empty'),
				array('min_length', array(':value', 4)),
				array('max_length', array(':value', 254)),
				array('email'),
				array(array($this, 'unique'), array('mail', ':value')),
			),
		);
	}

	/**
	 * Filters to run when data is set in this model. The password filter
	 * automatically hashes the password when it's set in the model.
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
				array(array($this, 'upload_photo'))
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
			'pass'         => __('Password'),
			'nick'         => __('Display Name'),
			'old_pass'     => __('Old Password'),
			'gender'       => __('Gender'),
			'dob'          => __('Birthday'),
		);
	}

	public function __get($field)
	{
		if( $field === 'name' OR $field === 'mail' )
		{
			return Html::chars( parent::__get($field) );
		}

		// Return the best version of the user's name. Either their specified
		// nick name, or fall back to the user name.
		if( $field === 'nick' )
		{
			$nick = parent::__get('nick');
			return empty($nick) ? Html::chars( $this->name ) : Html::chars($nick);
		}

		if( $field === 'rawurl' )
			return Route::get('user')->uri( array( 'id' => $this->id ) );

	        // Model specefic links; view, edit, delete url's.
                if( $field === 'url' )
			return ($path = Path::load($this->rawurl) ) ? $path['alias'] : $this->rawurl;

                if( $field === 'edit_url' )
			return Route::get('user')->uri( array( 'id' => $this->id, 'action' => 'edit' ) );

                if( $field === 'delete_url' )
			return Route::get('user')->uri( array( 'id' => $this->id, 'action' => 'delete' ) );

		return parent::__get($field);
	}

	/**
	 *  Override the create method with defaults
	 */
	public function create(Validation $validation = NULL)
	{
		if ($this->_loaded)
			throw new Kohana_Exception('Cannot create :model model because it is already loaded.', array(':model' => $this->_object_name));

		$this->init = $this->mail;
		$this->status  = (int) 1;

		return parent::create($validation);
	}

  	public function roles()
	{
    		return $this->roles->find_all();
  	}

	public function find_all($id = NULL)
	{
		$this->where($this->_object_name.'.id', '!=', 1);
		return parent::find_all($id);
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
			$this->logins = new Database_Expression('logins + 1');

			// Set the last login date
			$this->login = time();

			//if the pass is md5.. convert to new hash system
			if( strlen($this->pass) == 32 AND isset($this->password) AND strlen($this->password) > 3 )
			{
				$this->pass = $this->password;
			}

			// Save the user
			$this->update();
		}
	}

	/**
	 * Does the reverse of unique_key_exists() by triggering error if username exists.
	 * Validation callback.
	 *
	 * @param   Validation  Validation object
	 * @param   string      Field name
	 * @return  void
	 */
	public function username_available(Validation $validation, $field)
	{
		if ($this->unique_key_exists($validation[$field], 'name'))
		{
			$validation->error($field, 'username_available', array($validation[$field]));
		}
	}

	/**
	 * Does the reverse of unique_key_exists() by triggering error if email exists.
	 * Validation callback.
	 *
	 * @param   Validation  Validation object
	 * @param   string      Field name
	 * @return  void
	 */
	public function email_available(Validation $validation, $field)
	{
		if ($this->unique_key_exists($validation[$field], 'mail'))
		{
			$validation->error($field, 'email_available', array($validation[$field]));
		}
	}

	/**
	 * Triggers an error if the email does not exist.
	 * Validation callback.
	 *
	 * @param   object  Validate
	 * @param   string  field name
	 * @return  void
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
	 * @param   mixed    the value to test
	 * @param   string   field name
	 * @return  boolean
	 */
	public function unique_key_exists($value, $field = NULL)
	{
		if ($field === NULL)
		{
			// Automatically determine field by looking at the value
			$field = $this->unique_key($value);
		}

		return (bool) DB::select(array('COUNT("*")', 'total_count'))
			->from($this->_table_name)
			->where($field, '=', $value)
			->where($this->_primary_key, '!=', $this->pk())
			->execute($this->_db)
			->get('total_count');
	}

	/**
	 * Allows a model use both email and username as unique identifiers for login
	 *
	 * @param   string  unique value
	 * @return  string  field name
	 */
	public function unique_key($value)
	{
		return Valid::email($value) ? 'mail' : 'name';
	}

	/**
	 * Password validation for plain passwords.
	 *
	 * @param array $values
	 * @return Validation
	 */
	public static function get_password_validation($values)
	{
		return Validation::factory($values)
			->rule('pass', 'min_length', array(':value', 4))
			->rule('pass_confirm', 'matches', array(':validation', ':field', 'pass'));
	}

	/**
	 * Picture validation for photo upload.
	 *
	 * @param string $file
	 * @return file path
	* @uses System::mkdir Making dir for uploading photo
	 */
	public function upload_photo($file)
	{
		//Uploads directory and url for profile pictures
		$profile_path = APPPATH . 'media/pictures';
		if(!is_dir($profile_path))
		{
			System::mkdir($profile_path);
		}

		// check if there is an uploaded file
		if (Upload::valid($file))
		{
			$filename = uniqid().preg_replace('/\s+/u', '-', $file['name']);
			$path = Upload::save($file, $filename, $profile_path);

			if ($path)
			{
				return 'media/pictures/'.$filename;
			}
		}

		return NULL;
	}

	/**
	 * Validates login information from an array, and optionally redirects
	 * after a successful login.
	 *
	 * @param   array    values to check
	 * @param   string   URI or URL to redirect to
	 * @return  boolean
	 */
	public function login(array $array, $redirect = FALSE)
	{
		$labels = $this->labels();
		$rules  = $this->rules();

		$array = Validation::factory($array);

		//important to check isset to avoid unecessary routing
                if( isset( $array['name'] ) )
		{
			$login_name = $this->unique_key($array['name']);

			//be sure remove the name/email_available rule during login
			if( isset($rules[$login_name][4]))
				unset($rules[$login_name][4]);

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

                                Kohana::$log->add( Log::ERROR, 'User: :name account blocked.', array(':name' => $array['name']) );
                                throw new Validation_Exception($array, 'Account Blocked');
			}
			elseif ($this->loaded() AND Auth::instance()->login($this, $array['password'], $remember))
			{
				// Redirect after a successful login
				if (is_string($redirect))
					Request::initial()->redirect($redirect);

				return $this;
			}
			else
			{
                                $array->error('name', 'invalid');
                                Module::event('user_auth_failed', $array);

                                Kohana::$log->add( Log::ERROR, 'User: :name failed login.', array(':name' => $array['name']) );
				throw new Validation_Exception($array, 'Validation has failed for login');
			}
		}
		else
                {
			Kohana::$log->add( Log::ERROR, 'User Login error');
                        throw new Validation_Exception($array, 'Validation has failed for login');
                }

		return FALSE;
	}

	public function change_pass($values, $expected = NULL )
	{
		// Validation for passwords
		$extra_validation = Model_User::get_password_validation($values)
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
	 * @return  ORM
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
	 * @return  ORM
	 */
	public function sso_signup(array $data, array $provider)
	{
		if ( ! $this->_loaded )
		{
			// Add user
			$this->name = $provider['provider'].'_'.$data['id'];
			$this->pass = $data['id']; //set id as pass( we can't save without password)
			$this->nick = $data['nick'];
			$this->url  = $data['link'];
			$this->status  = (int) 1;

			// Set email if it's available via OAuth provider
			if ( isset($data['email']) )
			{
				$this->mail = $data['email'];
			}

			// Set gender if it's available via OAuth provider
			if( isset($data['gender']) )
			{
				$this->gender = ($data['gender'] === 'male') ? 1 : 2;
			}

			// Save user
			$this->save();

			//give "login" role as it is verified
			$this->add('roles', 2);

			$identity = ORM::factory('identity');
			$identity->user_id = $this->id;
			$identity->values($provider);
			$identity->save();

			//send welcome mail
			$this->welcome_mail();
		}
		elseif ( $this->_loaded )
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
				$this->status  = (int) 1;
				//$this->mail = $data['email'];
				$this->save();
			}
		}

		if ( ! $this->has('roles', 3))
		{
			// Give the user the "user" role
			$this->add('roles', 3);
		}

		// Return user
		return $this;
	}

	/**
	 * Sign-up: step 1.
	 * Validates sign-up information and creates a new user with the "login" role only.
	 *
	 * @param   array    values to check
	 * @return  boolean
	 */
	public function signup(array $data)
	{
		// Add user
		$this->values($data)->save();

		// Give user the "login" role
		if ( ! $this->has('roles', 2))
		{
			// Give the user the "user" role
			$this->add('roles', 2);
		}

		//Create e-mail body with reset password link
		//Token consists of email and the last_login field.
		//So as soon as the user logs in again, the reset link expires automatically
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
				->subject( __('Gleez - Validate account details for :name', array(':name' => $this->nick)) )
				->to($this->mail, $this->nick)
				->message($body);

		// Send the message
		$email->send();

		return TRUE;
	}

	/**
	 * Sign-up: step 2.
	 * Confirms a user sign-up by validating the confirmation link.
	 * Adds the "user" role to the user.
	 *
	 * @param   integer  user id
	 * @param   string   confirmation token
	 * @return  boolean
	 */
	public function confirm_signup($id, $token)
	{
		// Don't even bother, save us the user lookup query
		if (empty($id) OR empty($token))
			return FALSE;

		// Load user by id and status is active
		$this->where('id', '=', $id)->where('status', '=', 1)->find();

		// Invalid user id or account blocked
		if ( ! $this->loaded() )
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
	 */
	public function welcome_mail()
	{
		if ($this->_loaded)
		{
			$body = View::factory('email/welcome_signup', $this->as_array())
					->set('url', URL::site('', TRUE ));

			// Create an email message
			$email = Email::factory()
				->subject( __('Gleez - Account details for :name (approved)', array(':name' => $this->nick)) )
				->to($this->mail, $this->nick)
				->message($body);

			// Send the message
			$email->send();
		}

		return TRUE;
	}

	/**
	 * Reset password: step 1.
	 * The form where a user enters the email address he signed up with.
	 *
	 * @param   array    values to check
	 * @return  boolean
	 */
	public function reset_password(array & $data)
	{
		$labels = $this->labels();
		$rules  = $this->rules();

		$data = Validation::factory($data)
				->rule('mail', 'not_empty')
				->rule('mail', 'min_length', array(':value', 4) )
				->rule('mail', 'max_length', array(':value', 254) )
				->rule('mail', 'email' )
				->rule('mail', array($this, 'email_not_available'), array(':validation', ':field') );

		if ( ! $data->check())
			return FALSE;

		// Load user data
		$this->where('mail', '=', $data['mail'])->find();

		// Invalid user
		if ( ! $this->_loaded ) return FALSE;

		// Create e-mail body with reset password link
		//Token consists of email and the last_login field.
		//So as soon as the user logs in again, the reset link expires automatically
		$time = time();
		$token = Auth::instance()->hash($this->mail.'+'.$this->pass.'+'.$time.'+'.(int)$this->login);

		$body = View::factory('email/confirm_reset_password', $this->as_array())
			->set('time', $time)
			->set('url', URL::site(
				Route::get('user/reset')->uri(array('action' => 'confirm_password',
								    'id' => $this->id,
								    'token' => $token,
								    'time'  => $time
								    )),
				TRUE // Add protocol to URL
			));

		// Create an email message
		$email = Email::factory()
				->subject(__('Gleez - Reset password for :name', array(':name' => $this->nick)) )
				->to( $this->mail, $this->nick )
				->message($body);

		// Send the message
		$email->send();

		return TRUE;
	}

	/**
	 * Reset password: step 2a.
	 * Validates the confirmation link for a password reset.
	 *
	 * @param   integer  user id
	 * @param   string   confirmation token
	 * @param   integer  timestamp
	 * @return  boolean
	 */
	public function confirm_reset_password_link($id, $token, $time)
	{
		// Don't even bother, save us the user lookup query
		if (empty($id) OR empty($token) OR empty($time))
			return FALSE;

		// Confirmation link expired
		if ($time + Kohana::$config->load('site.reset_password_expiration') < time())
			return FALSE;

		//clear any loaded object in memory
		$this->clear();

		// Load user by id and status is active
		$this->where('id', '=', $id)->where('status', '=', 1)->find();

		// Invalid user id
		if ( ! $this->loaded() )
			return FALSE;

		// Used onetime login link
		if ( $time < $this->login ) return FALSE;

		// Invalid confirmation token
		if ($token !== Auth::instance()->hash($this->mail.'+'.$this->pass.'+'.$time.'+'.(int)$this->login))
			return FALSE;

		return TRUE;
	}


	/**
	 * Reset password: step 2b.
	 * Validates and saves a new password.
	 * Also adds the "user" role to the user, in case his sign-up wasn't confirmed yet.
	 *
	 * @param   array    values to check
	 * @return  boolean
	 */
	public function confirm_reset_password_form(array & $data)
	{
		$data = Validation::factory($data)
				->label('pass', __('Password') )
				->rule('pass', 'not_empty' )
				->rule('pass', 'min_length', array(':value', 4) )
				->rule('pass_confirm', 'matches', array(':validation', ':field', 'pass'));

		if ( ! $data->check())
			return FALSE;

		// Store the new password
		$this->pass = $data['pass'];
		$this->save();

		Kohana::$log->add(LOG::INFO, 'User %name used one-time login link.', array('%name' => $this->name) );

		// It could be that the user resets his password before he confirmed his sign-up,
		// or a the reset password form could be used in case the original sign-up confirmation mail got lost.
		// Since the user could only come to this point if he supplied a valid email address,
		// we confirm his account right here.
		if ( ! $this->has('roles', 3))
		{
			// Give the user the "user" role
			$this->add('roles', 3);
		}

		return TRUE;
	}

} // End Auth User Model
