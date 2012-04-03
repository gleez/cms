<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 *  Access Control Library.
 *      
 * @package	Gleez
 * @category	ACL
 * @author	Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2012 Gleez Technologies
 * @license	http://gleezcms.org/license
 */
class Gleez_ACL {
	
	const DENY  = false;
        const ALLOW = true;
	
	/**
	 * @var  bool Indicates whether perms are cached
	 */
	public static $cache = FALSE;
    
        /**
         * @var  array 
         */
        protected static $_perms = array();

        /**
         * @var  array 
         */
        protected static $perm = array();
	
	/**
	 * Returns a specific permission.
	 * 
	 * @param	string	The name of the permission.
	 * @return	Acl
	 * @throws  	Kohana_Exception
	 */
	public static function get($name)
	{
		if ( ! isset(ACL::$_perms[$name]))
		{
			throw new Kohana_Exception('The requested Permission does not exist: :permission',
				array(':permission' => $name));
		}

		return ACL::$_perms[$name];
	}
	
	/**
	 * Sets up a named Permission and returns it. 
	 *
	 *     ACL::set('admin/widgets',  array(
	 *					 'administer site widgets',
	 *					 'administer admin widgets'
	 *					)
	 *			 );
	 *
	 * @param   string   Permission name
	 * @param   array    access keys
	 * @return  ACL
	 */
	public static function set($name, Array $access_names)
	{		
		// Adds the action to the action array and returns it.
		return ACL::$_perms[$name] = $access_names;
	}

	/**
	 * Retrieves all named Permissions.
	 *
	 *     $permissions = ACL::all();
	 *
	 * @return  array  perms by name
	 */	
	public static function all()
	{
		return ACL::$_perms;
	}

	/**
	 * Get the name of a perm.
	 *
	 *     $name = ACL::name($perm)
	 *
	 * @param   object  ACL instance
	 * @return  string
	 */
	public static function name(ACL $perm)
	{
		return array_search($perm, ACL::$_perms);
	}
	
	/**
	 * Saves or loads the acl cache. If your perms will remain the same for
	 * a long period of time, use this to reload the acl from the cache
	 * rather than redefining them on every page load.
	 *
	 *     if ( ! ACL::cache())
	 *     {
	 *         // Set perms here
	 *         ACL::cache(TRUE);
	 *     }
	 *
	 * @param   boolean   cache the current perms
	 * @return  void      when saving perms
	 * @return  boolean   when loading perms
	 * @uses    Kohana::cache
	 */
	public static function cache($save = FALSE)
	{
		if ($save === TRUE)
		{
			// Cache all defined perms
			Kohana::cache('ACL::cache()', ACL::$_perms);
		}
		else
		{
			if ($perms = Kohana::cache('ACL::cache()'))
			{
				ACL::$_perms = $perms;

				// Perms were cached
				return ACL::$cache = TRUE;
			}
			else
			{
				// Perms were not cached
				return ACL::$cache = FALSE;
			}
		}
	}

        /**
         * If the active user does not have this permission, failed with an Exception_403.
         *
         * @throws	Exception_403	If the user doesn't have permission.
         * @param  	string     	$perm_name
         * @param	object    	User object
         * @return 	boolean
         */
        public static function required($perm_name, Model_User $user = NULL)
        {
                if ( ! ACL::check($perm_name, $user) )
                {
                        // If the action is set and the role hasn't been matched, the user doesn't have permission.
			throw new HTTP_Exception_403('Unauthorised access attempt to action :act.', array(
				':act' => $perm_name
			));
                }
        }
	
	/**
	 * Checks if the current user has permission to access the current request.
	 * 
	 * @param	string     	$perm_name
	 * @param	object    	User object
	 * @return	boolean
	 */
	public static function check($perm_name, Model_User $user = NULL)
	{
		// If we weren't given an auth object
		if ($user === NULL)
		{
			// Just get the default instance.
			$user = User::active_user();
		}
	
	        // User #2 has all privileges:
                if ($user->id == 2)
                {
                        return ACL::ALLOW;
                }
	
		$role_ids = array();
	
		if ($user->id == 1)
                {
			$role_ids[1] = 'Anonymous';
                }
                else
                {
			// Loop through each role in the object
			foreach ($user->roles() as $role)
                        {
                                $role_ids[$role->id] = $role->name;
			}
		}
	
		// To reduce the number of SQL queries, we cache the user's permissions
                // in a static variable.
                if ( !isset(ACL::$perm[$user->id]) )
                {
                        $role_permissions = ACL::getRolePerms($role_ids);
                        
                        $perms = array();
                        foreach ($role_permissions as $one_role)
                        {
                                $perms += $one_role;
                        }
			
                        ACL::$perm[$user->id] = $perms;
                }
	
		return isset(ACL::$perm[$user->id][$perm_name]);
	}

        /**
         * Does this group have this permission?
         *
         * @param  array      $roles
         * @return boolean
         */
        private static function getRolePerms($roles)
        {
                $perms = array();
                
                if (is_array($roles))
                {
                        $result = DB::select('rid', 'permission')->from('permissions')
				->where('rid', 'IN', array_keys($roles))->as_object(TRUE)->execute();
                        
                        foreach ($result as $row)
                        {
                                $perms[$row->rid][$row->permission] = self::ALLOW;
                        }
                }
                
                return $perms;
        }

