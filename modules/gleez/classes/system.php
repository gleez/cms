<?php
/**
 * Gleez Core Utils class
 *
 * @package    Gleez\Core
 * @author     Gleez Team
 * @version    1.4.0
 * @copyright  (c) 2011-2014 Gleez Technologies
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
	 * Gets prepared icons array
	 *
	 * @link   http://fontawesome.io/
	 * @return array
	 */
	public static function icons()
	{
		$icons = array(
			'fa-adjust' => 'adjust',
			'fa-adn' => 'adn',
			'fa-align-center' => 'align-center',
			'fa-align-justify' => 'align-justify',
			'fa-align-left' => 'align-left',
			'fa-align-right' => 'align-right',
			'fa-ambulance' => 'ambulance',
			'fa-anchor' => 'anchor',
			'fa-android' => 'android',
			'fa-angle-double-down' => 'angle-double-down',
			'fa-angle-double-left' => 'angle-double-left',
			'fa-angle-double-right' => 'angle-double-right',
			'fa-angle-double-up' => 'angle-double-up',
			'fa-angle-down' => 'angle-down',
			'fa-angle-left' => 'angle-left',
			'fa-angle-right' => 'angle-right',
			'fa-angle-up' => 'angle-up',
			'fa-apple' => 'apple',
			'fa-archive' => 'archive',
			'fa-arrow-circle-down' => 'arrow-circle-down',
			'fa-arrow-circle-left' => 'arrow-circle-left',
			'fa-arrow-circle-o-down' => 'arrow-circle-o-down',
			'fa-arrow-circle-o-left' => 'arrow-circle-o-left',
			'fa-arrow-circle-o-right' => 'arrow-circle-o-right',
			'fa-arrow-circle-o-up' => 'arrow-circle-o-up',
			'fa-arrow-circle-right' => 'arrow-circle-right',
			'fa-arrow-circle-up' => 'arrow-circle-up',
			'fa-arrow-down' => 'arrow-down',
			'fa-arrow-left' => 'arrow-left',
			'fa-arrow-right' => 'arrow-right',
			'fa-arrow-up' => 'arrow-up',
			'fa-arrows' => 'arrows',
			'fa-arrows-alt' => 'arrows-alt',
			'fa-arrows-h' => 'arrows-h',
			'fa-arrows-v' => 'arrows-v',
			'fa-asterisk' => 'asterisk',
			'fa-backward' => 'backward',
			'fa-ban' => 'ban',
			'fa-bar-chart-o' => 'bar-chart-o',
			'fa-barcode' => 'barcode',
			'fa-bars' => 'bars',
			'fa-beer' => 'beer',
			'fa-bell' => 'bell',
			'fa-bell-o' => 'bell-o',
			'fa-bitbucket' => 'bitbucket',
			'fa-bitbucket-square' => 'bitbucket-square',
			'fa-bold' => 'bold',
			'fa-bolt' => 'bolt',
			'fa-book' => 'book',
			'fa-bookmark' => 'bookmark',
			'fa-bookmark-o' => 'bookmark-o',
			'fa-briefcase' => 'briefcase',
			'fa-btc' => 'btc',
			'fa-bug' => 'bug',
			'fa-building-o' => 'building-o',
			'fa-bullhorn' => 'bullhorn',
			'fa-bullseye' => 'bullseye',
			'fa-calendar' => 'calendar',
			'fa-calendar-o' => 'calendar-o',
			'fa-camera' => 'camera',
			'fa-camera-retro' => 'camera-retro',
			'fa-caret-down' => 'caret-down',
			'fa-caret-left' => 'caret-left',
			'fa-caret-right' => 'caret-right',
			'fa-caret-square-o-down' => 'caret-square-o-down',
			'fa-caret-square-o-left' => 'caret-square-o-left',
			'fa-caret-square-o-right' => 'caret-square-o-right',
			'fa-caret-square-o-up' => 'caret-square-o-up',
			'fa-caret-up' => 'caret-up',
			'fa-certificate' => 'certificate',
			'fa-chain-broken' => 'chain-broken',
			'fa-check' => 'check',
			'fa-check-circle' => 'check-circle',
			'fa-check-circle-o' => 'check-circle-o',
			'fa-check-square' => 'check-square',
			'fa-check-square-o' => 'check-square-o',
			'fa-chevron-circle-down' => 'chevron-circle-down',
			'fa-chevron-circle-left' => 'chevron-circle-left',
			'fa-chevron-circle-right' => 'chevron-circle-right',
			'fa-chevron-circle-up' => 'chevron-circle-up',
			'fa-chevron-down' => 'chevron-down',
			'fa-chevron-left' => 'chevron-left',
			'fa-chevron-right' => 'chevron-right',
			'fa-chevron-up' => 'chevron-up',
			'fa-circle' => 'circle',
			'fa-circle-o' => 'circle-o',
			'fa-clipboard' => 'clipboard',
			'fa-clock-o' => 'clock-o',
			'fa-cloud' => 'cloud',
			'fa-cloud-download' => 'cloud-download',
			'fa-cloud-upload' => 'cloud-upload',
			'fa-code' => 'code',
			'fa-code-fork' => 'code-fork',
			'fa-coffee' => 'coffee',
			'fa-cog' => 'cog',
			'fa-cogs' => 'cogs',
			'fa-columns' => 'columns',
			'fa-comment' => 'comment',
			'fa-comment-o' => 'comment-o',
			'fa-comments' => 'comments',
			'fa-comments-o' => 'comments-o',
			'fa-compass' => 'compass',
			'fa-compress' => 'compress',
			'fa-credit-card' => 'credit-card',
			'fa-crop' => 'crop',
			'fa-crosshairs' => 'crosshairs',
			'fa-css3' => 'css3',
			'fa-cutlery' => 'cutlery',
			'fa-desktop' => 'desktop',
			'fa-dot-circle-o' => 'dot-circle-o',
			'fa-download' => 'download',
			'fa-dribbble' => 'dribbble',
			'fa-dropbox' => 'dropbox',
			'fa-eject' => 'eject',
			'fa-ellipsis-h' => 'ellipsis-h',
			'fa-ellipsis-v' => 'ellipsis-v',
			'fa-envelope' => 'envelope',
			'fa-envelope-o' => 'envelope-o',
			'fa-eraser' => 'eraser',
			'fa-eur' => 'eur',
			'fa-exchange' => 'exchange',
			'fa-exclamation' => 'exclamation',
			'fa-exclamation-circle' => 'exclamation-circle',
			'fa-exclamation-triangle' => 'exclamation-triangle',
			'fa-expand' => 'expand',
			'fa-external-link' => 'external-link',
			'fa-external-link-square' => 'external-link-square',
			'fa-eye' => 'eye',
			'fa-eye-slash' => 'eye-slash',
			'fa-facebook' => 'facebook',
			'fa-facebook-square' => 'facebook-square',
			'fa-fast-backward' => 'fast-backward',
			'fa-fast-forward' => 'fast-forward',
			'fa-female' => 'female',
			'fa-fighter-jet' => 'fighter-jet',
			'fa-file' => 'file',
			'fa-file-o' => 'file-o',
			'fa-file-text' => 'file-text',
			'fa-file-text-o' => 'file-text-o',
			'fa-files-o' => 'files-o',
			'fa-film' => 'film',
			'fa-filter' => 'filter',
			'fa-fire' => 'fire',
			'fa-fire-extinguisher' => 'fire-extinguisher',
			'fa-flag' => 'flag',
			'fa-flag-checkered' => 'flag-checkered',
			'fa-flag-o' => 'flag-o',
			'fa-flask' => 'flask',
			'fa-flickr' => 'flickr',
			'fa-floppy-o' => 'floppy-o',
			'fa-folder' => 'folder',
			'fa-folder-o' => 'folder-o',
			'fa-folder-open' => 'folder-open',
			'fa-folder-open-o' => 'folder-open-o',
			'fa-font' => 'font',
			'fa-forward' => 'forward',
			'fa-foursquare' => 'foursquare',
			'fa-frown-o' => 'frown-o',
			'fa-gamepad' => 'gamepad',
			'fa-gavel' => 'gavel',
			'fa-gbp' => 'gbp',
			'fa-gift' => 'gift',
			'fa-github' => 'github',
			'fa-github-alt' => 'github-alt',
			'fa-github-square' => 'github-square',
			'fa-gittip' => 'gittip',
			'fa-glass' => 'glass',
			'fa-globe' => 'globe',
			'fa-google-plus' => 'google-plus',
			'fa-google-plus-square' => 'google-plus-square',
			'fa-h-square' => 'h-square',
			'fa-hand-o-down' => 'hand-o-down',
			'fa-hand-o-left' => 'hand-o-left',
			'fa-hand-o-right' => 'hand-o-right',
			'fa-hand-o-up' => 'hand-o-up',
			'fa-hdd-o' => 'hdd-o',
			'fa-headphones' => 'headphones',
			'fa-heart' => 'heart',
			'fa-heart-o' => 'heart-o',
			'fa-home' => 'home',
			'fa-hospital-o' => 'hospital-o',
			'fa-html5' => 'html5',
			'fa-inbox' => 'inbox',
			'fa-indent' => 'indent',
			'fa-info' => 'info',
			'fa-info-circle' => 'info-circle',
			'fa-inr' => 'inr',
			'fa-instagram' => 'instagram',
			'fa-italic' => 'italic',
			'fa-jpy' => 'jpy',
			'fa-key' => 'key',
			'fa-keyboard-o' => 'keyboard-o',
			'fa-krw' => 'krw',
			'fa-laptop' => 'laptop',
			'fa-leaf' => 'leaf',
			'fa-lemon-o' => 'lemon-o',
			'fa-level-down' => 'level-down',
			'fa-level-up' => 'level-up',
			'fa-lightbulb-o' => 'lightbulb-o',
			'fa-link' => 'link',
			'fa-linkedin' => 'linkedin',
			'fa-linkedin-square' => 'linkedin-square',
			'fa-linux' => 'linux',
			'fa-list' => 'list',
			'fa-list-alt' => 'list-alt',
			'fa-list-ol' => 'list-ol',
			'fa-list-ul' => 'list-ul',
			'fa-location-arrow' => 'location-arrow',
			'fa-lock' => 'lock',
			'fa-long-arrow-down' => 'long-arrow-down',
			'fa-long-arrow-left' => 'long-arrow-left',
			'fa-long-arrow-right' => 'long-arrow-right',
			'fa-long-arrow-up' => 'long-arrow-up',
			'fa-magic' => 'magic',
			'fa-magnet' => 'magnet',
			'fa-mail-reply-all' => 'mail-reply-all',
			'fa-male' => 'male',
			'fa-map-marker' => 'map-marker',
			'fa-maxcdn' => 'maxcdn',
			'fa-medkit' => 'medkit',
			'fa-meh-o' => 'meh-o',
			'fa-microphone' => 'microphone',
			'fa-microphone-slash' => 'microphone-slash',
			'fa-minus' => 'minus',
			'fa-minus-circle' => 'minus-circle',
			'fa-minus-square' => 'minus-square',
			'fa-minus-square-o' => 'minus-square-o',
			'fa-mobile' => 'mobile',
			'fa-money' => 'money',
			'fa-moon-o' => 'moon-o',
			'fa-music' => 'music',
			'fa-outdent' => 'outdent',
			'fa-pagelines' => 'pagelines',
			'fa-paperclip' => 'paperclip',
			'fa-pause' => 'pause',
			'fa-pencil' => 'pencil',
			'fa-pencil-square' => 'pencil-square',
			'fa-pencil-square-o' => 'pencil-square-o',
			'fa-phone' => 'phone',
			'fa-phone-square' => 'phone-squar',
			'fa-picture-o' => 'picture-o',
			'fa-pinterest' => 'pinterest',
			'fa-pinterest-square' => 'pinterest-square',
			'fa-plane' => 'plane',
			'fa-play' => 'play',
			'fa-play-circle' => 'play-circle',
			'fa-play-circle-o' => 'play-circle-o',
			'fa-plus' => 'plus',
			'fa-plus-circle' => 'plus-circle',
			'fa-plus-square' => 'plus-square',
			'fa-plus-square-o' => 'plus-square-o',
			'fa-power-off' => 'power-off',
			'fa-print' => 'print',
			'fa-puzzle-piece' => 'puzzle-piece',
			'fa-qrcode' => 'qrcode',
			'fa-question' => 'question',
			'fa-question-circle' => 'question-circle',
			'fa-quote-left' => 'quote-left',
			'fa-quote-right' => 'quote-right',
			'fa-random' => 'random',
			'fa-refresh' => 'refresh',
			'fa-renren' => 'renren',
			'fa-repeat' => 'repeat',
			'fa-reply' => 'reply',
			'fa-reply-all' => 'reply-all',
			'fa-retweet' => 'retweet',
			'fa-road' => 'road',
			'fa-rocket' => 'rocket',
			'fa-rss' => 'rss',
			'fa-rss-square' => 'rss-square',
			'fa-rub' => 'rub',
			'fa-scissors' => 'scissors',
			'fa-search' => 'search',
			'fa-search-minus' => 'search-minus',
			'fa-search-plus' => 'search-plus',
			'fa-share' => 'share',
			'fa-share-square' => 'share-square',
			'fa-share-square-o' => 'share-square-o',
			'fa-shield' => 'shield',
			'fa-shopping-cart' => 'shopping-cart',
			'fa-sign-in' => 'sign-in',
			'fa-sign-out' => 'sign-out',
			'fa-signal' => 'signal',
			'fa-sitemap' => 'sitemap',
			'fa-skype' => 'skype',
			'fa-smile-o' => 'smile-o',
			'fa-sort' => 'sort',
			'fa-sort-alpha-asc' => 'sort-alpha-asc',
			'fa-sort-alpha-desc' => 'sort-alpha-desc',
			'fa-sort-amount-asc' => 'sort-amount-asc',
			'fa-sort-amount-desc' => 'sort-amount-desc',
			'fa-sort-asc' => 'sort-asc',
			'fa-sort-desc' => 'sort-desc',
			'fa-sort-numeric-asc' => 'sort-numeric-asc',
			'fa-sort-numeric-desc' => 'sort-numeric-desc',
			'fa-spinner' => 'spinner',
			'fa-square' => 'square',
			'fa-square-o' => 'square-o',
			'fa-stack-exchange' => 'stack-exchange',
			'fa-stack-overflow' => 'stack-overflow',
			'fa-star' => 'star',
			'fa-star-half' => 'tar-half',
			'fa-star-half-o' => 'star-half-o',
			'fa-star-o' => 'star-o',
			'fa-step-backward' => 'step-backward',
			'fa-step-forward' => 'step-forward',
			'fa-stethoscope' => 'stethoscope',
			'fa-stop' => 'stop',
			'fa-strikethrough' => 'strikethrough',
			'fa-subscript' => 'subscript',
			'fa-suitcase' => 'suitcase',
			'fa-sun-o' => 'sun-o',
			'fa-superscript' => 'superscript',
			'fa-table' => 'table',
			'fa-tablet' => 'tablet',
			'fa-tachometer' => 'tachometer',
			'fa-tag' => 'tag',
			'fa-tags' => 'tags',
			'fa-tasks' => 'tasks',
			'fa-terminal' => 'terminal',
			'fa-text-height' => 'text-height',
			'fa-text-width' => 'text-width',
			'fa-th' => 'th',
			'fa-th-large' => 'th-large',
			'fa-th-list' => 'th-list',
			'fa-thumb-tack' => 'fa-thumb-tack',
			'fa-thumbs-down' => 'fa-thumbs-down',
			'fa-thumbs-o-down' => 'fa-thumbs-o-down',
			'fa-thumbs-o-up' => 'fa-thumbs-o-up',
			'fa-thumbs-up' => 'fa-thumbs-up',
			'fa-ticket' => 'fa-ticket',
			'fa-times' => 'fa-times',
			'fa-times-circle' => 'fa-times-circle',
			'fa-times-circle-o' => 'fa-times-circle-o',
			'fa-tint' => 'fa-tint',
			'fa-trash-o' => 'fa-trash-o',
			'fa-trello' => 'trello',
			'fa-trophy' => 'fa-trophy',
			'fa-truck' => 'fa-truck',
			'fa-try' => 'try',
			'fa-tumblr' => 'tumblr',
			'fa-tumblr-square' => 'tumblr-square',
			'fa-twitter' => 'twitter',
			'fa-twitter-square' => 'twitter-square',
			'fa-umbrella' => 'fa-umbrella',
			'fa-underline' => 'underline',
			'fa-undo' => 'undo',
			'fa-unlock' => 'fa-unlock',
			'fa-unlock-alt' => 'fa-unlock-alt',
			'fa-upload' => 'fa-upload',
			'fa-usd' => 'usd',
			'fa-user' => 'fa-user',
			'fa-user-md' => 'user-md',
			'fa-users' => 'fa-users',
			'fa-video-camera' => 'fa-video-camera',
			'fa-vimeo-square' => 'vimeo-square',
			'fa-vk' => 'vk',
			'fa-volume-down' => 'fa-volume-down',
			'fa-volume-off' => 'fa-volume-off',
			'fa-volume-up' => 'fa-volume-up',
			'fa-weibo' => 'weibo',
			'fa-wheelchair' => 'wheelchair',
			'fa-windows' => 'windows',
			'fa-wrench' => 'wrench',
			'fa-xing' => 'xing',
			'fa-xing-square' => 'xing-square',
			'fa-youtube' => 'youtube',
			'fa-youtube-play' => 'youtube-play',
			'fa-youtube-square' => 'youtube-square',
		);

		//sort icons by natural order
		natsort($icons);

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
			'php_version'           => version_compare(PHP_VERSION, '5.3.7', '>='),
			'mysqli'                => function_exists("mysqli_query"),
			'mysql'                 => function_exists("mysql_query"),
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

		//Allow other modules to override or add
		$criteriae = Module::action('system_check', $criteria);

		return $criteriae;
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
}
