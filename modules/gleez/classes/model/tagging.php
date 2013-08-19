<?php
/**
 * An adaptation of tagging
 *
 * @package    Gleez\ORM\Tagging
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Model_Tagging extends ORM {

	/**
	 * Table name
	 * @var string
	 */
	protected $_table_name = 'posts_tags';

	/**
	 * Table columns
	 * @var array
	 */
	protected $_table_columns = array(
		'post_id' => array( 'type' => 'int' ),
		'tag_id'  => array( 'type' => 'int' ),
		'author'  => array( 'type' => 'int' ),
		'type'    => array( 'type' => 'string' ),
		'created' => array( 'type' => 'int' ),
	);

	/**
	 * "Belongs to" relationships
	 * @var array
	 */
	protected $_belongs_to = array(
		'user' => array(
			'foreign_key' => 'author'
		),
		'tags' => array(
			'foreign_key' => 'tag_id'
		),
		'posts' => array(
			'foreign_key' => 'post_id'
		)
	);


	/**
	 * Auto-update columns for creation
	 * @var string
	 */
	protected $_created_column = array(
		'column' => 'created',
		'format' => TRUE
	);

}
