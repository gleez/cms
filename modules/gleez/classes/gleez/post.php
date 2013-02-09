<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Core Post Class for handling content and content types.
 *
 * This is the API for handling content, extend this for handling content types.
 * see blog model for example
 *
 * Note: by design, this class does not do any permission checking.
 *
 * @package	Gleez
 * @category	Post
 * @author	Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2012 Gleez Technologies
 * @license	http://gleezcms.org/license
 */
class Gleez_Post extends ORM_Versioned {

	protected $_table_columns = array(
					'id'       => array( 'type' => 'int' ),
					'version'  => array( 'type' => 'int' ),
					'author'   => array( 'type' => 'int' ),
					'title'    => array( 'type' => 'string' ),
					'body'     => array( 'type' => 'string' ),
					'teaser'   => array( 'type' => 'string' ),
					'status'   => array( 'type' => 'string' ),
					'promote'  => array( 'type' => 'int' ),
					'moderate' => array( 'type' => 'int' ),
					'sticky'   => array( 'type' => 'int' ),
					'type'     => array( 'type' => 'string' ),
					'format'   => array( 'type' => 'int' ),
					'created'  => array( 'type' => 'int' ),
					'updated'  => array( 'type' => 'int' ),
					'pubdate'  => array( 'type' => 'int' ),
					'password' => array( 'type' => 'string' ),
					'comment'  => array( 'type' => 'int' ),
					'lang' => array( 'type' => 'string' ),
					);
	
	/**
	 * Auto fill create and update columns
	 */
	//protected $_created_column = array('column' => 'created', 'format' => TRUE);
	protected $_updated_column = array('column' => 'updated', 'format' => TRUE);
        
	protected $_belongs_to = array( 'user' => array('foreign_key' => 'author') );
	protected $_has_one    = array( 'book' => array('model'   => 'book', 'foreign_key' => 'post_id' ) );
        protected $_has_many   = array(
					'tags'     => array('through' => 'posts_tags',  'foreign_key' => 'post_id' ),
                                        'terms'    => array('through' => 'posts_terms', 'foreign_key' => 'post_id' ),
					'comments' => array('model'   => 'comment',     'foreign_key' => 'post_id' )
                                );

	protected $_ignored_columns = array('author_name', 'author_date', 'author_pubdate', 'path', 'categories', 'ftags', 'fbook', 'fbook_pid', 'content');
	
	/**
	 * @access  protected
	 * @var     string  post_type post
	 */
        protected $_post_type  = 'post';
        
        /**
	 * Rules for the post model.
	 *
	 * @return array Rules
	 */
	public function rules()
	{
		return array(
			'title' => array(
				array('not_empty'),
			),
			'body' => array(
				array('not_empty'),
				array('min_length', array(':value', 10)),
			),
                        'author' => array(
                                array(array($this, 'valid_author'), array(':validation', ':field')),
                        ),
                        'created' => array(
                                array(array($this, 'valid_created'), array(':validation', ':field')),
                        ),
                        'pubdate' => array(
                                array(array($this, 'valid_pubdate'), array(':validation', ':field')),
                        ),
			'status' => array(
				array('not_empty'),
                                array('Post::valid_state', array(':value')),
                        ),
			'categories' => array(
                                array(array($this, 'valid_category'), array(':validation', ':field')),
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
			'title'    => 'Title',
			'body'     => 'Body',
			'teaser'   => 'Teaser',
		);
	}
        
	/**
	 * Make sure we have a valid term id set.
	 * Validation callback.
	 *
	 * @param   Validation  Validation object
	 * @param   string      Field name
	 * @return  void
	 */
	public function valid_category(Validation $validation, $field)
	{
		if( isset($this->categories) AND is_array($this->categories) )
		{
			foreach ($this->categories as $id => $term)
			{
				if($term == 'last' OR !Valid::numeric($term) )
					$validation->error('categories', 'invalid', array($validation[$field]));
			}
		}
	}
	
	/**
	 * Make sure we have an valid author id set, or a guest id.
	 * Validation callback.
	 *
	 * @param   Validation  Validation object
	 * @param   string      Field name
	 * @return  void
	 */
	public function valid_tags(Validation $validation, $field)
	{
		if( isset($this->ftags) AND is_array($this->ftags) )
		{

		}
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
                        $validation->error($field, 'invalid', array($this->author_name));
                }
                else
                {
			if( isset($account) )  $this->author = $account->id;
                }
	}

