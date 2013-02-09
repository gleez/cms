<?php defined("SYSPATH") or die("No direct script access.");

class Model_Menu extends ORM_MPTT {
        
	protected $_table_columns =  array(
					'id' 	 => array( 'type' => 'int' ),
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
	 * @access  public
	 * @var     string  scope column name
	 */
	public $scope_column = 'scp';

	/**
	 * @access  public
	 * @var     string  parent column name
	 */
	public $parent_column = 'pid';
        
	public function rules()
	{
		return array(
			'name' => array(
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
			'title'    => 'Title',
			'name'     => 'slug',
			'url'      => 'Link',
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
                $this->name   = $this->_unique_slug(Url::title(empty($this->name) ? $this->title : $this->name));
                $this->params = empty($this->params) ? NULL : serialize($this->params);
       
                return parent::save( $validation );
        }
        
        private function _unique_slug($str)
	{
		static $i; $i = 1;
		$original = $str;
		
		while ($post = ORM::factory('menu', array('name' => $str )) AND $post->loaded() AND $post->id !== $this->id)
		{
			$str = $original . '-' . $i;
			$i++;
		}
	
		return $str;
	}
        
	/**
	 * Create a new term in the tree as a child of $parent
	 *
	 *    if $location is "first" or "last" the term will be the first or last child
	 *    if $location is an int, the term will be the next sibling of term with id $location
	 *    
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
			$target = ORM::factory('menu',(int) $location);
			
			if ( ! $target->loaded())
			{
				throw new Gleez_Exception("Could not create menu, could not find target for
							  insert_as_next_sibling id: " . (int) $location);
			}
			
			//$this->insert_as_next_sibling($target);
			$this->insert_as_last_child($target);
		}
		
		//Menu_Item::rebuild(TRUE);
		return $this;
	}

	/**
	 * Move the item to $target based on action
	 *
	 *    
	 * @param  Term   int 		The target term id
	 * @param  Action string    	The action to perform (before/after/first/last) after
	 * @return void
	 */
	public function move_to($target, $action = 'after')
	{
		// Find the target
		$target = ORM::factory('menu',(int) $target);
		
		// Make sure it exists
		if ( !$target->loaded())
		{
			throw new Gleez_Exception("Could not move item, target item did not exist." . (int) $target->id );
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
		
		//Menu_Item::rebuild(TRUE);
	}
        
}