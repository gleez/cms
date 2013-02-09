<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 *  An adaptation of Freetag
 *
 * @package	Gleez
 * @category	Tags
 * @author	Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2012 Gleez Technologies
 * @license	http://gleezcms.org/license
 */
class Gleez_Tags {

	// Configuration
	protected $config;
	
	/**
	 * Tag instance
	 *
	 * @var string
	 */
	protected static $_instance;

	/**
	 * Create an instance of Tags.
	 *
	 * @return  object
	 */
	public static function factory($config = array())
	{
		if ( ! isset(Tags::$_instance))
		{
			// Create a new session instance
			Tags::$_instance = new Tags($config);
		}

		return Tags::$_instance;
	}

	/**
	 * Loads configuration options.
	 *
	 * @return  void
	 */
	public function __construct($config = array())
	{
		// Append default tags configuration
		$config += Kohana::$config->load('tags')->as_array();

		// Save the config in the object
		$this->config = $config;

		Kohana::$log->add(Log::DEBUG, 'Tags Library loaded');
	}
	
	/**
	* Tag Object
	*
	* This function allows you to pass in a string directly from a form, which is then
	* parsed for quoted phrases and special characters, normalized and converted into tags.
	* The tag phrases are then individually sent through the safe_tag() method for processing
	* and the object referenced is set with that tag.
	*
	* This method has been refactored to automatically look for existing tags and run
	* adds/updates/deletes as appropriate. 
	*
	* @param	string		tags		The raw string form of the tag to delete. See above for notes.
	* @param	Model   	Object		The Model Object
	* @param	int		user_id		The User id
	* @param	boolean	Whether to skip the update portion for objects that haven't been tagged. (Default: TRUE)
	* @return	boolean	Returns TRUE if successful, FALSE otherwise.
	*/
	public function tagging( $tags, Model $object, $user_id = FALSE, $skip_updates = TRUE) 
	{
		//if (empty($tags))   return array(); // If the tag string is empty, return the empty set.
		if ( ! $user_id )   return FALSE;
		if ( ! $object )    return FALSE;
	
		$tags = Tags::explode($tags);
		$old_tags = $object->tags->find_all();
	
		$preserve_tags = array();
		$remove_tags = array();
		
		if ( ! $skip_updates AND count($old_tags)) 
		{
			foreach ($old_tags as $tag)
			{
				if ( ! in_array($tag->name, $tags)) 
				{
					$remove_tags[] = intval($tag->id);
				}
				else 
				{
					// We need to preserve old tags that appear (to save timestamps)
					$preserve_tags[] = $tag->name;
				}
			}
		}
		
		if( count($remove_tags) )
		{
			// remove unexisting tags
			$object->remove('tags', $remove_tags);
		}
		
		$new_tags = array_diff($tags, $preserve_tags);

		$this->_tag_object_array($user_id, $object, $new_tags);
	
		return TRUE;
	}
	
	/**
	* Tag Object Array
	*
	* Private method to add tags to an object from an array.
	*
	* @param	integer	user_id		Unique ID of user.
	* @param	Model   Object		The Model Object
	* @param	array	tags		Array of tags to be add.
	* @return	boolean	TRUE if successful, FALSE otherwise.
	*/
	private function _tag_object_array($user_id, Model $object, $tags) 
	{	
		foreach($tags as $tag) 
		{
			$tag = trim($tag);
			
			if ( ! empty($tag) AND (strlen($tag) <= $this->config['max_tag_length'])) 
			{
				$this->safe_tag($user_id, $object, $tag);
			}
		}
		
		return TRUE;
	}

