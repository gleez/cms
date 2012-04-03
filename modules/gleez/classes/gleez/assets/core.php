<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Allows assets (CSS, Javascript, etc.) to be included throughout the application, and then outputted later based on dependencies.
 * This makes sure all assets will be included in the correct order, no matter what order they are defined in.
 *
 *     // Call this anywhere in your application, most likely in a template controller
 *     Assets::css('global', 'assets/css/global.css', array('grid', 'reset'), array('media' => 'screen', 'weight' => -10));
 *     Assets::css('reset', 'assets/css/reset.css', NULL, array('weight' => -10));
 *     Assets::css('grid', 'assets/css/grid.css', 'reset');
 *     
 *     Assets::js('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js', NULL, FALSE, array('weight' => -10));
 *     Assets::js('global', 'assets/js/global.js', array('jquery'));
 *     Assets::js('stats', 'assets/js/stats.js', NULL, TRUE);
 *
 *     Assets::codes('alert', 'alert(\'test\')', NULL, FALSE, array('weight' => -10));
 *
 *     Assets::settings('settings', 'settings');
 *     
 *     Assets::group('head', 'keywords', '<meta name="keywords" content="one,two,three,four,five" />');
 *     Assets::group('head', 'description', '<meta name="description" content="Description of webpage here" />');
 *     
 *     // In your view file
 *     <html>
 *         <head>
 *             <title>Kohana Assets</title>
 *             <?php echo Assets::css() ?>
 *             <?php echo Assets::js() ?>
 *             <?php echo Assets::group('head') ?>
 *         </head>
 *         <body>
 *             <!-- Content -->
 *             <?php echo Assets::js(TRUE) ?>
 *         </body>
 *     </html>
 *     
 * @package    Gleez
 * @category   Assets
 * @author     Corey Worrell
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Gleez_Assets_Core {

	// The formats that compile can return.
	const FORMAT_TAG  	= 'tag';
	const FORMAT_FILENAME	= 'filename';
	
	/**
	 * @var  array  CSS assets
	 */
	public static $css = array();
	
	/**
	 * @var  array  Javascript assets
	 */
	public static $js = array();
	
	/**
	* @var array  script blocks
	*/
	public static $codes = array();
	
	/**
	* @var array  settings blocks
	*/
	public static $settings = array();
	
	/**
	 * @var  array  Other asset groups (meta data, links, etc...)
	 */
	public static $groups = array();
	
	/**
	 * CSS wrapper
	 *
	 * Gets or sets CSS assets
	 *
	 * @param   string   Asset name.
	 * @param   string   Asset source
	 * @param   mixed    Dependencies
	 * @param   array    Attributes for the <link /> element
	 * @return  mixed    Setting returns asset array, getting returns asset HTML
	 */
	public static function css($handle = NULL, $src = NULL, $deps = NULL, $attrs = NULL, $format = Assets::FORMAT_TAG)
	{
		if( Kohana::$environment === Kohana::PRODUCTION )
		{
			$format = Assets::FORMAT_FILENAME;
		}
		
		// Return all CSS assets, sorted by dependencies
		if ($handle === NULL)
		{
			return Assets::all_css($format);
		}
		
		// Return individual asset
		if ($src === NULL)
		{
			return Assets::get_css($handle, $format);
		}
		
		// Set default media attribute
		if ( ! isset($attrs['media']) )
		{
			$attrs['media'] = 'all';
		}
	
		$weight = isset($attrs['weight']) ? $attrs['weight'] : 0;
		//unset weight attribute if its set, we processed it already
		if( isset($attrs['weight']) ) unset( $attrs['weight'] );
	
		return Assets::$css[$handle] = array(
			'src'   => $src,
			'deps'  => (array) $deps,
			'attrs' => (array) $attrs,
			'weight' => (int) $weight,
		);
	}
	
	/**
	 * Get a single CSS asset
	 *
	 * @param   string   Asset name
	 * @return  string   Asset HTML
	 */
	public static function get_css($handle, $format = Assets::FORMAT_TAG)
	{
		if ( ! isset(Assets::$css[$handle]))
		{
			return FALSE;
		}
	
		$asset = Assets::$css[$handle];
	
		switch ($format)
		{
			case Assets::FORMAT_TAG:
				return HTML::style($asset['src'], $asset['attrs']);
			break;
			
			case Assets::FORMAT_FILENAME:
				return $asset['src'];
			break;
			
			default:
				throw new Exception("Unknown format: $format.");
		}
	}
	
	/**
	 * Get all CSS assets, sorted by dependencies
	 *
	 * @return   string   Asset HTML
	 */
	public static function all_css( $format = Assets::FORMAT_TAG )
	{
		if (empty(Assets::$css))
		{
			return FALSE;
		}
		
		foreach (Assets::_sort(Assets::$css) as $handle => $data)
		{
			$assets[] = Assets::get_css($handle, $format);
		}
	
		switch ($format)
		{
			case Assets::FORMAT_TAG:
				return implode(PHP_EOL, $assets)."\n";
			break;
			
			case Assets::FORMAT_FILENAME:
				return Assets::compile($assets, $format, 'css');
			break;
			
			default:
				throw new Exception("Unknown format: $format.");
		}
	}
	
	/**
	 * Remove a CSS asset, or all
	 *
	 * @param   mixed   Asset name, or `NULL` to remove all
	 * @return  mixed   Empty array or void
	 */
	public static function remove_css($handle = NULL)
	{
		if ($handle === NULL)
		{
			return Assets::$css = array();
		}
		
		unset(Assets::$css[$handle]);
	}
	
	/**
	 * Javascript wrapper
	 *
	 * Gets or sets javascript assets
	 *
	 * @param   mixed    Asset name if `string`, sets `$footer` if boolean
	 * @param   string   Asset source
	 * @param   mixed    Dependencies
	 * @param   bool     Whether to show in header or footer
	 * @return  mixed    Setting returns asset array, getting returns asset HTML
	 */
	public static function js($handle, $src = NULL, $deps = NULL, $footer = FALSE, $attrs = NULL, $format = Assets::FORMAT_TAG)
	{
		if( Kohana::$environment === Kohana::PRODUCTION )
		{
			$format = Assets::FORMAT_FILENAME;
		}
	
		if ($handle === TRUE OR $handle === FALSE)
		{
			return Assets::all_js($handle, $format);
		}
		
		if ($src === NULL)
		{
			return Assets::get_js($handle, $format);
		}
	
		$weight = isset($attrs['weight']) ? $attrs['weight'] : 0;
		//unset weight attribute if its set, we processed it already
		if( isset($attrs['weight']) ) unset( $attrs['weight'] );
	
		return Assets::$js[$handle] = array(
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
	 * @param   string   Asset name
	 * @return  string   Asset HTML
	 */
	public static function get_js($handle, $format = Assets::FORMAT_TAG)
	{
		if ( ! isset(Assets::$js[$handle]))
		{
			return FALSE;
		}
		
		$asset = Assets::$js[$handle];
	
		switch ($format)
		{
			case Assets::FORMAT_TAG:
				return HTML::script($asset['src']);
			break;
			
			case Assets::FORMAT_FILENAME:
				return $asset['src'];
			break;
			
			default:
				throw new Exception("Unknown format: $format.");
		}
	}
	
	/**
	 * Get all javascript assets of section (header or footer)
	 *
	 * @param   bool   FALSE for head, TRUE for footer
	 * @return  string Asset HTML
	 */
	public static function all_js($footer = FALSE, $format = Assets::FORMAT_TAG)
	{
		if (empty(Assets::$js))
		{
			return FALSE;
		}
		Assets::_init_js();
		$assets = array();
	
		foreach (Assets::$js as $handle => $data)
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
	
		foreach (Assets::_sort($assets) as $handle => $data)
		{
			$sorted[] = Assets::get_js($handle, $format);
		}
	
		switch ($format)
		{
			case Assets::FORMAT_TAG:
				return implode(PHP_EOL, $sorted)."\n";
			break;
			
			case Assets::FORMAT_FILENAME:
				return Assets::compile($sorted);
			break;
			
			default:
				throw new Exception("Unknown format: $format.");
		}
	}
	
	/**
	 * Remove a javascript asset, or all
	 *
	 * @param   mixed   Remove all if `NULL`, section if `TRUE` or `FALSE`, asset if `string`
	 * @return  mixed   Empty array or void
	 */
	public static function remove_js($handle = NULL)
	{
		if ($handle === NULL)
		{
			return Assets::$js = array();
		}
		
		if ($handle === TRUE OR $handle === FALSE)
		{
			foreach (Assets::$js as $handle => $data)
			{
				if ($data['footer'] === $handle)
				{
					unset(Assets::$js[$handle]);
				}
			}
			
			return;
		}
		
		unset(Assets::$js[$handle]);
	}

	/**
	 * Javascript code wrapper
	 *
	 * Gets or sets javascript code
	 *
	 * @param   mixed    Asset name if `string`, sets `$footer` if boolean
	 * @param   string   Asset code
	 * @param   mixed    Dependencies
	 * @param   bool     Whether to show in header or footer
	 * @return  mixed    Setting returns asset array, getting returns asset HTML
	 */
	public static function codes($handle, $code = NULL, $deps = NULL, $footer = FALSE, $attrs = NULL)
	{
		if ($handle === TRUE OR $handle === FALSE )
		{
			return Assets::all_codes($handle);
		}
		
		if ($code === NULL)
		{
			return Assets::get_codes($handle);
		}
	
		$weight = isset($attrs['weight']) ? $attrs['weight'] : 0;
		//unset weight attribute if its set, we processed it already
		if( isset($attrs['weight']) ) unset( $attrs['weight'] );
	
		return Assets::$codes[$handle] = array(
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
	 * @param   string   Asset name
	 * @return  string   Asset HTML
	 */
	public static function get_codes($handle)
	{
		if ( ! isset(Assets::$codes[$handle]))
		{
			return FALSE;
		}
		
		$asset = Assets::$codes[$handle];
		
		return "<script".HTML::attributes(array('type' => 'text/javascript')).'>
		<!--//--><![CDATA['."\n".$asset['code']."\n".'<!--//-->]]></script>';
	}
	
	/**
	 * Get all javascript codes of section (header or footer)
	 *
	 * @param   bool   FALSE for head, TRUE for footer
	 * @return  string Asset HTML
	 */
	public static function all_codes($footer = FALSE)
	{
		if (empty(Assets::$codes))
		{
			return FALSE;
		}
	
		Assets::_init_js();
		$assets = array();
		
		foreach (Assets::$codes as $handle => $data)
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
		
		foreach (Assets::_sort($assets) as $handle => $data)
		{
			$sorted[] = Assets::get_codes($handle);
		}
		
		return implode(PHP_EOL, $sorted)."\n";
	}
	
	/**
	 * Remove a javascript code, or all codes
	 *
	 * @param   mixed   Remove all if `NULL`, section if `TRUE` or `FALSE`, asset if `string`
	 * @return  mixed   Empty array or void
	 */
	public static function remove_code($handle = NULL)
	{
		if ($handle === NULL)
		{
			return Assets::$codes = array();
		}
		
		if ($handle === TRUE OR $handle === FALSE)
		{
			foreach (Assets::$codes as $handle => $data)
			{
				if ($data['footer'] === $handle)
				{
					unset(Assets::$codes[$handle]);
				}
			}
			
			return;
		}
		
		unset(Assets::$codes[$handle]);
	}

	/**
	 * Javascript code settings wrapper
	 *
	 * Gets or sets javascript code
	 *
	 * @param   mixed    Asset name if `string`, sets `$footer` if boolean
	 * @param   string   Asset code
	 * @param   mixed    Dependencies
	 * @param   bool     Whether to show in header or footer
	 * @return  mixed    Setting returns asset array, getting returns asset HTML
	 */
	public static function settings($handle, $code = NULL)
	{	
		return Assets::$settings[$handle] = $code;
	}

	/**
	 * Remove a js settings asset, all of a groups assets, or all group assets
	 *
	 * @param   string   Asset name
	 * @return  mixed    Empty array or void
	 */
	public static function remove_settings($handle = NULL)
	{
		
		if ($handle === NULL)
		{
			return Assets::$settings = array();
		}
		
		unset(Assets::$settings[$handle]);
	}
	
	/**
	 * Group wrapper
	 *
	 * @param   string   Group name
	 * @param   string   Asset name
	 * @param   string   Asset content
	 * @param   mixed    Dependencies
	 * @return  mixed    Setting returns asset array, getting returns asset content
	 */
	public static function group($group, $handle = NULL, $content = NULL, $deps = NULL, $attrs = NULL)
	{
		if ($handle === NULL)
		{
			return Assets::all_groups($group);
		}
		
		if ($content === NULL)
		{
			return Assets::get_group($group, $handle);
		}
	
		$weight = isset($attrs['weight']) ? $attrs['weight'] : 0;
		//unset weight attribute if its set, we processed it already
		if( isset($attrs['weight']) ) unset( $attrs['weight'] );
	
		return Assets::$groups[$group][$handle] = array(
			'content' => $content,
			'deps'    => (array) $deps,
			'attrs'   => (array) $attrs,
			'weight'  => (int) $weight,
		);
	}
	
	/**
	 * Get a single group asset
	 *
	 * @param   string   Group name
	 * @param   string   Asset name
	 * @return  string   Asset content
	 */
	public static function get_group($group, $handle)
	{
		if ( ! isset(Assets::$groups[$group]) OR ! isset(Assets::$groups[$group][$handle]))
		{
			return FALSE;
		}
		
		return Assets::$groups[$group][$handle]['content'];
	}
	
	/**
	 * Get all of a groups assets, sorted by dependencies
	 *
	 * @param  string   Group name
	 * @return string   Assets content
	 */
	public static function all_groups($group)
	{
		if ( ! isset(Assets::$groups[$group]))
		{
			return FALSE;
		}
		
		foreach (Assets::_sort(Assets::$groups[$groups]) as $handle => $data)
		{
			$assets[] = Assets::get_group($group, $handle);
		}
		
		return implode("\n", $assets);
	}
	
	/**
	 * Remove a group asset, all of a groups assets, or all group assets
	 *
	 * @param   string   Group name
	 * @param   string   Asset name
	 * @return  mixed    Empty array or void
	 */
	public static function remove_group($group = NULL, $handle = NULL)
	{
		if ($group === NULL)
		{
			return Assets::$groups = array();
		}
		
		if ($handle === NULL)
		{
			unset(Assets::$groups[$group]);
			return;
		}
		
		unset(Assets::$groups[$group][$handle]);
	}
	
	/**
	 * Sorts assets based on dependencies
	 *
	 * @param   array   Array of assets
	 * @return  array   Sorted array of assets
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
	
	public static function tabledrag($table_id, $action, $relationship, $group, $subgroup = NULL, $source = NULL, $hidden = TRUE, $limit = 0)
	{
		Assets::js('tabledrag', 'media/js/tabledrag.js');
		Assets::css('tabledrap', 'media/css/tabledrag.css');
		
		// If a subgroup or source isn't set, assume it is the same as the group.
		$target = isset($subgroup) ? $subgroup : $group;
		$source = isset($source) ? $source : $target;
		$settings['tableDrag'][$table_id][$group][] = array(
		    'target' => $target,
		    'source' => $source,
		    'relationship' => $relationship,
		    'action' => $action,
		    'hidden' => $hidden,
		    'limit' => $limit,
		  );
		
		Assets::settings( rand(), $settings );
	}

	private static function _init_js()
	{
		if(isset(Assets::$js) OR isset(Assets::$codes) OR isset(Assets::$settings))
		{
			Assets::js('jquery', 'media/js/jquery-1.7.1.min.js', NULL, FALSE, array('weight' => -20));
			Assets::js('jquery_ui', 'media/js/jquery-ui-1.8.13.min.js',array('jquery'),FALSE,array('weight' => -15));
			Assets::js('jquery_once', 'media/js/jquery.once-1.1.js', array('jquery'), FALSE, array('weight' => -10));
			Assets::js('gleez', 'media/js/gleez.js', array('jquery'), FALSE, array('weight' => -5));
		
			$data = array_merge( array(array('basePath' => URL::base(TRUE))), Assets::$settings );
			$code = 'jQuery.extend(Gleez.settings, ' . JSON::encode(
                                                        call_user_func_array('array_merge_recursive', $data)) . ");";
		
			Assets::codes('settings', $code);
		}
	}
	
	/*
	 * Rich text editor, by default Gleez uses CLEditor - WYSIWYG HTML Editor
	 * @link http://premiumsoftware.net/cleditor
	 *
	 * @param 	string  $name css class identifier of the textarea
	 * @param 	string  $width The width of the textarea
	 * @param 	string  $height The height of the textarea
	 * @param 	string  $controls the buttons of the textarea
	 */
	public static function editor($name, $width=NULL, $height=NULL, $controls=NULL)
	{
		$default_controls = 'bold italic underline strikethrough subscript superscript style | bullets numbering | outdent indent | alignleft center alignright justify | undo redo | rule image link unlink | cut copy paste pastetext | print source removeformat';
	
		// Add the core javascipt and css files
		Assets::js('cleditor', 'media/js/cleditor.js', array('jquery'), FALSE, array('weight' => 7));
		Assets::js('cleditorimage', 'media/js/jquery.cleditor.extimage.js', array('cleditor'), FALSE, array('weight' => 8));
		Assets::css('cleditor', 'media/css/cleditor.css');
	
		$width    = empty($width)    ? '500' : $width;
		$height   = empty($height)   ? '250' : $height;
		$controls = empty($controls) ? $default_controls : $controls;
	
		Assets::codes('cleditors', 'jQuery(document).ready(function(){
				jQuery("'.$name.'").cleditor({
					width:"'.$width.'",
					height:"'.$height.'",
					controls: "'.$controls.'",
				});
			      });');
	}
	
	/**
	 * Enforce static usage
	 */	
	private function __contruct() {}
	private function __clone() {}

	/**
	 * Compiles multiple files into one.
	 *
	 * @param array  $files  The files to compile.
	 * @param string $format The format to return the compiled files in.
	 * @param string $type  The type js or css.
	 * 
	 * @return string
	 */
	public static function compile($files = array(), $format = Assets::FORMAT_TAG, $type = 'js')
	{
		// Compiled contents of file
		$compiled = "";
	
		// Load config file
		$config = Kohana::$config->load('media');
        
		// If no files to compile, no tag necessary
		if (empty($files)) return;
        
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
					Kohana::$log->add(LOG::ERROR, "Could not find file: $file");
					continue;
				}
				
				// Get contents of file
				$contents = file_get_contents($file);
				
				// Compress if allowed
				if ($config['compress'])
				{
					//$contents = JSMin::minify($contents);
				}
				
				// Append
				$compiled .= "\n$contents";
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
			
			default:
				throw new Exception("Unknown format: $format.");
		}
		
		return $result;
	}
	
	protected static function _get_file_path($file, $type)
	{
		// @todo need to overwrite the asserts set and get to fix this 
		$file = str_replace( array('media/', '.'.$type), array('', ''), $file);
		return Kohana::find_file('media', $file, $type);
	}
	
	/**
	 * Gets the filename that will be used to save these files.
	 *
	 * @param array  $files The files to be compiled.
	 * @param string $path  The path to save the compiled file to.
	 * @param string $type  The mime type css or js to save the compiled file to.
	 *
	 * @return string
	 */
	private static function get_filename($files, $path, $type)
	{
		// Most recently modified file
		$last_modified = 0;
        
		foreach($files as $file)
		{
			$raw_file = self::_get_file_path( $file, $type);
			// Check if this file was the most recently modified
			$last_modified = max(filemtime($raw_file), $last_modified);
		}
		return "{$path}/{$type}/{$type}-" . md5(implode("|", $files)) . "-{$last_modified}.{$type}";
	}

}