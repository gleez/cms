<?php
/**
 * Module Model Class
 *
 * @package    Gleez\ORM\Module
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Model_Module extends ORM {

	/**
	 * Table columns
	 * @var array
	 */
	protected $_table_columns = array(
		'id'      => array( 'type'=>'int' ),
		'name'    => array( 'type'=>'string' ),
		'active'  => array( 'type'=>'int' ),
		'weight'  => array( 'type'=>'int' ),
		'version' => array( 'type'=>'float' ),
		'path'    => array( 'type'=>'string' ),
	);

}
