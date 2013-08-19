<?php
/**
 * OAuth v2 Request Data
 *
 * @package    Gleez\OAuth
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class OAuth2_Request_Data extends OAuth2_Request {

	protected $name = 'data';
	protected $format = FALSE;

	public function execute(array $options = NULL)
	{
		return parent::execute($options);
	}

}
