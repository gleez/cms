<?php
/**
 * Gleez Core Utils class
 *
 * @package    Gleez\Core
 * @author     Gleez Team
 * @version    1.6.1
 * @copyright  (c) 2011-2015 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class System {

	/**
	 * Windows OS
	 * @type string
	 */
	const WIN = 'WINDOWS';

	/**
	 * Linux OS
	 * @type string
	 */
	const LIN = 'LINUX';

	/**
	 * Minimum amount of memory allocated to php-script.
	 * Can be used if ini_get('memory_limit') returns 0, -1, NULL or FALSE.
	 * This amount is used by default since PHP 5.3
	 * @type integer
	 */
	const MIN_MEMORY_LIMIT = 16777216;

	/**
	 * Get the server load averages (if possible)
	 *
	 * @return  string
	 * @link    http://php.net/manual/en/function.sys-getloadavg.php sys-getloadavg()
	 */
	public static function get_avg()
	{
		// Default return
		$not_available = __('Not available');

		if (function_exists('sys_getloadavg') && is_array(sys_getloadavg()))
		{
			$load_averages = sys_getloadavg();
			array_walk($load_averages, create_function('&$v', '$v = round($v, 3);'));
			$server_load = $load_averages[0] . ' ' . $load_averages[1] . ' ' . $load_averages[2];
		}
		elseif (@is_readable('/proc/loadavg'))
		{
			// We use @ just in case
			$fh            = @fopen('/proc/loadavg', 'r');
			$load_averages = @fread($fh, 64);
			@fclose($fh);

			$load_averages = empty($load_averages) ? array() : explode(' ', $load_averages);

			$server_load = isset($load_averages[2]) ? $load_averages[0] . ' ' . $load_averages[1] . ' ' . $load_averages[2] : $not_available;
		}
		elseif (!in_array(PHP_OS, array(
			'WINNT',
			'WIN32'
		)) && preg_match('/averages?: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/i', @exec('uptime'), $load_averages))
		{
			$server_load = $load_averages[1] . ' ' . $load_averages[2] . ' ' . $load_averages[3];
		}
		else
			$server_load = $not_available;

		return $server_load;
	}

	/**
	 * Attempts to create the directory specified by `$path`
	 *
	 * To create the nested structure, the `$recursive` parameter
	 * to mkdir() must be specified.
	 *
	 * @param   string  $path       The directory path
	 * @param   integer $mode       Set permission mode (as in chmod) [Optional]
	 * @param   boolean $recursive  Create directories recursively if necessary [Optional]
	 * @return  boolean             Returns TRUE on success or FALSE on failure
	 *
	 * @link    http://php.net/manual/en/function.mkdir.php mkdir()
	 */
	public static function mkdir($path, $mode = 0777, $recursive = TRUE)
	{
		$out = FALSE;
		$oldumask = umask(0);
		if (! is_dir($path))
		{
			$out = @mkdir($path, $mode, $recursive);
		}
		umask($oldumask);

		return $out;
	}

	/**
	 * Dynamically generate icons list from font-awesome.css file and cached for performance
	 *
	 * @link   http://fontawesome.io/
	 * @return array
	 */
	public static function icons()
	{
		$icons = array();
		if( !$icons = Cache::instance('icons')->get('fa-icons'))
		{
			if ($path = Kohana::find_file('media/css', 'font-awesome', 'css'))
			{
				$array = System::faGetArray($path);
				$icons = System::faReadableName($array);
			}

			// Sort array by key name
			ksort( $icons );

			// set the cache for performance in production
			if (Kohana::$environment === Kohana::PRODUCTION)
			{
				Cache::instance('icons')->set('fa-icons', $icons, Date::WEEK);
			}
		}

		$icons = array("fa-none" => __('none')) + $icons;
		return $icons;
	}

	/**
	 * Get current server OS
	 *
	 * @return  string
	 * @todo    add more OS
	 */
	public static function os()
	{
		if (Kohana::$is_windows)
		{
			return System::WIN;
		}
		return System::LIN;
	}

	/**
	 * Merge user defined arguments into defaults array
	 *
	 * This function is used throughout Gleez to allow for both string
	 * or array to be merged into another array.
	 *
	 * @since  1.1.0
	 *
	 * @param   string|array  $args      Value to merge with `$defaults`
	 * @param   array         $defaults  Array that serves as the defaults [Optional]
	 * @return  array                    Merged user defined values with defaults
	 */
	public static function parse_args($args, array $defaults = array())
	{
		if (is_object($args))
		{
			$result = get_object_vars($args);
		}
		elseif (is_array($args))
		{
			$result = &$args;
		}
		else
		{
			parse_str($args, $result);
		}

		if ( ! empty($defaults))
		{
			return Arr::merge($defaults, $result);
		}

		return $result;
	}

	/**
	 * Sanitize id
	 *
	 * Replaces troublesome characters with underscores
	 *
	 * ~~~
	 * 	$id = System::sanitize_id($id);
	 * ~~~
	 *
	 * @since   1.2.0
	 *
	 * @param   string  $id  ID to sanitize
	 *
	 * @return  string
	 */
	public static function sanitize_id($id)
	{
		// Change slashes and spaces to underscores
		return str_replace(array(
			'/',
			'\\',
			' '
		), '_', $id);
	}

	public static function check()
	{
		$criteria = array(
			'php_version'           => version_compare(PHP_VERSION, Gleez::PHP_MIN_REQ, '>='),
			'mysqli'                => function_exists("mysqli_query"),
			'system_directory'      => is_dir(SYSPATH),
			'application_directory' => (is_dir(APPPATH) && is_file(APPPATH.'bootstrap'.EXT)),
			'modules_directory'     => is_dir(MODPATH),
			'config_writable'       => (is_dir(APPPATH.'config') && is_writable(APPPATH.'config')),
			'cache_writable'        => (is_dir(APPPATH.'cache') && is_writable(APPPATH.'cache')),
			'pcre_utf8'             => ( @preg_match('/^.$/u', 'ñ') ),
			'pcre_unicode'          => ( @preg_match('/^\pL$/u', 'ñ') ),
			'reflection_enabled'    => class_exists('ReflectionClass'),
			'spl_autoload_register' => function_exists('spl_autoload_register'),
			'filters_enabled'       => function_exists('filter_list'),
			'iconv_loaded'          => extension_loaded('iconv'),
			'simplexml'             => extension_loaded('simplexml'),
			'json_encode'           => function_exists('json_encode'),
			'mbstring'              => (extension_loaded('mbstring') && MB_OVERLOAD_STRING),
			'ctype_digit'           => function_exists('ctype_digit'),
			'uri_determination'     => isset($_SERVER['REQUEST_URI']) || isset($_SERVER['PHP_SELF']) || isset($_SERVER['PATH_INFO']),
			'gd_info'               => function_exists('gd_info'),
		);

		// Allow other modules to override or add
		$criteria = Module::action('system_check', $criteria);

		return $criteria;
	}

	/**
	 * Get PHP memory_limit
	 *
	 * It can be used to obtain a human-readable form
	 * of a PHP memory_limit.
	 *
	 * [!!] Note: If ini_get('memory_limit') returns 0, -1, NULL or FALSE
	 *      returns [System::MIN_MEMORY_LIMIT]
	 *
	 * @since   1.4.0
	 *
	 * @return  int|string
	 *
	 * @uses    Num::bytes
	 * @uses    Text::bytes
	 */
	public static function get_memory_limit()
	{
		$memory_limit = Num::bytes(ini_get('memory_limit'));

		return Text::bytes((int)$memory_limit <= 0 ? self::MIN_MEMORY_LIMIT : $memory_limit, 'MiB');
	}

	/**
	 * Get PHP version
	 *
	 * @since   1.6.0
	 *
	 * @param  boolean $idOnly Return PHP version as an integer? [Optional]
	 * @return string
	 */
	public static function getPhpVersion($idOnly = false)
	{
		return $idOnly ? PHP_VERSION_ID : PHP_VERSION;
	}

	/**
	 * Compare two hashes in a time-invariant manner
	 *
	 * Prevents cryptographic side-channel attacks (timing attacks, specifically).
	 *
	 * @since  1.4.0  Introduced
	 *
	 * @param  string $a cryptographic hash
	 * @param  string $b cryptographic hash
	 *
	 * @return bool
	 */
	public static function equalsHashes($a, $b)
	{
		$diff = strlen($a) ^ strlen($b);

		for($i = 0; $i < strlen($a) && $i < strlen($b); $i++) {
			$diff |= ord($a[$i]) ^ ord($b[$i]);
		}

		return $diff === 0;
	}

	/**
	* Font Awesome icons as array
	*
	* @param    string   $path font awesome css file path
	* @param    string   $class_prefix change this if the class names does not start with `fa-`
	* @return   array
	* @link     https://github.com/Smartik89/SMK-Font-Awesome-PHP-JSON
	* @license  MIT
	*/
	public static function faGetArray($path, $class_prefix = 'fa-')
	{
		if( ! file_exists($path) ) 
		{
			return false;//if path is incorect or file does not exist, stop.
		}

		$css = file_get_contents($path);
		$pattern = '/\.('. $class_prefix .'(?:\w+(?:-)?)+):before\s+{\s*content:\s*"(.+)";\s+}/';
		preg_match_all($pattern, $css, $matches, PREG_SET_ORDER);

		$icons = array();
		foreach ($matches as $match) {
			$icons[$match[1]] = $match[2];
		}

		return $icons;
	}

	/**
	* Readable class name for fontawesome. Ex: fa-video-camera => Video Camera
	*
	* @param    array    $array font awesome array. Create it using `getArray` method
	* @param    string   $class_prefix change this if the class names does not start with `fa-`
	* @return   array
	* @link     https://github.com/Smartik89/SMK-Font-Awesome-PHP-JSON
	* @license  MIT
	*/
	public static function faReadableName($array, $class_prefix = 'fa-')
	{
		if( ! is_array($array) )
		{
			return false;//Do not proceed if is not array
		}

		$temp = array();
		foreach ($array as $class => $unicode) {
			$temp[$class] = ucfirst( str_ireplace(array($class_prefix, '-'), array('', ' '), $class) );
		}

		return $temp;
	}
}
