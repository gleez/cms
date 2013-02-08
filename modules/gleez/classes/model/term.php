<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * An adaptation of taxonomy
 *
 * @package    Gleez
 * @category   Terms
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Model_Term extends ORM_MPTT {
	
	protected $_table_columns = array(
					'id'   => array( 'type' => 'int' ),
					'name' => array( 'type' => 'string' ),
					'description' => array( 'type' => 'string' ),
					'image' => array( 'type' => 'string' ),
					'type' => array( 'type' => 'string' ),
					'pid' => array( 'type' => 'int' ),
					'lft' => array( 'type' => 'int' ),
					'rgt' => array( 'type' => 'int' ),
					'lvl' => array( 'type' => 'int' ),
					'scp' => array( 'type' => 'int' ),
				);
	
	protected $_has_many   = array(
                                        'posts'    => array('through' => 'posts_terms', 'foreign_key' => 'term_id' ),
                                );
	
	/**
	 * @access  public
	 * @var     string  left column name
	 */
	public $left_column = 'lft';

	/**
	 * @access  public
	 * @var     string  right column name
	 */
	public $right_column = 'rgt';

	/**
	 * @access  public
	 * @var     string  level column name
	 */
	public $level_column = 'lvl';

	/**
	 * @access  public
	 * @var     string  scope column name
	 */
	public $scope_column = 'scp';

	/**
	 * @access  public
	 * @var     string  parent column name
	 */
	public $parent_column = 'pid';
	
	protected $_ignored_columns = array('path', 'action');
	
	public function rules()
	{
		return array(
			'name' => array(
				array('not_empty'),
				//array(array($this, 'term_available'), array(':validation', ':field')),
			),
		);
	}

	public function save(Validation $validation = NULL)
	{
		$this->type  = empty($this->type) ? 'post' : $this->type;
		parent::save( $validation );
	
		if ( $this->loaded())
		{		
			//add or remove path aliases
			$this->_aliases();
		}
	
		return $this;
	}

	/**
	 * Deletes a single post or multiple posts, ignoring relationships.
	 *
	 * @chainable
	 * @return ORM
	 */
	public function delete($query = NULL)
	{
		if ( ! $this->_loaded)
			throw new Kohana_Exception('Cannot delete :model model because it is not loaded.', array(':model' => $this->_object_name));

		$source = $this->rawurl;
		parent::delete($query);
	
		//Delete the path aliases associated with this object
		Path::delete( array('source' => $source) );
		unset($source);
	
		return $this;
	}
	
	/**
	 * Adds or deletes path aliases
	 *
	 * @return void
	 */
	private function _aliases()
	{
		// create and save alias for the post
		$values = array();
		
		$path	= Path::load($this->rawurl);
		if( $path ) $values['id'] = (int) $path['id'];
		
		$alias  = empty($this->path) ? 'category/'.$this->name : $this->path;
		$values['source'] = $this->rawurl;
		$values['alias']  = Path::clean( $alias );
		$values['type']   = $this->type ;
		$values['action'] = empty($this->action) ? 'category' : $this->action ;
	
		$values = Module::action('term_aliases', $values, $this);
		Path::save($values);
	}
	
	public function __get($field)
	{
		if($field === 'name')
			return HTML::chars(parent::__get('name'));

                //Raw fields without markup. Usage: during edit or etc!
		if($field === 'rawname')
			return parent::__get('name');
	
		if( $field === 'rawurl' )
			return Route::get($this->type)->uri( array( 'action' => 'term', 'id' => $this->id ) );
		
		// Model specefic links; view, edit, delete url's.
                if( $field === 'url'  OR $field === 'link')
			return ($path = Path::load($this->rawurl) ) ? $path['alias'] : $this->rawurl;
	
                if( $field === 'edit_url' )
			return Route::get('admin/term')->uri( array( 'id' => $this->id, 'action' => 'edit' ) );

                if( $field === 'delete_url' )
			return Route::get('admin/term')->uri( array( 'id' => $this->id, 'action' => 'delete' ) );
		
		return parent::__get($field);
	}
	
	/**
	 * Check by triggering error if name exists.
	 * Validation callback.
	 *
	 * @param   Validation  Validation object
	 * @param   string      Field name
	 * @return  void
	 */
	public function term_available(Validation $validation, $field)
	{
		if( DB::select(array('COUNT("*")', 'total_count'))
			->from($this->_table_name)
			->where('name', '=', $validation[$field])
			->where($this->_primary_key, '!=', $this->pk())
			->where($this->scope_column, '=', $this->scope())
			->execute($this->_db)
			->get('total_count') > 0)
                {
			$validation->error($field, 'term_available', array($validation[$field]));
		}
	}

	
	/**
	 * Create a new term in the tree as a child of $parent
	 *
	 *    if $location is "first" or "last" the term will be the first or last child
	 *    if $location is an int, the term will be the next sibling of term with id $location
	 * @param  Term  the parent
	 * @param  string/int    the location
	 * @return void
	 */
	public function create_at($parent, $location = 'last')
	{
		// Create the term as first child, last child, or as next sibling based on location
		if ($location == 'first')
		{
			$this->insert_as_first_child($parent);
		}
		else if ($location == 'last')
		{
			$this->insert_as_last_child($parent);
		}
		else
		{
			$target = ORM::factory('term',(int) $location);
			
			if ( ! $target->loaded())
			{
				throw new Gleez_Exception('Could not create term, could not find target
					for insert_as_next_sibling id: :location ', array( ':location' =>  (int) $location) );
			}
			
			//$this->insert_as_next_sibling($target);
			$this->insert_as_last_child($target);
		}
	}
        
}