<?php
/**
 * Acts as an object wrapper for HTML pages with embedded PHP, called "views"
 *
 * Variables can be assigned with the view object and referenced locally within
 * the view.
 *
 * @package    Gleez\Base
 * @author     Gleez Team
 * @version    1.1.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class View {

	/**
	 * Array of global variables
	 * @var array
	 */
	protected static $_global_data = array();

	/**
	 * View filename
	 * @var string
	 */
	protected $_file;

	/**
	 * Array of local variables
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Returns a new View object
	 *
	 * If you do not define the "file" parameter, you must call [View::set_filename].
	 *
	 * Example:
	 * ~~~
	 * $view = View::factory($file);
	 * ~~~
	 *
	 * @param   string  $file  View filename [Optional]
	 * @param   array   $data  Array of values [Optional]
	 *
	 * @return  View
	 */
	public static function factory($file = NULL, array $data = NULL)
	{
		return new View($file, $data);
	}

	/**
	 * Sets the initial view filename and local data
	 *
	 * Views should almost always only be created using [View::factory].
	 *
	 * Example:
	 * ~~~
	 * $view = new View($file);
	 * ~~~
	 *
	 * @param   string  $file  View filename [Optional]
	 * @param   array   $data  Array of values [Optional]
	 *
	 * @uses    View::set_filename
	 */
	public function __construct($file = NULL, array $data = NULL)
	{
		if ( ! is_null($file))
		{
			$this->set_filename($file);
		}

		if ( ! is_null($data))
		{
			// Add the values to the current data
			$this->_data = $data + $this->_data;
		}
	}

	/**
	 * Captures the output that is generated when a view is included
	 *
	 * The view data will be extracted to make local variables.
	 * This method is static to prevent object scope resolution.
	 *
	 * Example:
	 * ~~~
	 * $output = View::capture($file, $data);
	 * ~~~
	 *
	 * @param   string $view_filename  Filename
	 * @param   array $view_data       Variables
	 *
	 * @throws  Exception
	 *
	 * @return  string
	 */
	protected static function capture($view_filename, array $view_data)
	{
		// Import the view variables to local namespace
		extract($view_data, EXTR_SKIP);

		if (self::$_global_data)
		{
			// Import the global view variables to local namespace
			extract(self::$_global_data, EXTR_SKIP | EXTR_REFS);
		}

		// Capture the view output
		ob_start();

		try
		{
			// Load the view within the current scope
			include $view_filename;
		}
		catch (Exception $e)
		{
			// Delete the output buffer
			ob_end_clean();

			// Re-throw the exception
			throw $e;
		}

		// Get the captured output and close the buffer
		return ob_get_clean();
	}

	/**
	 * Sets a global variable, similar to [View::set], except that the
	 * variable will be accessible to all views.
	 *
	 * Example:
	 * ~~~
	 * View::set_global($name, $value);
	 * ~~~
	 *
	 * @param   string  $key    Variable name or an array of variables
	 * @param   mixed   $value  Value [Optional]
	 */
	public static function set_global($key, $value = NULL)
	{
		if (is_array($key))
		{
			foreach ($key as $key2 => $value)
			{
				self::$_global_data[$key2] = $value;
			}
		}
		else
		{
			self::$_global_data[$key] = $value;
		}
	}

	/**
	 * Assigns a global variable by reference, similar to [View::bind], except
	 * that the variable will be accessible to all views.
	 *
	 * Example:
	 * ~~~
	 * View::bind_global($key, $value);
	 * ~~~
	 *
	 * @param   string  $key    variable name
	 * @param   mixed   $value  referenced variable
	 */
	public static function bind_global($key, & $value)
	{
		self::$_global_data[$key] =& $value;
	}

	/**
	 * Magic method, searches for the given variable and returns its value
	 *
	 * Local variables will be returned before global variables.
	 *
	 * Example:
	 * ~~~
	 * $value = $view->foo;
	 * ~~~
	 *
	 * [!!] Note: If the variable has not yet been set, an exception will be thrown.
	 *
	 * @param   string  $key  Variable name
	 *
	 * @return  mixed
	 *
	 * @throws  Gleez_Exception
	 */
	public function & __get($key)
	{
		if (array_key_exists($key, $this->_data))
		{
			return $this->_data[$key];
		}
		elseif (array_key_exists($key, View::$_global_data))
		{
			return self::$_global_data[$key];
		}
		else
		{
			throw new Gleez_Exception('View variable is not set: :var',
				array(':var' => $key)
			);
		}
	}

	/**
	 * Magic method, calls [View::set] with the same parameters
	 *
	 * Example:
	 * ~~~
	 * $view->foo = 'something';
	 * ~~~
	 *
	 * @param  string  $key    Variable name
	 * @param  mixed   $value  Value
	 */
	public function __set($key, $value)
	{
		$this->set($key, $value);
	}

	/**
	 * Magic method, determines if a variable is set
	 *
	 * Example:
	 * ~~~
	 * isset($view->foo);
	 * ~~~
	 *
	 * [!!] Note: `NULL` variables are not considered to be set by [isset](http://php.net/isset).
	 *
	 * @param   string  $key  Variable name
	 * @return  boolean
	 */
	public function __isset($key)
	{
		return (isset($this->_data[$key]) OR isset(View::$_global_data[$key]));
	}

	/**
	 * Magic method, unsets a given variable
	 *
	 * Example:
	 * ~~~
	 * unset($view->foo);
	 * ~~~
	 *
	 * @param  string  $key  Variable name
	 */
	public function __unset($key)
	{
		unset($this->_data[$key], self::$_global_data[$key]);
	}

	/**
	 * Magic method, returns the output of [View::render]
	 *
	 * @return  string
	 *
	 * @uses    View::render
	 * @uses    Gleez_Exception::_handler
	 * @uses    Response::body
	 */
	public function __toString()
	{
		try
		{
			return $this->render();
		}
		catch (Exception $e)
		{
			/**
			 * Display the exception message
			 *
			 * We use this method here because it's impossible to throw and
			 * exception from __toString().
			 */
			$error_response = Gleez_Exception::_handler($e);

			return $error_response->body();
		}
	}

	/**
	 * Sets the view filename
	 *
	 * Example:
	 * ~~~
	 * $view->set_filename($file);
	 * ~~~
	 *
	 * @param   string  $file  View filename
	 *
	 * @return  View
	 *
	 * @throws  View_Exception
	 */
	public function set_filename($file)
	{
		// Search our view directories for the view
		// instead of just the 'views' directory.
		if (($path = Kohana::find_file('themes', $file)) === FALSE)
		{
			// Otherwise, revert to the old method
			if (($path = Kohana::find_file('views', $file)) === FALSE)
			{
				throw new View_Exception('The requested view :file could not be found', array(
					':file' => $file,
				));
			}
		}


		// Store the file path locally
		$this->_file = $path;

		return $this;
	}

	/**
	 * Assigns a variable by name
	 *
	 * Assigned values will be available as a variable within the view file:
	 * ~~~
	 * // This value can be accessed as $foo within the view
	 * $view->set('foo', 'my value');
	 * ~~~
	 *
	 * You can also use an array to set several values at once:
	 * ~~~
	 * // Create the values $food and $beverage in the view
	 * $view->set(array('food' => 'bread', 'beverage' => 'water'));
	 * ~~~
	 *
	 * @param   string  $key    Variable name or an array of variables
	 * @param   mixed   $value  Value [Optional]
	 *
	 * @return  $this
	 */
	public function set($key, $value = NULL)
	{
		if (is_array($key))
		{
			foreach ($key as $name => $value)
			{
				$this->_data[$name] = $value;
			}
		}
		else
		{
			$this->_data[$key] = $value;
		}

		return $this;
	}

	/**
	 * Assigns a value by reference
	 *
	 * The benefit of binding is that values can be altered without re-setting them.
	 * It is also possible to bind variables before they have values.
	 *
	 * Assigned values will be available as a variable within the view file:
	 * ~~~
	 * // This reference can be accessed as $ref within the view
	 * $view->bind('ref', $bar);
	 * ~~~
	 *
	 * @param   string  $key    Variable name
	 * @param   mixed   $value  Referenced variable
	 *
	 * @return  $this
	 */
	public function bind($key, & $value)
	{
		$this->_data[$key] = & $value;

		return $this;
	}

	/**
	 * Renders the view object to a string
	 *
	 * Global and local data are merged and extracted to create
	 * local variables within the view file.
	 *
	 * Example:
	 * ~~~
	 * $output = $view->render();
	 * ~~~
	 *
	 * [!!] Note: Global variables with the same key name as local variables will be
	 *      overwritten by the local variable.
	 *
	 * @param   string  $file  View filename [Optional]
	 *
	 * @return  string
	 *
	 * @throws  View_Exception
	 *
	 * @uses    View::capture
	 */
	public function render($file = NULL)
	{
		if ( ! is_null($file))
		{
			$this->set_filename($file);
		}

		if (empty($this->_file))
		{
			throw new View_Exception('You must set the file to use within your view before rendering');
		}

		// Combine local and global data and capture the output
		return self::capture($this->_file, $this->_data);
	}
}
