<?php
/**
 * OAuth v2 Request
 *
 * @package    Gleez\OAuth
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
abstract class OAuth2_Request {

	/**
	 * @var  integer  connection timeout
	 */
	public $timeout = 10;

	/**
	 * @var  boolean  send Authorization header?
	 */
	public $send_header = FALSE;

	/**
	 * @var  string  request type name: token, authorize, access, resource
	 */
	protected $name;

	/**
	 * @var  string  request method: GET, POST, etc
	 */
	protected $method = 'GET';

	/**
	 * @var  string  request URL
	 */
	protected $url;

	/**
	 * @var   array   request parameters
	 */
	protected $params = array();

	/**
	 * @var  array  upload parameters
	 */
	protected $upload = array();

	/**
	 * @var  array  required parameters
	 */
	protected $required = array();

	/**
	 * @static
	 * @param  string $type
	 * @param  string $method
	 * @param  string $url
	 * @param  array  $params
	 * @return OAuth2_Request
	 */
	public static function factory($type, $method, $url = NULL, array $params = NULL)
	{
		$class = 'OAuth2_Request_'.$type;

		return new $class($method, $url, $params);
	}

	/**
	 * Set the request URL, method, and parameters.
	 *
	 * @param  string  request method
	 * @param  string  request URL
	 * @param  array   request parameters
	 * @uses   OAuth::parse_url
	 */
	public function __construct($method, $url, array $params = NULL)
	{
		if ($method)
		{
			// Set the request method
			$this->method = strtoupper($method);
		}

		// Separate the URL and query string, which will be used as additional
		// default parameters
		list ($url, $default) = OAuth2::parse_url($url);

		// Set the request URL
		$this->url = $url;

		if ($default)
		{
			// Set the default parameters
			$this->params($default);
		}

		if ($params)
		{
			// Set the request parameters
			$this->params($params);
		}
	}

	/**
	 * Return the value of any protected class variable.
	 *
	 *     // Get the request parameters
	 *     $params = $request->params;
	 *
	 *     // Get the request URL
	 *     $url = $request->url;
	 *
	 * @param   string  $key variable name
	 * @return  mixed
	 */
	public function __get($key)
	{
		return $this->$key;
	}

	/**
	 * Generates the UNIX timestamp for a request.
	 *
	 *     $time = $request->timestamp();
	 *
	 * [!!] This method implements [OAuth 1.0 Spec 8](http://oauth.net/core/1.0/#rfc.section.8).
	 *
	 * @return  integer
	 */
	public function timestamp()
	{
		return time();
	}

	/**
	 * Parameter getter and setter. Setting the value to `NULL` will remove it.
	 *
	 * Example:
	 * ~~~
	 * // Set the "oauth_consumer_key" to a new value
	 * $request->param('oauth_consumer_key', $key);
	 *
	 * // Get the "oauth_consumer_key" value
	 * $key = $request->param('oauth_consumer_key');
	 * ~~~
	 *
	 * @param   string   $name       Parameter name
	 * @param   mixed    $value      Parameter value [Optional]
	 * @param   boolean  $duplicate  Allow duplicates? [Optional]
	 *
	 * @return  mixed    when getting
	 * @return  $this    when setting
	 *
	 * @uses    Arr::get
	 */
	public function param($name, $value = NULL, $duplicate = FALSE)
	{
		if ($value === NULL)
		{
			// Get the parameter
			return Arr::get($this->params, $name);
		}

		if (isset($this->params[$name]) AND $duplicate)
		{
			if ( ! is_array($this->params[$name]))
			{
				// Convert the parameter into an array
				$this->params[$name] = array($this->params[$name]);
			}

			// Add the duplicate value
			$this->params[$name][] = $value;
		}
		else
		{
			// Set the parameter value
			$this->params[$name] = $value;
		}

		return $this;
	}

	/**
	 * Set multiple parameters.
	 *
	 *     $request->params($params);
	 *
	 * @param   array    parameters
	 * @param   boolean  allow duplicates?
	 * @return  $this
	 * @uses    OAuth_Request::param
	 */
	public function params(array $params, $duplicate = FALSE)
	{
		foreach ($params as $name => $value)
		{
			$this->param($name, $value, $duplicate);
		}

		return $this;
	}

	/**
	 * Convert the request parameters into a query string, suitable for GET and
	 * POST requests.
	 *
	 *     $query = $request->as_query();
	 *
	 * [!!] This method implements [OAuth 1.0 Spec 5.2 (2,3)](http://oauth.net/core/1.0/#rfc.section.5.2).
	 *
	 * @param   boolean   include oauth parameters?
	 * @param   boolean   return a normalized string?
	 * @return  string
	 */
	public function as_query($include_oauth = NULL, $as_string = TRUE)
	{
		if ($include_oauth === NULL)
		{
			// If we are sending a header, OAuth parameters should not be
			// included in the query string.
			$include_oauth = ! $this->send_header;
		}

		if ($include_oauth)
		{
			$params = $this->params;
		}
		else
		{
			$params = array();
			foreach ($this->params as $name => $value)
			{
				if (strpos($name, 'oauth_') !== 0)
				{
					// This is not an OAuth parameter
					$params[$name] = $value;
				}
			}
		}

		return $as_string ? OAuth2::normalize_params($params) : $params;
	}

	/**
	 * Return the entire request URL with the parameters as a GET string.
	 *
	 *     $url = $request->as_url();
	 *
	 * @return  string
	 * @uses    OAuth_Request::as_query
	 */
	public function as_url()
	{
		return $this->url.'?'.$this->as_query(TRUE);
	}

	/**
	 * Get and set required parameters.
	 *
	 *     $request->required($field, $value);
	 *
	 * @param   string   parameter name
	 * @param   boolean  field value
	 * @return  boolean  when getting
	 * @return  $this    when setting
	 */
	public function required($param, $value = NULL)
	{
		if ($value === NULL)
		{
			// Get the current status
			return ! empty($this->required[$param]);
		}

		// Change the requirement value
		$this->required[$param] = (boolean) $value;

		return $this;
	}

	/**
	 * Checks that all required request parameters have been set. Throws an
	 * exception if any parameters are missing.
	 *
	 *     try
	 *     {
	 *         $request->check();
	 *     }
	 *     catch (OAuth_Exception $e)
	 *     {
	 *         // Request has missing parameters
	 *     }
	 *
	 * @return  TRUE
	 * @throws  Kohana_OAuth_Exception
	 */
	public function check()
	{
		foreach ($this->required as $param => $required)
		{
			if ($required AND ! isset($this->params[$param]))
			{
				throw new Kohana_OAuth_Exception('Request to :url requires missing parameter ":param"', array(
					':url'   => $this->url,
					':param' => $param,
				));
			}
		}

		return TRUE;
	}

	/**
	 * Execute the request and return a response.
	 *
	 * @param   array    additional cURL options
	 * @return  string   request response body
	 * @uses    OAuth_Request::check
	 * @uses    Arr::get
	 * @uses    Remote::get
	 */
	public function execute(array $options = NULL)
	{
		// Check that all required fields are set
		$this->check();

		// Get the URL of the request
		$url = $this->url;

		if ( ! isset($options[CURLOPT_CONNECTTIMEOUT]))
		{
			// Use the request default timeout
			$options[CURLOPT_CONNECTTIMEOUT] = $this->timeout;
		}

		if ($this->method === 'POST')
		{
			// Send the request as a POST
			$options[CURLOPT_POST] = TRUE;

			if ($post = $this->as_query(NULL, empty($this->upload)))
			{
				// Attach the post fields to the request
				$options[CURLOPT_POSTFIELDS] = $post;
			}
		}
		elseif ($query = $this->as_query())
		{
			// Append the parameters to the query string
			$url = "{$url}?{$query}";
		}

		return OAuth2::remote($url, $options);
	}

	public function format($format = 'json')
	{
		$this->format = 'json';

		return $this;
	}

}