        /**
	 * Make sure we have an valid date is set, or current time.
	 * Validation callback.
	 *
	 * @param   Validation  Validation object
	 * @param   string      Field name
	 * @return  void
	 */
	public function valid_created(Validation $validation, $field)
	{
                if( ! empty($this->author_date) AND ($date = strtotime($this->author_date)) === false )
                {
                        $validation->error($field, 'invalid', array($this->author_date));
                }
		else
		{
			if( isset($date) ) $this->created = $date;
		}
	}

        /**
	 * Make sure we have an valid date is set, or current time.
	 * Validation callback.
	 *
	 * @param   Validation  Validation object
	 * @param   string      Field name
	 * @return  void
	 */
	public function valid_pubdate(Validation $validation, $field)
	{
                if( ! empty($this->author_pubdate) AND ($date = strtotime($this->author_pubdate)) === false )
                {
                        $validation->error($field, 'invalid', array($validation[$field]));
                }
        	else
		{
			if( isset($date) ) $this->pubdate = $date;
		}
	}
        
	/**
         * Make sure that the state is legal.
         */
        public static function valid_state($value)
        {
                return in_array( $value, array_keys( Post::status() ) );
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
		$this->status  = empty($this->status)  ? 'draft' : $this->status;
		$this->promote = empty($this->promote) ? 0 : $this->promote;
		$this->sticky  = empty($this->sticky)  ? 0 : $this->sticky;
		$this->comment = empty($this->comment) ? 0 : $this->comment;
	
		$this->created = empty($this->created) ? time() : $this->created;
		$this->pubdate = empty($this->pubdate) ? time() : $this->pubdate;
		$this->updated = empty($this->updated) ? time() : $this->updated;
	
		$this->type    = empty($this->type)    ? $this->_post_type : $this->type;
		$this->author  = empty($this->author)  ? User::active_user()->id : $this->author;
		$this->format  = empty($this->format)  ? Kohana::$config->load('inputfilter.default_format', 1) : $this->format;
	
		//always save only raw text, unformated text
		$this->teaser  = empty($this->teaser)  ? Text::limit_words( $this->rawbody, 105, ' ...')  : $this->rawteaser;
		$this->body = $this->rawbody;
	
		parent::save( $validation );
	
		if ( $this->loaded())
		{
			//add or remove terms
			$this->_terms();
		
			//add or remove tags
			$this->_tags();
		
			//add or remove path aliases
			$this->aliases();
		
			//add or remove book pages
			$this->_books();
		}
	
		Cache::instance($this->type)->delete($this->type.'-'.$this->id);
		return $this;
	}

	/**
	 * Adds or deletes terms
	 *
	 * @return void
	 */
	private function _terms()
	{
		if ( !empty($this->categories) ) $this->categories = array_filter($this->categories); // Filter out empty terms
		
		if( isset($this->categories) AND is_array($this->categories) )
		{
			//remove the previous terms relationship
			$this->remove('terms');
		
			foreach ($this->categories as $id => $term)
			{
				//add the term relationship
				if(  isset($term) AND !empty($term) AND $term != 'last')
				{
					$this->add('terms', (int)$term, array('parent_id' => (int)$id, 'type' => $this->type) );
				}
			}
		}
	}
	
	/**
	 * Adds or deletes terms
	 *
	 * @return void
	 */
	private function _tags()
	{
		if( isset($this->ftags) )
		{
			$tags = Tags::factory()->tagging($this->ftags, $this, $this->author, false);
		}
	}
	
