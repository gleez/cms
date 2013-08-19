<?php
/**
 * An adaptation of Freetag
 *
 * @package    Gleez\Tags
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Model_Tag extends ORM {

	/**
	 * Table columns
	 * @var array
	 */
	protected $_table_columns =  array(
		'id' 	=> array( 'type' => 'int' ),
		'name' 	=> array( 'type' => 'string' ),
		'type' 	=> array( 'type' => 'string' ),
		'count' => array( 'type' => 'int' ),
	);

	/**
	 * "Has many" relationships
	 * @var array
	 */
	protected $_has_many = array(
		'posts' => array(
			'model'       => 'post',
			'through'     => 'posts_tags',
			'foreign_key' => 'tag_id'
		),
	);

	/**
	 * Ignored columns
	 * @var array
	 */
	protected $_ignored_columns = array(
		'path',
		'action'
	);

	/**
	 * Labels for fields in this model
	 *
	 * @return  array  Labels
	 */
	public function labels()
	{
		return array(
			'name' => __('Tag'),
			'type'  => __('Type'),
		);
	}

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
				array(array($this, 'tag_available'), array(':validation', ':field')),
			),
		);
	}

	/**
	 * Updates or Creates the record depending on loaded()
	 *
	 * @param   Validation  $validation  Validation object [Optional]
	 * @return  ORM
	 */
	public function save(Validation $validation = NULL)
	{
		parent::save( $validation );

		if ( $this->loaded())
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
	public function delete()
	{
		if ( ! $this->_loaded)
		{
			throw new Gleez_Exception('Cannot delete :model model because it is not loaded.', array(':model' => $this->_object_name));
		}

		$source = $this->rawurl;

		parent::delete();

		// Delete the path aliases associated with this object
		Path::delete( array('source' => $source) );
		unset($source);

		return $this;
	}

	/**
	 * Adds or deletes path aliases
	 *
	 * @uses  Path::load
	 * @uses  Path::clean
	 * @uses  Path::save
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

		$alias  = empty($this->path) ? 'tags/'.$this->name : $this->path;
		$values['source'] = $this->rawurl;
		$values['alias']  = Path::clean($alias);
		$values['type']   = empty($this->type) ? FALSE : $this->type ;
		$values['action'] = empty($this->action) ? 'tag' : $this->action;

		$values = Module::action('tag_aliases', $values, $this);
		Path::save($values);
	}

	/**
	 * Reading data from inaccessible properties
	 *
	 * @param   string  $field
	 * @return  mixed
	 *
	 * @uses    HTML::chars
	 * @uses    Path::load
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
				return Route::get($this->type)->uri(array('action' => 'tag', 'id' => $this->id));
			break;
			case 'edit_url':
				// Model specific links; view, edit, delete url's.
				return Route::get('admin/tag')->uri(array('id' => $this->id, 'action' => 'edit'));
			break;
			case 'delete_url':
				// Model specific links; view, edit, delete url's.
				return Route::get('admin/tag')->uri(array('id' => $this->id, 'action' => 'delete'));
			break;
			case 'url':
			case 'link':
				return ($path = Path::load($this->rawurl)) ? $path['alias'] : $this->rawurl;
			break;
		}

		return parent::__get($field);
	}

	/**
	 * Check by triggering error if name exists.
	 * Validation callback.
	 *
	 * @param   Validation  $validation Validation object
	 * @param   string      $field      Field name
	 *
	 * @uses    DB::select
	 * @uses    DB::expr
	 * @uses    Validation::error
	 */
	public function tag_available(Validation $validation, $field)
	{
		$result = DB::select(array(DB::expr('COUNT(*)'), 'total_count'))
				->from($this->_table_name)
				->where('name', '=', $validation[$field])
				->where($this->_primary_key, '!=', $this->pk())
				->where('type', '=', $this->type)
				->execute($this->_db)
				->get('total_count');

		if($result > 0)
		{
			$validation->error($field, 'tag_available', array($validation[$field]));
		}
	}

}