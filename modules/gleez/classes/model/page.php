<?php defined("SYSPATH") or die("No direct script access.");

class Model_Page extends Post {
        
        protected $_table_name = 'posts';
	protected $_post_type = 'page';
	
	protected $_table_columns = array(
					'id'       => array( 'type' => 'int' ),
					'version'  => array( 'type' => 'int' ),
					'author'   => array( 'type' => 'int' ),
					'title'    => array( 'type' => 'string' ),
					'body'     => array( 'type' => 'string' ),
					'teaser'   => array( 'type' => 'string' ),
					'status'   => array( 'type' => 'string' ),
					'promote'  => array( 'type' => 'int' ),
					'moderate' => array( 'type' => 'int' ),
					'sticky'   => array( 'type' => 'int' ),
					'type'     => array( 'type' => 'string' ),
					'format'   => array( 'type' => 'int' ),
					'created'  => array( 'type' => 'int' ),
					'updated'  => array( 'type' => 'int' ),
					'pubdate'  => array( 'type' => 'int' ),
					'password' => array( 'type' => 'string' ),
					'comment'  => array( 'type' => 'int' ),
					'lang' 	   => array( 'type' => 'string' ),
        );
        
	public function save(Validation $validation = NULL)
	{
                $config = Kohana::$config->load('page');
		$this->status  = empty($this->status)  ? $config->get('default_status', 'draft') : $this->status;
	
		if( !$config->use_comment )
			$this->comment = empty($this->comment) ? $config->get('comment', 0) : $this->comment;
		
		if( !$config->use_excerpt ) $this->teaser = FALSE;
        
		return parent::save($validation);
	}

	public function values(array $values, array $expected = NULL)
	{	
		$this->type = $this->_post_type;
		return parent::values($values, $expected);
	}
	public function find($id = NULL)
	{
		$this->where($this->_object_name.'.type', '=', $this->_post_type);
		return parent::find($id);
	}

	public function find_all($id = NULL)
	{
		$this->where($this->_object_name.'.type', '=', $this->_post_type);
		return parent::find_all($id);
	}

	public function count_all($id = NULL)
	{
		$this->where($this->_object_name.'.type', '=', $this->_post_type);
		return parent::count_all($id);
	}

	public function delete($id = NULL)
	{
		$this->where($this->_object_name.'.type', '=', $this->_post_type);
		return parent::delete($id);
	}
        
}