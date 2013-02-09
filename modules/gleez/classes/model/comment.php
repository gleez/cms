<?php defined("SYSPATH") or die("No direct script access.");

class Model_Comment extends ORM {

	//protected $_load_with = array('post', 'user');
	
        protected $_belongs_to = array(
                                       'post'   => array('model' => 'post', 'foreign_key' => 'post_id'),
				       'user' => array('foreign_key' => 'author')
                                );
	
        protected $_table_columns =     array(
          'id' => array( 'type' => 'int' ),
          'post_id' => array( 'type' => 'int' ),
          'pid' => array( 'type' => 'int' ),
	  'author' => array( 'type' => 'int' ),
          'title' => array( 'type' => 'string' ),
          'body' => array( 'type' => 'string' ),
	  'hostname' => array( 'type' => 'string' ),
          'created' => array( 'type' => 'int' ),
          'updated' => array( 'type' => 'int' ),
          'status' => array( 'type' => 'string' ),
          'type' => array( 'type' => 'string' ),
	  'format' => array( 'type' => 'int' ),
          'thread' => array( 'type' => 'string' ),
	  'guest_email' => array( 'type' => 'string' ),
          'guest_name' => array( 'type' => 'string' ),
          'guest_url' => array( 'type' => 'string' ),
	  'karma' => array( 'type' => 'int' ),
        );
	
	protected $_ignored_columns = array('author_name', 'author_date');
	
        public function rules()
	{
		return array(
			'author' => array(
				array('not_empty'),
			),
			'post_id' => array(
				array('not_empty'),
				array(array($this, 'valid_post'), array(':validation', ':field')),
			),
                        'guest_name' => array(
				array(array($this, 'valid_author'), array(':validation', ':field')),
			),
			'guest_email' => array(
				array(array($this, 'valid_email'), array(':validation', ':field')),
			),
			'guest_url' => array(
				array('url'),
			),
			'status' => array(
                                array('Comment::valid_state', array(':value')),
			),
			'body' => array(
				array('not_empty'),
			),
			'type' => array(
				array('not_empty'),
			),
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
			'title'       => 'Title',
			'body'        => 'Comment',
			'guest_name'  => 'Name',
			'guest_email' => 'Email',
			'guest_url'   => 'Website',
			'author'      => 'Author',
		);
	}
	
        /**
	 * Updates or Creates the record depending on loaded()
	 *
	 * @chainable
	 * @param  Validation $validation Validation object
	 * @return ORM
	 */
	public function save(Validation $validation = NULL)
	{
                //set some defaults
                $this->updated = time();
                $this->format = empty($this->format) ? Kohana::$config->load('inputfilter.default_format', 1) : $this->format;
		$this->author = empty($this->author) ? User::active_user()->id : $this->author;
        
                if ( !$this->loaded() )
                {
                        // New comment
                        $this->created   = $this->updated;
			$this->hostname  = substr(Request::$client_ip, 0, 32); //set hostname only if its new comment.
			
                        if (empty($this->status))
				$this->status = ACL::check('skip comment approval') ? 'publish' : 'draft';
                }
        
		// Validate the comment's title. If not specified, extract from comment body.
		if (trim($this->title) == '' AND !empty($this->body) )
		{
			// The body may be in any format, so:
			// 1) Filter it into HTML
			// 2) Strip out all HTML tags
			// 3) Convert entities back to plain-text.
			$this->title = Text::limit_words(
					trim( UTF8::clean( strip_tags( Text::markup($this->body, $this->format) ) ) ), 10, ''
					);
			// Edge cases where the comment body is populated only by HTML tags will
    
			// require a default subject.
			if ($this->title == '')
			{
				$this->title = __('(No subject)');
			}
		}
		
                parent::save( $validation );
                
                return $this;
        }
        
