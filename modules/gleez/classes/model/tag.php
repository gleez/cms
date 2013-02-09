<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * An adaptation of Freetag
 *
 * @package    Gleez
 * @category   Tags
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Model_Tag extends ORM {  

	protected $_table_columns =  array(
					'id' 	=> array( 'type' => 'int' ),
					'name' 	=> array( 'type' => 'string' ),
					'type' 	=> array( 'type' => 'string' ),
					'count' => array( 'type' => 'int' ),
					);
	
	protected $_has_many   = array(
                                        'posts'    => array('through' => 'posts_tags', 'foreign_key' => 'tag_id' ),
                                );
	
	protected $_ignored_columns = array('path', 'action');
	
	public function rules()
	{
		return array(
			'name' => array(
				array('not_empty'),
				//array('min_length', array(':value', 3)),
				//array('max_length', array(':value', 32)),
				array(array($this, 'tag_available'), array(':validation', ':field')),
			),
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
		
		$alias  = empty($this->path) ? 'tags/'.$this->name : $this->path;
		$values['source'] = $this->rawurl;
		$values['alias']  = Path::clean( $alias );
		$values['type']   = empty($this->type) ? FALSE : $this->type ;
		$values['action'] = empty($this->action) ? 'tag' : $this->action;
	
		$values = Module::action('tag_aliases', $values, $this);
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
			return Route::get($this->type)->uri( array( 'action' => 'tag', 'id' => $this->id ) );
		
		// Model specefic links; view, edit, delete url's.
                if( $field === 'url'  OR $field === 'link')
			return ($path = Path::load($this->rawurl) ) ? $path['alias'] : $this->rawurl;
	
                if( $field === 'edit_url' )
			return Route::get('admin/tag')->uri( array( 'id' => $this->id, 'action' => 'edit' ) );

                if( $field === 'delete_url' )
			return Route::get('admin/tag')->uri( array( 'id' => $this->id, 'action' => 'delete' ) );
		
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
	public function tag_available(Validation $validation, $field)
	{
		if( DB::select(array('COUNT("*")', 'total_count'))
			->from($this->_table_name)
			->where('name', '=', $validation[$field])
			->where($this->_primary_key, '!=', $this->pk())
			->where('type', '=', $this->type)
			->execute($this->_db)
			->get('total_count') > 0)
                {
			$validation->error($field, 'tag_available', array($validation[$field]));
		}
	}
        
}