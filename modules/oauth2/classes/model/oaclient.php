<?php

class Model_Oaclient extends ORM {

	protected $_table_name = "oauth_clients";

	protected $_table_columns = array(
		'id'            => array( 'type' => 'int' ),
		'title'         => array( 'type' => 'string' ),
		'user_id'       => array( 'type' => 'int' ),
		'client_id'     => array( 'type' => 'string' ),
		'client_secret' => array( 'type' => 'string' ),
		'redirect_uri'  => array( 'type' => 'string' ),
		'grant_types'   => array( 'type' => 'string' ),
		'description'   => array( 'type' => 'string' ),
		'logo'          => array( 'type' => 'string' ),
		'status'        => array( 'type' => 'int' ),
		'created'       => array( 'type' => 'int' ),
		'updated'       => array( 'type' => 'int' ),
	);

	/**
	 * Auto fill create and update columns
	 */
	protected $_created_column = array('column' => 'created', 'format' => TRUE);
	protected $_updated_column = array('column' => 'updated', 'format' => TRUE);
	    
	protected $_belongs_to = array(
		'user' => array('model' => 'user', 'foreign_key' => 'user_id'),
	);

	public function rules()
	{
		return array(
			'title' => array(
				array('not_empty'),
			),
			'redirect_uri' => array(
				array('not_empty'),
			),
		);
	}

	public function __get($field)
	{
		if( $field === 'url')
			return Route::get('oauth2/client')->uri( array( 'id' => $this->id, 'action' => 'view' ) );

		if( $field === 'edit_url' )
				return Route::get('oauth2/client')->uri( array( 'id' => $this->id, 'action' => 'edit' ) );

		if( $field === 'delete_url' )
				return Route::get('oauth2/client')->uri( array( 'id' => $this->id, 'action' => 'delete' ) );
			
			return parent::__get($field);
	}

	public function save(Validation $validation = NULL)
	{
		$this->user_id   		= User::active_user()->id;
		$this->client_id 		= sha1($this->user_id.uniqid().microtime());
		$this->client_secret    = sha1($this->user_id.uniqid().microtime());
		
		return parent::save($validation);
	}
    
}