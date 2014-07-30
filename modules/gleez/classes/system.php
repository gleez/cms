<?php
/**
 * Gleez Core Utils class
 *
 * @package    Gleez\Core
 * @author     Gleez Team
 * @version    1.4.3
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
			'fa-anchor' => 'anchor',
			'fa-archive' => 'archive',
			'fa-arrows' => 'arrows',
			'fa-arrows-h' => 'arrows-h',
			'fa-arrows-v' => 'arrows-v',
			'fa-asterisk' => 'asterisk',
			'fa-automobile' => 'automobile',
			'fa-ban' => 'ban',
			'fa-bank' => 'bank',
			'fa-bar-chart-o' => 'bar-chart-o',
			'fa-barcode' => 'barcode',
			'fa-bars' => 'bars',
			'fa-beer' => 'beer',
			'fa-bell' => 'bell',
			'fa-bell-o' => 'bell-o',
			'fa-bolt' => 'bolt',
			'fa-bomb' => 'bomb',
			'fa-book' => 'book',
			'fa-bookmark' => 'bookmark',
			'fa-bookmark-o' => 'bookmark-o',
			'fa-briefcase' => 'briefcase',
			'fa-bug' => 'bug',
			'fa-building' => 'building',
			'fa-building-o' => 'building-o',
			'fa-bullhorn' => 'bullhorn',
			'fa-bullseye' => 'bullseye',
			'fa-cab' => 'cab',
			'fa-calendar' => 'calendar',
			'fa-calendar-o' => 'calendar-o',
			'fa-camera' => 'camera',
			'fa-camera-retro' => 'camera-retro',
			'fa-car' => 'car',
			'fa-caret-square-o-down' => 'caret-square-o-down',
			'fa-caret-square-o-left' => 'caret-square-o-left',
			'fa-caret-square-o-right' => 'caret-square-o-right',
			'fa-caret-square-o-up' => 'caret-square-o-up',
			'fa-certificate' => 'certificate',
			'fa-check' => 'check',
			'fa-check-circle' => 'check-circle',
			'fa-check-circle-o' => 'check-circle-o',
			'fa-check-square' => 'check-square',
			'fa-check-square-o' => 'check-square-o',
			'fa-child' => 'child',
			'fa-circle' => 'circle',
			'fa-circle-o' => 'circle-o',
			'fa-circle-o-notch' => 'circle-o-notch',
			'fa-circle-thin' => 'circle-thin',
			'fa-clock-o' => 'clock-o',
			'fa-cloud' => 'cloud',
			'fa-cloud-download' => 'cloud-download',
			'fa-cloud-upload' => 'cloud-upload',
			'fa-code' => 'code',
			'fa-code-fork' => 'code-fork',
			'fa-coffee' => 'coffee',
			'fa-cog' => 'cog',
			'fa-cogs' => 'cogs',
			'fa-comment' => 'comment',
			'fa-comment-o' => 'comment-o',
			'fa-comments' => 'comments',
			'fa-comments-o' => 'comments-o',
			'fa-compass' => 'compass',
			'fa-credit-card' => 'credit-card',
			'fa-crop' => 'crop',
			'fa-crosshairs' => 'crosshairs',
			'fa-cube' => 'cube',
			'fa-cubes' => 'cubes',
			'fa-cutlery' => 'cutlery',
			'fa-dashboard' => 'dashboard',
			'fa-database' => 'database',
			'fa-desktop' => 'desktop',
			'fa-dot-circle-o' => 'dot-circle-o',
			'fa-download' => 'download',
			'fa-edit' => 'edit',
			'fa-ellipsis-h' => 'ellipsis-h',
			'fa-ellipsis-v' => 'ellipsis-v',
			'fa-envelope' => 'envelope',
			'fa-envelope-o' => 'envelope-o',
			'fa-envelope-square' => 'envelope-square',
			'fa-eraser' => 'eraser',
			'fa-exchange' => 'exchange',
			'fa-exclamation' => 'exclamation',
			'fa-exclamation-circle' => 'exclamation-circle',
			'fa-exclamation-triangle' => 'exclamation-triangle',
			'fa-external-link' => 'external-link',
			'fa-external-link-square' => 'external-link-square',
			'fa-eye' => 'eye',
			'fa-eye-slash' => 'eye-slash',
			'fa-fax' => 'fax',
			'fa-female' => 'female',
			'fa-fighter-jet' => 'fighter-jet',
			'fa-file-archive-o' => 'file-archive-o',
			'fa-file-audio-o' => 'file-audio-o',
			'fa-file-code-o' => 'file-code-o',
			'fa-file-excel-o' => 'file-excel-o',
			'fa-file-image-o' => 'file-image-o',
			'fa-file-movie-o' => 'file-movie-o',
			'fa-file-pdf-o' => 'file-pdf-o',
			'fa-file-photo-o' => 'file-photo-o',
			'fa-file-picture-o' => 'file-picture-o',
			'fa-file-powerpoint-o' => 'file-powerpoint-o',
			'fa-file-sound-o' => 'file-sound-o',
			'fa-file-video-o' => 'file-video-o',
			'fa-file-word-o' => 'file-word-o',
			'fa-file-zip-o' => 'file-zip-o',
			'fa-film' => 'film',
			'fa-filter' => 'filter',
			'fa-fire' => 'fire',
			'fa-fire-extinguisher' => 'fire-extinguisher',
			'fa-flag' => 'flag',
			'fa-flag-checkered' => 'flag-checkered',
			'fa-flag-o' => 'flag-o',
			'fa-flash' => 'flash',
			'fa-flask' => 'flask',
			'fa-folder' => 'folder',
			'fa-folder-o' => 'folder-o',
			'fa-folder-open' => 'folder-open',
			'fa-folder-open-o' => 'folder-open-o',
			'fa-frown-o' => 'frown-o',
			'fa-gamepad' => 'gamepad',
			'fa-gavel' => 'gavel',
			'fa-gear' => 'gear',
			'fa-gears' => 'gears',
			'fa-gift' => 'gift',
			'fa-glass' => 'glass',
			'fa-globe' => 'globe',
			'fa-graduation-cap' => 'graduation-cap',
			'fa-group' => 'group',
			'fa-hdd-o' => 'hdd-o',
			'fa-headphones' => 'headphones',
			'fa-heart' => 'heart',
			'fa-heart-o' => 'heart-o',
			'fa-history' => 'history',
			'fa-home' => 'home',
			'fa-image' => 'image',
			'fa-inbox' => 'inbox',
			'fa-info' => 'info',
			'fa-info-circle' => 'info-circle',
			'fa-institution' => 'institution',
			'fa-key' => 'key',
			'fa-keyboard-o' => 'keyboard-o',
			'fa-language' => 'language',
			'fa-laptop' => 'laptop',
			'fa-leaf' => 'leaf',
			'fa-legal' => 'legal',
			'fa-lemon-o' => 'lemon-o',
			'fa-level-down' => 'level-down',
			'fa-level-up' => 'level-up',
			'fa-life-bouy' => 'life-bouy',
			'fa-life-ring' => 'life-ring',
			'fa-life-saver' => 'life-saver',
			'fa-lightbulb-o' => 'lightbulb-o',
			'fa-location-arrow' => 'location-arrow',
			'fa-lock' => 'lock',
			'fa-magic' => 'magic',
			'fa-magnet' => 'magnet',
			'fa-mail-forward' => 'mail-forward',
			'fa-mail-reply' => 'mail-reply',
			'fa-mail-reply-all' => 'mail-reply-all',
			'fa-male' => 'male',
			'fa-map-marker' => 'map-marker',
			'fa-meh-o' => 'meh-o',
			'fa-microphone' => 'microphone',
			'fa-microphone-slash' => 'microphone-slash',
			'fa-minus' => 'minus',
			'fa-minus-circle' => 'minus-circle',
			'fa-minus-square' => 'minus-square',
			'fa-minus-square-o' => 'minus-square-o',
			'fa-mobile' => 'mobile',
			'fa-mobile-phone' => 'mobile-phone',
			'fa-money' => 'money',
			'fa-moon-o' => 'moon-o',
			'fa-mortar-board' => 'mortar-board',
			'fa-music' => 'music',
			'fa-navicon' => 'navicon',
			'fa-paper-plane' => 'paper-plane',
			'fa-paper-plane-o' => 'paper-plane-o',
			'fa-paw' => 'paw',
			'fa-pencil' => 'pencil',
			'fa-pencil-square' => 'pencil-square',
			'fa-pencil-square-o' => 'pencil-square-o',
			'fa-phone' => 'phone',
			'fa-phone-square' => 'phone-square',
			'fa-photo' => 'photo',
			'fa-picture-o' => 'picture-o',
			'fa-plane' => 'plane',
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
			'fa-recycle' => 'recycle',
			'fa-refresh' => 'refresh',
			'fa-reorder' => 'reorder',
			'fa-reply' => 'reply',
			'fa-reply-all' => 'reply-all',
			'fa-retweet' => 'retweet',
			'fa-road' => 'road',
			'fa-rocket' => 'rocket',
			'fa-rss' => 'rss',
			'fa-rss-square' => 'rss-square',
			'fa-search' => 'search',
			'fa-search-minus' => 'search-minus',
			'fa-search-plus' => 'search-plus',
			'fa-send' => 'send',
			'fa-send-o' => 'send-o',
			'fa-share' => 'share',
			'fa-share-alt' => 'share-alt',
			'fa-share-alt-square' => 'share-alt-square',
			'fa-share-square' => 'share-square',
			'fa-share-square-o' => 'share-square-o',
			'fa-shield' => 'shield',
			'fa-shopping-cart' => 'shopping-cart',
			'fa-sign-in' => 'sign-in',
			'fa-sign-out' => 'sign-out',
			'fa-signal' => 'signal',
			'fa-sitemap' => 'sitemap',
			'fa-sliders' => 'sliders',
			'fa-smile-o' => 'smile-o',
			'fa-sort' => 'sort',
			'fa-sort-alpha-asc' => 'sort-alpha-asc',
			'fa-sort-alpha-desc' => 'sort-alpha-desc',
			'fa-sort-amount-asc' => 'sort-amount-asc',
			'fa-sort-amount-desc' => 'sort-amount-desc',
			'fa-sort-asc' => 'sort-asc',
			'fa-sort-desc' => 'sort-desc',
			'fa-sort-down' => 'sort-down',
			'fa-sort-numeric-asc' => 'sort-numeric-asc',
			'fa-sort-numeric-desc' => 'sort-numeric-desc',
			'fa-sort-up' => 'sort-up',
			'fa-space-shuttle' => 'space-shuttle',
			'fa-spinner' => 'spinner',
			'fa-spoon' => 'spoon',
			'fa-square' => 'square',
			'fa-square-o' => 'square-o',
			'fa-star' => 'star',
			'fa-star-half' => 'star-half',
			'fa-star-half-empty' => 'star-half-empty',
			'fa-star-half-full' => 'star-half-full',
			'fa-star-half-o' => 'star-half-o',
			'fa-star-o' => 'star-o',
			'fa-suitcase' => 'suitcase',
			'fa-sun-o' => 'sun-o',
			'fa-support' => 'support',
			'fa-tablet' => 'tablet',
			'fa-tachometer' => 'tachometer',
			'fa-tag' => 'tag',
			'fa-tags' => 'tags',
			'fa-tasks' => 'tasks',
			'fa-taxi' => 'taxi',
			'fa-terminal' => 'terminal',
			'fa-thumb-tack' => 'thumb-tack',
			'fa-thumbs-down' => 'thumbs-down',
			'fa-thumbs-o-down' => 'thumbs-o-down',
			'fa-thumbs-o-up' => 'thumbs-o-up',
			'fa-thumbs-up' => 'thumbs-up',
			'fa-ticket' => 'ticket',
			'fa-times' => 'times',
			'fa-times-circle' => 'times-circle',
			'fa-times-circle-o' => 'times-circle-o',
			'fa-tint' => 'tint',
			'fa-toggle-down' => 'toggle-down',
			'fa-toggle-left' => 'toggle-left',
			'fa-toggle-right' => 'toggle-right',
			'fa-toggle-up' => 'toggle-up',
			'fa-trash-o' => 'trash-o',
			'fa-tree' => 'tree',
			'fa-trophy' => 'trophy',
			'fa-truck' => 'truck',
			'fa-umbrella' => 'umbrella',
			'fa-university' => 'university',
			'fa-unlock' => 'unlock',
			'fa-unlock-alt' => 'unlock-alt',
			'fa-unsorted' => 'unsorted',
			'fa-upload' => 'upload',
			'fa-user' => 'user',
			'fa-users' => 'users',
			'fa-video-camera' => 'video-camera',
			'fa-volume-down' => 'volume-down',
			'fa-volume-off' => 'volume-off',
			'fa-volume-up' => 'volume-up',
			'fa-warning' => 'warning',
			'fa-wheelchair' => 'wheelchair',
			'fa-wrench' => 'wrench',
			'fa-file' => 'file',
			'fa-file-o' => 'file-o',
			'fa-file-text' => 'file-text',
			'fa-file-text-o' => 'file-text-o',
			'fa-bitcoin' => 'bitcoin',
			'fa-btc' => 'btc',
			'fa-cny' => 'cny',
			'fa-dollar' => 'dollar',
			'fa-eur' => 'eur',
			'fa-euro' => 'euro',
			'fa-gbp' => 'gbp',
			'fa-inr' => 'inr',
			'fa-jpy' => 'jpy',
			'fa-krw' => 'krw',
			'fa-rmb' => 'rmb',
			'fa-rouble' => 'rouble',
			'fa-rub' => 'rub',
			'fa-ruble' => 'ruble',
			'fa-rupee' => 'rupee',
			'fa-try' => 'try',
			'fa-turkish-lira' => 'turkish-lira',
			'fa-usd' => 'usd',
			'fa-won' => 'won',
			'fa-yen' => 'yen',
			'fa-align-center' => 'align-center',
			'fa-align-justify' => 'align-justify',
			'fa-align-left' => 'align-left',
			'fa-align-right' => 'align-right',
			'fa-bold' => 'bold',
			'fa-chain' => 'chain',
			'fa-chain-broken' => 'chain-broken',
			'fa-clipboard' => 'clipboard',
			'fa-columns' => 'columns',
			'fa-copy' => 'copy',
			'fa-cut' => 'cut',
			'fa-dedent' => 'dedent',
			'fa-files-o' => 'files-o',
			'fa-floppy-o' => 'floppy-o',
			'fa-font' => 'font',
			'fa-header' => 'header',
			'fa-indent' => 'indent',
			'fa-italic' => 'italic',
			'fa-link' => 'link',
			'fa-list' => 'list',
			'fa-list-alt' => 'list-alt',
			'fa-list-ol' => 'list-ol',
			'fa-list-ul' => 'list-ul',
			'fa-outdent' => 'outdent',
			'fa-paperclip' => 'paperclip',
			'fa-paragraph' => 'paragraph',
			'fa-paste' => 'paste',
			'fa-repeat' => 'repeat',
			'fa-rotate-left' => 'rotate-left',
			'fa-rotate-right' => 'rotate-right',
			'fa-save' => 'save',
			'fa-scissors' => 'scissors',
			'fa-strikethrough' => 'strikethrough',
			'fa-subscript' => 'subscript',
			'fa-superscript' => 'superscript',
			'fa-table' => 'table',
			'fa-text-height' => 'text-height',
			'fa-text-width' => 'text-width',
			'fa-th' => 'th',
			'fa-th-large' => 'th-large',
			'fa-th-list' => 'th-list',
			'fa-underline' => 'underline',
			'fa-undo' => 'undo',
			'fa-unlink' => 'unlink',
			'fa-angle-double-down' => 'angle-double-down',
			'fa-angle-double-left' => 'angle-double-left',
			'fa-angle-double-right' => 'angle-double-right',
			'fa-angle-double-up' => 'angle-double-up',
			'fa-angle-down' => 'angle-down',
			'fa-angle-left' => 'angle-left',
			'fa-angle-right' => 'angle-right',
			'fa-angle-up' => 'angle-up',
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
			'fa-arrows-alt' => 'arrows-alt',
			'fa-caret-down' => 'caret-down',
			'fa-caret-left' => 'caret-left',
			'fa-caret-right' => 'caret-right',
			'fa-caret-up' => 'caret-up',
			'fa-chevron-circle-down' => 'chevron-circle-down',
			'fa-chevron-circle-left' => 'chevron-circle-left',
			'fa-chevron-circle-right' => 'chevron-circle-right',
			'fa-chevron-circle-up' => 'chevron-circle-up',
			'fa-chevron-down' => 'chevron-down',
			'fa-chevron-left' => 'chevron-left',
			'fa-chevron-right' => 'chevron-right',
			'fa-chevron-up' => 'chevron-up',
			'fa-hand-o-down' => 'hand-o-down',
			'fa-hand-o-left' => 'hand-o-left',
			'fa-hand-o-right' => 'hand-o-right',
			'fa-hand-o-up' => 'hand-o-up',
			'fa-long-arrow-down' => 'long-arrow-down',
			'fa-long-arrow-left' => 'long-arrow-left',
			'fa-long-arrow-right' => 'long-arrow-right',
			'fa-long-arrow-up' => 'long-arrow-up',
			'fa-backward' => 'backward',
			'fa-compress' => 'compress',
			'fa-eject' => 'eject',
			'fa-expand' => 'expand',
			'fa-fast-backward' => 'fast-backward',
			'fa-fast-forward' => 'fast-forward',
			'fa-forward' => 'forward',
			'fa-pause' => 'pause',
			'fa-play' => 'play',
			'fa-play-circle' => 'play-circle',
			'fa-play-circle-o' => 'play-circle-o',
			'fa-step-backward' => 'step-backward',
			'fa-step-forward' => 'step-forward',
			'fa-stop' => 'stop',
			'fa-youtube-play' => 'youtube-play',
			'fa-adn' => 'adn',
			'fa-android' => 'android',
			'fa-apple' => 'apple',
			'fa-behance' => 'behance',
			'fa-behance-square' => 'behance-square',
			'fa-bitbucket' => 'bitbucket',
			'fa-bitbucket-square' => 'bitbucket-square',
			'fa-codepen' => 'codepen',
			'fa-css3' => 'css3',
			'fa-delicious' => 'delicious',
			'fa-deviantart' => 'deviantart',
			'fa-digg' => 'digg',
			'fa-dribbble' => 'dribbble',
			'fa-dropbox' => 'dropbox',
			'fa-drupal' => 'drupal',
			'fa-empire' => 'empire',
			'fa-facebook' => 'facebook',
			'fa-facebook-square' => 'facebook-square',
			'fa-flickr' => 'flickr',
			'fa-foursquare' => 'foursquare',
			'fa-ge' => 'ge',
			'fa-git' => 'git',
			'fa-git-square' => 'git-square',
			'fa-github' => 'github',
			'fa-github-alt' => 'github-alt',
			'fa-github-square' => 'github-square',
			'fa-gittip' => 'gittip',
			'fa-google' => 'google',
			'fa-google-plus' => 'google-plus',
			'fa-google-plus-square' => 'google-plus-square',
			'fa-hacker-news' => 'hacker-news',
			'fa-html5' => 'html5',
			'fa-instagram' => 'instagram',
			'fa-joomla' => 'joomla',
			'fa-jsfiddle' => 'jsfiddle',
			'fa-linkedin' => 'linkedin',
			'fa-linkedin-square' => 'linkedin-square',
			'fa-linux' => 'linux',
			'fa-maxcdn' => 'maxcdn',
			'fa-openid' => 'openid',
			'fa-pagelines' => 'pagelines',
			'fa-pied-piper' => 'pied-piper',
			'fa-pied-piper-alt' => 'pied-piper-alt',
			'fa-pied-piper-square' => 'pied-piper-square',
			'fa-pinterest' => 'pinterest',
			'fa-pinterest-square' => 'pinterest-square',
			'fa-qq' => 'qq',
			'fa-ra' => 'ra',
			'fa-rebel' => 'rebel',
			'fa-reddit' => 'reddit',
			'fa-reddit-square' => 'reddit-square',
			'fa-renren' => 'renren',
			'fa-skype' => 'skype',
			'fa-slack' => 'slack',
			'fa-soundcloud' => 'soundcloud',
			'fa-spotify' => 'spotify',
			'fa-stack-exchange' => 'stack-exchange',
			'fa-stack-overflow' => 'stack-overflow',
			'fa-steam' => 'steam',
			'fa-steam-square' => 'steam-square',
			'fa-stumbleupon' => 'stumbleupon',
			'fa-stumbleupon-circle' => 'stumbleupon-circle',
			'fa-tencent-weibo' => 'tencent-weibo',
			'fa-trello' => 'trello',
			'fa-tumblr' => 'tumblr',
			'fa-tumblr-square' => 'tumblr-square',
			'fa-twitter' => 'twitter',
			'fa-twitter-square' => 'twitter-square',
			'fa-vimeo-square' => 'vimeo-square',
			'fa-vine' => 'vine',
			'fa-vk' => 'vk',
			'fa-wechat' => 'wechat',
			'fa-weibo' => 'weibo',
			'fa-weixin' => 'weixin',
			'fa-windows' => 'windows',
			'fa-wordpress' => 'wordpress',
			'fa-xing' => 'xing',
			'fa-xing-square' => 'xing-square',
			'fa-yahoo' => 'yahoo',
			'fa-youtube' => 'youtube',
			'fa-youtube-square' => 'youtube-square',
			'fa-ambulance' => 'ambulance',
			'fa-h-square' => 'h-square',
			'fa-hospital-o' => 'hospital-o',
			'fa-medkit' => 'medkit',
			'fa-stethoscope' => 'stethoscope',
			'fa-user-md' => 'user-md',
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
