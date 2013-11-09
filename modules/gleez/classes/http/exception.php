<?php
/**
 * HTTP_Exception Class
 *
 * @package    Gleez\HTTP
 * @author     Gleez Team
 * @version    1.1.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class HTTP_Exception extends Gleez_Exception {

	/**
	 * HTTP status code
	 * @var integer
	 */
	protected $_code = 0;

	/**
	 * Request instance that triggered this exception
	 * @var Request
	 */
	protected $_request;

	/**
	 * Creates an HTTP_Exception of the specified type
	 *
	 * Example:
	 * ~~~
	 * throw HTTP_Exception::factory(404, 'File not found!');
	 * ~~~
	 *
	 * @param  integer    $code       The HTTP status code
	 * @param  string     $message    Status message, custom content to display with error [Optional]
	 * @param  array      $variables  Translation variables [Optional]
	 * @param  Exception  $previous   Previous exception [Optional]
	 */
	public static function factory($code, $message = NULL, array $variables = NULL, Exception $previous = NULL)
	{
		$class = 'HTTP_Exception_'.$code;

		return new $class($message, $variables, $previous);
	}

	/**
	 * Creates a new translated exception
	 *
	 * Example:
	 * ~~~
	 * throw new HTTP_Exception('Something went terrible wrong, :user',
	 *     array(':user' => $user)
	 * );
	 * ~~~
	 *
	 * @param  string     $message    Status message, custom content to display with error [Optional]
	 * @param  array      $variables  Translation variables [Optional]
	 * @param  Exception  $previous   Previous exception [Optional]
	 */
	public function __construct($message = NULL, array $variables = NULL, Exception $previous = NULL)
	{
		parent::__construct($message, $variables, $this->_code, $previous);
	}

	/**
	 * Getter and setter the Request that triggered this exception
	 *
	 * @param   Request  $request  Request object that triggered this exception [Optional]
	 *
	 * @return  HTTP_Exception|Request
	 */
	public function request(Request $request = NULL)
	{
		if (is_null($request))
		{
			return $this->_request;
		}

		$this->_request = $request;

		return $this;
	}

	/**
	 * Generate a Response for the current Exception
	 *
	 * @return  Response
	 *
	 * @uses    Gleez_Exception::response
	 * @uses    Gleez_Exception::log
	 */
	public function get_response()
	{
		// Lets log the Exception
		parent::log($this);

		return parent::response($this);
	}

}
