<?php
/**
 * Default auth user token
 *
 * @package    Gleez\User
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Model_User_Token extends ORM {

	protected $_table_columns = array(
		'id' => array( 'type' => 'int' ),
		'user_id' => array( 'type' => 'int' ),
		'user_agent' => array( 'type' => 'string' ),
		'token' => array( 'type' => 'string' ),
		'type' => array( 'type' => 'string', "column_default" => NULL ),
		'created' => array( 'type' => 'int' ),
		'expires' => array( 'type' => 'int' ),
	);

	// Relationships
	protected $_belongs_to = array('user' => array());

	/**
	 * Handles garbage collection and deleting of expired objects.
	 *
	 * @return  void
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (mt_rand(1, 100) === 1)
		{
			// Do garbage collection
			$this->delete_expired();
		}

		if ($this->expires < time() AND $this->_loaded)
		{
			// This object has expired
			$this->delete();
		}
	}

	/**
	 * Deletes all expired tokens.
	 *
	 * @return  ORM
	 */
	public function delete_expired()
	{
		// Delete all expired tokens
		DB::delete($this->_table_name)
			->where('expires', '<', time())
			->execute($this->_db);

		return $this;
	}

	public function create(Validation $validation = NULL)
	{
		$this->token = $this->create_token();

		return parent::create($validation);
	}

	protected function create_token()
	{
		do
		{
			$token = sha1(uniqid(Text::random('alnum', 32), TRUE));
		}
		while(ORM::factory('user_token', array('token' => $token))->loaded());

		return $token;
	}

}