<?php
/**
 * Core Post Class for handling content and content types
 *
 * This is the API for handling content, extend this for handling content types.
 * See blog model for example
 *
 * @package    Gleez\Post
 * @author     Gleez Team
 * @version    1.1.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 *
 * @todo       This class does not do any permission checking
 */
class Post extends ORM_Versioned {

	/**
	 * Special tag for stopping widgets setting
	 * @type string
	 */
	const NO_WIDGETS_TAG = '<!--nowidgets-->';

	/**
	 * Special tag for stopping teaser setting
	 * @type string
	 */
	const TEASER_TAG = '<!--break-->';

	/**
	 * Table columns
	 * @var array
	 */
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
		'lang'     => array( 'type' => 'string' ),
		'layout'   => array( 'type' => 'string' ),
		'image'    => array( 'type' => 'string' ),
	);

	/**
	 * Auto fill updated columns
	 */
	protected $_updated_column = array(
		'column' => 'updated',
		'format' => TRUE
	);

	/**
	 * "Belongs to" relationships
	 * @var array
	 */
	protected $_belongs_to = array(
		'user' => array(
			'foreign_key' => 'author'
		)
	);

	/**
	 * "Has many" relationships
	 * @var array
	 */
	protected $_has_many = array(
		'tags' => array(
			'model'       => 'tag',
			'through'     => 'posts_tags',
			'foreign_key' => 'post_id',
			'far_key'     => 'tag_id'
		),
		'terms' => array(
			'model'       => 'term',
			'through'     => 'posts_terms',
			'foreign_key' => 'post_id',
			'far_key'     => 'term_id'
		),
		'comments' => array(
			'model' => 'comment',
			'foreign_key' => 'post_id'
		)
	);

	/**
	 * Ignored columns
	 * @var array
	 */
	protected $_ignored_columns = array(
		'author_name',
		'author_date',
		'author_pubdate',
		'path',
		'categories',
		'ftags',
		'content'
	);

	/**
	 * Post type
	 * @var string
	 */
	protected $_post_type  = 'post';

	/**
	 * Post table name
	 * @var string
	 */
	protected $_table_name = 'posts';

	/**
	 * Post primary image path
	 * @var string
	 */
	protected $_image_path;

	/**
	 * Post primary image url
	 * @var string
	 */
	protected $_image_url;

	/**
	 * Constructs a new model and loads a record if given
	 *
	 * @param  mixed $id  Parameter for find or object to load [Optional]
	 */
	public function __construct($id = NULL)
	{
		// Set primary image defaults
		$this->_image_path = APPPATH.'media/posts/';
		$this->_image_url  = trim(URL::site('media/posts'), '/').'/';

		parent::__construct($id);
	}

	/**
	 * Rules for the post model
	 *
	 * @return  array  Rules
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
				array(array($this, 'is_valid'), array('author', ':validation', ':field')),
			),
			'created' => array(
				array(array($this, 'is_valid'), array('created', ':validation', ':field')),
			),
			'pubdate' => array(
				array(array($this, 'is_valid'), array('pubdate', ':validation', ':field')),
			),
			'status' => array(
				array('not_empty'),
				array('Post::valid_state', array(':value')),
			),
			'categories' => array(
				array(array($this, 'is_valid'), array('category', ':validation', ':field')),
			),
			'image' => array(
				array(array($this, 'is_valid'), array('image', ':validation', ':field')),
			),
		);
	}

	/**
	 * Labels for fields in this model
	 *
	 * @return  array  Labels
	 */
	public function labels()
	{
		return array(
			'title'    => __('Title'),
			'body'     => __('Body'),
			'teaser'   => __('Teaser'),
			'image'    => __('Primary Image'),
		);
	}

	/**
	 * Validation callback
	 *
	 * @param   string      $name        Validation name
	 * @param   Validation  $validation  Validation object
	 * @param   string      $field       Field name
	 *
	 * @uses    Valid::numeric
	 * @uses    Config::get
	 */
	public function is_valid($name, Validation $validation, $field)
	{
		// Make sure we have a valid term id set
		if ($name == 'category')
		{
			if (isset($this->categories) AND is_array($this->categories))
			{
				foreach ($this->categories as $id => $term)
				{
					if ($term == 'last' OR ! Valid::numeric($term))
					{
						$validation->error('categories', 'invalid', array($validation[$field]));
					}
				}
			}
		}
		// Make sure we have an valid date is set, or current time
		elseif ($name == 'created')
		{
			if ( ! empty($this->author_date) AND ! ($date = strtotime($this->author_date)))
			{
				$validation->error($field, 'invalid', array($this->author_date));
			}
			else
			{
				if (isset($date))
				{
					$this->created = $date;
				}
			}
		}
		// Make sure we have an valid author id set, or a guest id
		elseif ($name == 'author')
		{
			if ( ! empty($this->author_name) AND ! ($account = User::lookup_by_name($this->author_name)))
			{
				$validation->error($field, 'invalid', array($this->author_name));
			}
			else
			{
				if (isset($account))
				{
					$this->author = $account->id;
				}
			}
		}
		// Make sure we have an valid date is set, or current time
		elseif ($name == 'pubdate')
		{
			if ( ! empty($this->author_pubdate) AND ! ($date = strtotime($this->author_pubdate)))
			{
				$validation->error($field, 'invalid', array($validation[$field]));
			}
			else
			{
				if (isset($date))
				{
					$this->pubdate = $date;
				}
			}
		}
		// Make sure we have an valid image is uploaded
		elseif ($name == 'image')
		{
			if (isset($_FILES['image']['name']) AND ! empty($_FILES['image']['name']))
			{
				$allowed_types = Config::get('media.supported_image_formats', array('jpg', 'png', 'gif'));
				$data = Validation::factory($_FILES)
					->rule('image', 'Upload::not_empty')
					->rule('image', 'Upload::valid')
					->rule('image', 'Upload::type', array(':value', $allowed_types));

				if ( ! $data->check() )
				{
					$validation->error($field, 'invalid', array($validation[$field]));
				}
			}
		}
	}

	/**
	 * Make sure that the state is legal
	 *
	 * @param   string  $value  Status name
	 * @return  boolean
	 *
	 * @uses    Post::status
	 */
	public static function valid_state($value)
	{
		return in_array($value, array_keys(Post::status()));
	}

	/**
	 * Override this method to take certain actions before the data is saved
	 *
	 * @uses  System::mkdir
	 * @uses  Upload::save
	 * @uses  Debug::path
	 * @uses  File::getUnique
	 */
	protected function before_save()
	{
		if (isset($_FILES['image']['name']) AND ! empty($_FILES['image']['name']))
		{
			// create directory if not
			System::mkdir($this->_image_path);

			// delete previous image if exists, to cleanup stale images
			$this->_delete_image();

			// generate a unique filename to avoid conflicts
			$filename = File::getUnique($_FILES['image']['name']);

			if ($file = Upload::save($_FILES['image'], $filename, $this->_image_path))
			{
				$this->image = $filename;
			}
		}
	}

	/**
	 * Updates or Creates the record depending on loaded()
	 *
	 * @param   Validation $validation Validation object [Optional]
	 * @return  Post
	 *
	 * @uses    URL::site
	 * @uses    User::active_user
	 * @uses    Config::get
	 * @uses    Cache::delete
	 */
	public function save(Validation $validation = NULL)
	{
		// Set some defaults
		$this->status  = empty($this->status)  ? 'draft' : $this->status;
		$this->promote = empty($this->promote) ? 0 : $this->promote;
		$this->sticky  = empty($this->sticky)  ? 0 : $this->sticky;
		$this->comment = empty($this->comment) ? 0 : $this->comment;

		$this->created = empty($this->created) ? time() : $this->created;
		$this->pubdate = empty($this->pubdate) ? time() : $this->pubdate;
		$this->updated = empty($this->updated) ? time() : $this->updated;

		//Ugly existing image check, not sure why empty behaves strange on $this->rawimage
		$image = isset($this->_original_values['image']) ? $this->_original_values['image'] : FALSE;

		$this->image   = empty($image) 		? NULL : $image;
		$this->type    = empty($this->type)     ? $this->_post_type : $this->type;
		$this->author  = empty($this->author)   ? User::active_user()->id : $this->author;
		$this->format  = empty($this->format)   ? Config::get('inputfilter.default_format', 1) : $this->format;

		// Always save only raw text, unformated text
		$this->teaser  = empty($this->rawteaser) ? $this->_teaser() : $this->rawteaser;
		$this->body    = $this->rawbody;

		parent::save($validation);

		if ( $this->loaded())
		{
			// Add or remove terms
			$this->_terms();

			// Add or remove tags
			$this->_tags();

			// Add or remove path aliases
			$this->aliases();
		}

		Cache::instance($this->type)->delete($this->type.'-'.$this->id);

		return $this;
	}

	/**
	 * Get teaser from the body either by delimiter or size
	 *
	 * @param   integer  $size  Defaults to 105 words [Optional]
	 * @return  string   Teaser
	 * @uses    Text::limit_words
	 */
	protected function _teaser($size = 105)
	{
		// Find where the delimiter is in the body
		$delimiter = strpos($this->rawbody, self::TEASER_TAG);

		// If the size is zero, and there is no delimiter, the entire body is teaser.
		if ($size == 0 AND $delimiter === FALSE)
		{
			return $this->rawbody;
		}

		// If a valid delimiter has been specified, use it to chop off the teaser.
		if ($delimiter !== FALSE)
		{
			return substr($this->rawbody, 0, $delimiter);
		}

		return Text::limit_words($this->rawbody, $size, ' ...');
	}

	/**
	 * Adds or deletes terms
	 */
	private function _terms()
	{
		if ( !empty($this->categories))
		{
			// Filter out empty terms
			$this->categories = array_filter($this->categories);
		}

		if (isset($this->categories) AND is_array($this->categories))
		{
			// Remove the previous terms relationship
			$this->remove('terms');

			foreach ($this->categories as $id => $term)
			{
				// Add the term relationship
				if ( isset($term) AND !empty($term) AND $term != 'last')
				{
					$this->add('terms', (int)$term, array('parent_id' => (int)$id, 'type' => $this->type));
				}
			}
		}
	}

	/**
	 * Adds or deletes terms
	 *
	 * @uses    Tags::tagging
	 */
	private function _tags()
	{
		if (isset($this->ftags))
		{
			$tags = Tags::factory()
				->tagging($this->ftags, $this, $this->author, FALSE);
		}
	}

	/**
	 * Adds or deletes path aliases
	 *
	 * @uses  Module::action
	 * @uses  Path::load
	 * @uses  Path::save
	 */
	protected function aliases()
	{
		// Create and save alias for the post
		$values = array();

		$path = Path::load($this->rawurl);

		if ($path)
		{
			$values['id'] = (int) $path['id'];
		}

		$alias = empty($this->path) ? $this->_object_plural.'/'.$this->title : $this->path;
		$values['source'] = $this->rawurl;
		$values['alias']  = Path::clean( $alias );
		$values['type']   = NULL;
		$values['action'] = empty($this->action) ? $this->type : $this->action;

		$values = Module::action('post_aliases', $values, $this);

		Path::save($values);
	}

	/**
	 * Deletes a single post or multiple posts, ignoring relationships
	 *
	 * @return  Post
	 * @throws  Gleez_Exception
	 *
	 * @uses    Cache::delete
	 * @uses    Path::delete
	 */
	public function delete()
	{
		if ( ! $this->_loaded)
		{
			throw new Gleez_Exception('Cannot delete :model model because it is not loaded.',
				array(':model' => $this->_object_name)
			);
		}

		//delete image if exists, to cleanup stale images
		$this->_delete_image();

		$source = $this->rawurl;
		Cache::instance($this->type)->delete($this->type.'-'.$this->id);
		parent::delete();

		// Delete the path aliases associated with this object
		Path::delete(array('source' => $source));
		unset($source);

		return $this;
	}

	/**
	 * Reading data from inaccessible properties
	 *
	 * @param   string  $field
	 * @return  mixed
	 *
	 * @uses  Text::plain
	 * @uses  Text::markup
	 * @uses  HTML::links
	 * @uses  Path::load
	 * @uses  Route::get
	 * @uses  Route::uri
	 */
	public function __get($field)
	{
		switch ($field)
		{
			case 'title':
				return Text::plain(parent::__get('title'));
			break;
			case 'teaser':
				return Text::markup($this->rawteaser, $this->format);
			break;
			case 'body':
				return Text::markup($this->rawbody, $this->format);
			break;
			case 'terms_form':
				return $this->terms->find()->id;
			break;
			case 'tags_form':
				return $this->tags->find_all()->as_array('id', 'name');
			break;
			case 'taxonomy':
				return HTML::links($this->terms->find_all(), array('class' => 'nav nav-pills pull-right'));
			break;
			case 'tagcloud':
				return HTML::links($this->tags->find_all(), array('class' => 'nav nav-pills'));
			break;
			case 'links':
				return HTML::links($this->links(), array('class' => 'links inline'));
			break;
			case 'rawtitle':
				// Raw fields without markup. Usage: during edit or etc!
				return parent::__get('title');
			break;
			case 'rawteaser':
				// Raw fields without markup. Usage: during edit or etc!
				return parent::__get('teaser');
			break;
			case 'rawbody':
				// Raw fields without markup. Usage: during edit or etc!
				return parent::__get('body');
			break;
			case 'rawurl':
				return Route::get($this->type)->uri(array( 'id' => $this->id, 'action' => 'view'));
			break;
			case 'rawimage':
				// Raw fields without path. Usage: during edit or etc!
				return parent::__get('image');
			break;
			case 'url':
				// Model specific links; view, edit, delete url's
				return ($path = Path::load($this->rawurl)) ? $path['alias'] : $this->rawurl;
			break;
			case 'edit_url':
				return Route::get($this->type)->uri(array('id' => $this->id, 'action' => 'edit'));
			break;
			case 'delete_url':
				return Route::get($this->type)->uri(array('id' => $this->id, 'action' => 'delete'));
			break;
			case 'image':
				return $this->rawimage ? $this->_image_url.$this->rawimage : NULL;
			break;
			case 'count_comments':
				return (int) DB::select(array(DB::expr('COUNT(*)'), 'mycount'))
					->from('comments')
					->where('status', '=', 'publish')
					->where('post_id', '=', $this->id)
					->execute()
					->get('mycount');
			break;
		}

		return parent::__get($field);
	}

	/**
	 * List of status
	 *
	 * @return  array  Statuses
	 * @uses    Module::action
	 */
	public static function status()
	{
		$states = array(
			'archive' => __('Archive'),
			'draft'   => __('Draft'),
			'private' => __('Private'),
			'publish' => __('Published'),
		);

		$values = Module::action('post_status', $states);

		return $values;
	}

	/**
	 * List of links
	 *
	 * @return  array  Links
	 *
	 * @uses    Module::action
	 * @uses    Request::current
	 * @uses    Request::uri
	 */
	public function links()
	{
		$links = array(
			'more'   => array('link' => $this->url,        'name' => __('Read More')),
			'edit'   => array('link' => $this->edit_url,   'name' => __('Edit')),
			'delete' => array('link' => $this->delete_url, 'name' => __('Delete')),
		);

		// Unset read more link on full page view
		if (Request::current()->uri() == $this->url)
		{
			unset($links['more']);
		}

		$values = Module::action('post_links', $links);

		return $values;
	}

	/**
	 * Bulk actions
	 *
	 * @param   boolean  $list  TRUE for dropdown for bulk actions [Optional]
	 * @param   string   $type  Type of post [Optional]
	 * @return  mixed    States
	 *
	 * @uses    Post::bulk_update
	 * @uses    Post::bulk_convert
	 * @uses    Module::action
	 */
	public static function bulk_actions($list = FALSE, $type = 'post')
	{
		$states = array(
			'publish'    => array(
				'label'     => __('Publish'),
				'callback'  => 'Post::bulk_update',
				'arguments' => array('updates' => array('status' => 'publish')),
			),
			'unpublish'  => array(
				'label'     => __('Unpublish'),
				'callback'  => 'Post::bulk_update',
				'arguments' => array('updates' => array('status' => 'draft')),
			),
			'promote'    => array(
				'label'     => __('Promote to front page'),
				'callback'  => 'Post::bulk_update',
				'arguments' => array('updates' => array('status' => 'publish', 'promote' => 1)),
			),
			'demote'     => array(
				'label'     => __('Demote from front page'),
				'callback'  => 'Post::bulk_update',
				'arguments' => array('updates' => array('promote' => 0)),
			),
			'sticky'     => array(
				'label'     => __('Make sticky'),
				'callback'  => 'Post::bulk_update',
				'arguments' => array('updates' => array('sticky' => 1)),
			),
			'unsticky'   => array(
				'label'     => __('Remove stickiness'),
				'callback'  => 'Post::bulk_update',
				'arguments' => array('updates' => array('sticky' => 0)),
			),
			'delete'     => array(
				'label'     => __('Delete'),
				'callback'  => NULL,
			),
			'ct_page'    => array(
				'label'     => __('Convert to @page', array('@page' => __('Page'))),
				'callback'  => 'Post::bulk_convert',
				'arguments' => array('actions' => array('page')),
			),
			'ct_blog'    => array(
				'label'     => __('Convert to @blog', array('@blog' => __('Blog'))),
				'callback'  => 'Post::bulk_convert',
				'arguments' => array('actions' => array('blog')),
			),
			'ct_forum'   => array(
				'label'     => __('Convert to @forum', array('@forum' => __('Forum'))),
				'callback'  => 'Post::bulk_convert',
				'arguments' => array('actions' => array('forum')),
			),
		);

		// Allow module developers to override
		$values = Module::action('post_bulk_actions', $states);

		if($list)
		{
			$options = array('' => __('Bulk Options'));
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
	 * Bulk update posts
	 *
	 * Usage:<br>
	 * <code>
	 *    Post::bulk_update(array(1, 2, 3, ...), array('status' => 'publish', 'promote' => 1), 'blog');
	 * </code>
	 *
	 * @param   array   $ids      Array of post id's
	 * @param   array   $actions  Array of post actions
	 * @param   string  $type     Type of post [Optional]
	 */
	public static function bulk_update(array $ids, array $actions, $type = 'post')
	{
		$posts = ORM::factory($type)
			->where('id', 'IN', $ids)
			->find_all();

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
	 * Bulk delete posts
	 *
	 * Usage:<br>
	 * <code>
	 *    Post::bulk_delete(array(1, 2, 3, ...), 'blog');
	 * </code>
	 *
	 * @param   array   $ids   Array of post id's
	 * @param   string  $type  Type of post [Optional]
	 */
	public static function bulk_delete(array $ids, $type = 'post')
	{
		$posts = ORM::factory($type)
			->where('id', 'IN', $ids)
			->find_all();

		foreach($posts as $post)
		{
			$post->delete();
		}

	}

	/**
	 * Bulk convert post type(s)
	 *
	 * Usage:<br>
	 * <code>
	 *    Post::bulk_convert(array(1, 2, 3, ...), 'blog');
	 * </code>
	 *
	 * @param   array   $ids      Array of post id's
	 * @param   array   $actions  Array of post type (new type)
	 * @param   string  $type     Type of post [Optional]
	 * @uses    Path::delete
	 */
	public static function bulk_convert(array $ids, array $actions, $type)
	{
		$new_type = (string) $actions[0];

		$posts = ORM::factory($type)
			->where('id', 'IN', $ids)
			->find_all();

		foreach($posts as $post)
		{
			// Delete the path aliases associated with this object
			Path::delete(array('source' => $post->rawurl));

			// Remove the previous terms relationship
			$post->remove('terms');

			// Remove the previous tags relationship
			$post->remove('tags');

			// Update the type column in comments
			DB::update('comments')
				->set(array('type' => $new_type))
				->where('post_id', '=', $post->id)
				->execute();

			// Set the post type to new type
			$post->type = $new_type;

			// Be sure unpublish the converted posts
			$post->status  = 'draft';
			$post->promote = 0;
			$post->sticky  = 0;

			// Finally update the object
			$post->save();
		}
	}

	/**
	 * Display widgets inline of post body
	 *
	 * @param   string  $content  The post content
	 * @param   string  $region   The widget's region name
	 * @return  string  The replaced content with widgets
	 * @uses    Widgets::render
	 */
	public static function widgets($content, $region = 'post_inline')
	{
		// Save some cpu cycles, when the content is empty
		if ($content == NULL or empty($content))
		{
			return $content;
		}

		// We found special tag, so don't set widgets!
		// Just return the content
		if (strpos($content, self::NO_WIDGETS_TAG) !== FALSE)
		{
			return $content;
		}

		$poses = array();
		$lastpos = -1;
		$repchar = "<p";

		// if we didn't find a p tag, try br tag
		if (strpos($content, "<p") === FALSE)
		{
			$repchar = "<br";
		}

		while (strpos($content, $repchar, $lastpos+1) !== FALSE)
		{
			$lastpos = strpos($content, $repchar, $lastpos+1);
			$poses[] = $lastpos;
		}

		// Cut the doc in half, so the widgets don't go past the end of the article.
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
	 * @param   integer  $id      The post id
	 * @param   string   $type    The post type
	 * @param   object   $config  The post type config object
	 * @return  object   $post    The post object
	 * @throws  HTTP_Exception_404
	 */
	public static function dcache($id, $type, $config)
	{
		$cache     = Cache::instance($type);
		$use_cache = (bool) $config->get('use_cache', FALSE);
		$post      = ($use_cache) ? $cache->get("$type-$id", FALSE) : FALSE;

		if (empty($post))
		{
			$post = ORM::factory($type, $id);

			if ( ! $post->loaded())
			{
				throw HTTP_Exception::factory(404, 'Attempt to access non-existent post.');
			}

			$post->content = View::factory($type."/body")->set('config', $config)->bind('post', $post)->render();

			if ($use_cache)
			{
				$data               = array();
				$data['author']     = (int)$post->author;
				$data['status']     = $post->status;
				$data['title']      = $post->title;
				$data['comment']    = $post->comment;
				$data['rawurl']     = $post->rawurl;
				$data['url']        = $post->url;
				$data['edit_url']   = $post->edit_url;
				$data['delete_url'] = $post->delete_url;
				$data['id']         = (int)$post->id;
				$data['type']       = $post->type;
				$data['image']      = $post->image;
				$data['content']    = (string) $post->content;

				$cache->set($type.'-'.$id, (object) $data, DATE::WEEK);
			}
		}

		return $post;
	}

	/**
	 * Gets recent articles (post, page, blog, etc.)
	 *
	 * Return FALSE if articles not found
	 *
	 * @since   1.1.0
	 *
	 * @param   array  $args  Array of arguments. Overrides defaults [Optional]
	 * @return  mixed
	 *
	 * @uses    Arr::unpack_string
	 * @uses    Post::status
	 * @uses    System::parse_args
	 * @uses    Cache::get
	 * @uses    Cache::set
	 */
	public static function recent_posts(array $args = array())
	{
		$default = array(
			'limit'     => 10,
			'offset'    => 0,
			'type'      => 'post',
			'orderby'   => 'created',
			'order'     => 'DESC',
			'status'    => Arr::unpack_string(array_keys(Post::status()), FALSE, ','),
			'use_cache' => TRUE,
			'as_array'  => TRUE,
		);

		$params = (object) System::parse_args($args, $default);
		$cache  = Cache::instance('post');
		$post   = $params->use_cache ? $cache->get('recent_'.$params->type, NULL) : NULL;

		if (empty($post))
		{
			$post = ORM::factory($params->type)
						->where('status', 'IN', $params->status)
						->order_by($params->orderby, $params->order)
						->limit($params->limit)
						->offset($params->offset)
						->find_all();

			if ($params->as_array)
			{
				$post->as_array();
			}

			if ($params->use_cache)
			{
				$cache->set('recent_'.$params->type, $post, Date::HOUR);
			}
		}

		return ( ! empty($post)) ? $post : FALSE;
	}

	/**
	 * Deletes the primary image; used during upload and delete
	 *
	 * @return Post
	 */
	protected function _delete_image()
	{
		if($this->rawimage AND file_exists($this->_image_path.$this->rawimage))
		{
			@unlink($this->_image_path.$this->rawimage);
		}

		return $this;
	}
}