	/**
	 * Make sure the user has permission to do certain action on this object
	 *
	 * Similar to post::access but this return True/False instead of exception
	 * 
	 * @param  String  $action  	The action view|edit|delete default view
	 * @param  mixed   $post 	The Post object/array
	 * @param  Object  $user    	The user object to check permission, defaults to logded in user
	 * @param  String  $misc	The misc element usually id|slug for logging purpose
	 * 
	 * @return Bool
	 * 
	 */
	public static function post( $action = FALSE, $post, Model_User $user = NULL, $misc = NULL)
	{
		if( !$action ) $action = 'view';
	
                if (!in_array($action, array('view', 'edit', 'delete', 'add', 'list'), TRUE))
		{
			// If the $action was not one of the supported ones, we return access denied.
			Kohana::$log->add(Log::NOTICE, 'Unauthorised attempt to non-existent action :act.', array(
				':act' => $action
			));
			return FALSE;
		}
        
		if ($post instanceof ORM AND ! $post->loaded() )
		{
			// If the post was not loaded, we return access denied.
                        throw new HTTP_Exception_404('Attempt to non-existent post.');
		}
	
		if ( ! $post instanceof ORM )
		{
			$post = (object) $post;
		}
	
		// If no user object is supplied, the access check is for the current user.
		if( empty( $user ) )   $user = User::active_user();
        
		if (ACL::check('bypass post access', $user))
		{
			return TRUE;
		}

		//allow other modules to interact with access
		Module::event('post_access', $action, $post);
	
		if ($action === 'view')
		{		
			if( $post->status === 'publish' AND ACL::check('access content', $user))
			{
				return TRUE;
			}
			// Check if authors can view their own unpublished posts.
			elseif( $post->status != 'publish' AND ACL::check('view own unpublished content', $user) AND
			   $post->author == (int)$user->id AND $user->id != 1 )
			{
				return TRUE;
			}
			elseif( ACL::check('administer content', $user) OR ACL::check('administer content '.$post->type, $user))
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
			
			if( ( ACL::check('edit own '.$post->type) OR ACL::check('edit any '.$post->type) ) AND
				$post->author == (int)$user->id AND $user->id != 1 )
			{
				return TRUE;
			}
			elseif( ACL::check('administer content', $user) OR ACL::check('administer content '.$post->type, $user))
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
			
			if( ( ACL::check('delete own '.$post->type) OR ACL::check('delete any '.$post->type) ) AND
				$post->author == (int)$user->id AND $user->id != 1 )
			{
				return TRUE;
			}
			elseif( ACL::check('administer content', $user) OR ACL::check('administer content '.$post->type, $user))
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

	/**
	 * Make sure the user has permission to do the action on this object
	 *
	 * Similar to Comment::access but this return True/False instead of exception
	 * 
	 * @param  String  $action  	The action view|edit|delete default view
	 * @param  Object  $comment 	The Comment object
	 * @param  Object  $user    	The user object to check permission, defaults to logded in user
	 * @param  String  $misc	The misc element usually id|slug for logging purpose
	 * 
	 * @return Bool
	 * 
	 */
	public static function comment( $action = 'view', $comment, Model_User $user = NULL, $misc = NULL)
	{
                if (!in_array($action, array('view', 'edit', 'delete', 'add', 'list'), TRUE))
		{
			// If the $action was not one of the supported ones, we return access denied.
			Kohana::$log->add(Log::NOTICE, 'Unauthorised attempt to non-existent action :act.', array(
				':act' => $action
			));
			return FALSE;
		}
        
		if (! $comment->loaded() )
		{
			// If the $action was not one of the supported ones, we return access denied.
                        throw new HTTP_Exception_404('Attempt to non-existent comment.');
		}
	
		// If no user object is supplied, the access check is for the current user.
		if( empty( $user ) )   $user = User::active_user();
        
		if (ACL::check('bypass comment access', $user))
		{
			return TRUE;
		}
	
		//allow other modules to interact with access
		Module::event('comment_access', $action, $comment);
	
		if ($action === 'view')
		{		
			if( $comment->status === 'publish' AND ACL::check('access comment', $user))
			{
				return TRUE;
			}
			// Check if commenters can view their own unpublished comments.
			elseif( $comment->status != 'publish' AND $comment->author == (int)$user->id AND $user->id != 1 )
			{
				return TRUE;
			}
			elseif( ACL::check('administer comment', $user) )
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
			
			if( ACL::check('edit own comment') AND $comment->author == (int)$user->id AND $user->id != 1 )
			{
				return true;
			}
			elseif( ACL::check('administer comment', $user) )
			{
				return true;
			}
			else
			{
				return FALSE;
			}
			
		}
	
		if ($action === 'delete')
		{
			
			if( ( ACL::check('delete own comment') OR ACL::check('delete any comment') ) AND
				$comment->author == (int)$user->id AND $user->id != 1 )
			{
				return TRUE;
			}
			elseif( ACL::check('administer comment', $user) )
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
	
} // End ACL