	/**
	* Safe Tag
	*
	* Pass individual tag phrases along with object and object ID's in order to
	* set a tag on an object. If the tag in its raw form does not yet exist,
	* this function will create it.
	* Fails transparently on duplicates, and checks for dupes based on the
	* block_multiuser_tag_on_object constructor param.
	*
	* @param	int	user_id		The	user_id	unique ID of the person who tagged the object with this tag.
	* @param	Model   Object		The Model Object
	* @param	string	tag		A raw string from a web form containing tags.
	* @return	boolean	Returns true if successful, false otherwise. Does not operate as a transaction.
	*/
	public function safe_tag($user_id = 0, Model $object, $tag = '') 
	{
		$object_id = $object->id;
		
		if ( ! $user_id = intval($user_id) or ! $object_id = intval($object_id) or empty($tag)) 
		{
			return FALSE;
		}

		if ( ! empty($this->config['append_to_integer']) and is_numeric($tag) and intval($tag) == $tag) 
		{
			// Converts numeric tag "123" to "123_" to facilitate
			// alphanumeric sorting (otherwise, PHP converts string to
			// true integer).
			$tag = preg_replace('/^([0-9]+)$/', "$1".$this->config['append_to_integer'], $tag);
		}

		$normalized_tag = $this->normalize_tag( strtolower($tag) );
		
		//this is required to avoid duplicate tags, ex: 'demo, demo, test'
		$result    = ORM::factory(inflector::singular($this->config['tagging_model']))
				->join($this->config['tag_table'], 'INNER')
				->on('tag_id', '=', $this->config['tag_table'].'.id' )
				->where($this->config['object_foreign_key'], '=', $object_id)
				->where($this->config['tag_table'].'.type', '=', $object->type)
				->where('name', '=', $normalized_tag);

		if ($result->reset(FALSE)->count_all() > 0) 
		{
			return TRUE;
		}
	
		// Then see if a tag in this form exists.
		$result = ORM::factory(inflector::singular($this->config['tag_table']))
				->where('name', '=', $tag)->where('type', '=', $object->type);

		if ($result->reset(FALSE)->count_all() > 0) 
		{
			$result = $result->find();
			$tag_id = $result->id;
		}
		else 
		{
			// Add new tag!
			$new_tag = ORM::factory(inflector::singular($this->config['tag_table']));
			$new_tag->name = $normalized_tag;
			$new_tag->type = $object->type;
			$new_tag->save();

			$tag_id = $new_tag->id;
		}
		
		if ( ! ($tag_id > 0)) 
		{
			return FALSE;
		}

		$new_tagging = ORM::factory(inflector::singular($this->config['tagging_model']));
		$new_tagging->tag_id = $tag_id;
		$new_tagging->author = $user_id;
		$new_tagging->type   = $object->type;
		$new_tagging->{$this->config['object_foreign_key']} = $object_id;
		$new_tagging->save();
		
		return TRUE;
	}
	
	/**
	* Normalize Tag
	*
	* This is a utility function used to take a raw tag and convert it to normalized form.
	* Normalized form is essentially lowercased alphanumeric characters only,
	* with no spaces or special characters.
	*
	* Customize the normalized valid chars with your own set of special characters
	* in regex format within the option 'custom_normalization'. It acts as a filter
	* to let a customized set of characters through.
	*
	* After the filter is applied, the function also lowercases the characters using strtolower
	* in the current locale.
	*
	* The default for normalized_valid_chars is a-zA-Z0-9, or english alphanumeric.
	*
	* @param	string	tag	An individual tag in raw form that should be normalized.
	* @return	string	Returns the tag in normalized form.
	*/
	public function normalize_tag($tag) 
	{		
		if ($this->config['normalize_tags'] ) 
		{
			if ($this->config['use_gleez_normalization'])
			{
				$tag = URL::title($tag);
			}
			else
			{
				$normalized_valid_chars = $this->config['custom_normalization'];
				$tag = preg_replace("/[^$normalized_valid_chars]/", "", $tag);
			}
			
			return strtolower($tag);
		}
		else 
		{
			return $tag;
		}
	}

	/**
	 * Explode a string of given tags into an array.
	 * 
	 * @param 	string 		$tags
	 * @return 	array
	 * 
	 * @see 	Tags::implode()
	 */
	public static function explode($tags)
	{
		// This regexp allows the following types of user input:
		// this, "somecompany, llc", "and ""this"" w,o.rks", foo bar
		$regexp = '%(?:^|,\ *)("(?>[^"]*)(?>""[^"]* )*"|(?: [^",]*))%x';
		preg_match_all($regexp, $tags, $matches);
		$typed_tags = array_unique($matches[1]);

		$tags = array();
		foreach ($typed_tags as $tag)
		{
			// If a user has escaped a term (to demonstrate that it
			// is a group, or includes a comma or quote character),
			//we remove the escape formatting so to save the term into the database as the user intends.
			$tag = trim(str_replace('""', '"', preg_replace('/^"(.*)"$/', '\1', $tag)));
			if ($tag != "")
			{
				$tags[] = $tag;
			}
		}

		return $tags;
	}

	/**
	 * Implode an array of tags into a string.
	 * 
	 * @param 	array 		$tags
	 * @return 	string
	 * 
	 * @see 	Tags::explode()
	 */
	public static function implode( array $tags)
	{
		$encoded_tags = array();
		foreach ($tags as $tag)
		{
			// Commas and quotes in tag names are special cases, so encode them.
			if (strpos($tag, ',') !== FALSE OR strpos($tag, '"') !== FALSE)
			{
				$tag = '"' . str_replace('"', '""', $tag) . '"';
			}

			$encoded_tags[] = $tag;
		}
  
		return implode(', ', $encoded_tags);
	}
	
}