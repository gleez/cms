<?php
/**
 * Contains debugging and dumping tools
 *
 * @package    Gleez\Debug
 * @author     Gleez Team
 * @version    1.0.2
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Debug {

	/**
	 * Returns an HTML string of debugging information about any number of
	 * variables, each wrapped in a "pre" tag:
	 *
	 * Example:
	 * ~~~
	 * // Displays the type and value of each variable
	 * echo Debug::vars($foo, $bar, $baz);
	 * ~~~
	 *
	 * @return  string
	 */
	public static function vars()
	{
		if (func_num_args() === 0)
			return;

		// Get all passed variables
		$variables = func_get_args();

		$output = array();
		foreach ($variables as $var)
		{
			$output[] = Debug::_dump($var, 1024);
		}

		return '<pre class="debug">'.implode(PHP_EOL, $output).'</pre>';
	}

	/**
	 * Returns an HTML string of information about a single variable.
	 *
	 * Borrows heavily on concepts from the Debug class of [Nette](http://nettephp.com/).
	 *
	 * @param   mixed   $value            Variable to dump
	 * @param   integer $length           Maximum length of strings [Optional]
	 * @param   integer $level_recursion  Recursion limit [Optional]
	 *
	 * @return  string
	 */
	public static function dump($value, $length = 128, $level_recursion = 10)
	{
		return Debug::_dump($value, $length, $level_recursion);
	}

	/**
	 * Helper for Debug::dump(), handles recursion in arrays and objects.
	 *
	 * @param   mixed    $var     Variable to dump
	 * @param   integer  $length  Maximum length of strings [Optional]
	 * @param   integer  $limit   Recursion limit [Optional]
	 * @param   integer  $level   Current recursion level (internal usage only!) [Optional]
	 *
	 * @return  string
	 */
	protected static function _dump( & $var, $length = 128, $limit = 10, $level = 0)
	{
		if ($var === NULL)
		{
			return '<small style="color: #3465a4">NULL</small>';
		}
		elseif (is_bool($var))
		{
			return '<small>bool</small> <span style="color:#4e9a06">'.($var ? 'TRUE' : 'FALSE').'</span>';
		}
		elseif (is_float($var))
		{
			return '<small>float</small> <span style="color:#4e9a06">'.$var.'</span>';
		}
		elseif (is_integer($var))
		{
			return '<small>int</small> <span style="color:#4e9a06">'.$var.'</span>';
		}
		elseif (is_resource($var))
		{
			if (($type = get_resource_type($var)) === 'stream' AND $meta = stream_get_meta_data($var))
			{
				$meta = stream_get_meta_data($var);

				if (isset($meta['uri']))
				{
					$file = $meta['uri'];

					if (stream_is_local($file))
					{
						$file = Debug::path($file);
					}

					return '<small>resource</small><span>('.$type.')</span> '.htmlspecialchars($file, ENT_NOQUOTES, Kohana::$charset);
				}
			}
			else
			{
				return '<small>resource</small><span>('.$type.')</span>';
			}
		}
		elseif (is_string($var))
		{
			// Clean invalid multibyte characters. iconv is only invoked
			// if there are non ASCII characters in the string, so this
			// isn't too much of a hit.
			$var = UTF8::clean($var, Kohana::$charset);

			if (UTF8::strlen($var) > $length)
			{
				// Encode the truncated string
				$str = htmlspecialchars(UTF8::substr($var, 0, $length), ENT_NOQUOTES, Kohana::$charset).'&nbsp;&hellip;';
			}
			else
			{
				// Encode the string
				$str = htmlspecialchars($var, ENT_NOQUOTES, Kohana::$charset);
			}

			return '<small>string</small> <span style="color:#cc0000">\''.$str.'\'</span>(<span style="font-style:italic">length='.strlen($var).'</span>)';
		}
		elseif (is_array($var))
		{
			$output = array();

			// Indentation for this variable
			$space = str_repeat($s = '    ', $level);

			static $marker;

			if ($marker === NULL)
			{
				// Make a unique marker
				$marker = uniqid("\x00");
			}

			if (empty($var))
			{
				// Do nothing
			}
			elseif (isset($var[$marker]))
			{
				$output[] = "\n$space$s*RECURSION*\n$space";
			}
			elseif ($level < $limit)
			{
				$output[] = "<span>";

				$var[$marker] = TRUE;
				foreach ($var as $key => & $val)
				{
					if ($key === $marker) continue;
					if ( ! is_int($key))
					{
						$key = '"'.htmlspecialchars($key, ENT_NOQUOTES, Kohana::$charset).'"';
					}

					$output[] = "$space$s$key => ".Debug::_dump($val, $length, $limit, $level + 1);
				}
				unset($var[$marker]);

				$output[] = "$space</span>";
			}
			else
			{
				// Depth too great
				$output[] = "\n$space$s...\n$space";
			}

			return '<strong>array</strong> <span style="font-style:italic">(size='.count($var).')</span> '.implode(PHP_EOL, $output);
		}
		elseif (is_object($var))
		{
			// Copy the object as an array
			$array = (array) $var;

			$output = array();

			// Indentation for this variable
			$space = str_repeat($s = '    ', $level);

			$hash = spl_object_hash($var);

			// Objects that are being dumped
			static $objects = array();

			if (empty($var))
			{
				// Do nothing
			}
			elseif (isset($objects[$hash]))
			{
				$output[] = "{\n$space$s*RECURSION*\n$space}";
			}
			elseif ($level < $limit)
			{
				$output[] = "<code>";

				$objects[$hash] = TRUE;
				foreach ($array as $key => & $val)
				{
					if ($key[0] === "\x00")
					{
						// Determine if the access is protected or protected
						$access = '<span style="font-style:italic">'.(($key[1] === '*') ? 'protected' : 'private').'</span>';

						// Remove the access level from the variable name
						$key = substr($key, strrpos($key, "\x00") + 1);
					}
					else
					{
						$access = '<span style="font-style:italic">public</span>';
					}

					$output[] = "$space$s$access '$key' <span style='color:#888a85'>=&gt;</span> ".Debug::_dump($val, $length, $limit, $level + 1);
				}
				unset($objects[$hash]);

				$output[] = "$space</code>";
			}
			else
			{
				// Depth too great
				$output[] = "{\n$space$s...\n$space}";
			}

			return '<strong>object</strong>(<span style="font-style:italic">'.get_class($var).'</span>)'.'[<span style="font-style:italic">'.count($array).'</span>]'.implode(PHP_EOL, $output);
		}
		else
		{
			return '<small>'.gettype($var).'</small> '.htmlspecialchars(print_r($var, TRUE), ENT_NOQUOTES, Kohana::$charset);
		}
	}

	/**
	 * Removes application, system, modpath, theme path or docroot
	 * from a filename, replacing them with the plain text equivalents.
	 * Useful for debugging when you want to display a shorter path.
	 *
	 * Examples:
	 * ~~~
	 * // Displays SYSPATH/classes/kohana.php
	 * echo Debug::path(Kohana::find_file('classes', 'kohana'));
	 * ~~~
	 *
	 * @param   string  $file  Path to debug
	 *
	 * @return  string
	 */
	public static function path($file)
	{
		if (strpos($file, APPPATH) === 0)
		{
			$file = 'APPPATH'.DS.substr($file, strlen(APPPATH));
		}
		elseif (strpos($file, SYSPATH) === 0)
		{
			$file = 'SYSPATH'.DS.substr($file, strlen(SYSPATH));
		}
		elseif (strpos($file, MODPATH) === 0)
		{
			$file = 'MODPATH'.DS.substr($file, strlen(MODPATH));
		}
		elseif (strpos($file, THEMEPATH) === 0)
		{
			$file = 'THEMEPATH'.DS.substr($file, strlen(THEMEPATH));
		}
		elseif (strpos($file, DOCROOT) === 0)
		{
			$file = 'DOCROOT'.DS.substr($file, strlen(DOCROOT));
		}

		return $file;
	}

	/**
	 * Returns an HTML string, highlighting a specific line of a file, with some
	 * number of lines padded above and below.
	 *
	 *     // Highlights the current line of the current file
	 *     echo Debug::source(__FILE__, __LINE__);
	 *
	 * @param   string   $file         File to open
	 * @param   integer  $line_number  Line number to highlight
	 * @param   integer  $padding      Number of padding lines [Optional]
	 *
	 * @return  string   source of file
	 * @return  boolean  FALSE    file is unreadable
	 */
	public static function source($file, $line_number, $padding = 5)
	{
		if ( ! $file OR ! is_readable($file))
		{
			// Continuing will cause errors
			return FALSE;
		}

		// Open the file and set the line position
		$file = fopen($file, 'r');
		$line = 0;

		// Set the reading range
		$range = array('start' => $line_number - $padding, 'end' => $line_number + $padding);

		// Set the zero-padding amount for line numbers
		$format = '% '.strlen($range['end']).'d';

		$source = '';
		while (($row = fgets($file)) !== FALSE)
		{
			// Increment the line number
			if (++$line > $range['end'])
				break;

			if ($line >= $range['start'])
			{
				// Make the row safe for output
				$row = htmlspecialchars($row, ENT_NOQUOTES, Kohana::$charset);

				// Trim whitespace and sanitize the row
				$row = '<span class="number">'.sprintf($format, $line).'</span> '.$row;

				if ($line === $line_number)
				{
					// Apply highlighting to this row
					$row = '<span class="line highlight">'.$row.'</span>';
				}
				else
				{
					$row = '<span class="line">'.$row.'</span>';
				}

				// Add to the captured source
				$source .= $row;
			}
		}

		// Close the file
		fclose($file);

		return '<pre class="source"><code>'.$source.'</code></pre>';
	}

	/**
	 * Returns an array of HTML strings that represent each step in the backtrace.
	 *
	 * Example:
	 * ~~~
	 * // Displays the entire current backtrace
	 * echo implode('<br/>', Debug::trace());
	 * ~~~
	 *
	 * @param   array  $trace [Optional]
	 *
	 * @return  string
	 */
	public static function trace(array $trace = NULL)
	{
		if ($trace === NULL)
		{
			// Start a new trace
			$trace = debug_backtrace();
		}

		// Non-standard function calls
		$statements = array('include', 'include_once', 'require', 'require_once');

		$output = array();
		foreach ($trace as $step)
		{
			if ( ! isset($step['function']))
			{
				// Invalid trace step
				continue;
			}

			if (isset($step['file']) AND isset($step['line']))
			{
				// Include the source of this step
				$source = Debug::source($step['file'], $step['line']);
			}

			if (isset($step['file']))
			{
				$file = $step['file'];

				if (isset($step['line']))
				{
					$line = $step['line'];
				}
			}

			// function()
			$function = $step['function'];

			if (in_array($step['function'], $statements))
			{
				if (empty($step['args']))
				{
					// No arguments
					$args = array();
				}
				else
				{
					// Sanitize the file path
					$args = array($step['args'][0]);
				}
			}
			elseif (isset($step['args']))
			{
				if ( ! function_exists($step['function']) OR strpos($step['function'], '{closure}') !== FALSE)
				{
					// Introspection on closures or language constructs in a stack trace is impossible
					$params = NULL;
				}
				else
				{
					if (isset($step['class']))
					{
						if (method_exists($step['class'], $step['function']))
						{
							$reflection = new ReflectionMethod($step['class'], $step['function']);
						}
						else
						{
							$reflection = new ReflectionMethod($step['class'], '__call');
						}
					}
					else
					{
						$reflection = new ReflectionFunction($step['function']);
					}

					// Get the function parameters
					$params = $reflection->getParameters();
				}

				$args = array();

				foreach ($step['args'] as $i => $arg)
				{
					if (isset($params[$i]))
					{
						// Assign the argument by the parameter name
						$args[$params[$i]->name] = $arg;
					}
					else
					{
						// Assign the argument by number
						$args[$i] = $arg;
					}
				}
			}

			if (isset($step['class']))
			{
				// Class->method() or Class::method()
				$function = $step['class'].$step['type'].$step['function'];
			}

			$output[] = array(
				'function' => $function,
				'args'     => isset($args)   ? $args : NULL,
				'file'     => isset($file)   ? $file : NULL,
				'line'     => isset($line)   ? $line : NULL,
				'source'   => isset($source) ? $source : NULL,
			);

			unset($function, $args, $file, $line, $source);
		}

		return $output;
	}

}
