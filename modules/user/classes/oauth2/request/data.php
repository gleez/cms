<?php defined('SYSPATH') OR die('No direct access allowed.');

class OAuth2_Request_Data extends OAuth2_Request {

	protected $name = 'data';
	protected $format = FALSE;

	public function execute(array $options = NULL)
	{
		return parent::execute($options);
	}
	
}