	public function __get($field)
	{
                if( $field === 'title' )
			return Text::plain( parent::__get('title') );

                if( $field === 'body' )
			return Text::markup( parent::__get('body'), $this->format );
	
                //Raw fields without markup. Usage: during edit or etc!
                if( $field === 'rawtitle' )
			return parent::__get('title');
        
                if( $field === 'rawbody' )
			return parent::__get('body');

                // Model specefic links; view, edit, delete url's.
                if( $field === 'url' )
			return Route::get('comment')->uri( array( 'id' => $this->id, 'action' => 'view' ) );
	
                if( $field === 'edit_url' )
			return Route::get('comment')->uri( array( 'id' => $this->id, 'action' => 'edit' ) );

                if( $field === 'delete_url' )
			return Route::get('comment')->uri( array( 'id' => $this->id, 'action' => 'delete' ) );
	
                return parent::__get($field);
        }

        /**
	 * Make sure we have an valid author id set, or a guest id.
	 * Validation callback.
	 *
	 * @param   Validation  Validation object
	 * @param   string      Field name
	 * @return  void
	 */
        public function valid_author(Validation $validation, $field)
        {
		if ( !empty($this->author_name) AND !($account = User::lookup_by_name($this->author_name)))
                {
                        $validation->error('author', 'invalid', array($this->author_name));
                }
		else
		{
			if( isset($account) )  $this->author = $account->id;
		}
        
		if ( empty($this->author) )
                {
                        $validation->error($field, 'not_empty', array($validation[$field]));
                }
                elseif( $this->author == 1 AND empty($this->guest_name) )
                {
                        $validation->error('guest_name', 'not_empty', array($validation[$field]));
                }
		elseif( $this->author == 1 AND !empty($this->guest_name) )
		{
			if( $query = DB::select(array('COUNT("*")', 'total_count'))
			->from('users')
			->where('name', 'LIKE', $this->guest_name)
			->or_where('nick', 'LIKE', $this->guest_name)
			->execute($this->_db)
			->get('total_count') > 0)
			{
				$validation->error($field, 'registered_user', array($validation[$field]));
			}
		}
        }
        
        /**
         * Make sure that the email address is legal.
         */
        public function valid_email(Validation $validation, $field)
        {
                if ($this->author == 1)
                {
                        if ( empty($validation[$field]) )
                        {
                                $validation->error($field, 'not_empty', array($validation[$field]));
                        }
                        elseif ( !valid::email($validation[$field]) )
                        {
                                $validation->error($field, 'invalid', array($validation[$field]));
                        }
                }
        }
        
        /**
	 * Check by triggering error if post exists.
	 * Validation callback.
	 *
	 * @param   Validation  Validation object
	 * @param   string      Field name
	 * @return  void
	 */
	public function valid_post(Validation $validation, $field)
	{
		if( DB::select(array('COUNT("*")', 'total_count'))
			->from('posts')
			->where('id', '=', $this->post_id)
			->execute($this->_db)
			->get('total_count') != 1)
                {
			$validation->error($field, 'invalid', array($validation[$field]));
		}
	}

