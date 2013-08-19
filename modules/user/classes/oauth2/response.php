<?php
/**
 * OAuth v2 Response
 *
 * @package    Gleez\OAuth
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class OAuth2_Response {

	public static function factory($body)
	{
		return new OAuth2_Response($body);
	}

	/**
	 * @var   array   response parameters
	 */
	protected $params = array();

	public function __construct($body = NULL)
	{
		if ($body AND is_array($body))
		{
			$this->params = $body;
		}
		elseif($body)
		{
			$this->params = OAuth2::parse_params($body);
		}
	}

	/**
	 * Return the value of any protected class variable.
	 *
	 *     // Get the response parameters
	 *     $params = $response->params;
	 *
	 * @param   string  variable name
	 * @return  mixed
	 */
	public function __get($key)
	{
		return $this->$key;
	}

	public function param($name, $default = NULL)
	{
		return Arr::get($this->params, $name, $default);
	}

}
