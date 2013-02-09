<?php defined("SYSPATH") or die("No direct script access.");

class Model_Widget extends ORM {

	protected $_table_columns = array(
					'id' => array( 'type' => 'int' ),
					'name'   => array( 'type' => 'string' ),
					'title'  => array( 'type' => 'string' ),
					'module' => array( 'type' => 'string' ),
					'theme'  => array( 'type' => 'string' ),
					'status' => array( 'type' => 'int' ),
					'region' => array( 'type' => 'string' ),
					'weight' => array( 'type' => 'int' ),
					'cache'  => array( 'type' => 'int' ),
					'visibility' => array( 'type' => 'int' ),
					'pages' => array( 'type' => 'string' ),
					'show_title' => array( 'type' => 'int' ),
					'roles'  => array( 'type' => 'string' ),
					'body'   => array( 'type' => 'string' ),
					'format' => array( 'type' => 'int' ),
					);
	
	protected $_ignored_columns = array('config', 'visible', 'content');

	public function rules()
	{
		return array(
			'name' => array(
				array('not_empty'),
				//array(array($this, 'term_available'), array(':validation', ':field')),
			),
		);
	}
        
	public function save(Validation $validation = NULL)
	{
		//Message::success('Widget - Save : ' . Debug::vars($this->_object));
                if( is_array($this->roles) AND count($this->roles) > 0)
                {
                        $this->roles = implode(',', $this->roles);
                }
                else
                {
                       $this->roles = NULL; 
                }
		return parent::save( $validation );
	}
        
}
