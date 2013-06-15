<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Input Filter
 *
 * Filter object to clean a string.
 * Note: by design, this class does not do any permission checking.
 *
 * @package    Gleez\HTML
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2012 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Gleez_Filter {

	/**
	 * @var  array 
	 */
	protected static $_filters = array();

	/**
	 * @var  bool Indicates whether filters are cached
	 */
	public static $cache = FALSE;
        
	/**
	 * Stores a named filter and returns it. The "action" will always be set to
	 * "index" if it is not defined.
	 *
	 *     Filter::set('html', array('prepare callback' => FALSE, 'process callback' => 'Text::html' ) )
	 *              ->settings(array(
	 *                      'html_nofollow' => true,
	 *                      'allowed_html'  => '<a> <em> <strong> <cite> <blockquote>'
	 *              ));
	 *
	 * @param   string   filter name
	 * @param   array    filter callbacks
	 * @return  Route
	 */
	public static function set($name, $callbacks = array())
	{
		return Filter::$_filters[$name] = new Filter($name, $callbacks);
	}

	/**
	 * Retrieves a named filter.
	 *
	 *     $filter = Filter::get('html');
	 *
	 * @param   string  filter name
	 * @return  Filter
	 * @throws  Gleez_Exception
	 */
	public static function get($name)
	{
		if ( ! isset(Filter::$_filters[$name]))
		{
			throw new Gleez_Exception('The requested filter does not exist: :filter',
				array(':filter' => $name));
		}

		return Filter::$_filters[$name];
	}

	/**
	 * Retrive(s) all named filters.
	 *
	 *     $filters = Filter::all();
	 *
	 * @return  array  filter by name
	 */
	public static function all()
	{
		return Filter::$_filters;
	}
        
        /**
         * Retrive(s) all available formats from config
         *
         * $formats = Filter::formats();
         * @return  array  format by name
         */
	public static function formats()
	{
		$config = Kohana::$config->load('inputfilter');
	
		$formats = array();
		foreach($config->formats as $id => $format)
		{
			$formats[$id] = $format['name'];
		}
	
		return $formats;
	}
        
	/**
	 * Saves or loads the filter cache. If your filters will remain the same for
	 * a long period of time, use this to reload the filters from the cache
	 * rather than redefining them on every page load.
	 *
	 *     if ( ! Filter::cache())
	 *     {
	 *         // Set filters here
	 *         Filter::cache(TRUE);
	 *     }
	 *
	 * @param   boolean $save   cache the current filters
	 * @param   boolean $append append, rather than replace, cached filters when loading
	 * @return  void    when saving filters
	 * @return  boolean when loading filters
	 * @uses    Kohana::cache
	 */
	public static function cache($save = FALSE, $append = FALSE)
	{
		if ($save === TRUE)
		{
			// Cache all defined routes
			Kohana::cache('Filter::cache()', Filter::$_filters);
		}
		else
		{
			if ($filters = Kohana::cache('Filter::cache()'))
			{
				if ($append)
				{
					// Append cached filters
					Filter::$_filters += $filters;
				}
				else
				{
					// Replace existing filters
					Filter::$_filters = $filters;
				}

				// Filters were cached
				return Filter::$cache = TRUE;
			}
			else
			{
				// Filters were not cached
				return Filter::$cache = FALSE;
			}
		}
	}

	/**
	 * Method to run all enabled filters by the format id on given string 
         *
         * @param  object  $text       The text object to be filtered.
         * @return string  $text       The filtered text
         */
	public static function process($text)
	{
		$config = Kohana::$config->load('inputfilter');
		if(!array_key_exists($text->format, $config->get('formats') ) OR !isset($text->format))
		{
			//make sure a valid format id exists, if not set default format id
			$text->format = (int) $config->get('default_format', 1);
		}

		$filters = $config->formats[$text->format]['filters'];
		$filter_info = Filter::all();
	
		//sort filters by weight
		$filters = Arr::array_sort($filters, 'weight');
	
		// Give filters the chance to escape HTML-like data such as code or formulas.
		foreach ($filters as $name => $filter)
		{
			$prepare_callback = $filter_info[$name]->prepare_callback;
			if ($filter['status'] AND !empty($prepare_callback))
			{
				$text->text = Filter::execute( $prepare_callback, $text->text, $text->format, $filter );
			}
		}
	
		// Perform filtering
		foreach ($filters as $name => $filter)
		{
			$process_callback = $filter_info[$name]->process_callback;
			if ($filter['status'] AND !empty($process_callback))
			{
				$text->text = Filter::execute( $process_callback, $text->text, $text->format, $filter );
			}
		}
		
		return $text->text;
	}
        
        /**
         * Execute a filter on the given text
         *
         * @param  mixed   $callback   The callback to be executed.
         * @param  string  $text       The text to be filtered.
         * @param  string  $format     The format id of the text to be filtered.
         * @param  object  $filter     The filter object.
         *
         * @return string  $text       The filtered text
         */
        public static function execute($callback, $text, $format, $filter)
        {
                $args = func_get_args();
                array_shift($args);
        
                if (is_string($callback) AND strpos($callback, '::') !== FALSE)
		{
			// Make the static callback into an array
			$callback = explode('::', $callback, 2);
		}
        
                if ($callback AND is_callable($callback))
		{
                        try
                        {
                            return  call_user_func_array($callback, $args);
                        }
                        catch (Exception $e)
                        {
                                Kohana::$log->add(Log::ERROR, __('Filter callback :class for :filter',
                                                                 array(':class' => $e->getMessage(), 'filter' => $filter['name'])));
                                return $text;
                        }
                }
        
                return $text;
        }
        
	/**
	 * @var  string  Filter Title
	 */
	protected $_title = '';
        
	/**
	 * @var  callbacks     The prepare and process callbacks for filter
	 */
	protected $_callbacks = array('prepare callback' => FALSE, 'process callback' => FALSE);

        /**
	 * @var  array Filter Settings
	 */
	protected $_settings = array();

	/**
	 * @var  string  Filter Description
	 */
	protected $_description = '';
        
        /**
	 * @param   string   filter title
	 * @param   array    filter callbacks
	 * @return  void
	 */
	public function __construct($title, $callbacks = array())
	{
                $this->_title = $title;
                $this->_callbacks = $callbacks;
        }

        public function __get($key)
        {
                if($key == 'title')
                {
                        return $this->_title;
                }
                else if($key == 'description')
                {
                        return $this->_description;
                }
                else if($key == 'prepare_callback')
                {
                        return $this->_callbacks['prepare callback'];
                }
                else if($key == 'process_callback')
                {
                        return $this->_callbacks['process callback'];
                }
                else if($key == 'callbacks')
                {
                        return $this->_callbacks;
                }
                else if($key == 'settings')
                {
                        return $this->_settings;
                }
                else
                {
                        throw new Gleez_Exception('The requested property does not exist: :key',
				array(':key' => $key));
                }
        }
        
	/**
	 * Set or get callbacks for filter
	 *
	 *     $filter->callbacks(array(
	 *         'prepare callback'   => FALSE,
	 *         'process callback'   => 'Text::html'
	 *     ));
	 * 
	 * If no parameter is passed, this method will act as a getter.
	 *
	 * @param   array  key values
	 * @return  $this or array
	 */
	public function callbacks(array $callbacks = NULL)
	{
		if ($callbacks === NULL)
		{
			return $this->_callbacks;
		}

		$this->_callbacks = $callbacks;

		return $this;
	}

	/**
	 * Set or get settings for filter
	 *
	 *     $filter->settings(array(
	 *         'html_nofollow' => true,
	 *         'allowed_html'  => '<a> <em> <strong> <cite> <blockquote>'
	 *     ));
	 * 
	 * If no parameter is passed, this method will act as a getter.
	 *
	 * @param   array  key values
	 * @return  $this or array
	 */
	public function settings(array $settings = NULL)
	{
		if ($settings === NULL)
		{
			return $this->_settings;
		}

		$this->_settings = $settings;

		return $this;
	}

        /**
	 * Set or get title for filter
	 *
	 *     $filter->title(__('Limit allowed HTML tags'));
	 * 
	 * If no parameter is passed, this method will act as a getter.
	 *
	 * @param   string  title
	 * @return  $this or string
	 */
	public function title($title = NULL)
	{
		if ($title === NULL)
		{
			return $this->_title;
		}

		$this->_title = $title;

		return $this;
	}

        /**
	 * Set or get description for filter
	 *
	 *     $filter->description(__('Allowed HTML tags'));
	 * 
	 * If no parameter is passed, this method will act as a getter.
	 *
	 * @param   string  description
	 * @return  $this or string
	 */
	public function description($description = NULL)
	{
		if ($description === NULL)
		{
			return $this->_description;
		}

		$this->_description = $description;

		return $this;
	}
        
}