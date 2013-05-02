<?php defined("SYSPATH") OR die("No direct script access.");
/**
 * Gleez Page Model
 *
 * @package    Gleez\ORM\Page
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Model_Page extends Post {
        
	/**
	 * Post table name
	 * @var string
	 */
	protected $_table_name = 'posts';
	
	/**
	 * Post type
	 * @var string
	 */
	protected $_post_type = 'page';
        
	public function save(Validation $validation = NULL)
	{
                $config = Kohana::$config->load('page');
		$this->status  = empty($this->status)  ? $config->get('default_status', 'draft') : $this->status;
	
		if( !$config->use_comment )
		{
			$this->comment = empty($this->comment) ? $config->get('comment', 0) : $this->comment;
		}
		
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