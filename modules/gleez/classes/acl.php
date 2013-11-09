<?php
/**
 * Access Control Library
 *
 * ### Introduction
 *
 * Gleez_ACL provides a lightweight and flexible database-based
 * Access Control Library (ACL) implementation for privileges
 * management. In general, an application may utilize such ACL's
 * to control access to certain protected objects by other
 * requesting objects.
 *
 * ### System Requirements
 *
 * - Default Database module
 * - Any ORM implementation
 *
 * @package    Gleez\ACL
 * @version    2.1.2
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 *
 * @todo       Implement their own exceptions (eg. ACL_Exception)
 */
class ACL {

	/** Rule type: deny */
	const DENY = FALSE;

	/** Rule type: allow */
	const ALLOW = TRUE;

	/** Rule type: deny */
	const PERM_DENY = 2;

	/** Rule type: allow */
	const PERM_ALLOW = 1;
	
	/** Guest ID */
	const ID_GUEST = 1;

	/** Admin ID */
	const ID_ADMIN = 2;

	/** Anonymous role */
	const ANONYMOUS_ROLE = 'Anonymous';

	/**
	 * @var boolean Indicates whether perms are cached
	 */
	public static $cache = FALSE;

	/**
	 * @var array All permissions
	 */
	protected static $_all_perms = array();

	/**
	 * @var array Single permission
	 */
	protected static $_perm = array();

	/**
	 * Get all roles for user
	 *
	 * @since   2.0
	 * @param   Model_User  $user  User object
	 * @return  array  All roles for user
	 */
	private static function get_user_roles(Model_User $user)
	{
		$roles = array();

		// User #1 is guest
		if ($user->id == self::ID_GUEST)
		{
			$roles[self::ID_GUEST] = self::ANONYMOUS_ROLE;
		}
		else
		{
			$roles = $user->roles();
		}

		return $roles;
	}

	/**
	 * Returns a specific permission
	 *
	 * @param   string  $name The name of the permission.
	 * @return  ACL
	 * @throws  Gleez_Exception
	 */
	public static function get($name)
	{
		if ( ! isset(self::$_all_perms[$name]))
		{
			throw new Gleez_Exception('The requested Permission does not exist: :permission',
				array(':permission' => $name));
		}

	  return self::$_all_perms[$name];
	}

	/**
	 * Sets up a named Permission and returns it.
	 *
	 * Example:
	 * ~~~
	 *  ACL::set('admin/widgets',
	 *    array(
	 *      'administer site widgets',
	 *      'administer admin widgets'
	 *    )
	 * );
	 * ~~~
	 *
	 * @param   string  $name          Permission name
	 * @param   array   $access_names  Access keys
	 *
	 * @return  ACL
	 */
	public static function set($name, array $access_names)
	{
		// Adds the action to the action array and returns it.
		return self::$_all_perms[$name] = $access_names;
	}

	/**
	 * Retrieves all named permissions
	 *
	 * Example:
	 * ~~~
	 * $permissions = ACL::all();
	 * ~~~
	 *
	 * @return  array  Perms by name
	 */
	public static function all()
	{
		return self::$_all_perms;
	}

	/**
	 * Setter/Getter for ACL cache
	 *
	 * If your perms will remain the same for a long period of time, use this
	 * to reload the ACL from the cache rather than redefining them on every page load.
	 *
	 * Example:
	 * ~~~
	 *  if ( ! ACL::cache())
	 *  {
	 *    // Set perms here
	 *    ACL::cache(TRUE);
	 *  }
	 * ~~~
	 *
	 * @param   boolean  $save    Cache the current perms [Optional]
	 * @param   boolean  $append  Append, rather than replace, cached perms when loading [Optional]
	 *
	 * @return  boolean
	 *
	 * @uses    Cache::set
	 * @uses    Cache::get
	 * @uses    Arr::merge
	 */
	public static function cache($save = FALSE, $append = FALSE)
	{
		$cache = Cache::instance();

		if ($save)
		{
			// Cache all defined perms
			return $cache->set('ACL::cache()', self::$_all_perms);
		}
		else
		{
			if ($perms = $cache->get('ACL::cache()'))
			{
				if ($append)
				{
					// Append cached perms
					self::$_all_perms = Arr::merge(self::$_all_perms, $perms);
				}
				else
				{
					// Replace existing perms
					self::$_all_perms = $perms;
				}

				// perms were cached
				return self::$cache = TRUE;
			}
			else
			{
				// perms were not cached
				return self::$cache = FALSE;
			}
		}
	}

