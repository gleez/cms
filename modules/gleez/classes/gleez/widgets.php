<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Widgets Core Class
 *
 * This class for handling widget(s) in template regions (sidebar left/right etc).
 *
 * @package    Gleez\Widget
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
abstract class Gleez_Widgets {

	/**
	 * Widgets instances
	 *
	 * @var array
	 */
	protected static $instance;

	/**
	 * Associative array of widgets
	 * 
	 * @var string
	 */
	protected $_widgets = array();

	/**
	 * Associative array of widget regions that will be loaded
	 * 
	 * @var string
	 */
	protected $_regions = array();

	/**
	 * count of widgets inside a region
	 *
	 * @var string
	 */
	protected $_widget_count = array();

	/**
	 * status of Widgets, if it's already loaded from the database
	 *
	 * @var string
	 */
	protected $_loaded = FALSE;

	/**
	 * @var  string  region name right/left etc
	 */
	protected $_region;

	/**
	 * @var  string  render style html/json etc
	 */
	protected $_format;

	/**
	 * Singleton pattern
	 * @param  string  $region  Region. By default `right`. [Optional]
	 * @param  string  $format  Format. By default `html`. [Optional]
	 * @return Region
	 */
	public static function instance($region = 'right', $format = 'html')
	{	
		if ( ! isset(Widgets::$instance))
		{
			new Widgets($region, $format);
		}

		return Widgets::$instance;
	}

	/**
	 * Constructor, globally sets region and format
	 *
	 * @return  void
	 */
	public function __construct($region, $format)
	{
		// Store the region locally
		$this->_region = $region;
		
		// Store the format locally
		$this->_format = $format;
	
		// Load the widgets from database
		$this->load();
	
		// Store the widgets instance
		Widgets::$instance = $this;
	}

	/**
	 * Add's a new widget to the widgets
	 *
	 * @chainable
	 * @param   string   widget region
	 * @param   string   Unique widget name
	 * @param   string   widget object
	 * @return  Widget
	 */
	public function add($region, $name, $widget)
	{
		if( ! is_object($widget))
		{
			throw new Kohana_Exception('Not a valid widget object: :widget', array(':widget' => $name));
		}
	
		if ( ! isset($this->_regions[$region]))
		{
			$this->_regions[$region] = array();
		}

		array_push($this->_regions[$region], $name);
	
		// set default widget members
		$widget->config = FALSE;
		$widget->content = FALSE;
		$widget->visible = TRUE;

		$this->_widgets[$name] = $widget;
		
		return $this;
	}

	/**
	 * Retrieves a named widget.
	 *
	 *     $widget = $region->get('login');
	 *
	 * @param   string  widget name
	 * @return  Widget
	 */
	public function get($name)
	{
		if ( ! isset($this->_widgets[$name]) ) return FALSE;

		return $this->_widgets[$name];
	}

	/**
	 * Remove a widget from the widgets or region from regions
	 *
	 *	$widget = $region->remove('right'); // removes right sidebar
	 *
	 *      $widget = $region->remove(FALSE, 'login'); // removes login widget
	 *	
	 * @param   string   region name
	 * @param   string   widget name
	 * @return  void
	 */
	public function remove($region = FALSE, $widget = FALSE)
	{
		if($region)
		{
			if( isset($this->_regions[$region]) ) unset($this->_regions[$region]);
		}
	
		if($widget)
		{
			if( isset($this->_widgets[$widget]) ) unset($this->_widgets[$widget]);
		}
	}

	/**
	 * sets/gets region.
	 *
	 *     $widget = $region->region('right'); // Sets region to right sidebar
	 *     
	 * @chainable
	 * @param   string  region name
	 * @return  Region
	 */
	public function region($region = NULL)
	{
		if($region === NULL) return $this->_region;
		if($region) $this->_region = $region;
	
		return $this;
	}

	/**
	 * sets/gets format.
	 *
	 *     $widget = $region->format('html'); //Sets format to html output
	 *     
	 * @chainable
	 * @param   string  format name
	 * @return  Region
	 */
	public function format($format = NULL)
	{
		if($format === NULL) return $this->_format;
		if($format) $this->_format = $format;

		return $this;
	}

	/**
	 * Renders the HTML output of widgets
	 *
	 * @return   string
	 */
	public function __toString()
	{
		try
		{
			return $this->render();
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}
	}

	/**
	 * Renders the HTML output for the widgets
	 *
	 * @return  string  HTML widgets
	 */
	public function render($region = FALSE, $format =  FALSE)
	{
		//set region, respect $this->region();
		if($region) $this->region($region);

		//set format, respect $this->format();
		if($format) $this->format($format);
	
		if ( ! isset($this->_regions[$this->_region]) OR $this->_regions[$this->_region] === NULL )
			return false;

		$response = array();
		foreach ($this->_regions[$this->_region] as $id => $name)
		{
			$response[] = $this->get_widget($name, TRUE, $this->_region, $this->_format);
		}

		return trim( implode("\n\n", $response) );
	}