	/**
	 * Make sure the user has permission to do the action on this object
	 * 
	 * @param String $action The action view|edit|delete default view
	 * @param Object $user   The user object to check permission,
	 * 				defaults to logded in user
	 * @param String $misc	 The misc element usually id|slug for logging purpose
	 * 
	 * @return Void
	 * @throws Exception	Throws Gleez Exception if fails
	 * 
	 */
	public function access( $action = FALSE, Model_User $user = NULL, $misc = NULL)
	{
		if( !$action ) $action = 'view';
	
                if (!in_array($action, array('view', 'edit', 'delete', 'add', 'list'), TRUE))
		{
			// If the $action was not one of the supported ones, we return access denied.
                        throw new HTTP_Exception_404('Unauthorised attempt to non-existent action :act.', array(
				':act' => $action
			));
		}
        
		if (! $this->loaded() )
		{
			// If the $action was not one of the supported ones, we return access denied.
                        throw new HTTP_Exception_404('Attempt to non-existent post.');
		}
	
		// If no user object is supplied, the access check is for the current user.
		if( empty( $user ) )   $user = User::active_user();
        
		if (ACL::check('bypass comment access', $user))
		{
			return $this;
		}
	
		//allow other modules to interact with access
		Module::event('comment_access', $action, $this);
	
		if ($action === 'view')
		{		
			if( $this->status === 'publish' AND ACL::check('access comment', $user))
			{
				return $this;
			}
			// Check if authors can view their own unpublished posts.
			elseif( $this->status != 'publish' AND $this->author == (int)$user->id AND $user->id != 1 )
			{
				return $this;
			}
			elseif( ACL::check('administer comment', $user) )
			{
				return $this;
			}
			else
			{
                                throw new HTTP_Exception_403('Unauthorised attempt to view comment :post.', array(
                                        ':post' => $this->id
                                ));
			}
			
		}
	
		if ($action === 'edit')
		{
			
			if( ACL::check('edit own comment') AND $this->author == (int)$user->id AND $user->id != 1 )
			{
				return $this;
			}
			elseif( ACL::check('administer comment', $user) )
			{
				return $this;
			}
			else
			{
                                throw new HTTP_Exception_403('Unauthorised attempt to edit comment :post', array(
                                        ':post' => $this->id,
                                ));
			}
			
		}
	
		if ($action === 'delete')
		{
			
			if( ( ACL::check('delete own comment') OR ACL::check('delete any comment') ) AND
				$this->author == (int)$user->id AND $user->id != 1 )
			{
				return $this;
			}
			elseif( ACL::check('administer comment', $user) )
			{
				return $this;
			}
			else
			{
				throw new HTTP_Exception_403('Unauthorised attempt to delete comment :post', array(
                                        ':post' => $this->id
                                ));
			}
			
		}
	
                return $this;
	}


	/**
	 * Make sure the user has permission to do the action on this object
	 *
	 * Similar to Comment::access but this return True/False instead of exception
	 * 
	 * @param String $action The action view|edit|delete default view
	 * @param Object $user   The user object to check permission,
	 * 				defaults to logded in user
	 * @param String $misc	 The misc element usually id|slug for logging purpose
	 * 
	 * @return Bool
	 * 
	 */
	public function user_can( $action = FALSE, Model_User $user = NULL, $misc = NULL)
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
        
		if (! $this->loaded() )
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
		Module::event('comment_access', $action, $this);
	
		if ($action === 'view')
		{		
			if( $this->status === 'publish' AND ACL::check('access comment', $user))
			{
				return $this;
			}
			// Check if commenters can view their own unpublished comments.
			elseif( $this->status != 'publish' AND $this->author == (int)$user->id AND $user->id != 1 )
			{
				return $this;
			}
			elseif( ACL::check('administer comment', $user) )
			{
				return $this;
			}
			else
			{
				Kohana::$log->add(Log::NOTICE, 'Unauthorised attempt to view comment :post.', array(
                                        ':post' => $this->id
                                ));
				return FALSE;
			}
			
		}
	
		if ($action === 'edit')
		{
			
			if( ACL::check('edit own comment') AND $this->author == (int)$user->id AND $user->id != 1 )
			{
				return $this;
			}
			elseif( ACL::check('administer comment', $user) )
			{
				return $this;
			}
			else
			{
				Kohana::$log->add(Log::NOTICE, 'Unauthorised attempt to edit comment :post.', array(
                                        ':post' => $this->id
                                ));
				
				return FALSE;
			}
			
		}
	
		if ($action === 'delete')
		{
			
			if( ( ACL::check('delete own comment') OR ACL::check('delete any comment') ) AND
				$this->author == (int)$user->id AND $user->id != 1 )
			{
				return $this;
			}
			elseif( ACL::check('administer comment', $user) )
			{
				return $this;
			}
			else
			{
				Kohana::$log->add(Log::NOTICE, 'Unauthorised attempt to delete comment :post.', array(
                                        ':post' => $this->id
                                ));
				return FALSE;
			}
			
		}
	
                return TRUE;
	}
	
}
