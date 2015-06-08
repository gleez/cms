<?php
/**
 * User library
 *
 * @package    Gleez\User
 * @author     Gleez Team
 * @version    1.2.0
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class User {

	/**
	 * Guest user ID
	 * @type integer
	 */
	const GUEST_ID = 1;

	/**
	 * Main admin user ID
	 * @type integer
	 */
	const ADMIN_ID = 2;

	/**
	 * Anonymous role ID
	 * @type integer
	 */
	const GUEST_ROLE_ID = 1;

	/**
	 * Login role ID
	 * @type integer
	 */
	const LOGIN_ROLE_ID = 2;

	/**
	 * User role ID
	 * @type integer
	 */
	const USER_ROLE_ID = 3;

	/**
	 * Admin role ID
	 * @type integer
	 */
	const ADMIN_ROLE_ID = 4;

	/**
	 * All Roles
	 * @var array
	 */
	protected static $roles = array();

	/**
	 * Return the active user.  If there's no active user, return the guest user.
	 *
	 * @return Model_User
	 */
	public static function active_user()
	{
		// @todo (maybe) cache this object so we're not always doing session lookups.
		return (! (Auth::instance()->get_user()) ? self::guest() : Auth::instance()->get_user());
	}

	/**
	 * Check if current user is guest
	 *
	 * @return boolean TRUE if current user is guest
	 */
	public static function is_guest()
	{
		return ( ! Auth::instance()->get_user() ? TRUE : FALSE );
	}

	/**
	 * Check if current user is admin
	 *
	 * @return  boolean TRUE if current user is admin
	 */
	public static function is_admin()
	{
		if(User::is_guest())
		{
			return FALSE;
		}

		$user = Auth::instance()->get_user();

		// To reduce the number of SQL queries, we cache the user's roles in a static variable.
		if ( ! isset(User::$roles[$user->id]))
		{
			// @todo fetch and save in session to avoid recursive lookups
			User::$roles[$user->id] = $user->roles();
		}

		if(in_array('admin', User::$roles[$user->id]) OR  array_key_exists(4, User::$roles[$user->id]))
		{
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Generates a default anonymous $user object.
	 *
	 * @return Object - the user object.
	 */
	public static function guest()
	{
		return self::lookup(1);
	}

	/**
	 * Counting all users
	 *
	 * @return integer Total number of registered users
	 */
	public static function count_all()
	{
		// initialize the cache
		$cache = Cache::instance('users');

		// To first check cache
		if(! $all = $cache->get('count_all'))
		{
			// Counting from database
			$all = ORM::factory('user')->count_all();
			// Save to cache on an hour
			$cache->set('count_all', $all, Date::HOUR);
		}

		// Return the amount of users
		return $all;
	}

	/**
	 * Checks if user belongs to group(s)
	 *
	 * @param   mixed    $groups  Group(s)
	 * @return  boolean  TRUE if user belongs to group(s)
	 */
	public static function belongsto($groups)
	{
		if ($groups == 'all' OR is_null($groups))
		{
			return TRUE;
		}

		if ( ! is_array($groups))
		{
			$groups = @explode(',', $groups);
		}

		if (Auth::instance()->logged_in())
		{
			$user = Auth::instance()->get_user();

			// To reduce the number of SQL queries, we cache the user's roles in a static variable.
			if ( ! isset(User::$roles[$user->id]))
			{
				// @todo fetch and save in session to avoid recursive lookups
				User::$roles[$user->id] = $user->roles();
			}

			// array_diff is not safe
			if (array_intersect(array_values($groups), array_keys(User::$roles[$user->id])))
			{
				return TRUE;
			}

			return FALSE;
		}

		if (in_array('guest', $groups) OR array_key_exists(1, $groups))
		{
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Look up a user by id.
	 * @param integer      $id the user id
	 * @return Model_User  the user object, or boolean if the id was invalid.
	 */
	public static function lookup($id)
	{
		return self::_lookup_by_field('id', $id);
	}

	/**
	 * Look up a user by name.
	 * @param integer      $name the user name
	 * @return Model_User  the user object, or boolean if the name was invalid.
	 */
	public static function lookup_by_name($name)
	{
		return self::_lookup_by_field('name', $name);
	}

	/**
	 * Look up a user by email.
	 * @param integer      $email the user email
	 * @return Model_User  the user object, or boolean if the email was invalid.
	 */
	public static function lookup_by_mail($email)
	{
		return self::_lookup_by_field('mail', $email);
	}

	/**
	 * Look up a user by field value
	 *
	 * @param   string  $field  Search field
	 * @param   string  $value  Search value
	 * @return  Model_User  the user object, or boolean if the name was invalid.
	 */
	private static function _lookup_by_field($field, $value)
	{
		try
		{
			$user = ORM::factory('user')->where($field, '=', $value)->find();
			if ($user->loaded())
			{
				return $user;
			}
		}
		catch (Exception $e)
		{
			return FALSE;
		}

		return FALSE;
	}

	/**
	 * Get role by id
	 *
	 * @since  1.2.0
	 *
	 * @param  integer  $id  Role id
	 * @return Model_Role|boolean The Role object, or FALSE if ID is invalid or not found
	 */
	public static function getRoleById($id)
	{
		try
		{
			$role = ORM::factory('role', $id);
			if ($role->loaded())
			{
				return $role;
			}
		}
		catch (Exception $e)
		{
			return FALSE;
		}

		return FALSE;
	}

	/**
	 * Is the password provided correct?
	 *
	 * @param  Model_User $user     User
	 * @param  string     $password A plaintext password
	 *
	 * @return boolean TRUE if the password is correct
	 *
	 * @uses   Auth::hash
	 */
	public static function check_pass($user, $password)
	{
		if( !isset($user) || !isset($password) )
		{
			return FALSE;
		}

		$valid = $user->pass;
		$guess = Auth::instance()->hash($password);
		
		return Auth::hashEquals($valid, $guess);
	}

	/**
	 * Saves visitor information as a cookie so it can be reused.
	 *
	 * @param $values
	 *   An array of key/value pairs to be saved into a cookie.
	 */
	public static function cookie_save(array $values)
	{
		foreach ($values as $field => $value)
		{
			// Set cookie for 365 days.
			Cookie::set('Gleez.visitor.' . $field, rawurlencode($value), time() + 31536000);
		}
	}

	/**
	 * Delete a visitor information cookie.
	 *
	 * @param $cookie_name
	 *   A cookie name such as 'homepage'.
	 */
	public static function cookie_delete($cookie_name)
	{
		Cookie::set('Gleez.visitor.' . $cookie_name, '', time() - 3600);
	}

	/**
	 * Check whether that id exists in our identities table (provider_id field)
	 *
	 * @param string $provider_id The provider user id
	 * @param string $provider_name The provider name (facebook, google, live etc)
	 *
	 * @return  mixed user object or FALSE
	 */
	public static function check_identity($provider_id, $provider_name)
	{
		$uid = (int) DB::select('user_id')
			->from('identities')
			->where('provider', '=',  $provider_name)
			->where('provider_id', '=', $provider_id)
			->execute()
			->get('user_id');

		// if the user id is found return the user object
		if($uid AND $uid > 1) return ORM::factory('user', $uid);

		return FALSE;
	}

	/**
	 * Themed list of providers to print
	 *  
	 * @todo move to HTML class
	 * @return string html to display
	 */
	public static function providers()
	{
		if(! Auth::instance()->logged_in())
		{
			$providers = array_filter(Auth::providers());
			return View::factory('oauth/providers')->set('providers', $providers);
		}
	}

	/**
	 * Themed list of roles to print
	 *
	 * @param   ORM     $user  The user object
	 * @return  string  html to display
	 */
	public static function roles(ORM $user)
	{
		$roles = '<div class="user-roles">';
		foreach ($user->roles() as $role)
		{
			$roles .= '<p><span class="label label-default">'. Text::plain($role) . '</span></p>';
		}
		$roles .= '</div>';

		return $roles;
	}

	/**
	 * Get user avatar, and creates a image link
	 *
	 * Optionally, if it is allowed, used [Gravatar].
	 *
	 * Example:
	 * ~~~
	 * $post = Post::dcache($id, 'page', $config);
	 *
	 * echo HTML::anchor($post->user->url, User::getAvatar($post->user));
	 * ~~~
	 *
	 * @since   1.1.0
	 *
	 * @param   ORM      $user      User model
	 * @param   array    $attrs     Default attributes + type = crop|ratio [Optional]
	 * @param   mixed    $protocol  Protocol to pass to `URL::base()` [Optional]
	 * @param   boolean  $index     Include the index page [Optional]
	 *
	 * @return  string
	 *
	 * @uses    Config::get
	 * @uses    Gravatar::setSize
	 * @uses    Gravatar::setDefaultImage
	 * @uses    Gravatar::getImage
	 * @uses    URL::site
	 * @uses    Arr::merge
	 */
	public static function getAvatar(ORM $user, array $attrs = array(), $protocol = NULL, $index = FALSE)
	{
		// Default user pic
		$avatar = 'media/images/avatar-user-400.png';

		// Set default attributes
		$attrs_default = array(
			'size'          => 32,
			'type'          => 'resize',
			'itemprop'      => 'image',
			'default_image' => URL::site($avatar, TRUE),
		);

		// Merge attributes
		$attrs = Arr::merge($attrs_default, $attrs);

		$use_gravatar = Config::get('site.use_gravatars', FALSE);

		if ($use_gravatar)
		{
			$avatar = Gravatar::instance($user->mail)
				->setSize($attrs['size'])
				->setDefaultImage($attrs['default_image'])
				->getImage(array(
					'alt'      => $user->nick,
					'itemprop' => $attrs['itemprop'],
					'width'    => $attrs['size'],
					'height'   => $attrs['size']
				), $protocol, $index);
		}
		else
		{
			if ( ! empty($user->picture))
			{
				$avatar = $user->picture;
			}

			$avatar = HTML::resize($avatar, array(
				'alt'      => $user->nick,
				'height'   => $attrs['size'],
				'width'    => $attrs['size'],
				'type'     => $attrs['type'],
				'itemprop' => $attrs['itemprop'],
			), $protocol, $index);
		}

		return $avatar;
	}

}