	/**
	 * Check permission for user
	 *
	 * If the user doesn't have this permission,
	 * failed with an HTTP_Exception_403 or execute `$callback` if it is defined
	 *
	 * Example:
	 * ~~~
	 * // Example with a callable function
	 * ACL::required(
	 *    'administer site',
	 *    NULL,
	 *    $this->request->redirect(Route::get('user')->uri(array('action' => 'login')))
	 * );
	 *
	 * // Simple check
	 *   ACL::required('administer site');
	 * ~~~
	 *
	 * @since     2.0
	 *
	 * @param     string      $perm_name  Permission name
	 * @param     Model_User  $user       User object [Optional]
	 * @param     callable    $callback   A callable function that execute if it is defined [Optional]
	 * @param     array       $args       The callback arguments
	 *
	 * @return    boolean
	 *
	 * @throws    HTTP_Exception_403 If the user doesn't have permission
	 * @throws    Exception          if the `$callback` is a not valid callback
	 */
	public static function required($perm_name, Model_User $user = NULL, $callback = NULL, array $args = array())
	{
		if ( ! self::check($perm_name, $user))
		{
			if ( ! is_null($callback))
			{
				// Check if the $callback is a valid callback
				if ( ! is_callable($callback))
				{
					throw new Exception('An invalid callback was added to the ACL::required().');
				}
				call_user_func($callback, $args);

				return;
			}

			// If the action is set and the role hasn't been matched, the user doesn't have permission
			throw HTTP_Exception::factory(403, 'Unauthorized attempt to access action :perm.',
				array(':perm' => $perm_name));
		}
	}

	/**
	 * Check permission for current user
	 *
	 * Checks permission and redirects if is required to URL
	 * defined in `$route`
	 *
	 * @since  2.0
	 * @param  string  $perm_name  Permission name
	 * @param  string  $route      Route name [Optional]
	 * @param  array   $uri        Additional route params [Optional]
	 *
	 * @throws HTTP_Exception_403
	 *
	 * @uses   Request::redirect()
	 * @uses   Route::get()
	 */
	public static function redirect($perm_name, $route = NULL, array $uri = array())
	{
		if ( ! self::check($perm_name))
		{
			if ( ! is_null($route) AND is_string($route))
			{
				Request::initial()->redirect(Route::get($route)->uri($uri), 403);

				return;
			}

			// If the action is set and the role hasn't been matched, the user doesn't have permission.
			throw HTTP_Exception::factory(403, 'Unauthorized attempt to access action :perm.',
				array(':perm' => $perm_name));
		}
	}
	
	/**
	 * Checks if the current user has permission to access the current request
	 *
	 * If the user is not given, used currently active user
	 *
	 * @param   string      $perm_name  Permission name
	 * @param   Model_User  $user       User object [Optional]
	 *
	 * @return  boolean
	 *
	 * @uses    User::active_user
	 */
	public static function check($perm_name, Model_User $user = NULL)
	{
		// If we weren't given an auth object
		if (is_null($user))
		{
			// Just get the default instance.
			$user = User::active_user();
		}

		// User #2 has all privileges:
		if ($user->id == self::ID_ADMIN)
		{
			return self::ALLOW;
		}

		// To reduce the number of SQL queries, we cache the user's permissions
		// in a static variable.
		if ( ! isset(self::$_perm[$user->id]))
		{
			self::_set_permissions($user);
		}

		return isset(self::$_perm[$user->id][$perm_name]);
	}
	
