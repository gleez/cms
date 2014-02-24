<?php
/**
 * OAuth2 Exception
 *
 * @package    Gleez\oAuth2
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Oauth2_Exception extends Exception
{
	/**
	 * REQUIRED.  A single error code
	 *
	 * @access	public
	 * @var		string	$code
	 */
	public $code = 500;

	/**
	 * REQUIRED.  A single oauth2 error
	 *
	 * @access	public
	 * @var		string	$error
	 */
	public $error = 'server_error';

	/**
	 * OPTIONAL.  A human-readable text providing additional information,
	 *   used to assist in the understanding and resolution of the error occurred.
	 *
	 * @access	public
	 * @var		string	$error_description
	 */
	public $error_description;

	/**
	 * OPTIONAL.  A URI identifying a human-readable web page with
	 *   information about the error, used to provide the resource owner
	 *   with additional information about the error.
	 *
	 * @access	public
	 * @var		string	$error_uri
	 */
	public $error_uri;

	/**
	 * Creates a new translated OAuth2 error
	 *
	 * Usage:
	 * ~~~
	 * throw new Oauth2_Exception(400, 'invalid_client', 'The client id (:id) supplied is invalid', array(':id' => $client_id));
	 * ~~~
	 *
	 * @param  integer    $code       The status code
	 * @param  string     $error      The oauth2 error
	 * @param  string     $message    Error message [Optional]
	 * @param  array      $variables  Translation variables [Optional]
	 * @param  string     $error_uri  The Error URL [Optional]
	 */
	public static function factory($code, $error, $message = NULL, array $variables = NULL, $error_uri = NULL)
	{
		return new self($code, $error, $message, $variables, $error_uri);
	}

	/**
	 * Creates a new translated OAuth2 error
	 *
	 * Usage:
	 * ~~~
	 * throw new Oauth2_Exception(400, 'invalid_client', 'The client id (:id) supplied is invalid', array(':id' => $client_id));
	 * ~~~
	 *
	 * @param  integer    $code       The status code
	 * @param  string     $error      The oauth2 error
	 * @param  string     $message    Error message [Optional]
	 * @param  array      $variables  Translation variables [Optional]
	 * @param  string     $error_uri  The Error URL [Optional]
	 */
	public function __construct($code, $error, $message = NULL, array $variables = NULL, $error_uri = NULL)
	{
		// Set the message
		$pmessage = is_null($message) ? $error : __($message, $variables);

		// Pass the message and integer code to the parent
		parent::__construct($pmessage, (int) $code);

		// Save the unmodified code
		// @link http://bugs.php.net/39615
		$this->code 				= (int) $code;
		$this->error 				= $error;
		$this->error_description 	= $message;
		$this->error_uri			= $error_uri;

		if (!is_null($error_uri)) 
		{
			if (strlen($error_uri) > 0 && $error_uri[0] == '#')
			{
				// we are referencing an oauth bookmark (for brevity)
				$this->error_uri = 'http://tools.ietf.org/html/rfc6749' . $error_uri;
			}
		}
	}

	/**
	 * Magic object-to-string method
	 *
	 * Usage:
	 * ~~~
	 * echo $exception;
	 * ~~~
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getJsonError();
	}


	public function getError()
	{
		return $this->error;
	}

	public function getJsonError()
	{
		return json_encode(array(
			'error'				=> $this->getError(),
			'error_description'	=> $this->getMessage(),
		));
	}

	public function render()
	{
		try
		{
			// Instantiate the error view.
			//$view = View::factory(self::$error_view, get_defined_vars());

			// Prepare the response object.
			$response = Response::factory();

			// Set the response status
			$response->status($this->code);

			// Set the response headers
			//$response->headers('Content-Type', self::$error_view_content_type.'; charset='.Kohana::$charset);
			$response->headers('cache-control', 'no-store');
			//$response->headers('WWW-Authenticate', 'Bearer');

			// Set the response body
			//$response->body($view->render());
			$response->body($this->error);
		}
		catch (Exception $e)
		{
			/**
			 * Things are going badly for us, Lets try to keep things under control by
			 * generating a simpler response object.
			 */
			$response = Response::factory();
			$response->status($this->code);
			$response->headers('Content-Type', 'text/plain');
			$response->body($this->error);
		}

		return $response;
	}
}