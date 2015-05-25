<?php
/**
 * Gleez Assets Manager
 *
 * ### Overview
 *
 * Gleez Assets Manager allows to include throughout the application different
 * assets (CSS, Javascript, etc.) with dependencies support and use then later.
 *
 * Gleez Assets Manager makes sure all assets will be included in the correct order,
 * no matter what order they are defined in.
 *
 * ### Usage
 *
 * Call this anywhere in your application, most likely in a template controller:
 * ~~~
 * Assets::css('global', 'assets/css/global.css', array('grid', 'reset'), array('media' => 'screen', 'weight' => -10));
 * Assets::css('reset', 'assets/css/reset.css', NULL, array('weight' => -10));
 * Assets::css('grid', 'assets/css/grid.css', 'reset');
 *
 * Assets::js('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js', NULL, FALSE, array('weight' => -10));
 * Assets::js('global', 'assets/js/global.js', array('jquery'));
 * Assets::js('stats', 'assets/js/stats.js', NULL, TRUE);
 *
 * Assets::codes('alert', 'alert(\'test\')', NULL, FALSE, array('weight' => -10));
 *
 * Assets::settings('settings', 'settings');
 * ~~~
 *
 * In your view file:
 * ~~~
 * <html>
 *   <head>
 *     <title>Kohana Assets</title>
 *      <?php echo Assets::css() ?>
 *      <?php echo Assets::js() ?>
 *      <?php echo Assets::group('head') ?>
 *   </head>
 *   <body>
 *     <!-- Content -->
 *     <?php echo Assets::js(TRUE) ?>
 *   </body>
 * </html>
 * ~~~
 *
 * @package    Gleez\Assets\Core
 * @author     Corey Worrell
 * @author     Gleez Team
 * @version    1.1.1
 * @copyright  (c) 2011-2015 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Assets {

	/** Formats that compile can return */
	const FORMAT_TAG      = 'tag';
	const FORMAT_FILENAME = 'filename';
	const FORMAT_AJAX     = 'ajax';

	/**
	 * CSS assets
	 * @var array
	 */
	public static $css = array();

	/**
	 * Javascript assets
	 * @var array
	 */
	public static $js = array();

	/**
	 * Script blocks
	 * @var array
	 */
	public static $codes = array();

	/**
	 * Settings blocks
	 * @var array
	 */
	public static $settings = array();

	/**
	 * Other asset groups (meta data, links, etc...)
	 * @var array
	 */
	public static $groups = array();

	/**
	 * CSS wrapper
	 *
	 * Gets or sets CSS assets
	 *
	 * @param   string  $handle  Asset name [Optional]
	 * @param   string  $src     Asset source [Optional]
	 * @param   mixed   $deps    Dependencies [Optional]
	 * @param   array   $attrs   An array of attributes for the <link> element [Optional]
	 * @param   string  $format  Format that be returned [Optional]
	 * @return  mixed   Setting returns asset array, getting returns asset HTML
	 */
	public static function css($handle = NULL, $src = NULL, $deps = NULL, $attrs = NULL, $format = self::FORMAT_TAG)
	{
		$config = Config::load('media');

		if(Kohana::$environment === Kohana::PRODUCTION AND $config->get('combine', FALSE))
		{
			$format = self::FORMAT_FILENAME;
		}

		// Return all CSS assets, sorted by dependencies
		if (is_null($handle))
		{
			return self::all_css($format);
		}

		// Return individual asset
		if (is_null($src))
		{
			return self::get_css($handle, $format);
		}

		// Set default media attribute
		if ( ! isset($attrs['media']))
		{
			$attrs['media'] = 'all';
		}

		$weight = isset($attrs['weight']) ? $attrs['weight'] : 0;

		// Unset weight attribute if its set, we processed it already
		if(isset($attrs['weight']))
		{
			unset($attrs['weight']);
		}

		return self::$css[$handle] = array(
			'src'    => $src,
			'deps'   => (array) $deps,
			'attrs'  => (array) $attrs,
			'weight' => (int) $weight,
		);
	}

	/**
	 * Get a single CSS asset
	 *
	 * @param   string  $handle  Asset name
	 * @param   string  $format  Format that be returned [Optional]
	 * @return  string  Asset HTML
	 * @throws  Exception
	 * @uses    HTML::style
	 */
	public static function get_css($handle, $format = self::FORMAT_TAG)
	{
		if ( ! isset(self::$css[$handle]))
		{
			return FALSE;
		}

		$asset = self::$css[$handle];

		switch ($format)
		{
			case self::FORMAT_TAG:
				return HTML::style($asset['src'], $asset['attrs']);
				break;

			case self::FORMAT_FILENAME:
			case self::FORMAT_AJAX:
				return $asset['src'];
				break;

			default:
				throw new Exception("Unknown format: $format.");
		}
	}

	/**
	 * Get all CSS assets, sorted by dependencies
	 *
	 * @param   string  $format  Format that be returned [Optional]
	 * @return  string  Asset HTML
	 * @throws  Exception
	 */
	public static function all_css($format = self::FORMAT_TAG)
	{
		if (empty(self::$css))
		{
			return FALSE;
		}

		$assets = array();

		foreach (self::_sort(self::$css) as $handle => $data)
		{
			$assets[] = self::get_css($handle, $format);
		}

		switch ($format)
		{
			case self::FORMAT_TAG:
				return implode(PHP_EOL, $assets).PHP_EOL;
				break;

			case self::FORMAT_FILENAME:
				return self::compile($assets, $format, 'css');
				break;

			case self::FORMAT_AJAX:
				return $assets;
				break;

			default:
				throw new Exception("Unknown format: $format.");
		}
	}

	/**
	 * Remove a CSS asset, or all
	 *
	 * @param   mixed  $handle  Asset name, or NULL to remove all [Optional]
	 * @return  mixed  Empty array or void
	 */
	public static function remove_css($handle = NULL)
	{
		if (is_null($handle))
		{
			return self::$css = array();
		}

		unset(self::$css[$handle]);
	}

	/**
	 * Javascript wrapper
	 *
	 * Gets or sets javascript assets
	 *
	 * @param   mixed   $handle  Asset name if `string`, sets `$footer` if boolean
	 * @param   string  $src     Asset source [Optional]
	 * @param   mixed   $deps    Dependencies [Optional]
	 * @param   boolean $footer  Whether to show in header or footer [Optional]
	 * @param   array   $attrs   An array of attributes for the <script> element [Optional]
	 * @param   string  $format  Format that be returned [Optional]
	 * @return  mixed   Setting returns asset array, getting returns asset HTML
	 */
	public static function js($handle, $src = NULL, $deps = NULL, $footer = FALSE, $attrs = NULL, $format = Assets::FORMAT_TAG)
	{
		$config = Config::load('media');

		if(Kohana::$environment === Kohana::PRODUCTION AND $config->get('combine', FALSE))
		{
			$format = self::FORMAT_FILENAME;
		}

		if ($handle === TRUE OR $handle === FALSE)
		{
			return self::all_js($handle, $format);
		}

		if (is_null($src))
		{
			return self::get_js($handle, $format);
		}

		$weight = isset($attrs['weight']) ? $attrs['weight'] : 0;

		// Unset weight attribute if its set, we processed it already
		if(isset($attrs['weight']))
		{
			unset($attrs['weight']);
		}

		return self::$js[$handle] = array(
			'src'    => $src,
			'deps'   => (array) $deps,
			'footer' => $footer,
			'attrs'  => (array) $attrs,
			'weight' => (int) $weight,
		);
	}

	/**
	 * Get a single javascript asset
	 *
	 * @param   string  $handle  Asset name
	 * @param   string  $format  Format that be returned [Optional]
	 * @return  string  Asset HTML
	 * @throws  Exception
	 * @uses    HTML::script
	 */
	public static function get_js($handle, $format = self::FORMAT_TAG)
	{
		if ( ! isset(self::$js[$handle]))
		{
			return FALSE;
		}

		$asset = self::$js[$handle];

		switch ($format)
		{
			case self::FORMAT_TAG:
				return HTML::script($asset['src']);
				break;

			case self::FORMAT_FILENAME:
			case self::FORMAT_AJAX:
				return $asset['src'];
				break;

			default:
				throw new Exception("Unknown format: $format.");
		}
	}

	/**
	 * Get all javascript assets of section (header or footer)
	 *
	 * @param   boolean  $footer  FALSE for head, TRUE for footer
	 * @param   string   $format  Format that be returned [Optional]
	 * @return  string   Asset HTML
	 * @throws  Exception
	 */
	public static function all_js($footer = FALSE, $format = self::FORMAT_TAG)
	{
		if (empty(self::$js))
		{
			return FALSE;
		}

		self::_init_js();

		$assets = array();

		foreach (self::$js as $handle => $data)
		{
			if ($data['footer'] === $footer)
			{
				$assets[$handle] = $data;
			}
		}

		if (empty($assets))
		{
			return FALSE;
		}

		foreach (self::_sort($assets) as $handle => $data)
		{
			$sorted[] = self::get_js($handle, $format);
		}

		switch ($format)
		{
			case self::FORMAT_TAG:
				return implode(PHP_EOL, $sorted).PHP_EOL;
				break;

			case self::FORMAT_FILENAME:
				return self::compile($sorted);
				break;

			case self::FORMAT_AJAX:
				return $sorted;
				break;

			default:
				throw new Exception("Unknown format: $format.");
		}
	}

	/**
	 * Remove a javascript asset, or all
	 *
	 * @param   mixed  $handle  Remove all if NULL, section if TRUE or FALSE, asset if string
	 * @return  mixed  Empty array or void
	 */
	public static function remove_js($handle = NULL)
	{
		if (is_null($handle))
		{
			return self::$js = array();
		}

		if ($handle === TRUE OR $handle === FALSE)
		{
			foreach (self::$js as $handle => $data)
			{
				if ($data['footer'] === $handle)
				{
					unset(self::$js[$handle]);
				}
			}

			return;
		}

		unset(self::$js[$handle]);
	}

	/**
	 * Javascript code wrapper
	 *
	 * Gets or sets javascript code
	 *
	 * @param   mixed   $handle  Asset name if string, sets $footer if boolean
	 * @param   string  $code    Asset code [Optional]
	 * @param   mixed   $deps    Dependencies [Optional]
	 * @param   boolean $footer  Whether to show in header or footer [Optional]
	 * @param   array   $attrs   An array of attributes for the <script> element [Optional]
	 * @return  mixed   Setting returns asset array, getting returns asset HTML
	 */
	public static function codes($handle, $code = NULL, $deps = NULL, $footer = FALSE, $attrs = NULL)
	{
		if ($handle === TRUE OR $handle === FALSE )
		{
			return self::all_codes($handle);
		}

		if ($code === NULL)
		{
			return self::get_codes($handle);
		}

		$weight = isset($attrs['weight']) ? $attrs['weight'] : 0;

		// Unset weight attribute if its set, we processed it already
		if(isset($attrs['weight']))
		{
			unset($attrs['weight']);
		}

		return self::$codes[$handle] = array(
			'code'   => $code,
			'deps'   => (array) $deps,
			'footer' => $footer,
			'attrs'  => (array) $attrs,
			'weight' => (int) $weight,
		);
	}

	/**
	 * Get a single javascript code
	 *
	 * @param   string  $handle  Asset name
	 * @return  string  Asset HTML
	 * @uses    HTML::attributes
	 */
	public static function get_codes($handle)
	{
		if ( ! isset(self::$codes[$handle]))
		{
			return FALSE;
		}

		$asset = self::$codes[$handle];

		return "<script".HTML::attributes(array('type' => 'text/javascript')).'>
		<!--//--><![CDATA['.PHP_EOL.$asset['code'].PHP_EOL.'<!--//-->]]></script>';
	}

	/**
	 * Get all javascript codes of section (header or footer)
	 *
	 * @param   boolean  $footer  FALSE for head, TRUE for footer [Optional]
	 * @return  string   Asset HTML
	 */
	public static function all_codes($footer = FALSE)
	{
		if (empty(self::$codes))
		{
			return FALSE;
		}

		self::_init_js();

		$assets = array();

		foreach (self::$codes as $handle => $data)
		{
			if ($data['footer'] === $footer)
			{
				$assets[$handle] = $data;
			}
		}

		if (empty($assets))
		{
			return FALSE;
		}

		foreach (self::_sort($assets) as $handle => $data)
		{
			$sorted[] = self::get_codes($handle);
		}

		return implode(PHP_EOL, $sorted).PHP_EOL;
	}

	/**
	 * Remove a javascript code, or all codes
	 *
	 * @param   mixed  $handle  Remove all if NULL, section if TRUE or FALSE, asset if string [Optional]
	 * @return  mixed  Empty array or void
	 */
	public static function remove_code($handle = NULL)
	{
		if (is_null($handle))
		{
			return self::$codes = array();
		}

		if ($handle === TRUE OR $handle === FALSE)
		{
			foreach (self::$codes as $handle => $data)
			{
				if ($data['footer'] === $handle)
				{
					unset(self::$codes[$handle]);
				}
			}

			return;
		}

		unset(self::$codes[$handle]);
	}

	/**
	 * Javascript code settings wrapper
	 *
	 * Gets or sets javascript code
	 *
	 * @param   mixed   $handle  Asset name if `string`, sets `$footer` if boolean
	 * @param   string  $code    Asset code [Optional]
	 * @return  mixed    Setting returns asset array, getting returns asset HTML
	 */
	public static function settings($handle, $code = NULL)
	{
		return self::$settings[$handle] = $code;
	}

	/**
	 * Remove a js settings asset, all of a groups assets, or all group assets
	 *
	 * @param   string  $handle  Asset name
	 * @return  mixed   Empty array or void
	 */
	public static function remove_settings($handle = NULL)
	{
		if (is_null($handle))
		{
			return self::$settings = array();
		}

		unset(self::$settings[$handle]);
	}

	/**
	 * Group wrapper
	 *
	 * @param   string  $group    Group name
	 * @param   string  $handle   Asset name [Optional]
	 * @param   string  $content  Asset content [Optional]
	 * @param   mixed   $deps     Dependencies [Optional]
	 * @param   array   $attrs    An array of attributes [Optional]
	 * @return  mixed   Setting returns asset array, getting returns asset content
	 */
	public static function group($group, $handle = NULL, $content = NULL, $deps = NULL, $attrs = NULL)
	{
		if (is_null($handle))
		{
			return self::all_groups($group);
		}

		if (is_null($content))
		{
			return self::get_group($group, $handle);
		}

		$weight = isset($attrs['weight']) ? $attrs['weight'] : 0;

		// Unset weight attribute if its set, we processed it already
		if (isset($attrs['weight']))
		{
			unset($attrs['weight']);
		}

		return self::$groups[$group][$handle] = array(
			'content' => $content,
			'deps'    => (array) $deps,
			'attrs'   => (array) $attrs,
			'weight'  => (int) $weight,
		);
	}

	/**
	 * Get a single group asset
	 *
	 * @param   string  $group   Group name
	 * @param   string  $handle  Asset name
	 * @return  string  Asset content
	 */
	public static function get_group($group, $handle)
	{
		if ( ! isset(self::$groups[$group]) OR ! isset(self::$groups[$group][$handle]))
		{
			return FALSE;
		}

		return self::$groups[$group][$handle]['content'];
	}

	/**
	 * Get all of a groups assets, sorted by dependencies
	 *
	 * @param  string  $group  Group name
	 * @return string  Assets content
	 */
	public static function all_groups($group)
	{
		if ( ! isset(self::$groups[$group]))
		{
			return FALSE;
		}

		$assets = array();

		foreach (self::_sort(self::$groups[$group]) as $handle => $data)
		{
			$assets[] = self::get_group($group, $handle);
		}

		return implode(PHP_EOL, $assets);
	}

	/**
	 * Remove a group asset, all of a groups assets, or all group assets
	 *
	 * @param   string  $group  Group name [Optional]
	 * @param   string  $handle Asset name [Optional]
	 * @return  mixed    Empty array or void
	 */
	public static function remove_group($group = NULL, $handle = NULL)
	{
		if (is_null($group))
		{
			return self::$groups = array();
		}

		if (is_null($handle))
		{
			unset(self::$groups[$group]);
			return;
		}

		unset(self::$groups[$group][$handle]);
	}

	/**
	 * Sorts assets based on dependencies
	 *
	 * @param   array  $assets  Array of assets
	 * @return  array  Sorted array of assets
	 */
	protected static function _sort($assets)
	{
		$original = $assets;
		$sorted   = array();

		while (count($assets) > 0)
		{
			foreach ($assets as $key => $value)
			{
				// No dependencies anymore, add it to sorted
				if (empty($assets[$key]['deps']))
				{
					$sorted[$key] = $value;
					unset($assets[$key]);
				}
				else
				{
					foreach ($assets[$key]['deps'] as $k => $v)
					{
						// Remove dependency if doesn't exist, if its dependent on itself, or if the dependent is dependent on it
						if ( ! isset($original[$v]) OR $v === $key OR (isset($assets[$v]) AND in_array($key, $assets[$v]['deps'])))
						{
							unset($assets[$key]['deps'][$k]);
							continue;
						}

						// This dependency hasn't been sorted yet
						if ( ! isset($sorted[$v]))
							continue;

						// This dependency is taken care of, remove from list
						unset($assets[$key]['deps'][$k]);
					}
				}
			}

			// Sort the Assets so that it appears in the correct order.
			uasort($sorted, array('self', 'sort_assets'));
		}

		return $sorted;
	}

	/**
	 * Custom sorting method for assets based on 'weight' key
	 *
	 * @param   array    $a
	 * @param   array    $b
	 * @return  integer  The sorted order for assests
	 */
	protected static function sort_assets($a, $b)
	{
		$a_weight = (is_array($a) AND isset($a['weight'])) ? $a['weight'] : 0;
		$b_weight = (is_array($b) AND isset($b['weight'])) ? $b['weight'] : 0;

		if ($a_weight == $b_weight)
		{
			return 0;
		}

		return ($a_weight > $b_weight) ? +1 : -1;
	}

	/**
	 */
	public static function tabledrag()
	{
		self::js('jquery_once', 'media/js/jquery.once-1.1.js', array('jquery'), FALSE, array('weight' => -10));
		self::js('tabledrag', 'media/js/greet.tableDrag.js');
		self::css('tabledrag', 'media/css/greet.tableDrag.css');
	}

	/**
	 * Initial JavaScript setting
	 * @uses    URL::base
	 * @uses    JSON::encode
	 * @return  array
	 */
	private static function _init_js()
	{
		if(isset(self::$js) OR isset(self::$codes) OR isset(self::$settings))
		{
			self::js('jquery', 'media/js/jquery-2.1.4.min.js', NULL, FALSE, array('weight' => -20));
			self::js('gleez', 'media/js/gleez.js', array('jquery'), FALSE, array('weight' => -5));

			$data = Arr::merge(array(array('basePath' => URL::base(TRUE))), self::$settings);

			$code = 'jQuery.extend(Gleez.settings, ' . JSON::encode(call_user_func_array('array_merge_recursive', $data)) . ');';

			self::codes('settings', $code);
		}
	}

	/**
	 * Rich text editor
	 *
	 * By default Gleez uses redactor-js - jQuery based WYSIWYG-editor
	 * @link  https://github.com/dybskiy/redactor-js
	 * @link  http://redactorjs.com/
	 *
	 * For I18n support see http://imperavi.com/redactor/docs/languages/
	 *
	 * @param  string  $name  CSS class or ID of editable area [Optional]
	 * @param  string  $lang  Language  [Optional]
	 */
	public static function editor($name = '.textarea', $lang = 'en')
	{
		self::css('redactor', 'media/css/redactor.css', array('default'), array('weight' => 1));
		self::js('redactor', 'media/js/redactor.min.js', array('jquery'), FALSE, array('weight' => 15));
		self::js('redactor/lang', 'media/js/redactor/langs/'.$lang.'.js', array('jquery'), FALSE, array('weight' => 16));

		self::codes('editor', 'jQuery(document).ready(function(){
					jQuery("'.$name.'").redactor({
						lang: "'.$lang.'",
						minHeight: 300,
						autoresize: false
					});
			});'
		);
	}

	/**
	 * Paste google stats code
	 *
	 * Note: `"\t"` and `PHP_EOL` needed only for convenient output
	 * @param  string  $ua  User Agent ID
	 * @param  string  $site  Site URL without protocol, eg. gleez.com
	 *
	 * @link   https://www.google.com/analytics/
	 *
	 * @todo   DON'T WORK
	 */
	public static function google_stats($ua, $site)
	{
		self::codes('google-stats',
			"\t"."(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){".PHP_EOL.
			"\t"."(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),".PHP_EOL.
			"\t"."m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)".PHP_EOL.
			"\t"."})(window,document,'script','//www.google-analytics.com/analytics.js','ga');".PHP_EOL.
			"\t"."ga('create', '".$ua."', '".$site."');".PHP_EOL.
			"\t"."ga('send', 'pageview');"
		);
	}

	/**
	 * Select2 jQuery plugin
	 *
	 * @link  http://ivaynberg.github.com/select2/index.html
	 */
	public static function select2()
	{
		self::js('select2', 'media/js/select2.min.js', array('jquery'), FALSE, array('weight' => -10));
		self::css('select2','media/css/select2.css');
	}

	/**
	 * DataTables jQuery plugin
	 *
	 * @link  http://datatables.net/
	 */
	public static function datatables()
	{
		self::js('datatables', 'media/js/jquery.dataTables.min.js', array('jquery'), FALSE, array('weight' => -10));
		self::js('greet.dataTables', 'media/js/greet.dataTables.js', array('bootstrap'), FALSE, array('weight' => -6));
		self::css('greet.dataTables', 'media/css/greet.dataTables.css', array('datatables'), array('weight' => -2));
	}

	/**
	 * Sets CSS and JS assets for popup modal windows
	 */
	public static function popup()
	{
		self::css('form', 'media/css/form.css', array('weight' => 2));
		self::css('greet.popup', 'media/css/greet.popup.css', array('bootstrap'), array('media' => 'screen', 'weight' => 15));

		self::js('form', 'media/js/jquery.form.min.js', array('jquery'), FALSE, array('weight' => 15));
		self::js('greet.ajaxform', 'media/js/greet.ajaxform.js', NULL, FALSE, array('weight' => 17));
		self::js('greet.typeahead', 'media/js/greet.typeahead.js', 'gleez');
		self::js('greet.popup', 'media/js/greet.popup.js', array('bootstrap'), FALSE, array('weight' => 20));
	}

	/**
	 * Enforce static usage
	 */
	private function __construct() {}
	private function __clone() {}

	/**
	 * Compiles multiple files into one
	 *
	 * @param  array   $files  The files to compile [Optional]
	 * @param  string  $format The format to return the compiled files in [Optional]
	 * @param  string  $type   The type js or css [Optional]
	 * @return string
	 * @throws Exception
	 * @uses   HTML::style
	 * @uses   HTML::script
	 */
	public static function compile($files = array(), $format = self::FORMAT_TAG, $type = 'js')
	{
		// Compiled contents of file
		$compiled = "";

		// Load config file
		$config = Config::load('media');

		// If no files to compile, no tag necessary
		if (empty($files))
		{
			return;
		}

		// Get filename to save compiled files to
		$compiled_filename = self::get_filename($files, $config['public_dir'], $type);

		// If file doesn't exist already, files have changed, recompile them
		if ( ! file_exists($compiled_filename))
		{
			// Loop through all files
			foreach ($files as $file)
			{
				$file = self::_get_file_path( $file, $type);

				// If file doesn't exist, log the fact and skip
				if ( ! file_exists($file))
				{
					Log::error('Could not find file: [:file]', array(':file' => $file));
					continue;
				}

				// Get contents of file
				$contents = file_get_contents($file);

				// Compress if allowed
				if ($config['compress'])
				{
					// @todo self::minify($contents);
				}

				// Append
				$compiled .= PHP_EOL.$contents;
			}

			// Write new file
			file_put_contents($compiled_filename, $compiled);
		}

		switch ($type)
		{
			case 'css':
				$result = HTML::style($compiled_filename);
				break;

			case 'js':
				$result = HTML::script($compiled_filename);
				break;

			// @todo less

			default:
				throw new Exception("Unknown format: $format.");
		}

		return $result;
	}

	/**
	 * Get file path
	 *
	 * @param   string  $file  File name
	 * @param   string  $type  File type [Optional]
	 * @return  array
	 * @uses    Kohana::find_file
	 */
	protected static function _get_file_path($file, $type = EXT)
	{
		// @todo need to overwrite the assets set and get to fix this
		$file = str_replace(array('media/', '.'.$type), '', $file);

		return Kohana::find_file('media', $file, $type);
	}

	/**
	 * Gets the filename that will be used to save these files
	 *
	 * @param  array   $files The files to be compiled
	 * @param  string  $path  The path to save the compiled file to
	 * @param  string  $type  The mime type css or js to save the compiled file to
	 *
	 * @return string
	 */
	private static function get_filename($files, $path, $type)
	{
		// Most recently modified file
		$last_modified = 0;

		foreach($files as $file)
		{
			$raw_file = self::_get_file_path($file, $type);

			// Check if this file was the most recently modified
			$last_modified = max(filemtime($raw_file), $last_modified);
		}

		if(Theme::$is_admin == TRUE)
		{
			$path = $path.DS.'admin';
		}

		//set unqiue filename based on criteria
		$filename  = $path.DS.$type.DS.$type.'-'.md5(implode("|", $files)).$last_modified.'.'.$type;
		$directory = dirname($filename);

		if ( ! is_dir($directory))
		{
			// Recursively create the directories needed for the file
			System::mkdir($directory, 0777, TRUE);
		}

		return $filename;
	}

}
