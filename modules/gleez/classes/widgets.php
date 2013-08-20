<?php
/**
 * Widgets Core Class
 *
 * This class for handling widget(s) in template regions (sidebar left/right etc).
 *
 * @package    Gleez\Widget
 * @author     Gleez Team
 * @version    1.0.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Widgets {

	/**
	 * Widgets instance
	 * @var array
	 */
	protected static $instance;

	/**
	 * Associative array of widgets
	 * @var string
	 */
	protected $_widgets = array();

	/**
	 * Associative array of widget regions that will be loaded
	 * @var string
	 */
	protected $_regions = array();

	/**
	 * Count of widgets inside a region
	 * @var string
	 */
	protected $_widget_count = array();

	/**
	 * Status of Widgets, if it's already loaded from the database
	 * @var string
	 */
	protected $_loaded = FALSE;

	/**
	 * Region name right|left etc
	 * @var string
	 */
	protected $_region;

	/**
	 * Render style html|json etc
	 * @var string
	 */
	protected $_format;

	/**
	 * Singleton pattern
	 *
	 * @param  string  $region  Region. By default `right`. [Optional]
	 * @param  string  $format  Format. By default `html`. [Optional]
	 * @return Widgets instance
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
	 * @param $region
	 * @param $format
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
	 * @param   string  $region  Widget region
	 * @param   string  $name    Unique widget name
	 * @param   string  $widget  Widget object
	 * @throws  Gleez_Exception
	 * @return  Widget
	 */
	public function add($region, $name, $widget)
	{
		if ( ! is_object($widget))
		{
			throw new Gleez_Exception('Not a valid widget object: :widget', array(':widget' => $name));
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
	 * Retrieves a named widget
	 *
	 * Example:
	 * ~~~
	 * $widget = $region->get('login');
	 * ~~~
	 *
	 * @param   string  $name  Widget name
	 * @return  Widget
	 */
	public function get($name)
	{
		if ( ! isset($this->_widgets[$name]))
		{
			return FALSE;
		}

		return $this->_widgets[$name];
	}

	/**
	 * Remove a widget from the widgets or region from regions
	 *
	 * Example:
	 * ~~~
	 * // Removes right sidebar
	 * $widget = $region->remove('right');
	 *
	 * // Removes login widget
	 * $widget = $region->remove(FALSE, 'login');
	 * ~~~
	 *
	 * @param   string  $region  Region name [Optional]
	 * @param   string  $widget  Widget name [Optional]
	 */
	public function remove($region = NULL, $widget = NULL)
	{
		if ( ! is_null($region))
		{
			if (isset($this->_regions[$region]))
			{
				unset($this->_regions[$region]);
			}
		}

		if ( ! is_null($widget))
		{
			if (isset($this->_widgets[$widget]))
			{
				unset($this->_widgets[$widget]);
			}
		}
	}

	/**
	 * Sets or gets region
	 *
	 * Example:
	 * ~~~
	 * // Sets region to right sidebar
	 * $widget = $region->region('right');
	 * ~~~
	 *
	 * @param   string  $region  Region name [Optional]
	 * @return  $this|string
	 */
	public function region($region = NULL)
	{
		if (is_null($region))
		{
			return $this->_region;
		}

		$this->_region = $region;

		return $this;
	}

	/**
	 * Sets or gets format
	 *
	 * Example:
	 * ~~~
	 * // Sets format to html output
	 * $widget = $region->format('html');
	 * ~~~
	 *
	 * @param   string  $format  Format name [Optional]
	 * @return  $this|string
	 */
	public function format($format = NULL)
	{
		if (is_null($format))
		{
			return $this->_format;
		}

		$this->_format = $format;

		return $this;
	}

	/**
	 * Renders the HTML output of widgets
	 *
	 * @return string
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
	 * @param   string  $region  Theme region [Optional]
	 * @param   string  $format  Widget format [Optional]
	 * @return  string  HTML widgets
	 * @return  boolean If widget not exists
	 */
	public function render($region = NULL, $format = NULL)
	{
		//set region, respect $this->region();
		if ( ! is_null($region))
		{
			$this->region($region);
		}

		//set format, respect $this->format();
		if ( ! is_null($format))
		{
			$this->format($format);
		}

		if ( ! isset($this->_regions[$this->_region]) OR is_null($this->_regions[$this->_region]))
		{
			return FALSE;
		}

		$response = array();

		foreach ($this->_regions[$this->_region] as $id => $name)
		{
			$response[] = $this->get_widget($name, TRUE, $this->_region, $this->_format);
		}

		return trim(implode(PHP_EOL.PHP_EOL, $response));
	}

	/**
	 * Returns the named widget
	 *
	 * @param   string   $name     Name of the widget
	 * @param   boolean  $visible  Visibility permission from widget or FALSE to skip
	 * @param   boolean  $region   The name of the region ex:left, right or FALSE for all regions
	 * @param   boolean  $format   The format of the output ex:xhtml, html or FALSE for object
	 *
	 * @return  object  Widget widget
	 * @return  string  HTML widget
	 */
	public function get_widget($name, $visible = FALSE, $region = FALSE, $format =  FALSE)
	{
		$response = FALSE;

		if ( ! $widget = $this->get($name))
		{
			return $response;
		}

		($visible == TRUE) ? $this->is_visible($widget) : $widget->visible == TRUE;

		// Enable developers to override widget
		Module::event('Widget', $widget);
		Module::event('Widget_'.ucfirst($name), $widget);

		if ($widget->status AND $widget->visible)
		{
			try
			{
				$widget->content = Widget::factory($name, $widget, $widget->config)->render();
				$response = ($format === FALSE) ? $widget : trim($this->_html($widget, $this->_region, $this->_format));
			}
			catch (Exception $e)
			{
				Log::error('Error processing widget: :name', array(':name' => $name));
			}
		}

		return $response;
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
	 * Install the widget into database during module install
	 *
	 * Defaults to inactive widget
	 *
	 * @param  array   $widget  A widget array unique name and title are required
	 * @param  string  $module  The name of the module for this widget
	 */
	public static function install(array $widget, $module)
	{
		if (isset($widget['name']) AND isset($widget['title']))
		{
			// name must be unique
			$values['name']   = @strtolower($widget['name']);
			$values['title']  = (string) $widget['title'];
			$values['module'] = (string) $module;
			$values['status'] = 0;
			$values['region'] = '-1';

			try
			{
				ORM::factory('widget')->values($values)->save();
				Log::debug('Insert widget where module: :module', array(':module' => $module));
			}
			catch (Database_Exception $e)
			{
				Log::error('Unable to insert widgets: :mgs', array(':msg' => $e->getMessage()));
			}
		}
	}

	/**
	 * Remove the widget from database during module uninstall
	 *
	 * @param  string  $module  The name of the module for this widget
	 */
	public static function uninstall($module)
	{
		try
		{
			ORM::factory('widget')->where('module', '=', $module)->delete();
			Cache::instance('widgets')->delete_all();

			Log::info('Deleted widgets where module: :module', array(':module' => $module));
		}
		catch (Database_Exception $e)
		{
			Log::error('Unable to delete widgets: :msg', array(':msg' => $e->getMessage()));
		}
	}

	/**
	 * Load the widgets from database
	 *
	 * @return $this|array|string
	 */
	protected function load()
	{
		// if the widgets have been loaded already, just return it.
		if ($this->_loaded)
		{
			return $this->_widgets;
		}

		$cache = Cache::instance('widgets');

		if ( ! $widgets = $cache->get('widgets'))
		{
			$_widgets = ORM::factory('widget')
				->where('status', '=', '1')
				->order_by('region', 'ASC')
				->order_by('weight', 'ASC')
				->find_all();

			$widgets = array();

			foreach($_widgets as $_widget)
			{
				/** @var $_widget ORM */
				$widgets[] = (object)$_widget->as_array();
			}

			// set the cache
			$cache->set('widgets', $widgets, Date::DAY);
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

		// role based widget access
		if ( ! User::belongsto($widget->roles))
		{
			$widget->visible = FALSE;
		}

		if ($widget->pages)
		{
			$pages = UTF8::strtolower($widget->pages);
			$page_match =  Path::match_path($current_route, $pages);

			$widget->visible = !($widget->visibility XOR $page_match);
		}

		return $widget;
	}

	private function _html($widget, $region = FALSE, $format )
	{
		$zebra = $id = FALSE;

		// Remove empty strings if content is string instead of view object
		if (is_string($widget->content))
		{
			//@todo needs a better way
			$widget->content = trim($widget->content);
		}

		// Don't render any widget if the content is null or empty
		if (empty($widget->content))
		{
			return;
		}

		if ($region)
		{
			// All widgets get an independent counter for each region.
			if ( ! isset($this->_widget_count[$region]))
				$this->_widget_count[$region] = 1;

			// Same with zebra striping.
			$zebra = ($this->_widget_count[$region] % 2) ? 'odd' : 'even';
			$id    = $this->_widget_count[$region]++;
		}

		$widget->name = str_replace('/', '-', $widget->name);
		$widget->menu = (bool)(strpos($widget->name, 'menu-'));

		return View::factory('widgets/' .$format)
			->set('content', $widget->content)
			->set('title',   $widget->title)
			->set('widget',  $widget)
			->set('zebra',   $zebra)
			->set('id', $id)
			->render();
	}
}
