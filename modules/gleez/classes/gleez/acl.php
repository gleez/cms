<?php defined('SYSPATH') OR die('No direct access allowed.');
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
 * ### Dependencies
 *
 * - Default Database module
 * - Any ORM implementation
 * - Gleez User
 * - Gleez Core
 *
 * @package    Gleez\ACL
 * @version    2.0
 * @author     Sandeep Sangamreddi - Gleez
 * @author     Sergey Yakovlev - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License Agreement
 *
 * @todo       Implement their own exceptions (eg. ACL_Exception)
 */
class Gleez_ACL {

	/** Rule type: deny */
	const DENY = FALSE;

	/** Rule type: allow */
	const ALLOW = TRUE;

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
			// Loop through each role in the object
			foreach ($user->roles() as $role)
			{
				$roles[$role->id] = $role->name;
			}
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
		if ( ! isset(ACL::$_all_perms[$name]))
		{
			throw new Gleez_Exception('The requested Permission does not exist: :permission',
				array(':permission' => $name));
		}

	  return ACL::$_all_perms[$name];
	}

	/**
	 * Sets up a named Permission and returns it.
	 *
	 * Example:<br>
	 * <code>
	 *  ACL::set('admin/widgets',
	 *    array(
	 *      'administer site widgets',
	 *      'administer admin widgets'
	 *    )
	 * );
	 * </code>
	 *
	 * @param   string  $name          Permission name
	 * @param   array   $access_names  Access keys
	 * @return  ACL
	 */
	public static function set($name, array $access_names)
	{
		// Adds the action to the action array and returns it.
		return ACL::$_all_perms[$name] = $access_names;
	}

	/**
	 * Retrieves all named permissions
	 *
	 * Example:<br>
	 * <code>
	 *  $permissions = ACL::all();
	 * </code>
	 *
	 * @return  array  Perms by name
	 */
	public static function all()
	{
		return ACL::$_all_perms;
	}

	/**
	 * Saves or loads the ACL cache
	 *
	 * If your perms will remain the same for a long period of time,
	 * use this to reload the ACL from the cache rather than
	 * redefining them on every page load.
	 *
	 * Example:<br>
	 * <code>
	 *  if ( ! ACL::cache())
	 *  {
	 *    // Set perms here
	 *    ACL::cache(TRUE);
	 *  }
	 * </code>
	 *
	 * @param   boolean $save   Cache the current perms
	 * @param   boolean $append Append, rather than replace, cached perms when loading
	 * @return  void            When saving perms
	 * @return  boolean         When loading perms
	 * @uses    Kohana::cache
	 * @uses    Arr::merge
	 */
	public static function cache($save = FALSE, $append = FALSE)
	{
		if ($save)
		{
			// Cache all defined perms
			Kohana::cache('ACL::cache()', ACL::$_all_perms);
		}
		else
		{
			if ($perms = Kohana::cache('ACL::cache()'))
			{
				if ($append)
				{
					// Append cached perms
					ACL::$_all_perms = Arr::merge(ACL::$_all_perms, $perms);
				}
				else
				{
					// Replace existing perms
					ACL::$_all_perms = $perms;
				}

				// perms were cached
				return ACL::$cache = TRUE;
			}
			else
			{
				// perms were not cached
				return ACL::$cache = FALSE;
			}
		}
	}

	/**
	 * Check permission for user
	 *
	 * If the user doesn't have this permission,
	 * failed with an HTTP_Exception_403 or execute `$callback` if it is defined
	 *
	 * Example with a callable function:<br>
	 * <code>
	 * ACL::required(
	 *    'administer site',
	 *    NULL,
	 *    $this->request->redirect(Route::get('user')->uri(array('action' => 'login')))
	 * );
	 * </code>
	 *
	 * Simple check:<br>
	 * <code>
	 *   ACL::required('administer site');
	 * </code>
	 *
	 * @since     2.0
	 * @param     string      $perm_name  Permession name
	 * @param     Model_User  $user       User object [Optional]
	 * @param     callable    $callback   A callable function that execute if it is defined [Optional]
	 * @param     array       $args       The callback's arguments
	 * @return    boolean
	 * @throws    HTTP_Exception_403 If the user doesn't have permission
	 * @throws    Exception          if the `$callback` is a not valid callback
	 */
	public static function required($perm_name, Model_User $user = NULL, $callback = NULL, array $args = array())
	{
		if ( ! ACL::check($perm_name, $user))
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

			Kohana::$log->add(Log::ALERT, 'Unauthorised access attempt to action :act.',
				array(':act' => $perm_name)
			);

			// If the action is set and the role hasn't been matched, the user doesn't have permission.
			throw new HTTP_Exception_403('Unauthorised access attempt to action :act.',
				array(':act' => $perm_name)
			);
		}
	}

	/**
	 * Checks if the current user has permission to access the current request
	 *
	 * If the user is not given, used currently active user
	 *
	 * @param   string      $perm_name  Permession name
	 * @param   Model_User  $user       User object [Optional]
	 * @return  boolean
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
			return ACL::ALLOW;
		}

		// To reduce the number of SQL queries, we cache the user's permissions
		// in a static variable.
		if ( ! isset(ACL::$_perm[$user->id]))
		{
			$roles = self::get_user_roles($user);

			if ( ! $role_permissions = ACL::role_can($roles))
			{
				return FALSE;
			}

			ACL::$_perm[$user->id] = call_user_func_array('array_merge', $role_permissions);
		}

		return isset(ACL::$_perm[$user->id][$perm_name]);
	}

	/**
	 * Checks whether the role(s) have some permission(s)
	 * Added cache support for performance
	 *
	 * @since   2.0
	 * @param   array    $roles  An array of roles as id => name
	 * @return  boolean  FALSE If the role(s) doesn't have any permission
	 * @return  array    Role(s) with permission(s) as array
	 */
	public static function role_can( array $roles)
	{
		$perms = array();

		$cache = Cache::instance('roles');
		
		if( ! $perms = $cache->get('roles'))
		{
			$result = DB::select('rid', 'permission')
						->from('permissions')
						->where('rid', 'IN', array_keys($roles))
						->as_object(TRUE)
						->execute();

			if ( ! count($result))
			{
				return FALSE;
			}

			foreach ($result as $row)
			{
				$perms[$row->rid][$row->permission] = ACL::ALLOW;
			}
			
			//set the cache
			$cache->set('roles', $perms, DATE::DAY);
		}

		return $perms;
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
	 * @uses   Request::redirect()
	 * @uses   Route::get()
	 */
	public static function redirect($perm_name, $route = NULL, array $uri = array())
	{
		if ( ! ACL::check($perm_name))
		{
			if ( ! is_null($route) AND is_string($route))
			{
				Request::initial()->redirect(Route::get($route)->uri($uri), 403);

				return;
			}

			Kohana::$log->add(Log::ALERT, 'Unauthorised access attempt to action :act.',
				array(':act' => $perm_name)
			);

			// If the action is set and the role hasn't been matched, the user doesn't have permission.
			throw new HTTP_Exception_403('Unauthorised access attempt to action :act.',
				array(':act' => $perm_name)
			);
		}
	}

	/**
	 * Make sure the user has permission to do certain action on this object
	 *
	 * Similar to Post::access but this return TRUE/FALSE instead of exception
	 *
	 * @param   string     $action  The action `view|edit|delete` default `view`
	 * @param   ORM        $post    The post object
	 * @param   Model_User $user    The user object to check permission, defaults to logded in user
	 * @param   string     $misc    The misc element usually `id|slug` for logging purpose
	 * @return  boolean
	 * @throws  HTTP_Exception_404
	 * @uses    User::active_user
	 * @uses    Module::event
	 */
	public static function post($action = 'view', $post, Model_User $user = NULL, $misc = NULL)
	{
		if ( ! in_array($action, array('view', 'edit', 'delete', 'add', 'list'), TRUE))
		{
			// If the $action was not one of the supported ones, we return access denied.
			Kohana::$log->add(Log::NOTICE, 'Unauthorised attempt to non-existent action :act.',
				array(':act' => $action)
			);
			return FALSE;
		}

		if ($post instanceof ORM AND ! $post->loaded())
		{
			// If the post was not loaded, we return access denied.
			throw new HTTP_Exception_404('Attempt to non-existent post.');
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

		if (ACL::check('bypass post access', $user))
		{
			return TRUE;
		}

		// Allow other modules to interact with access
		Module::event('post_access', $action, $post);

		if ($action === 'view')
		{
			if ($post->status === 'publish' AND ACL::check('access content', $user))
			{
				return TRUE;
			}
			// Check if authors can view their own unpublished posts.
			elseif ($post->status != 'publish'
				AND ACL::check('view own unpublished content', $user)
				AND $post->author == (int)$user->id
				AND $user->id != 1)
			{
				return TRUE;
			}
			else
			{
				return ACL::check('administer content', $user) OR ACL::check('administer content '.$post->type, $user);
			}
		}

		if ($action === 'edit')
		{
			if ((ACL::check('edit own '.$post->type) OR ACL::check('edit any '.$post->type))
				AND $post->author == (int)$user->id
				AND $user->id != 1)
			{
				return TRUE;
			}
			else
			{
				return ACL::check('administer content', $user) OR ACL::check('administer content '.$post->type, $user);
			}
		}

		if ($action === 'delete')
		{
			if ((ACL::check('delete own '.$post->type) OR ACL::check('delete any '.$post->type))
				AND $post->author == (int)$user->id
				AND $user->id != 1)
			{
				return TRUE;
			}
			else
			{
				return ACL::check('administer content', $user) OR ACL::check('administer content '.$post->type, $user);
			}
		}

		return TRUE;
	}

	/**
	 * Make sure the user has permission to do the action on this object
	 *
	 * Similar to Comment::access but this return TRUE/FALSE instead of exception
	 *
	 * @param   string     $action   The action `view|edit|delete` default `view`
	 * @param   ORM        $comment  The comment object
	 * @param   Model_User $user     The user object to check permission, defaults to logded in user
	 * @param   string     $misc     The misc element usually `id|slug` for logging purpose
	 * @return  boolean
	 * @throws  HTTP_Exception_404
	 * @uses    User::active_user
	 * @uses    Module::event
	 */
	public static function comment($action = 'view', ORM $comment, Model_User $user = NULL, $misc = NULL)
	{
		if ( ! in_array($action, array('view', 'edit', 'delete', 'add', 'list'), TRUE))
		{
			// If the $action was not one of the supported ones, we return access denied.
			Kohana::$log->add(Log::NOTICE, 'Unauthorised attempt to non-existent action :act.',
				array(':act' => $action)
			);
			return FALSE;
		}

		if ( ! $comment->loaded())
		{
			// If the $action was not one of the supported ones, we return access denied.
			throw new HTTP_Exception_404('Attempt to non-existent comment.');
		}

		// If no user object is supplied, the access check is for the current user.
		if (is_null($user))
		{
			$user = User::active_user();
		}

		if (ACL::check('bypass comment access', $user))
		{
			return TRUE;
		}

		// Allow other modules to interact with access
		Module::event('comment_access', $action, $comment);

		if ($action === 'view')
		{
			if ($comment->status === 'publish' AND ACL::check('access comment', $user))
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
			elseif (ACL::check('administer comment', $user))
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
			if (ACL::check('edit own comment')
				AND $comment->author == (int)$user->id
				AND $user->id != 1)
			{
				return TRUE;
			}
			elseif (ACL::check('administer comment', $user))
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
			if ((ACL::check('delete own comment') OR ACL::check('delete any comment'))
				AND $comment->author == (int)$user->id
				AND $user->id != 1)
			{
				return TRUE;
			}
			elseif (ACL::check('administer comment', $user))
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
