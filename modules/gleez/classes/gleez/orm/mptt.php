<?php
/**
 * Modified Preorder Tree Traversal Class
 *
 * A port of Banks' Sprig_MPTT plus some code from BiakaVeron's ORM_MPTT module.
 * Using Transactions instead of lock.
 *
 * @package    Gleez\ORM
 * @author     Gleez Team
 * @author     Mathew Davies
 * @author     Kiall Mac Innes
 * @author     Paul Banks
 * @author     Brotkin Ivan
 * @author     Brandon Summers
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Gleez_ORM_MPTT extends ORM {

	/**
	 * Left column name
	 * @var string
	 */
	public $left_column = 'lft';

	/**
	 * Right column name
	 * @var string
	 */
	public $right_column = 'rgt';

	/**
	 * Level column name
	 * @var string
	 */
	public $level_column = 'lvl';

	/**
	 * Scope column name
	 * @var string
	 */
	public $scope_column = 'scope';

	/**
	 * Parent column name
	 * @var string
	 */
	public $parent_column = 'parent_id';

	/**
	 * Load the default column names
	 *
	 * @param   mixed  $id  Parameter for find or object to load [Optional]
	 * @uses    Arr::unshift
	 */
	public function __construct($id = NULL)
	{
		if (empty($this->_sorting))
		{
			$this->_sorting = array($this->scope_column => 'ASC', $this->left_column => 'ASC');
		}
		else
		{
			$this->_sorting = Arr::unshift($this->_sorting, $this->left_column, 'ASC');
			$this->_sorting = Arr::unshift($this->_sorting, $this->scope_column, 'ASC');
		}
		
		parent::__construct($id);
	}

	/**
	 * Checks if the current node has any children.
	 *
	 * @return  boolean
	 */
	public function has_children()
	{
		return ($this->size() > 2);
	}

	/**
	 * Is the current node a leaf node?
	 *
	 * @return  boolean
	 */
	public function is_leaf()
	{
		return ( ! $this->has_children());
	}

	/**
	 * Is the current node a descendant of the supplied node.
	 *
	 * @param   ORM_MPTT|integer  ORM_MPTT object or primary key value of target node
	 * @return  bool
	 */
	public function is_descendant($target)
	{
		if ( ! ($target instanceof $this))
		{
			$target = self::factory($this->object_name(), $target);
		}
		
		return (
				$this->{$this->left_column} > $target->{$target->left_column}
				AND $this->{$this->right_column} < $target->{$target->right_column}
				AND $this->{$this->scope_column} = $target->{$target->scope_column}
			);
	}

	/**
	 * Checks if the current node is a direct child of the supplied node.
	 * 
	 * @param   ORM_MPTT|integer  ORM_MPTT object or primary key value of target node
	 * @return  boolean
	 */
	public function is_child($target)
	{
		if ( ! ($target instanceof $this))
		{
			$target = self::factory($this->object_name(), $target);
		}

		return ((int) $this->{$this->parent_column} === (int) $target->pk());
	}

	/**
	 * Checks if the current node is a direct parent of a specific node.
	 * 
	 * @param   ORM_MPTT|integer  ORM_MPTT object or primary key value of child node
	 * @return  boolean
	 */
	public function is_parent($target)
	{
		if ( ! ($target instanceof $this))
		{
			$target = self::factory($this->object_name(), $target);
		}

		return ((int) $this->pk() === (int) $target->{$this->parent_column});
	}

	/**
	 * Checks if the current node is a sibling of a supplied node.
	 * (Both have the same direct parent)
	 * 
	 * @param   ORM_MPTT|integer  ORM_MPTT object or primary key value of target node
	 * @return  boolean
	 */
	public function is_sibling($target)
	{
		if ( ! ($target instanceof $this))
		{
			$target = self::factory($this->object_name(), $target);
		}
		
		if ((int) $this->pk() === (int) $target->pk())
			return FALSE;

		return ((int) $this->{$this->parent_column} === (int) $target->{$target->parent_column});
	}

	/**
	 * Checks if the current node is a root node.
	 * 
	 * @return  boolean
	 */
	public function is_root()
	{
		return ($this->left() === 1);
	}

	/**
	 * Checks if the current node is one of the parents of a specific node.
	 * 
	 * @param   integer|object  ID or object of parent node
	 * @return  boolean
	 */
	public function is_in_parents($target)
	{
		if ( ! ($target instanceof $this))
		{
			$target = self::factory($this->object_name(), $target);
		}

		return $target->is_descendant($this);
	}

	/**
	 * Overloaded save method.
	 * 
	 * @param   Validation $validation  Validation object
	 * @return  mixed
	 */
	public function save(Validation $validation = NULL)
	{
		if ( ! $this->loaded())
		{
			return $this->make_root($validation);
		}
		elseif ($this->loaded() === TRUE)
		{
			return parent::save($validation);
		}

		return FALSE;
	}

	/**
	 * Creates a new node as root, or moves a node to root
	 *
	 * @param   Validation $validation  Validation object
	 * @param   integer    $scope       The new scope [Optional]
	 *
	 * @return  ORM_MPTT|boolean
	 *
	 * @throws  ORM_Validation_Exception
	 */
	public function make_root(Validation $validation = NULL, $scope = NULL)
	{
		// If node already exists, and already root, exit
		if ($this->loaded() AND $this->is_root())
			return $this;

		// delete node space first
		if ($this->loaded())
		{
			$this->delete_space($this->left(), $this->size());
		}

		if (is_null($scope))
		{
			// Increment next scope
			$scope = self::get_next_scope();
		}
		elseif ( ! $this->scope_available($scope))
		{
			return FALSE;
		}

		$this->{$this->scope_column} = $scope;
		$this->{$this->level_column} = 1;
		$this->{$this->left_column} = 1;
		$this->{$this->right_column} = 2;
		$this->{$this->parent_column} = NULL;

		try
		{
			parent::save($validation);
		}
		catch (ORM_Validation_Exception $e)
		{
			// Some fields didn't validate, throw an exception
			throw $e;
		}

		return $this;
	}

	/**
	 * Sets the parent_column value to the given targets column value. Returns the target ORM_MPTT object.
	 * 
	 * @param   ORM_MPTT|integer  $target  primary key value or ORM_MPTT object of target node
	 * @param   string            $column  name of the targets nodes column to use [Optional]
	 * @return  ORM_MPTT
	 */
	protected function parent_from($target, $column = NULL)
	{
		if ( ! $target instanceof $this)
		{
			$target = self::factory($this->object_name(), array($this->primary_key() => $target));
		}

		if ($column === NULL)
		{
			$column = $target->primary_key();
		}

		if ($target->loaded())
		{
			$this->{$this->parent_column} = $target->{$column};
		}
		else
		{
			$this->{$this->parent_column} = NULL;
		}

		return $target;
	}

	/**
	 * Inserts a new node as the first child of the target node.
	 * 
	 * @param   ORM_MPTT|integer  $target  primary key value or ORM_MPTT object of target node
	 * @return  ORM_MPTT
	 */
	public function insert_as_first_child($target)
	{
		$target = $this->parent_from($target);
		return $this->insert($target, $this->left_column, 1, 1);
	}
	
	/**
	 * Inserts a new node as the last child of the target node.
	 * 
	 * @param   ORM_MPTT|integer  $target  primary key value or ORM_MPTT object of target node
	 * @return  ORM_MPTT
	 */
	public function insert_as_last_child($target)
	{
		$target = $this->parent_from($target, $this->primary_key());
		return $this->insert($target, $this->right_column, 0, 1);
	}
	
	/**
	 * Inserts a new node as a previous sibling of the target node.
	 * 
	 * @param   ORM_MPTT|integer  $target  primary key value or ORM_MPTT object of target node
	 * @return  ORM_MPTT
	 */
	public function insert_as_prev_sibling($target)
	{
		$target = $this->parent_from($target, $this->parent_column);
		return $this->insert($target, $this->left_column, 0, 0);
	}
	
	/**
	 * Inserts a new node as the next sibling of the target node.
	 * 
	 * @param   ORM_MPTT|integer  $target  primary key value or ORM_MPTT object of target node
	 * @return  ORM_MPTT
	 */
	public function insert_as_next_sibling($target)
	{
		$target = $this->parent_from($target, $this->parent_column);
		return $this->insert($target, $this->right_column, 1, 0);
	}
	
	/**
	 * Insert the object
	 *
	 * @param   ORM_MPTT|integer  $target  primary  key value or ORM_MPTT object of target node.
	 * @param   string            $copy_left_from   target object property to take new left value from
	 * @param   integer           $left_offset      offset for left value
	 * @param   integer           $level_offset     offset for level value
	 * @return  ORM_MPTT
	 * @throws  ORM_Validation_Exception
	 */
	protected function insert($target, $copy_left_from, $left_offset, $level_offset)
	{
		// Insert should only work on new nodes.. if its already it the tree it needs to be moved!
		if ($this->loaded())
			return FALSE;
		 
		 
		if ( ! $target instanceof $this)
		{
			$target = self::factory($this->object_name(), array($this->primary_key() => $target));
		 
			if ( ! $target->loaded())
			{
				return FALSE;
			}
		}
		else
		{
			$target->reload();
		}
		
		// Start the transaction
		$this->_db->begin();

		$this->{$this->left_column} = $target->{$copy_left_from} + $left_offset;
		$this->{$this->right_column} = $this->{$this->left_column} + 1;
		$this->{$this->level_column} = $target->{$this->level_column} + $level_offset;
		$this->{$this->scope_column} = $target->{$this->scope_column};

		$this->create_space($this->{$this->left_column});
		 
		try
		{
			parent::save();
		}
		catch (ORM_Validation_Exception $e)
		{
			// We had a problem saving, make sure we clean up the tree
			$this->delete_space($this->left());

			$this->_db->commit();
			throw $e;
		}
		
		// Commit the transaction
		$this->_db->commit();
		
		return $this;
	}

	/**
	 * Deletes the current node and all descendants.
	 *
	 * @throws  Gleez_Exception
	 */
	public function delete($query = NULL)
	{
		if ($query !== NULL)
		{
			throw new Gleez_Exception('ORM_MPTT does not support passing a query object to delete()');
		}
		
		// Start the transaction
		$this->_db->begin();

		try
		{
			DB::delete($this->_table_name)
				->where($this->left_column,' >=',$this->left())
				->where($this->right_column,' <= ',$this->right())
				->where($this->scope_column,' = ',$this->scope())
				->execute($this->_db);

			$this->delete_space($this->left(), $this->size());
		}
		catch (Gleez_Exception $e)
		{
			$this->_db->rollback();
			throw $e;
		}

		// Commit the transaction
		$this->_db->commit();
	}
	
	public function move_to_first_child($target)
	{
		$target = $this->parent_from($target, $this->primary_key());
		return $this->move($target, TRUE, 1, 1, TRUE);
	}
	
	public function move_to_last_child($target)
	{
		$target = $this->parent_from($target, $this->primary_key());
		return $this->move($target, FALSE, 0, 1, TRUE);
	}
	
	public function move_to_prev_sibling($target)
	{
		$target = $this->parent_from($target, $this->parent_column);
		return $this->move($target, TRUE, 0, 0, FALSE);
	}
	
	public function move_to_next_sibling($target)
	{
		$target = $this->parent_from($target, $this->parent_column);
		return $this->move($target, FALSE, 1, 0, FALSE);
	}
	
	protected function move($target, $left_column, $left_offset, $level_offset, $allow_root_target)
	{
		if ( ! $this->loaded())
			return FALSE;
	  
		// store the changed parent id before reload
		$parent_id = $this->{$this->parent_column};

		// Start the transaction
		$this->_db->begin();
		$this->reload();
		 
		// Catch any database or other exceptions and unlock
		try
		{
			if ( ! $target instanceof $this)
			{
				$target = self::factory($this->object_name(), array($this->primary_key() => $target));
				 
				if ( ! $target->loaded())
				{
					$this->_db->rollback();
					return FALSE;
				}
			}
			else
			{
				$target->reload();
			}

			// Stop $this being moved into a descendant or itself or disallow if target is root
			if ($target->is_descendant($this)
				OR $this->{$this->primary_key()} === $target->{$this->primary_key()}
				OR ($allow_root_target === FALSE AND $target->is_root()))
			{
				$this->_db->rollback();
				return FALSE;
			}

			if ($level_offset > 0)
			{
				// We're moving to a child node so add 1 to left offset.
				$left_offset = ($left_column === TRUE) ? ($target->left() + 1) : ($target->right() + $left_offset);
			}
			else
			{
				$left_offset = ($left_column === TRUE) ? $target->left() : ($target->right() + $left_offset);
			}
			
			$level_offset = $target->level() - $this->level() + $level_offset;
			$size = $this->size();

			$this->create_space($left_offset, $size);

			$this->reload();

			$offset = ($left_offset - $this->left());
			
			$this->_db->query(Database::UPDATE, 'UPDATE '.$this->_db->quote_table($this->_table_name).' SET `'
				. $this->left_column.'` = `'.$this->left_column.'` + '
				. $offset.', `'.$this->right_column.'` =  `'.$this->right_column.'` + '
				. $offset.', `'.$this->level_column.'` =  `'.$this->level_column.'` + '
				. $level_offset.', `'.$this->scope_column.'` = '.$target->scope()
				. ' WHERE `'.$this->left_column.'` >= '.$this->left().' AND `'
				. $this->right_column.'` <= '.$this->right().' AND `'
				. $this->scope_column.'` = '.$this->scope(), TRUE);
			
			$this->delete_space($this->left(), $size);
		}
		catch (Gleez_Exception $e)
		{
			$this->_db->rollback();
			throw $e;
		}

		// all went well so save the parent_id if changed
		if ($parent_id != $this->{$this->parent_column})
		{
			$this->{$this->parent_column} = $parent_id;
			$this->save();
		}

		// Commit the transaction
		$this->_db->commit();
		$this->reload();

		return $this;
	}

	/**
	 * Returns the next available value for scope.
	 *
	 * @return  integer
	 */
	protected function get_next_scope()
	{
		$scope = DB::select(DB::expr('IFNULL(MAX(`'.$this->scope_column.'`), 0) as scope'))
				->from($this->_table_name)
				->execute($this->_db)
				->current();

		if ($scope AND intval($scope['scope']) > 0)
			return intval($scope['scope']) + 1;

		return 1;
	}

	/**
	 * Returns the root node of the current object instance.
	 *
	 * @param   integer $scope  scope [Optional]
	 *
	 * @return  ORM_MPTT|boolean
	 *
	 * @throws  Gleez_Exception
	 */
	public function root($scope = NULL)
	{
		if (is_null($scope) AND $this->loaded())
		{
			$scope = $this->scope();
		}
		elseif (is_null($scope) AND ! $this->loaded())
		{
			throw new Gleez_Exception(':method must be called on an ORM_MPTT object instance.', array(':method' => 'root'));
		}
		
		return self::factory($this->object_name(), array($this->left_column => 1, $this->scope_column => $scope));
	}

	/**
	 * Returns all root node's
	 * 
	 * @return  ORM_MPTT
	 */
	public function roots()
	{
		return self::factory($this->object_name())
				->where($this->left_column, '=', 1)
				->find_all();
	}

	/**
	 * Returns the parent node of the current node
	 * 
	 * @return  ORM_MPTT
	 */
	public function parent()
	{
		if ($this->is_root())
			return NULL;

		return self::factory($this->object_name(), $this->{$this->parent_column});
	}

	/**
	 * Returns all of the current nodes parents.
	 * 
	 * @param   boolean  $root                include root node [optional
	 * @param   boolean  $with_self           include current node [optional
	 * @param   string   $direction           direction to order the left column by [optional
	 * @param   boolean  $direct_parent_only  retrieve the direct parent only [Optional]
	 * @return  ORM_MPTT
	 */
	public function parents($root = TRUE, $with_self = FALSE, $direction = 'ASC', $direct_parent_only = FALSE)
	{
		$suffix = $with_self ? '=' : '';

		$query = self::factory($this->object_name())
			->where($this->left_column, '<'.$suffix, $this->left())
			->where($this->right_column, '>'.$suffix, $this->right())
			->where($this->scope_column, '=', $this->scope())
			->order_by($this->left_column, $direction);
		
		if ( ! $root)
		{
			$query->where($this->left_column, '!=', 1);
		}
		
		if ($direct_parent_only)
		{
			$query
				->where($this->level_column, '=', $this->level() - 1)
				->limit(1);
		}
		
		return $query->find_all();
	}

	/**
	 * Returns direct children of the current node.
	 * 
	 * @param   boolean          $self       include the current node [Optional]
	 * @param   string           $direction  direction to order the left column by [Optional]
	 * @param   integer|boolean  $limit      number of children to get [Optional]
	 * @return  ORM_MPTT
	 */
	public function children($self = FALSE, $direction = 'ASC', $limit = FALSE)
	{
		return $this->descendants($self, $direction, TRUE, FALSE, $limit);
	}

	/**
	 * Returns a full hierarchical tree, with or without scope checking.
	 * 
	 * @param   boolean  $scope  only retrieve nodes with specified scope [Optional]
	 * @return  object
	 */
	public function fulltree($scope = NULL)
	{
		$result = self::factory($this->object_name());

		if ( ! is_null($scope))
		{
			$result->where($this->scope_column, '=', $scope);
		}
		else
		{
			$result->order_by($this->scope_column, 'ASC')
					->order_by($this->left_column, 'ASC');
		}

		return $result->find_all();
	}
	
	/**
	 * Returns the siblings of the current node
	 *
	 * @param   boolean  $self       include the current node [Optional]
	 * @param   string   $direction  direction to order the left column by [Optional]
	 * @return  ORM_MPTT
	 */
	public function siblings($self = FALSE, $direction = 'ASC')
	{
		$query = self::factory($this->object_name())
			->where($this->left_column, '>', $this->parent->left())
			->where($this->right_column, '<', $this->parent->right())
			->where($this->scope_column, '=', $this->scope())
			->where($this->level_column, '=', $this->level())
			->order_by($this->left_column, $direction);
		 
		if ( ! $self)
		{
			$query->where($this->primary_key(), '<>', $this->pk());
		}
		 
		return $query->find_all();
	}

	/**
	 * Returns the leaves of the current node.
	 *
	 * @param   boolean  $self       include the current node [Optional]
	 * @param   string   $direction  direction to order the left column by [Optional]
	 * @return  ORM_MPTT
	 */
	public function leaves($self = FALSE, $direction = 'ASC')
	{
		return $this->descendants($self, $direction, TRUE, TRUE);
	}
	
	/**
	 * Returns the descendants of the current node
	 *
	 * @param   boolean          $self                  include the current node [Optional]
	 * @param   string           $direction             direction to order the left column by [Optional]
	 * @param   boolean          $direct_children_only  include direct children only [Optional]
	 * @param   boolean          $leaves_only           include leaves only [Optional]
	 * @param   integer|boolean  $limit                 number of results to get [Optional]
	 *
	 * @return  ORM_MPTT
	 */
	public function descendants($self = FALSE, $direction = 'ASC', $direct_children_only = FALSE, $leaves_only = FALSE, $limit = FALSE)
	{
		$left_operator = $self ? '>=' : '>';
		$right_operator = $self ? '<=' : '<';
		
		$query = self::factory($this->object_name())
			->where($this->left_column, $left_operator, $this->left())
			->where($this->right_column, $right_operator, $this->right())
			->where($this->scope_column, '=', $this->scope())
			->order_by($this->left_column, $direction);
		
		if ($direct_children_only)
		{
			if ($self)
			{
				$query
					->and_where_open()
					->where($this->level_column, '=', $this->level())
					->or_where($this->level_column, '=', $this->level() + 1)
					->and_where_close();
			}
			else
			{
				$query->where($this->level_column, '=', $this->level() + 1);
			}
		}
		
		if ($leaves_only)
		{
			$query->where($this->right_column, '=', DB::expr($this->left_column.' + 1'));
		}
		
		if ($limit !== FALSE)
		{
			$query->limit($limit);
		}
		
		return $query->find_all();
	}

	/**
	 * Get all possible level values
	 *
	 * @param	integer  $scope  restrict to the given scope [Optional]
	 * @return	Database_Result
	 */
	public function get_levels($scope = NULL)
	{
		$result = DB::select($this->level_column)
			->distinct(TRUE)
			->from($this->_table_name);
		
		if ( ! empty($scope))
		{
			$result->where($this->scope_column, '=', (int) $scope);
		}
			
		return $result->execute($this->_db);
	}
	
	/**
	 * Adds space to the tree for adding or inserting nodes.
	 *
	 * @param  integer  $start  start position
	 * @param  integer  $size   size of the gap to add [Optional]
	 */
	protected function create_space($start, $size = 2)
	{
		DB::update($this->_table_name)
			->set(array($this->left_column => DB::expr($this->left_column.' + '.$size)))
			->where($this->left_column,'>=', $start)
			->where($this->scope_column, '=', $this->scope())
			->execute($this->_db);

		DB::update($this->_table_name)
			->set(array($this->right_column => DB::expr($this->right_column.' + '.$size)))
			->where($this->right_column,'>=', $start)
			->where($this->scope_column, '=', $this->scope())
			->execute($this->_db);
	}

	/**
	 * Removes space from the tree after deleting or moving nodes.
	 *
	 * @param  integer  $start  start position
	 * @param  integer  $size   size of the gap to remove [Optional]
	 */
	protected function delete_space($start, $size = 2)
	{
		DB::update($this->_table_name)
			->set(array($this->left_column => DB::expr($this->left_column.' - '.$size)))
			->where($this->left_column, '>=', $start)
			->where($this->scope_column, '=', $this->scope())
			->execute($this->_db);

		DB::update($this->_table_name)
			->set(array($this->right_column => DB::expr($this->right_column.' - '.$size)))
			->where($this->right_column,'>=', $start)
			->where($this->scope_column, '=', $this->scope())
			->execute($this->_db);
	}

	/**
	 * Locks the current table.
	 * 
	 * @return  void
	 */
	protected function lock()
	{
		$this->_db->query(NULL, 'LOCK TABLE '.$this->_db->quote_table($this->_table_name).' WRITE', TRUE);
	}

	/**
	 * Unlocks the current table.
	 */
	protected function unlock()
	{
		$this->_db->query(NULL, 'UNLOCK TABLES', TRUE);
	}

	/**
	 * Returns the value of the current nodes left column.
	 *
	 * @return  integer
	 */
 	public function left()
	{
		return (INT) $this->{$this->left_column};
	}

	/**
	 * Returns the value of the current nodes right column.
	 *
	 * @return  integer
	 */
	public function right()
	{
		return (INT) $this->{$this->right_column};
	}

	/**
	 * Returns the value of the current nodes level column.
	 * 
	 * @return  integer
	 */
	public function level()
	{
		return (INT) $this->{$this->level_column};
	}

	/**
	 * Returns the value of the current nodes scope column.
	 * 
	 * @return  integer
	 */
	public function scope()
	{
		return (INT) $this->{$this->scope_column};
	}

	/**
	 * Returns the size of the current node.
	 * 
	 * @return  integer
	 */
	public function size()
	{
		return $this->right() - $this->left() + 1;
	}

	/**
	 * Returns the number of descendants the current node has.
	 * 
	 * @return  integer
	 */
	public function count()
	{
		return ($this->size() - 2) / 2;
	}

	/**
	 * Checks if the supplied scope is available.
	 * 
	 * @param   integer  $scope  scope to check availability of
	 * @return  boolean
	 */
	protected function scope_available($scope)
	{
		return (bool) ! self::factory($this->_object_name)
			->where($this->scope_column, '=', $scope)
			->count_all();
	}

	/**
	 * Rebuilds the tree using the parent_column. Order of the tree is not guaranteed
	 * to be consistent with structure prior to reconstruction. This method will reduce the
	 * tree structure to eliminating any holes. If you have a child node that is outside of
	 * the left/right constraints it will not be moved under the root.
	 *
	 * @param   integer   $left    Starting value for left branch [Optional]
	 * @param   ORM_MPTT  $target  Target node to use as root [Optional]
	 * @return  integer
	 */
	public function rebuild_tree($left = 1, $target = NULL)
	{
		// check if using target or self as root and load if not loaded
		if (is_null($target) AND ! $this->loaded())
		{
			return FALSE;
		}
		elseif (is_null($target))
		{
			$target = $this;
		}

		if ( ! $target->loaded())
		{
			$target->_load();
		}

		// Use the current node left value for entire tree
		if (is_null($left))
		{
			$left = $target->{$target->left_column};
		}

		// Start the transaction
		$this->_db->begin();
		$right = $left + 1;
		$children = $target->children();

		foreach ($children as $child)
		{
			$right = $child->rebuild_tree($right);
		}

		$target->{$target->left_column} = $left;
		$target->{$target->right_column} = $right;
		$target->save();
		//$target->unlock();
		// Commit the transaction
		$this->_db->commit();

		return $right + 1;
	}

	/**
	 * Magic get function, maps field names to class functions.
	 * 
	 * @param   string  $column  name of the field to get
	 * @return  mixed
	 */
	public function __get($column)
	{
		switch ($column)
		{
			case 'parent':
				return $this->parent();
			case 'parents':
				return $this->parents();
			case 'children':
				return $this->children();
			case 'first_child':
				return $this->children(FALSE, 'ASC', 1);
			case 'last_child':
				return $this->children(FALSE, 'DESC', 1);
			case 'siblings':
				return $this->siblings();
			case 'root':
				return $this->root();
			case 'roots':
				return $this->roots();
			case 'leaves':
				return $this->leaves();
			case 'descendants':
				return $this->descendants();
			case 'fulltree':
				return $this->fulltree();
			default:
				return parent::__get($column);
		}
	}

	/**
	 * Overloads the select_list method to support indenting
	 *
	 * @param   string  $key     first table column [Optional]
	 * @param   string  $value   second table column [Optional]
	 * @param   string  $indent  character used for indenting [Optional]
	 *
	 * @return  array
	 */
	public function select_list($key = NULL, $value = NULL, $indent = NULL)
	{
		if ($key === NULL)
		{
			// Use the default key
			$key = $this->_primary_key;
		}

		if ($value === NULL)
		{
			// Use the default value
			$value = $this->pk();
		}

		$result = DB::select($key, $value, $this->level_column)
				->from($this->_table_name)
				->where($this->scope_column, '=', $this->{$this->scope_column})
				->where($this->_primary_key, '<>', $this->pk())
				->where($this->level_column, '<>', 1)
				->order_by($this->left_column, 'ASC')
				->execute($this->_db);
		
		if (is_string($indent))
		{
			$array = array('last' => '');
			foreach ($result as $row)
			{
				$array[$row[$key]] = str_repeat($indent, $row[$this->level_column] - 2).$row[$value];
			}

			return $array;
		}

		return $result->as_array($key, $value);
	}
	
}