	/**
	 * Whether role name is currently in the list of available roles.
	 * 
	 * If a role exists in user cache and not in roles table, will
	 * be checked for only available roles.
	 *
	 * @param   string  $role  Role name to look up
	 * @return  boolean
	 */
	public static function is_role($role)
	{
		$roles = self::site_roles();
		return isset($roles[$role]);
	}

	/**
	 * Get all the active roles
	 *
	 * Added cache support for performance.
	 *
	 * @since   2.0
	 * @return  array    Role(s) all roles as array
	 */
	public static function site_roles()
	{
		$roles = array();

		$cache = Cache::instance('roles');
		
		if( ! $roles = $cache->get('site_roles'))
		{
			$roles = ORM::factory('role')->find_all()->as_array('id', 'name');
			
			//set the cache
			$cache->set('site_roles', $roles, DATE::DAY);
		}

		return $roles;
	}
	
	/**
	 * Get all the enabled permissions for all roles
	 *
	 * Added cache support for performance.
	 *
	 * @since   2.0
	 *
	 * @return  boolean  FALSE If the role(s) doesn't have any permission
	 * @return  array    Role(s) with permission(s) as array
	 */
	public static function site_perms()
	{
		$perms = array();

		$cache = Cache::instance('roles');
		
		if( ! $perms = $cache->get('site_perms'))
		{
			$result = DB::select('rid', 'permission')
						->from('permissions')
						->as_object(TRUE)
						->execute();

			foreach ($result as $row)
			{
				$perms[$row->rid][$row->permission] = self::ALLOW;
			}
			
			//set the cache
			$cache->set('site_perms', $perms, DATE::DAY);
		}

		return $perms;
	}

	/**
	 * Sets the permissions; both role based and user based
	 *
	 * @param Model_User User object
	 */
	protected static function _set_permissions(Model_User $user)
	{
		$user_perms = $user->perms();
		$site_perms = self::site_perms();
		$user_roles = self::get_user_roles($user);
	
		// Filter out active roles
		$roles = array_filter(array_keys($user_roles), array('self', 'is_role'));
		self::$_perm[$user->id] = array();

		//role based permissions
		foreach($roles as $role)
		{
			if(isset($site_perms[$role]) AND is_array($site_perms[$role]))
			{
				self::$_perm[$user->id] = array_merge(
					(array) self::$_perm[$user->id],
					(array) $site_perms[$role]
				);
			}
		}

		// User based permissions
		foreach($user_perms as $perm => $val)
		{
			if($val == self::PERM_ALLOW)
			{
				self::$_perm[$user->id] = array_merge(self::$_perm[$user->id], array($perm => self::ALLOW) );
			}
			elseif($val == self::PERM_DENY)
			{
				//if we deny this permission unset if it exists
				if (isset(self::$_perm[$user->id][$perm]) )
				{
					unset(self::$_perm[$user->id][$perm]);
				}
			}
		}
	}
	
