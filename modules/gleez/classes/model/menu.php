<?php
/**
 * Menu Model Class
 *
 * @package    Gleez\ORM\Menu
 * @author     Sandeep Sangamreddi - Gleez
 * @version    1.1.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Model_Menu extends ORM_MPTT {

	/**
	 * Table columns
	 * @var array
	 */
	protected $_table_columns =  array(
		'id'     => array( 'type' => 'int' ),
		'title'  => array( 'type' =>  'string' ),
		'name'   => array( 'type' =>  'string' ),
		'descp'  => array( 'type' =>  'string' ),
		'image'  => array( 'type' =>  'string' ),
		'url'    => array( 'type' =>  'string' ),
		'params' => array( 'type' =>  'string' ),
		'active' => array( 'type' => 'int' ),
		'pid'    => array( 'type' => 'int' ),
		'lft'    => array( 'type' => 'int' ),
		'rgt'    => array( 'type' => 'int' ),
		'lvl'    => array( 'type' => 'int' ),
		'scp'    => array( 'type' => 'int' ),
	);

	/**
	 * Scope column name
	 * @var string
	 */
	public $scope_column = 'scp';

	/**
	 * Parent column name
	 * @var string
	 */
	public $parent_column = 'pid';

	/**
	 * Rule definitions for validation
	 *
	 * @return  array  Rules
	 */
	public function rules()
	{
		return array(
			'name' => array(
				array(array($this, 'is_valid'), array(':validation', ':field')),
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
			'title'  => __('Title'),
			'name'   => __('Slug'),
			'url'    => __('Link'),
		);
	}

	/**
	 * Reading data from inaccessible properties
	 *
	 * @since   1.1.0
	 *
	 * @param   string  $field
	 * @return  mixed
	 *
	 * @uses    Route::get
	 * @uses    Route::uri
	 */
	public function __get($field)
	{
		switch ($field)
		{
			case 'list_items_url':
				return Route::get('admin/menu/item')->uri(array('id' => $this->id));
			break;
			case 'add_item_url':
				return Route::get('admin/menu/item')->uri(array('id' => $this->id, 'action' => 'add'));
			break;
			case 'edit_url':
				return Route::get('admin/menu')->uri(array('id' => $this->id, 'action' => 'edit'));
			break;
			case 'delete_url':
				return Route::get('admin/menu')->uri(array('id' => $this->id, 'action' => 'delete'));
			break;
		}

		return parent::__get($field);
	}

	/**
	 * Validation callback
	 *
	 * @param   Validation  $validation  Validation object
	 * @param   string      $field       Field name
	 * @uses    Valid::numeric
	 * @return  void
	 */
	public function is_valid(Validation $validation, $field)
	{
		if ( empty($this->name) AND empty($this->title) )
		{
			$validation->error('title', 'not_empty', array($this->title));
		}
		else
		{
			$text = empty($this->name) ? $this->title : $this->name;
			$this->name = $this->_unique_slug(URL::title($text));
		}
	}

	/**
	 * Updates or Creates the record depending on loaded()
	 *
	 * @param   Validation $validation Validation object
	 * @return  ORM
	 */
	public function save(Validation $validation = NULL)
	{
		$this->params = empty($this->params) ? NULL : serialize($this->params);

		return parent::save( $validation );
	}

	/**
	 * Creates unique slug for menu
	 *
	 * @param   string  $str
	 * @return  string
	 */
	private function _unique_slug($str)
	{
		static $i;

		$i = 1;
		$original = $str;
		
		while ($post = ORM::factory('menu', array('name' => $str)) AND $post->loaded() AND $post->id !== $this->id)
		{
			$str = $original . '-' . $i;
			$i++;
		}

		return $str;
	}

	/**
	 * Create a new term in the tree as a child of $parent
	 *
	 * - if `$location` is "first" or "last" the term will be the first or last child
	 * - if `$location` is an int, the term will be the next sibling of term with id $location
	 *    
	 * @param   ORM_MPTT|integer  $parent    The parent
	 * @param   string|integer    $location  The location [Optional]
	 * @return  Model_Menu
	 * @throws  Gleez_Exception
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
			$target = ORM::factory('menu',(int) $location);
			
			if ( ! $target->loaded())
			{
				throw new Gleez_Exception("Could not create menu, could not find target for
							  insert_as_next_sibling id: " . (int) $location);
			}

			$this->insert_as_last_child($target);
		}

		return $this;
	}

	/**
	 * Move the item to $target based on action
	 *
	 * @param   $target  integer  The target term id
	 * @param   $action  string   The action to perform (before/after/first/last) after
	 * @throws  Gleez_Exception
	 */
	public function move_to($target, $action = 'after')
	{
		// Find the target
		$target = ORM::factory('menu',(int) $target);

		// Make sure it exists
		if ( ! $target->loaded())
		{
			throw new Gleez_Exception("Could not move item, target item did not exist." . (int) $target->id);
		}

		if ($action == 'before')
			$this->move_to_prev_sibling($target);
		elseif ($action == 'after')
			$this->move_to_next_sibling($target);
		elseif ($action == 'first')
			$this->move_to_first_child($target);
		elseif ($action == 'last')
			$this->move_to_last_child($target);
		else
			throw new Gleez_Exception("Could not move item, action should be 'before', 'after', 'first' or 'last'.");
	}

}