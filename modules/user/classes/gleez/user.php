<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * User library
 *
 * @package    Gleez\User
 * @author     Sandeep Sangamreddi - Gleez
 * @author     Sergey Yakovlev - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Gleez_User {

	/** @var array All Roles */
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
      User::$roles[$user->id] = $user->roles->find_all()->as_array('id', 'name');
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
	 * @param  mixed $groups Group(s)
	 * @returm boolean TRUE if user belongs to group(s)
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
				User::$roles[$user->id] = $user->roles->find_all()->as_array('id', 'name');
			}

			// array_diff is not safe
			if(array_intersect(array_values($groups), array_keys(User::$roles[$user->id])))
			{
				return TRUE;
			}

			return FALSE;
		}

		if(in_array('guest', $groups) OR array_key_exists(1, $groups))
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
	 * Look up a user by field value.
	 * @param string      search field
	 * @param string      search value
	 * @return Model_User  the user object, or boolean if the name was invalid.
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
			//throw new Gleez_Exception('Unknown user exception!');
			return FALSE;
		}

		return FALSE;
	}

	/**
	 * Is the password provided correct? support old/drupal style md5 and new hash
	 *
	 * @param user User Model
	 * @param string $password a plaintext password
	 * @return boolean TRUE if the password is correct
	 */
	public static function check_pass($user, $password)
	{
		if( !isset($user) OR !isset($password) ) return FALSE;
		$valid = $user->pass;

		// Support for old (Drupal md5 password sum) :
		$guess = (strlen($valid) == 32) ? md5($password) : Auth::instance()->hash($password);
		if (! strcmp($guess, $valid))
		{
			return TRUE;
		}

		return FALSE;
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
	 * @return string html to display
	 */
	public static function providers()
	{
		if(! Auth::instance()->logged_in())
		{
			$providers = array_filter(Kohana::$config->load('auth.providers'));
			return View::factory('oauth/providers')->set('providers', $providers);
		}
	}

}
