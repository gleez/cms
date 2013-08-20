<?php
/**
 * A HTTP Request specific interface that adds the methods required
 * by HTTP requests
 *
 * Over and above [HTTP_Interaction], this interface provides method,
 * uri, get and post methods.
 *
 * @package    Gleez\HTTP
 * @author     Kohana Team
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
interface HTTP_Request extends HTTP_Message {

	// HTTP Methods
	const GET     = 'GET';
	const POST    = 'POST';
	const PUT     = 'PUT';
	const DELETE  = 'DELETE';
	const HEAD    = 'HEAD';
	const OPTIONS = 'OPTIONS';
	const TRACE   = 'TRACE';
	const CONNECT = 'CONNECT';

	/**
	 * Gets or sets the HTTP method
	 *
	 * Usually GET, POST, PUT or DELETE in traditional CRUD applications.
	 *
	 * @param   string  $method  Method to use for this request [Optional]
	 *
	 * @return  mixed
	 */
	public function method($method = NULL);

	/**
	 * Gets the URI of this request.
	 *
	 * Optionally allows setting of [Route] specific parameters during
	 * the URI generation. If no parameters are passed, the request
	 * will use the default values defined in the Route.
	 *
	 * @return  string
	 */
	public function uri();

	/**
	 * Gets or sets HTTP query string
	 *
	 * @param   mixed   $key    Key or key value pairs to set [Optional]
	 * @param   string  $value  Value to set to a key [Optional]
	 *
	 * @return  mixed
	 */
	public function query($key = NULL, $value = NULL);

	/**
	 * Gets or sets HTTP POST parameters to the request.
	 *
	 * @param   mixed   $key   Key or key value pairs to set [Optional]
	 * @param   string  $value Value to set to a key [Optional]
	 *
	 * @return  mixed
	 */
	public function post($key = NULL, $value = NULL);
}