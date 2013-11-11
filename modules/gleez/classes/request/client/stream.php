<?php
/**
 * [Request_Client_External] Stream driver performs external requests using php
 * sockets
 *
 * To use this driver, ensure the following is completed before executing an
 * external request- ideally in the application bootstrap.
 *
 * Example:
 * ~~~
 * // In application bootstrap
 * Request_Client_External::$client = 'Request_Client_Stream';
 * ~~~
 *
 * @package    Gleez\Base
 * @author     Kohana Team
 * @author     Gleez Team
 * @version    1.0.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 *
 * @uses       http://php.net/manual/en/book.stream.php
 */
class Request_Client_Stream extends Request_Client_External {

	public function _send_message(Request $request, Response $response)
	{
		// Calculate stream mode
		$mode = ($request->method() === HTTP_Request::GET) ? 'r' : 'r+';

		// Process cookies
		if ($cookies = $request->cookie())
		{
			$request->headers('cookie', http_build_query($cookies, NULL, '; '));
		}

		// Get the message body
		$body = $request->body();

		if (is_resource($body))
		{
			$body = stream_get_contents($body);
		}

		// Set the content length
		$request->headers('content-length', (string) strlen($body));

		list($protocol) = explode('/', $request->protocol());

		// Create the context
		$options = array(
			strtolower($protocol) => array(
				'method'     => $request->method(),
				'header'     => (string) $request->headers(),
				'content'    => $body
			)
		);

		// Create the context stream
		$context = stream_context_create($options);

		/** @noinspection PhpParamsInspection */
		stream_context_set_option($context, $this->_options);

		$uri = $request->uri();

		if ($query = $request->query())
		{
			$uri .= '?'.http_build_query($query, NULL, '&');

		}

		$stream = fopen($uri, $mode, FALSE, $context);

		$meta_data = stream_get_meta_data($stream);

		// Get the HTTP response code
		$http_response = array_shift($meta_data['wrapper_data']);

		if (preg_match_all('/(\w+\/\d\.\d) (\d{3})/', $http_response, $matches) !== FALSE)
		{
			$protocol = $matches[1][0];
			$status   = (int) $matches[2][0];
		}
		else
		{
			$protocol = NULL;
			$status   = NULL;
		}

		// Get any existing response headers
		$response_header = $response->headers();

		// Process headers
		array_map(array($response_header, 'parse_header_string'), array(), $meta_data['wrapper_data']);

		$response->status($status)
			->protocol($protocol)
			->body(stream_get_contents($stream));

		// Close the stream after use
		fclose($stream);

		return $response;
	}
}