	/**
	 * Returns the named widget
	 *
	 * @chainable
	 * @param   name	string  name of the widget
	 * @param   visible	bool    visibility permisson from widget or FALSE to skip
	 * @param   region	string  The name of the region ex:left, right or FALSE for all regions
	 * @param   format	bool    The format of the output ex:xhtml, html or FALSE for object
	 * 
	 * @return  mixed   object/string  Widget widget/HTML widget
	 */
	public function get_widget($name, $visible = FALSE, $region = FALSE, $format =  FALSE)
	{
		$response = FALSE;
		if ( ! $widget = $this->get($name) ) return;
		($visible == TRUE) ? $this->is_visible($widget) : $widget->visible == TRUE;

		// Enable developers to override widget
		Module::event('widget', $widget);
		Module::event("widget_{$widget->name}", $widget);
	
		if ( $widget->status AND $widget->visible ) 
		{
			try
			{
				$widget->content = Widget::factory($name, $widget, $widget->config)->render();
				$response = ($format === FALSE) ? $widget : $this->_html($widget, $this->_region, $this->_format);
			}
			catch( Exception $e )
			{
				Kohana::$log->add(LOG::ERROR, 'Error processing widget: :name', array( ':name' => $name ));
			}

		}

		return trim( $response );
	}

	/**
	 * Nicely outputs contents of $this->_widgets for debugging info
	 *
	 * @return   string
	 */
	public function debug()
	{
		return Debug::vars($this->_widgets);
	}

	/**
	 * Register the widget into database during module install
	 */
	public static function register($widget) {}

	/**
	 * Remove the widget from database during module uninstall
	 */
	public static function deregister($module)
	{
		try
		{
			DB::delete('widgets')->where('module', '=', $module)->execute();
			Kohana::$log->add(LOG::DEBUG, 'Deleted widgets where module: :module', array(
					':module' => $module
			));
		}
		catch (Database_Exception $e)
		{
			Kohana::$log->add(LOG::DEBUG, __('Unable to Delete widgets, Error :error', array(
					':error' => $e->getMessage()
			)));
		}
	}

	/*
	 * Load the widgets from database
	 */
	protected function load()
	{
		//if the widgets have been loaded already, just return it.
		if ($this->_loaded) return $this->_widgets;
	
		$cache = Cache::instance('widgets');

		if( ! $widgets = $cache->get('widgets'))
		{
			$_widgets = ORM::factory('widget')
					->where('status', '=', '1')
					->order_by('region', 'ASC')
					->order_by('weight', 'ASC')
					->find_all();

			$widgets = array();
			foreach($_widgets as $_widget)
			{
				$widgets[] = (object)$_widget->as_array();
			}

			//set the cache
			$cache->set('widgets', $widgets, DATE::DAY);
		}

		foreach ($widgets as $widget)
		{
			$this->add($widget->region, $widget->name, $widget);
		}

		$this->_loaded = TRUE;
		return $this;
	}

	protected function is_visible($widget)
	{
		static $current_route;
		$widget->visible = TRUE;

		if (is_null($current_route))
		{
			$current_route = Request::current()->uri();
			$current_route = UTF8::strtolower($current_route);
		}

		//role based widget access
		if ( ! User::belongsto($widget->roles)) $widget->visible = FALSE;

		if($widget->pages)
		{
			$pages = UTF8::strtolower($widget->pages);
			$page_match =  Path::match_path($current_route, $pages);
		
			$widget->visible = !($widget->visibility xor $page_match);
		}

		return $widget;
	}

	private function _html($widget, $region = FALSE, $format )
	{
		$zebra = $id = FALSE;

		if( empty($widget->content) || !$widget->content) return;

		if( $region )
		{
			// All widgets get an independent counter for each region.
			if ( ! isset($this->_widget_count[$region]) )
				$this->_widget_count[$region] = 1;
				
			// Same with zebra striping.
			$zebra = ($this->_widget_count[$region] % 2) ? 'odd' : 'even';
			$id    = $this->_widget_count[$region]++;
		}

		//replace '/' with '-' for name in css
		$widget->name = str_replace('/', '-', $widget->name);
		$widget->menu = ( strpos($widget->name, 'menu-')  === false ) ? FALSE : TRUE;
		
				return View::factory('widgets/' .$format)
				->set('content', $widget->content)
				->set('title',   $widget->title)
				->set('widget',  $widget)
				->set('zebra',   $zebra)
				->set('id', $id)
				->render();
	}

}