	/**
	 * Make sure the user has permission to do certain action on this object
	 *
	 * Similar to [Post::access] but this return TRUE/FALSE instead of exception
	 *
	 * @param   string     $action  The action `view|edit|delete` default `view`
	 * @param   ORM        $post    The post object
	 * @param   Model_User $user    The user object to check permission, defaults to loaded in user
	 * @param   string     $misc    The misc element usually `id|slug` for logging purpose
	 *
	 * @return  boolean
	 *
	 * @throws  HTTP_Exception_404
	 *
	 * @uses    User::active_user
	 * @uses    Module::event
	 */
	public static function post($action = 'view', $post, Model_User $user = NULL, $misc = NULL)
	{
		if ( ! in_array($action, array('view', 'edit', 'delete', 'add', 'list'), TRUE))
		{
			// If the $action was not one of the supported ones, we return access denied.
			Log::notice('Unauthorized attempt to access non-existent action :act.',
				array(':act' => $action)
			);
			return FALSE;
		}

		if ($post instanceof ORM AND ! $post->loaded())
		{
			// If the post was not loaded, we return access denied.
			throw HTTP_Exception::factory(404, 'Attempt to access non-existent post.');
		}

		if ( ! $post instanceof ORM)
		{
			$post = (object) $post;
		}

		// If no user object is supplied, the access check is for the current user.
		if (is_null($user))
		{
			$user = User::active_user();
		}

		if (self::check('bypass post access', $user))
		{
			return TRUE;
		}

		// Allow other modules to interact with access
		Module::event('post_access', $action, $post);

		if ($action === 'view')
		{
			if ($post->status === 'publish' AND self::check('access content', $user))
			{
				return TRUE;
			}
			// Check if authors can view their own unpublished posts.
			elseif ($post->status != 'publish'
				AND self::check('view own unpublished content', $user)
				AND $post->author == (int)$user->id
				AND $user->id != 1)
			{
				return TRUE;
			}
			else
			{
				return self::check('administer content', $user) OR self::check('administer content '.$post->type, $user);
			}
		}

		if ($action === 'edit')
		{
			if ((self::check('edit own '.$post->type) OR self::check('edit any '.$post->type))
				AND $post->author == (int)$user->id
				AND $user->id != 1)
			{
				return TRUE;
			}
			else
			{
				return self::check('administer content', $user) OR self::check('administer content '.$post->type, $user);
			}
		}

		if ($action === 'delete')
		{
			if ((self::check('delete own '.$post->type) OR self::check('delete any '.$post->type))
				AND $post->author == (int)$user->id
				AND $user->id != 1)
			{
				return TRUE;
			}
			else
			{
				return self::check('administer content', $user) OR self::check('administer content '.$post->type, $user);
			}
		}

		return TRUE;
	}

	/**
	 * Make sure the user has permission to do the action on this object
	 *
	 * Similar to [Comment::access] but this return TRUE/FALSE instead of exception
	 *
	 * @param   string     $action   The action `view|edit|delete` default `view`
	 * @param   ORM        $comment  The comment object
	 * @param   Model_User $user     The user object to check permission, defaults to loaded in user
	 * @param   string     $misc     The misc element usually `id|slug` for logging purpose
	 *
	 * @return  boolean
	 *
	 * @throws  HTTP_Exception_404
	 *
	 * @uses    User::active_user
	 * @uses    Module::event
	 */
	public static function comment($action = 'view', ORM $comment, Model_User $user = NULL, $misc = NULL)
	{
		if ( ! in_array($action, array('view', 'edit', 'delete', 'add', 'list'), TRUE))
		{
			// If the $action was not one of the supported ones, we return access denied.
			Log::notice('Unauthorized attempt to access non-existent action :act.',
				array(':act' => $action)
			);
			return FALSE;
		}

		if ( ! $comment->loaded())
		{
			// If the $action was not one of the supported ones, we return access denied.
			throw HTTP_Exception::factory(404, 'Attempt to access non-existent comment.');
		}

		// If no user object is supplied, the access check is for the current user.
		if (is_null($user))
		{
			$user = User::active_user();
		}

		if (self::check('bypass comment access', $user))
		{
			return TRUE;
		}

		// Allow other modules to interact with access
		Module::event('comment_access', $action, $comment);

		if ($action === 'view')
		{
			if ($comment->status === 'publish' AND self::check('access comment', $user))
			{
				return TRUE;
			}
			// Check if commenters can view their own unpublished comments.
			elseif ($comment->status != 'publish'
				AND $comment->author == (int)$user->id
				AND $user->id != 1)
			{
				return TRUE;
			}
			elseif (self::check('administer comment', $user))
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}

		if ($action === 'edit')
		{
			if (self::check('edit own comment')
				AND $comment->author == (int)$user->id
				AND $user->id != 1)
			{
				return TRUE;
			}
			elseif (self::check('administer comment', $user))
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}

		if ($action === 'delete')
		{
			if ((self::check('delete own comment') OR self::check('delete any comment'))
				AND $comment->author == (int)$user->id
				AND $user->id != 1)
			{
				return TRUE;
			}
			elseif (self::check('administer comment', $user))
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}

		return TRUE;
	}

}
