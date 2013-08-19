<?php
/**
 * Default auth role
 *
 * @package    Gleez\User
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Model_Role extends ORM {

	/**
	 * Table columns
	 * @var array
	 */
	protected $_table_columns = array(
		'id'          => array( 'type' => 'int' ),
		'name'        => array( 'type' => 'string' ),
		'description' => array( 'type' => 'string' ),
		'special'     => array( 'type' => 'int' ),
	);

	/**
	 * A role has many users
	 *
	 * @var array Relationships
	 */
	protected $_has_many = array(
		'users' => array(
			'through' => 'roles_users'
		)
	);

	/**
	 * Rules for the role model
	 *
	 * @return array Rules
	 */
	public function rules()
	{
		return array(
			'name' => array(
				array('not_empty'),
				array('min_length', array(':value', 4)),
				array('max_length', array(':value', 32)),
			),
			'description' => array(
				array('max_length', array(':value', 255)),
			)
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
			'name'        => __('Name'),
			'description' => __('Description'),
			'special'     => __('Special Role'),
		);
	}

	/**
	 * Override the save method to clear cache
	 */
	public function save(Validation $validation = NULL)
	{
		parent::save( $validation );

		//cleanup the cache
		Cache::instance('roles')->delete_all();

		return $this;
	}

	/**
	 * Override the delete method to clear cache
	 */
	public function delete()
	{
		parent::delete();

		//cleanup the cache
		Cache::instance('roles')->delete_all();

		return $this;
	}

	/**
	 * Reading data from inaccessible properties
	 *
	 * @param   string  $field
	 * @return  mixed
	 *
	 * @uses  Route::get
	 * @uses  Route::uri
	 */
	public function __get($field)
	{
		switch ($field)
		{
			case 'edit_url':
				// Model specific links; view, edit, delete url's.
				return Route::get('admin/role')->uri(array('action' => 'edit', 'id' => $this->id));
				break;
			case 'delete_url':
				// Model specific links; view, edit, delete url's.
				return Route::get('admin/role')->uri(array('action' => 'delete', 'id' => $this->id));
				break;
			case 'perm_url':
				return Route::get('admin/permission')->uri(array('action' => 'role', 'id' => $this->id));
				break;
		}

		return parent::__get($field);
	}

}