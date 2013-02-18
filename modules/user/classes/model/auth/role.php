<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Default auth role
 *
 * @package    Gleez\User
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Model_Auth_Role extends ORM {

	protected $_table_columns = array(
					'id' => array( 'type' => 'int' ),
					'name' => array( 'type' => 'string' ),
					'description' => array( 'type' => 'string' ),
					'special' => array( 'type' => 'int' ),
					);

	// Relationships
	protected $_has_many = array('users' => array('through' => 'roles_users'));

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

	public function find_all($id = NULL)
	{
		//$this->where($this->_object_name.'.id', '>', 1);
		return parent::find_all($id);
	}

} // End Auth Role Model