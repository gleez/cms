<?php
/**
 * An adaptation of taxonomy
 *
 * @package    Gleez\ORM\Terms
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Model_Term extends ORM_MPTT {

	/**
	 * Table columns
	 * @var array
	 */
	protected $_table_columns = array(
		'id'          => array( 'type' => 'int' ),
		'name'        => array( 'type' => 'string' ),
		'description' => array( 'type' => 'string' ),
		'image'       => array( 'type' => 'string' ),
		'type'        => array( 'type' => 'string' ),
		'pid'         => array( 'type' => 'int' ),
		'lft'         => array( 'type' => 'int' ),
		'rgt'         => array( 'type' => 'int' ),
		'lvl'         => array( 'type' => 'int' ),
		'scp'         => array( 'type' => 'int' ),
	);

	/**
	 * "Has many" relationships
	 * @var array
	 */
	protected $_has_many = array(
		'posts' => array(
			'model'       => 'post',
			'through'     => 'posts_terms',
			'foreign_key' => 'term_id'
		),
	);

	/**
	 * Left column name
	 * @var  string
	 */
	public $left_column = 'lft';

	/**
	 * Right column name
	 * @var  string
	 */
	public $right_column = 'rgt';

	/**
	 * Level column name
	 * @var  string
	 */
	public $level_column = 'lvl';

	/**
	 * Scope column name
	 * @var  string
	 */
	public $scope_column = 'scp';

	/**
	 * Parent column name
	 * @var  string
	 */
	public $parent_column = 'pid';

	/**
	 * Ignored columns
	 * @var array
	 */
	protected $_ignored_columns = array(
		'path',
		'action'
	);

	/**
	 * Rules for the post model
	 *
	 * @return  array  Rules
	 */
	public function rules()
	{
		return array(
			'name' => array(
				array('not_empty'),
			),
		);
	}

	/**
	 * Updates or Creates the record depending on loaded()
	 *
	 * @param   Validation $validation Validation object [Optional]
	 * @return  ORM
	 */
	public function save(Validation $validation = NULL)
	{
		$this->type  = empty($this->type) ? 'post' : $this->type;

		parent::save($validation);

		if ($this->loaded())
		{
			// Add or remove path aliases
			$this->_aliases();
		}

		return $this;
	}

	/**
	 * Deletes a single post or multiple posts, ignoring relationships.
	 *
	 * @return  ORM
	 * @throws  Gleez_Exception
	 * @uses    Path::delete
	 */
	public function delete($query = NULL)
	{
		if ( ! $this->_loaded)
		{
			throw new Gleez_Exception('Cannot delete :model model because it is not loaded.',
				array(':model' => $this->_object_name)
			);
		}

		$source = $this->rawurl;

		parent::delete($query);

		// Delete the path aliases associated with this object
		Path::delete(array('source' => $source));
		unset($source);

		return $this;
	}

	/**
	 * Adds or deletes path aliases
	 *
	 * @uses  Path::load
	 * @uses  Path::clean
	 * @uses  Module::action
	 */
	private function _aliases()
	{
		// Create and save alias for the post
		$values = array();

		$path = Path::load($this->rawurl);

		if ($path)
		{
			$values['id'] = (int) $path['id'];
		}

		$alias = empty($this->path) ? 'category/'.$this->name : $this->path;
		$values['source'] = $this->rawurl;
		$values['alias']  = Path::clean($alias);
		$values['type']   = $this->type;
		$values['action'] = empty($this->action) ? 'category' : $this->action ;

		$values = Module::action('term_aliases', $values, $this);

		Path::save($values);
	}

	/**
	 * Reading data from inaccessible properties
	 *
	 * @param   string  $field
	 * @return  mixed
	 *
	 * @uses  HTML::chars
	 * @uses  Route::get
	 * @uses  Route::uri
	 * @uses  Path::load
	 */
	public function __get($field)
	{
		switch ($field)
		{
			case 'name':
				return HTML::chars(parent::__get('name'));
			break;
			case 'rawname':
				// Raw fields without markup. Usage: during edit or etc!
				return parent::__get('name');
			break;
			case 'rawurl':
				// Raw fields without markup. Usage: during edit or etc!
				return Route::get($this->type)->uri(array('action' => 'term', 'id' => $this->id));
			break;
			case 'url':
			case 'link':
				// Model specific links; view, edit, delete url's.
				return ($path = Path::load($this->rawurl)) ? $path['alias'] : $this->rawurl;
			break;
			case 'edit_url':
				// Model specific links; view, edit, delete url's.
				return Route::get('admin/term')->uri(array('id' => $this->id, 'action' => 'edit'));
			break;
			case 'delete_url':
				// Model specific links; view, edit, delete url's.
				return Route::get('admin/term')->uri(array('id' => $this->id, 'action' => 'delete'));
			break;
		}

		return parent::__get($field);
	}

	/**
	 * Check by triggering error if name exists
	 *
	 * Validation callback.
	 *
	 * @param   Validation  $validation  Validation object
	 * @param   string      $field       Field name
	 *
	 * @uses    DB::select
	 * @uses    Validation::error
	 */
	public function term_available(Validation $validation, $field)
	{
		$query = DB::select(array(DB::expr('COUNT(*)'), 'total_count'))
				->from($this->_table_name)
				->where('name', '=', $validation[$field])
				->where($this->_primary_key, '!=', $this->pk())
				->where($this->scope_column, '=', $this->scope())
				->execute($this->_db)
				->get('total_count');

		if ($query > 0)
		{
			$validation->error($field, 'term_available', array($validation[$field]));
		}
	}


	/**
	 * Create a new term in the tree as a child of `$parent`
	 *
	 * if `$location` is "first" or "last" the term will be the first or last child
	 * if `$location` is an int, the term will be the next sibling of term with id `$location`
	 *
	 * @param   ORM_MPTT|integer  Primary key value or ORM_MPTT object of parent term
	 * @param   string|integer    The location [Optional]
	 * @throws  Gleez_Exception
	 */
	public function create_at($parent, $location = 'last')
	{
		// Create the term as first child, last child, or as next sibling based on location
		if ($location == 'first')
		{
			$this->insert_as_first_child($parent);
		}
		elseif ($location == 'last')
		{
			$this->insert_as_last_child($parent);
		}
		else
		{
			$target = ORM::factory('term',(int) $location);

			if ( ! $target->loaded())
			{
				throw new Gleez_Exception('Could not create term, could not find target for insert_as_next_sibling id: :location ',
					array(':location' =>  (int) $location));
			}

			$this->insert_as_last_child($target);
		}
	}

}