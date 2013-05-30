<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Gleez Core Utils class
 *
 * @package    Gleez\Core
 * @author     Sergey Yakovlev - Gleez
 * @author     Sandeep Sangamreddi - Gleez
 * @version    1.1.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Gleez_System {

	/** Windows OS */
	const WIN = 'WINDOWS';

	/** Linux OS */
	const LIN = 'LINUX';

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
	 * Gets prepared icons array
	 *
	 * @return array
	 */
	public static function icons()
	{
		$icons = array(
			"icon-cloud-download" => 'cloud-download',
			"icon-cloud-upload" => 'cloud-upload',
			"icon-lightbulb" => 'lightbulb',
			"icon-exchange" => 'exchange',
			"icon-bell-alt" => 'bell-alt',
			"icon-file-alt" => 'file-alt',
			"icon-beer" => 'beer',
			"icon-coffee" => 'coffee',
			"icon-food" => 'food',
			"icon-fighter-jet" => 'fighter-jet',
			
			"icon-user-md" => 'user-md',
			"icon-stethoscope" => 'stethoscope',
			"icon-suitcase" => 'suitcase',
			"icon-building" => 'building',
			"icon-hospital" => 'hospital',
			"icon-ambulance" => 'ambulance',
			"icon-medkit" => 'medkit',
			"icon-h-sign" => 'h-sign',
			"icon-plus-sign-alt" => 'plus-sign-alt',
			"icon-spinner" => 'spinner',
			
			"icon-angle-left" => 'angle-left',
			"icon-angle-right" => 'angle-right',
			"icon-angle-up" => 'angle-up',
			"icon-angle-down" => 'angle-down',
			"icon-double-angle-left" => 'double-angle-left',
			"icon-double-angle-right" => 'double-angle-right',
			"icon-double-angle-up" => 'double-angle-up',
			"icon-double-angle-down" => 'double-angle-down',
			"icon-circle-blank" => 'circle-blank',
			"icon-circle" => 'circle',
			
			"icon-desktop" => 'desktop',
			"icon-laptop" => 'laptop',
			"icon-tablet" => 'tablet',
			"icon-mobile-phone" => 'mobile-phone',
			"icon-quote-left" => 'quote-left',
			"icon-quote-right" => 'quote-right',
			"icon-reply" => 'reply',
			"icon-github-alt" => 'github-alt',
			"icon-folder-close-alt" => 'folder-close-alt',
			"icon-folder-open-alt" => 'folder-open-alt',
			
			"icon-adjust" => 'adjust',
			"icon-asterisk" => 'asterisk',
			"icon-ban-circle" => 'ban-circle',
			"icon-bar-chart" => 'bar-chart',
			"icon-barcode" => 'barcode',
			"icon-beaker" => 'beaker',
			"icon-bell" => 'bell',
			"icon-bolt" => 'bolt',
			"icon-book" => 'book',
			"icon-bookmark" => 'bookmark',
			"icon-bookmark-empty" => 'bookmark-empty',
			"icon-briefcase" => 'briefcase',
			"icon-bullhorn" => 'bullhorn',
			"icon-calendar" => 'calendar',
			"icon-camera" => 'camera',
			"icon-camera-retro" => 'camera-retro',
			"icon-certificate" => 'certificate',
			"icon-check" => 'check',
			"icon-check-empty" => 'check-empty',
			"icon-cloud" => 'cloud',
			"icon-cog" => 'cog',
			"icon-cogs" => 'cogs',
			"icon-comment" => 'comment',
			"icon-comment-alt" => 'comment-alt',
			"icon-comments" => 'comments',
			"icon-comments-alt" => 'comments-alt',
			"icon-credit-card" => 'credit-card',
			"icon-dashboard" => 'dashboard',
			"icon-download" => 'download',
			"icon-download-alt" => 'download-alt',
			
			"icon-edit" => 'edit',
			"icon-envelope" => 'envelope',
			"icon-envelope-alt" => 'envelope-alt',
			"icon-exclamation-sign" => 'exclamation-sign',
			"icon-external-link" => 'external-link',
			"icon-eye-close" => 'eye-close',
			"icon-eye-open" => 'eye-open',
			"icon-facetime-video" => 'facetime-video',
			"icon-film" => 'film',
			"icon-filter" => 'filter',
			"icon-fire" => 'fire',
			"icon-flag" => 'flag',
			"icon-folder-close" => 'folder-close',
			"icon-folder-open" => 'folder-open',
			"icon-gift" => 'gift',
			"icon-glass" => 'glass',
			"icon-globe" => 'globe',
			"icon-group" => 'group',
			"icon-hdd" => 'hdd',
			"icon-headphones" => 'headphones',
			"icon-heart" => 'heart',
			"icon-heart-empty" => 'heart-empty',
			"icon-home" => 'home',
			"icon-inbox" => 'inbox',
			"icon-info-sign" => 'info-sign',
			"icon-key" => 'key',
			"icon-leaf" => 'leaf',
			"icon-legal" => 'legal',
			"icon-lemon" => 'lemon',
			"icon-lock" => 'lock',
			"icon-unlock" => 'unlock',
			
			"icon-magic" => 'magic',
			"icon-magnet" => 'magnet',
			"icon-map-marker" => 'map-marker',
			"icon-minus" => 'minus',
			"icon-minus-sign" => 'minus-sign',
			"icon-money" => 'money',
			"icon-move" => 'move',
			"icon-music" => 'music',
			"icon-off" => 'off',
			"icon-ok" => 'ok',
			"icon-ok-circle" => 'ok-circle',
			"icon-ok-sign" => 'ok-sign',
			"icon-pencil" => 'pencil',
			"icon-picture" => 'picture',
			"icon-plane" => 'plane',
			"icon-plus" => 'plus',
			"icon-plus-sign" => 'plus-sign',
			"icon-print" => 'print',
			"icon-pushpin" => 'pushpin',
			"icon-qrcode" => 'qrcode',
			"icon-question-sign" => 'question-sign',
			"icon-random" => 'random',
			"icon-refresh" => 'refresh',
			"icon-remove" => 'remove',
			"icon-remove-circle" => 'remove-circle',
			"icon-remove-sign" => 'remove-sign',
			"icon-reorder" => 'reorder',
			"icon-resize-horizontal" => 'resize-horizontal',
			"icon-resize-vertical" => 'resize-vertical',
			"icon-retweet" => 'retweet',
			"icon-road" => 'road',
			"icon-rss" => 'rss',
			"icon-screenshot" => 'screenshot',
			"icon-search" => 'search',
			
			"icon-share" => 'share',
			"icon-share-alt" => 'share-alt',
			"icon-shopping-cart" => 'shopping-cart',
			"icon-signal" => 'signal',
			"icon-signin" => 'signin',
			"icon-signout" => 'signout',
			"icon-sitemap" => 'sitemap',
			"icon-sort" => 'sort',
			"icon-sort-down" => 'sort-down',
			"icon-sort-up" => 'sort-up',
			"icon-star" => 'star',
			"icon-star-empty" => 'star-empty',
			"icon-star-half" => 'star-half',
			"icon-tag" => 'tag',
			"icon-tags" => 'tags',
			"icon-tasks" => 'tasks',
			"icon-thumbs-down" => 'thumbs-down',
			"icon-thumbs-up" => 'thumbs-up',
			"icon-time" => 'time',
			"icon-tint" => 'tint',
			"icon-trash" => 'trash',
			"icon-trophy" => 'trophy',
			"icon-truck" => 'truck',
			"icon-umbrella" => 'umbrella',
			"icon-upload" => 'upload',
			"icon-upload-alt" => 'upload-alt',
			"icon-user" => 'user',
			"icon-volume-off" => 'volume-off',
			"icon-volume-down" => 'volume-down',
			"icon-volume-up" => 'volume-up',
			"icon-warning-sign" => 'warning-sign',
			"icon-wrench" => 'wrench',
			"icon-zoom-in" => 'zoom-in',
			"icon-zoom-out" => 'zoom-out',
			
			"icon-file" => 'file',
			"icon-cut" => 'cut',
			"icon-copy" => 'copy',
			"icon-paste" => 'paste',
			"icon-save" => 'save',
			"icon-undo" => 'undo',
			"icon-repeat" => 'repeat',
			
			"icon-text-height" => 'text-height',
			"icon-text-width" => 'text-width',
			"icon-align-left" => 'align-left',
			"icon-align-center" => 'align-center',
			"icon-align-right" => 'align-right',
			"icon-align-justify" => 'align-justify',
			"icon-indent-left" => 'indent-left',
			"icon-indent-right" => 'indent-right',
			
			"icon-font" => 'font',
			"icon-bold" => 'bold',
			"icon-italic" => 'italic',
			"icon-strikethrough" => 'strikethrough',
			"icon-underline" => 'underline',
			"icon-link" => 'link',
			"icon-paper-clip" => 'paper-clip',
			"icon-columns" => 'columns',
			
			"icon-table" => 'table',
			"icon-th-large" => 'th-large',
			"icon-th" => 'th',
			"icon-th-list" => 'th-list',
			"icon-list" => 'list',
			"icon-list-ol" => 'list-ol',
			"icon-list-ul" => 'list-ul',
			"icon-list-alt" => 'list-alt',

			"icon-arrow-down" => 'arrow-down',
			"icon-arrow-left" => 'arrow-left',
			"icon-arrow-right" => 'arrow-right',
			"icon-arrow-up" => 'arrow-up',
			
			"icon-caret-down" => 'caret-down',
			"icon-caret-left" => 'caret-left',
			"icon-caret-right" => 'caret-right',
			"icon-caret-up" => 'caret-up',
			"icon-chevron-down" => 'chevron-down',
			"icon-chevron-left" => 'chevron-left',
			"icon-chevron-right" => 'chevron-right',
			"icon-chevron-up" => 'chevron-up',
			
			"icon-circle-arrow-down" => 'circle-arrow-down',
			"icon-circle-arrow-left" => 'circle-arrow-left',
			"icon-circle-arrow-right" => 'circle-arrow-right',
			"icon-circle-arrow-up" => 'circle-arrow-up',
			
			"icon-hand-down" => 'hand-down',
			"icon-hand-left" => 'hand-left',
			"icon-hand-right" => 'hand-right',
			"icon-hand-up" => 'hand-up',
			
			"icon-play-circle" => 'play-circle',
			"icon-play" => 'play',
			"icon-pause" => 'pause',
			"icon-stop" => 'stop',
			
			"icon-step-backward" => 'step-backward',
			"icon-fast-backward" => 'fast-backward',
			"icon-backward" => 'backward',
			"icon-forward" => 'forward',
			
			"icon-fast-forward" => 'fast-forward',
			"icon-step-forward" => 'step-forward',
			"icon-eject" => 'eject',
			
			"icon-fullscreen" => 'fullscreen',
			"icon-resize-full" => 'resize-full',
			"icon-resize-small" => 'resize-small',
			
			"icon-phone" => 'phone',
			"icon-phone-sign" => 'phone-sign',
			"icon-facebook" => 'facebook',
			"icon-facebook-sign" => 'facebook-sign',
			
			"icon-twitter" => 'twitter',
			"icon-twitter-sign" => 'twitter-sign',
			"icon-github" => 'github',
			
			"icon-github-sign" => 'github-sign',
			"icon-linkedin" => 'linkedin',
			"icon-linkedin-sign" => 'linkedin-sign',
			"icon-pinterest" => 'pinterest',
			
			"icon-pinterest-sign" => 'pinterest-sign',
			"icon-google-plus" => 'google-plus',
			"icon-google-plus-sign" => 'google-plus-sign',
			"icon-sign-blank" => 'sign-blank',
			
			"icon-expand-alt" => "expand-alt",
			"icon-collapse-alt" => "collapse-alt",
			"icon-smile" => "smile",
			"icon-frown" => "frown",
			"icon-meh" => "meh",
			"icon-gamepad" => "gamepad",
			"icon-keyboard" => "keyboard",
			"icon-flag-alt" => "flag-alt",
			"icon-flag-checkered" => "flag-checkered",
			"icon-terminal" => "terminal",
			"icon-code" => "code",
			"icon-mail-forward " => "mail-forward ",
			"icon-mail-reply " => "mail-reply",
			
			"icon-reply-all" => "reply-all",
			"icon-mail-reply-all " => "mail-reply-all",
			"icon-star-half-empty" => "star-half-empty",
			"icon-star-half-full " => "star-half-full",
			"icon-location-arrow" => "location-arrow",
			"icon-rotate-left " => "rotate-left",
			"icon-rotate-right " => "rotate-right ",
			"icon-crop" => "crop",
			"icon-code-fork" => "code-fork",
			"icon-unlink" => "unlink",
			"icon-question" => "question",
			"icon-info" => "info",
			"icon-exclamation" => "exclamation",
			"icon-superscript" => "superscript",
			"icon-subscript" => "subscript",
			"icon-eraser" => "eraser",
			"icon-puzzle-piece" => "puzzle-piece",
			
			"icon-microphone" => "microphone",
			"icon-microphone-off" => "microphone-off",
			"icon-shield" => "shield",
			"icon-calendar-empty" => "calendar-empty",
			"icon-fire-extinguisher" => "fire-extinguisher",
			"icon-rocket" => "rocket",
			"icon-maxcdn" => "maxcdn",
			"icon-chevron-sign-left" => "chevron-sign-left",
			"icon-chevron-sign-right" => "chevron-sign-right",
			"icon-chevron-sign-up" => "chevron-sign-up",
			"icon-chevron-sign-down" => "chevron-sign-down",
			"icon-html5" => "html5",
			"icon-css3" => "css3",
			"icon-anchor" => "anchor",
			"icon-unlock-alt" => "unlock-alt",
			
			"icon-bullseye" => "bullseye",
			"icon-ellipsis-horizontal" => "ellipsis-horizontal",
			"icon-ellipsis-vertical" => "ellipsis-vertical",
			"icon-rss-sign" => "rss-sign",
			"icon-play-sign" => "play-sign",
			"icon-ticket" => "ticket",
			"icon-minus-sign-alt" => "minus-sign-alt",
			"icon-check-minus" => "check-minus",
			"icon-level-up" => "level-up",
			"icon-level-down" => "level-down",
			"icon-check-sign" => "check-sign",
			"icon-edit-sign" => "edit-sign",
			"icon-external-link-sign" => "external-link-sign",
			"icon-share-sign" => "share-sign",
		);
		
		//sort icons by natural order
		natsort($icons);
		
		$icons = array("icon-none" => __('none')) + $icons;

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
	 * Checking the system for compatibility
	 *
	 * @param   mixed  $param Checked argument
	 * @return  boolean
	 */
	public static function check($param)
	{
		$status = TRUE;

		if (is_array($param))
		{
			foreach ($param as $req)
			{
				if ( ! $status = self::_do_check($req))
				{
					return $status;
				}
			}
		}

		if (is_string($param))
		{
			$status = self::_do_check($param);
		}

		if (is_bool($param))
		{
			$status = $param;
		}

		return $status;
	}

	/**
	 * Checking the system for compatibility
	 *
	 * This is called automatically by [System::check].
	 *
	 * @param   string  $param  Checked argument
	 * @return  boolean
	 *
	 * @link    http://php.net/manual/en/function.version-compare.php version_compare()
	 * @link    http://php.net/manual/en/function.class-exists.php class_exists()
	 * @link    http://php.net/manual/en/function.function-exists.php function_exists()
	 * @link    http://php.net/manual/en/function.extension-loaded.php extension_loaded()
	 * @link    http://php.net/manual/en/function.is-dir.php is_dir()
	 * @link    http://php.net/manual/en/function.is-readable.php is_readable()
	 * @link    http://php.net/manual/en/function.is-writable.php is_writable()
	 * @link    http://php.net/manual/en/function.preg-match.php preg_match()
	 * @link    http://php.net/manual/en/function.ini-get.php ini_get()
	 */
	protected static function _do_check($param)
	{
		switch($param)
		{
			case 'php':
				$status = version_compare(PHP_VERSION, '5.3', '>=');
			break;
			case 'mysql':
				$status = function_exists('mysqli_query') OR function_exists('mysql_query');
			break;
			case 'sys_dir':
				$status = (is_dir(SYSPATH) AND is_readable(SYSPATH.'classes/kohana'.EXT));
			break;
			case 'app_dir':
				$status = (is_dir(APPPATH) AND is_readable(APPPATH.'bootstrap'.EXT));
			break;
			case 'mod_dir':
				$status = is_dir(MODPATH);
			break;
			case 'theme_dir':
				$status = is_dir(THEMEPATH);
			break;
			case 'config':
				$status = (is_dir(APPPATH.'config') AND is_writable(APPPATH.'config'));
			break;
			case 'cache':
				$status = (is_dir(APPPATH.'cache') AND is_writable(APPPATH.'cache'));
			break;
			case 'utf8':
				$status = ( @preg_match('/^.$/u', 'ñ'));
			break;
			case 'unicode':
				$status = ( @preg_match('/^\pL$/u', 'ñ'));
			break;
			case 'reflection':
				$status = class_exists('ReflectionClass');
			break;
			case 'spl_autoload':
				$status = function_exists('spl_autoload_register');
			break;
			case 'filters':
				$status = function_exists('filter_list');
			break;
			case 'iconv':
				$status = extension_loaded('iconv');
			break;
			case 'simplexml':
				$status = extension_loaded('simplexml');
			break;
			case 'json':
				$status = function_exists('json_encode');
			break;
			case 'mbstring':
				$status = ( ! (extension_loaded('mbstring') AND ini_get('mbstring.func_overload') AND MB_OVERLOAD_STRING));
			break;
			case 'ctype':
				$status = function_exists('ctype_digit');
			break;
			case 'uri_determ':
				$status = (isset($_SERVER['REQUEST_URI']) OR isset($_SERVER['PHP_SELF']) OR isset($_SERVER['PATH_INFO']));
			break;
			case 'gd':
				$status = extension_loaded('gd');
			break;
			default:
				$status = FALSE;
		}

		return $status;
	}
}
