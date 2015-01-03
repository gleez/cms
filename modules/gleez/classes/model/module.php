<?php
/**
 * Module Model Class
 *
 * @package    Gleez\ORM\Module
 * @author     Gleez Team
 * @copyright  (c) 2011-2015 Gleez Technologies
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
		'type'    => array( 'type'=>'string' ),
		'active'  => array( 'type'=>'int' ),
		'weight'  => array( 'type'=>'int' ),
		'version' => array( 'type'=>'string' ),
		'path'    => array( 'type'=>'string' )
	);

}