	/**
	 * Adds or deletes path aliases
	 *
	 * @return void
	 */
	protected function aliases()
	{
		// create and save alias for the post
		$values = array();
		
		$path	= Path::load($this->rawurl);
		if( $path ) $values['id'] = (int) $path['id'];
	
		$alias  = empty($this->path) ? $this->_object_plural.'/'.$this->title : $this->path;
		$values['source'] = $this->rawurl;
		$values['alias']  = Path::clean( $alias );
		$values['type']   = NULL;
		$values['action'] = empty($this->action) ? $this->type : $this->action;
	
		$values = Module::action('post_aliases', $values, $this);
		Path::save($values);
	}
	
	/**
	 * Adds or deletes book relationships
	 *
	 * @return void
	 */
	private function _books()
	{
		if( isset($this->fbook) AND class_exists('book') AND !empty($this->fbook) )
		{
			//Message::debug( Debug::vars($this->fbook) );
			Book::save($this);
		}
	}
	
	/**
	 * Deletes a single post or multiple posts, ignoring relationships.
	 *
	 * @chainable
	 * @return ORM
	 */
	public function delete()
	{
		if ( ! $this->_loaded)
			throw new Kohana_Exception('Cannot delete :model model because it is not loaded.', array(':model' => $this->_object_name));

		$source = $this->rawurl;
		parent::delete();
	
		//Delete the path aliases associated with this object
		Path::delete( array('source' => $source) );
		unset($source);
	
		return $this;
	}
	
	public function __get($field)
	{
                if( $field === 'title' )
			return Text::plain( parent::__get('title') );

                if ( $field === 'teaser' )
			return Text::markup( $this->rawteaser, $this->format );
	
                if( $field === 'body' )
			return Text::markup( $this->rawbody, $this->format );
        
		if($field === 'terms_form')
			return $this->terms->find()->id;
	
		if($field === 'tags_form')
			return $this->tags->find_all()->as_array('id', 'name');
	
		if($field === 'taxonomy')
			return HTML::links($this->terms->find_all(), array('class' => 'nav nav-pills pull-right'));
	
		if($field === 'tagcloud')
			return HTML::links($this->tags->find_all(), array('class' => 'nav nav-pills'));
	
		if($field === 'links')
			return HTML::links($this->links(), array('class' => 'links inline'));
	
                //Raw fields without markup. Usage: during edit or etc!
                if( $field === 'rawtitle' )
			return parent::__get('title');

                if( $field === 'rawteaser' )
			return parent::__get('teaser');
        
                if( $field === 'rawbody' )
			return parent::__get('body');

                if( $field === 'rawurl' )
			return Route::get($this->type)->uri( array( 'id' => $this->id ) );
	
                // Model specefic links; view, edit, delete url's.
                if( $field === 'url' )
			return ($path = Path::load($this->rawurl) ) ? $path['alias'] : $this->rawurl;
	
                if( $field === 'edit_url' )
			return Route::get($this->type)->uri( array( 'id' => $this->id, 'action' => 'edit' ) );

                if( $field === 'delete_url' )
			return Route::get($this->type)->uri( array( 'id' => $this->id, 'action' => 'delete' ) );

		if( $field === 'count_comments' )
		{
			return (int) DB::select('COUNT("*") AS mycount')
						->from('comments')
						->where('status', '=', 'publish')
						->where('post_id', '=', $this->id)
						->execute()->get('mycount');
		}
	
                return parent::__get($field);
        }

	/**
	 *  list of status
         *  @return array statuses
         */
        public static function status()
        {
		$states = array(
				'archive'   => __('Archive'),
                                'draft'     => __('Draft'),
                                'private'   => __('Private'),
                                'publish'   => __('Publish'),
				);
	
		$values = Module::action('post_status', $states);
                return $values;
        }

