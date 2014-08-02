<?php
/**
 * Gleez Exception Class
 *
 * Translates exceptions using the [I18n] class
 *
 * @package    Gleez\Exceptions
 * @author     Gleez Team
 * @version    1.1.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Gleez_Exception extends Exception {

	/**
	 * PHP error `code => human readable name`
	 * @var array
	 */
	public static $php_errors = array(
		E_ERROR              => 'Fatal Error',
		E_USER_ERROR         => 'User Error',
		E_PARSE              => 'Parse Error',
		E_WARNING            => 'Warning',
		E_USER_WARNING       => 'User Warning',
		E_STRICT             => 'Strict',
		E_NOTICE             => 'Notice',
		E_RECOVERABLE_ERROR  => 'Recoverable Error',
		E_DEPRECATED         => 'Deprecated',
	);

	/**
	 * Error rendering view
	 * @var string
	 */
	public static $error_view = 'errors/error';

	/**
	 * Error view content type
	 * @var string
	 */
	public static $error_view_content_type = 'text/html';

	/**
	 * Translate (localize) error message or not
	 * @var bool
	 */
	public static $translate_errors = TRUE;

	/**
	 * Creates a new translated exception
	 *
	 * Usage:
	 * ~~~
	 * throw new Gleez_Exception('Something went terrible wrong, :user', array(':user' => $user));
	 * ~~~
	 *
	 * @param  string     $message    Error message [Optional]
	 * @param  array      $variables  Translation variables [Optional]
	 * @param  integer    $code       The exception code [Optional]
	 * @param  Exception  $previous   Previous exception [Optional]
	 */
	public function __construct($message = "", array $variables = NULL, $code = 0, Exception $previous = NULL)
	{
		if (self::$translate_errors)
		{
			// Set the message
			$message = __($message, $variables);
		}

		// Pass the message and integer code to the parent
		parent::__construct($message, (int) $code, $previous);

		// Save the unmodified code
		// @link http://bugs.php.net/39615
		$this->code = $code;
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
		return self::text($this);
	}

	/**
	 * Inline exception handler, displays the error message, source of the
	 * exception, and the stack trace of the error.
	 *
	 * @param  Exception  $e  Exception
	 */
	public static function handler(Exception $e)
	{
		$response = self::_handler($e);

		// Send the response to the browser
		echo $response->send_headers()->body();

		exit(1);
	}

	/**
	 * Exception handler, logs the exception and generates a Response object
	 * for display.
	 *
	 * @param   Exception  $e  Exception
	 * @return  Response
	 */
	public static function _handler(Exception $e)
	{
		try
		{
			// Log the exception
			self::log($e);

			// Generate the response
			$response = self::response($e);

			return $response;
		}
		catch (Exception $e)
		{
			/**
			 * Things are going *really* badly for us, We now have no choice
			 * but to bail. Hard.
			 */
			// Clean the output buffer if one exists
			ob_get_level() AND ob_clean();

			// Set the Status code to 500, and Content-Type to text/plain.
			header('Content-Type: text/plain; charset='.Kohana::$charset, TRUE, 500);

			echo self::text($e);

			exit(1);
		}
	}

	/**
	 * Logs an exception
	 *
	 * @param  Exception  $e      Exception
	 * @param  integer    $level  Level of message [Optional]
	 *
	 * @uses   Log::add
	 * @uses   Log::write
	 */
	public static function log(Exception $e, $level = Log::EMERGENCY)
	{
		// @todo
		if (is_object(Kohana::$log))
		{
			// Create a text version of the exception
			$error = self::text($e);

			switch($level)
			{
				case 0:
					$method = 'emergency';
				break;
				case 1:
					$method = 'alert';
				break;
				case 2:
					$method = 'critical';
				break;
				case 3:
					$method = 'error';
				break;
				case 4:
					$method = 'warning';
				break;
				case 5:
					$method = 'notice';
				break;
				case 6:
					$method = 'info';
				break;
				case 7:
					$method = 'debug';
				break;
				default:
					$method = 'debug';
			}

			// Add this exception to the log
			Log::$method($error, NULL, array('exception' => $e))->write();
		}
	}

	public static function response(Exception $e)
	{
		try
		{
			// Get the exception information
			$class   = get_class($e);
			$code    = $e->getCode();
			$message = $e->getMessage();
			$file    = $e->getFile();
			$line    = $e->getLine();
			$trace   = $e->getTrace();

			if ( ! headers_sent())
			{
				// Make sure the proper http header is sent
				$http_header_status = ($e instanceof HTTP_Exception) ? $code : 500;
			}

			/**
			 * HTTP_Exceptions are constructed in the HTTP_Exception::factory()
			 * method. We need to remove that entry from the trace and overwrite
			 * the variables from above.
			 */
			if ($e instanceof HTTP_Exception AND $trace[0]['function'] == 'factory')
			{
				extract(array_shift($trace));
			}

			if ($e instanceof ErrorException)
			{
				/**
				 * If XDebug is installed, and this is a fatal error,
				 * use XDebug to generate the stack trace
				 */
				if (function_exists('xdebug_get_function_stack') AND $code == E_ERROR)
				{
					$trace = array_slice(array_reverse(xdebug_get_function_stack()), 4);

					foreach ($trace as & $frame)
					{
						/**
						 * XDebug pre 2.1.1 doesn't currently set the call type key
						 * @link  http://bugs.xdebug.org/view.php?id=695
						 */
						if ( ! isset($frame['type']))
						{
							$frame['type'] = '??';
						}

						// XDebug also has a different name for the parameters array
						if (isset($frame['params']) AND ! isset($frame['args']))
						{
							$frame['args'] = $frame['params'];
						}
					}
				}

				if (isset(self::$php_errors[$code]))
				{
					// Use the human-readable error name
					$code = self::$php_errors[$code];
				}
			}

			/**
			 * The stack trace becomes unmanageable inside PHPUnit.
			 *
			 * The error view ends up several GB in size, taking
			 * several minutes to render.
			 */
			if (defined('PHPUnit_MAIN_METHOD'))
			{
				$trace = array_slice($trace, 0, 2);
			}

			// Instantiate the error view.
			$view = View::factory(self::$error_view, get_defined_vars());

			// Prepare the response object.
			$response = Response::factory();

			// Set the response status
			$response->status(($e instanceof HTTP_Exception) ? $e->getCode() : 500);

			// Set the response headers
			$response->headers('Content-Type', self::$error_view_content_type.'; charset='.Kohana::$charset);

			// Set the response body
			$response->body($view->render());
		}
		catch (Exception $e)
		{
			/**
			 * Things are going badly for us, Lets try to keep things under control by
			 * generating a simpler response object.
			 */
			$response = Response::factory();
			$response->status(500);
			$response->headers('Content-Type', 'text/plain');
			$response->body(self::text($e));
		}

		return $response;
	}

	/**
	 * Get a single line of text representing the exception:
	 *
	 * Error [ Code ]: Message ~ File [ Line ]
	 *
	 * @param   Exception  $e  Exception
	 * @return  string
	 */
	public static function text(Exception $e)
	{
		return sprintf('%s [ %s ]: %s ~ %s [ %d ]',
			get_class($e), $e->getCode(), strip_tags($e->getMessage()), Debug::path($e->getFile()), $e->getLine());
	}
}