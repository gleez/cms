<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Widget Model Class
 *
 * @package    Gleez\Widget
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Model_Widget extends ORM {

	/**
	 * Table columns
	 * @var array
	 */
	protected $_table_columns = array(
		'id'         => array( 'type' => 'int' ),
		'name'       => array( 'type' => 'string' ),
		'title'      => array( 'type' => 'string' ),
		'module'     => array( 'type' => 'string' ),
		'theme'      => array( 'type' => 'string' ),
		'status'     => array( 'type' => 'int' ),
		'region'     => array( 'type' => 'string' ),
		'weight'     => array( 'type' => 'int' ),
		'cache'      => array( 'type' => 'int' ),
		'visibility' => array( 'type' => 'int' ),
		'pages'      => array( 'type' => 'string' ),
		'show_title' => array( 'type' => 'int' ),
		'roles'      => array( 'type' => 'string' ),
		'body'       => array( 'type' => 'string' ),
		'format'     => array( 'type' => 'int' ),
	);

	/**
	 * Ignored columns
	 * @var array
	 */
	protected $_ignored_columns = array('
		config',
		'visible',
		'content'
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
		if (is_array($this->roles) AND count($this->roles) > 0)
		{
			$this->roles = implode(',', $this->roles);
		}
		else
		{
			$this->roles = NULL;
		}

		return parent::save($validation);
	}

}