	/**
	 *  list of links
         *  @return array links
         */
        public function links()
        {
		$links = array(
				'more'   => array('link' => $this->url,        'name' => __('Read More')),
				'edit'   => array('link' => $this->edit_url,   'name' => __('Edit')),
				'delete' => array('link' => $this->delete_url, 'name' => __('Delete')),
				);
	
		//unset read more link on full page view
		if( Request::current()->uri() == $this->url ) unset( $links['more']);

		$values = Module::action('post_links', $links);
                return $values;
        }
	
	/**
	 *  list of actions
	 *  
	 *  @param 	boolean   true for dropdown for bult actions
         *  @return 	array 	  states
         */
        public static function bulk_actions( $list = FALSE, $type = 'post' )
        {
		$states = array(
				'publish'   => array(
						'label' => __('Publish'),
						'callback' => 'Post::bulk_update',
						'arguments' => array('updates' => array('status' => 'publish')),
						),
                                'unpublish' => array(
						'label' => __('Unpublish'),
						'callback' => 'Post::bulk_update',
						'arguments' => array('updates' => array('status' => 'draft')),
						),
                                'promote'   => array(
						'label' => __('Promote to front page'),
						'callback' => 'Post::bulk_update',
						'arguments' => array('updates' => array('status' => 'publish', 'promote' => 1)),
						),
                                'demote'    => array(
						'label' => __('Demote from front page'),
						'callback' => 'Post::bulk_update',
						'arguments' => array('updates' => array('promote' => 0)),
						),
                                'sticky'    => array(
						'label' => __('Make sticky'),
						'callback' => 'Post::bulk_update',
						'arguments' => array('updates' => array('sticky' => 1)),
						),
                                'unsticky'  => array(
						'label' => __('Remove stickiness'),
						'callback' => 'Post::bulk_update',
						'arguments' => array('updates' => array('sticky' => 0)),
						),
                                'delete'    => array(
						'label' => __('Delete'),
						'callback' => NULL,
						),
				'ct_page'    => array(
						'label' => __('Convert to @page', array('@page' => 'Page')),
						'callback' => 'Post::bulk_convert',
						'arguments' => array('actions' => array('page') ),
						),
				'ct_blog'    => array(
						'label' => __('Convert to @blog', array('@blog' => 'Blog') ),
						'callback' => 'Post::bulk_convert',
						'arguments' => array('actions' => array('blog') ),
						),
				'ct_forum'    => array(
						'label' => __('Convert to @forum', array('@forum' => 'Forum')),
						'callback' => 'Post::bulk_convert',
						'arguments' => array('actions' => array('forum') ),
						),
				);
	
		//allow module developers to override
		$values = Module::action('post_bulk_actions', $states);
	
		if($list)
		{
			$options = array();
			foreach ($values as $operation => $array)
			{
				if( $operation == "ct_{$type}") continue;
				$options[$operation] = $array['label'];
			}
		
			return $options;
		}
	
                return $values;
        }

	/**
	 *  Bulk update posts
	 *  
	 *  @param 	array   $ids 	 	array of post id's
	 *  @param 	array   $actions 	array of post actions ex (status = publish, promote = 1 )
	 *  @param 	string  $type 		type of post
         *  @return 	void
         */
	public static function bulk_update(array $ids, array $actions, $type = 'post')
	{
		//Message::debug( Debug::vars($type));
		$posts = ORM::factory($type)->where('id', 'IN', $ids)->find_all();
		foreach($posts as $post)
		{
			foreach ($actions as $name => $value)
			{
				$post->$name = $value;
			}
			$post->save();
		}
		
	}

	/**
	 *  Bulk delete posts
	 *  
	 *  @param 	array   $ids 	array of post id's
	 *  @param 	string  $type 	type of post
         *  @return 	void
         */
	public static function bulk_delete(array $ids, $type = 'post')
	{
		$posts = ORM::factory($type)->where('id', 'IN', $ids)->find_all();
		foreach( $posts as $post )
		{
			$post->delete();
		}
		
	}

