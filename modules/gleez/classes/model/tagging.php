<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_Tagging extends ORM {

	protected $_table_columns = array(
					'post_id' => array( 'type' => 'int' ),
					'tag_id'  => array( 'type' => 'int' ),
					'author'  => array( 'type' => 'int' ),
					'type'    => array( 'type' => 'string' ),
					'created' => array( 'type' => 'int' ),
					);
	
	/**
	 * Table name
	 * @var string
	 */
	protected $_table_name = 'posts_tags';
        
        protected $_belongs_to = array('user' => array('foreign_key' => 'author'),
                                       'tags'  => array('foreign_key' => 'tag_id'),
				       'posts' => array('foreign_key' => 'post_id')
                                       );
	
	/**
	 * Auto fill create and update columns
	*/
	protected $_created_column = array('column' => 'created', 'format' => TRUE);
	
	/**
	 * Updates or Creates the record depending on loaded()
	 *
	 * @chainable
	 * @param  Validation $validation Validation object
	 * @return ORM
	 */
	public function save(Validation $validation = NULL)
	{
                //$this->author = User::active_user()->id;
	
		return parent::save( $validation );
	}
	
	
} // End Tagging Model
