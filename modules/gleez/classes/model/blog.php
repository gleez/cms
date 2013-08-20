<?php
/**
 * Gleez Blog Model
 *
 * @package    Gleez\ORM\Blog
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Model_Blog extends Post {

	/**
	 * Post table name
	 * @var string
	 */
	protected $_table_name = 'posts';

	/**
	 * Post type
	 * @var string
	 */
	protected $_post_type = 'blog';


	/**
	 * Updates or Creates the record depending on loaded()
	 *
	 * @param   Validation  $validation  Validation object [Optional]
	 * @return  Post
	 *
	 * @uses    Config::load
	 * @uses    Config::get
	 * @uses    Cache::delete
	 */
	public function save(Validation $validation = NULL)
	{
		$config = Kohana::$config->load('blog');
		$this->status = empty($this->status) ? $config->get('default_status', 'draft') : $this->status;

		if ( ! $config->use_comment)
		{
			$this->comment = empty($this->comment) ? $config->get('comment', 0) : $this->comment;
		}

		if( ! $config->use_excerpt)
		{
			$this->teaser = FALSE;
		}

		Cache::instance($this->type)->delete('recent_blogs');

		return parent::save($validation);
	}

	/**
	 * Set values from an array with support for one-one relationships
	 *
	 * This method should be used for loading in post data, etc.
	 *
	 * @param   array  $values    Array of `column => val`
	 * @param   array  $expected  Array of keys to take from `$values` [Optional]
	 * @return  ORM
	 */
	public function values(array $values, array $expected = NULL)
	{
		$this->type = $this->_post_type;

		return parent::values($values, $expected);
	}

	/**
	 * Finds and loads a single database row into the object
	 *
	 * @param   integer $id  Row ID. The search criteria [Optional]
	 * @return  Database_Result|ORM
	 */
	public function find($id = NULL)
	{
		$this->where($this->_object_name.'.type', '=', $this->_post_type);

		return parent::find($id);
	}

	/**
	 * Finds multiple database rows and returns an iterator of the rows found
	 *
	 * @param   integer  $id  Row ID. The search criteria [Optional]
	 * @return  Database_Result|ORM
	 */
	public function find_all($id = NULL)
	{
		$this->where($this->_object_name.'.type', '=', $this->_post_type);

		return parent::find_all($id);
	}

	/**
	 * Count the number of records in the table
	 *
	 * @param   integer  $id  Row ID. The search criteria [Optional]
	 * @return  integer
	 */
	public function count_all($id = NULL)
	{
		$this->where($this->_object_name.'.type', '=', $this->_post_type);

		return parent::count_all($id);
	}

	/**
	 * Deletes a single record or multiple records, ignoring relationships
	 *
	 * @param   integer  $id  Row ID. The search criteria [Optional]
	 * @return  Post
	 */
	public function delete($id = NULL)
	{
		$this->where($this->_object_name.'.type', '=', $this->_post_type);

		return parent::delete($id);
	}

}