	/**
	 *  Bulk convert post type(s)
	 *  
	 *  @param 	array   $ids 	 	array of post id's
	 *  @param 	array   $actions 	array of post type (new type)
	 *  @param 	string  $type 		type of post
         *  @return 	void
         */
	public static function bulk_convert(array $ids, array $actions, $type)
	{
		$new_type = (string)$actions[0];
	
		$posts = ORM::factory($type)->where('id', 'IN', $ids)->find_all();
		foreach($posts as $post)
		{
			//Delete the path aliases associated with this object
			Path::delete( array('source' => $post->rawurl));
	
			//remove the previous terms relationship
			$post->remove('terms');

			//remove the previous tags relationship
			$post->remove('tags');
	
			/*
			//update the type column in tags, post_tags
			$tags = $post->tags->find_all()->as_array('id', 'id');
			if( $tags = array_filter($tags) )
			{
				DB::update('tags')->set( array('type' => $new_type) )
						->where('id', 'IN', $tags)->execute();

				DB::update('posts_tags')->set( array('type' => $new_type) )
						->where('post_id', '=', $post->id)->execute();
			}
			*/

			//update the type column in comments
			DB::update('comments')->set( array('type' => $new_type) )
					->where('post_id', '=', $post->id)->execute();
	
			//set the post type to new type
			$post->type = $new_type;
	
			//be sure unpublish the converted posts
			$post->status  = 'draft';
			$post->promote = 0;
			$post->sticky  = 0;
	
			//finally update the object
			$post->save();
		}
	}
	
	/**
	 * Display widgets inline of post body.
	 *
	 * @param 	string 	$content 	The post content
	 * @param 	string 	$region  	The widget's region name
	 * @return 	string 	$content 	The replaced content with widgets
	 */
	public static function widgets( $content, $region = 'post_inline' )
	{
		//save some cpu cycles, when the content is empty
		if($content == NULL or empty($content)) return $content;
	
		//We found this special tag, so dont set widgets! Just return the content
		if(strpos($content, "<!--nowidgets-->") !== false) return $content;

		$poses = array();
		$lastpos = -1;
		$repchar = "<p";
	
		//if we didn't find a p tag, try br tag
		if(strpos($content, "<p") === false) $repchar = "<br";
	
		while(strpos($content, $repchar, $lastpos+1) !== false)
		{
			$lastpos = strpos($content, $repchar, $lastpos+1);
			$poses[] = $lastpos;
		}

		//cut the doc in half, so the widgets don't go past the end of the article.
		$pickme = $poses[ceil(sizeof($poses)/2) -1];

		$widgets     = Widgets::instance()->render($region);
		$replacewith = ($widgets) ? '<div id="'.$region.'" class="clear-block">'.$widgets.'</div>' : NULL;
		$content     = substr_replace($content, $replacewith.$repchar, $pickme, 2);
	
		// save some memory
		unset($poses, $lastpos, $repchar, $half, $pickme, $widgets, $replacewith);
	
		return $content;
	}

	/**
	 * Dynamic per post cache for performance
	 *
	 * @param 	int 	$id 	The post id
	 * @param 	string 	$type 	The post type
	 * @param 	object 	$config	The post type config object
	 *
	 * @return	object 	$post	The post object
	 * @throws	HTTP_Exception_404
	 */
	public static function dcache($id, $type, $config)
	{
		$cache  = Cache::instance($type);

		if( !$post = $cache->get("$type-$id") )
		{
			$post = ORM::factory($type, $id);
			if( ! $post->loaded() ) throw new HTTP_Exception_404('Attempt to non-existent post.');
			$post->content = View::factory('page/body')->set('config', $config)->bind('post', $post);

			$data = array();
			$data['author']  = (int)$post->author;
			$data['status']  = $post->status;
			$data['title']   = $post->title;
			$data['comment'] = $post->comment;
			$data['url']     = $post->url;
			$data['id']      = (int)$post->id;
			$data['type']    = $post->type;
			$data['content'] = (string) $post->content;
	
			$cache->set("$type-$id", (object) $data, DATE::WEEK);
		}
	
		return $post;
	}
	
}