<?php
/**
 * Abstract template class for automatic templating
 *
 * @package    Gleez\Template
 * @author     Gleez Team
 * @version    1.2.2
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
abstract class Template extends Controller {

	/**
	 * Page template
	 * @var string
	 */
	public $template = 'template';

	/**
	 * Auto render template?
	 * @var boolean
	 */
	public $auto_render = TRUE;

	/**
	 * Turn debugging on?
	 * @var boolean
	 */
	public $debug = FALSE;

	/**
	 * The site name
	 * @var string
	 */
	public $site_name;

	/**
	 * The page title
	 * @var string
	 */
	public $title = NULL;

	/**
	 * The page sub title
	 * @var string
	 */
	public $subtitle = FALSE;
	
	/**
	 * The delimiter page header and site name
	 * @var string
	 */
	public $title_separator;

	/**
	 * The page title icon
	 * @var string
	 */
	public $icon = FALSE;

	/**
	 * The sidebar content
	 * @var array
	 */
	protected $_regions = array();

	/**
	 * Is ajax request?
	 * @var boolean
	 */
	protected $_ajax = FALSE;

	/**
	 * is internal request?
	 * @var boolean
	 */
	protected $_internal = FALSE;

	/**
	 * The configuration settings
	 * @var Config
	 */
	protected $_config;

	/**
	 * The Auth instance
	 * @var Auth
	 */
	protected $_auth;

	/**
	 * The Widgets Object
	 * @var object
	 */
	protected $_widgets;

	/**
	 * An Format instance
	 * @var Format
	 */
	protected $_format;

	/**
	 * The destination url
	 * @var array
	 */
	protected $_desti;

	/**
	 * The destination url
	 * @var array
	 */
	protected $redirect;

	/**
	 * Current page class
	 * @var string
	 */
	protected $_page_class;

	/**
	 * Current page id, defaults to controller name
	 * @var string
	 */
	protected $_page_id;

	/**
	 * Tabs navigation
	 * @var array
	 */
	protected $_tabs;

	/**
	 * Sub Tabs navigation
	 * @var array
	 */
	protected $_subtabs;
	
	/**
	 * Quick Links navigation
	 * @var  array
	 */
	protected $_actions;
	
	/**
	 * Benchmark token
	 * @var string
	 */
	protected $_benchmark;

	/**
	 * Hold the response format for this request
	 * @var string
	 */
	protected $_response_format;

	/**
	 * List all supported formats for this controller
	 * (accept-type => path to format template)
	 * @var array
	 */
	protected $_accept_formats = array(
		'text/html'             => 'html',
		'application/xhtml+xml' => 'xhtml',
		'application/xml'       => 'xml',
		'application/json'      => 'json',
		'application/csv'       => 'csv',
		'text/plain'            => 'php',
		'text/javascript'       => 'jsonp',
		'*/*'                   => 'xhtml' //ie7 ie8
	);

	/**
	 * Enable sidebars for this request?
	 * For example: add or edit page don't requires sidebars
	 * @var boolean
	 */
	protected $_sidebars = TRUE;

	/**
	 * Datatable Object.
	 * @var object
	 */
	protected $_datatables;

	/**
	 * An array of form error messages to be displayed to the user.
	 * @var array
	 */
	protected $_errors = array();

	/**
	 * If JSON is going to be delivered to the client,
	 * this property will hold the values being sent.
	 * @var array
	 */
	protected $_json;

	/**
	 * Allows overriding 'FormSaved' property to send with JSON.
	 * @var boolean
	 */
	protected $_formsaved = FALSE;

	/**
	 * Loads the template View object, if it is direct request
	 *
	 * @return  void
	 * @throws  Http_Exception_415  If none of the accept-types are supported
	 */
	public function before()
	{
		// Execute parent::before first
		parent::before();

		if (Kohana::$profiling)
		{
			// Start a new benchmark token
			$this->_benchmark = Profiler::start('Gleez', ucfirst($this->request->controller()) .' Controller');
		}

		// Test whether the current request is command line request
		if (Kohana::$is_cli)
		{
			$this->_ajax       = FALSE;
			$this->auto_render = FALSE;
		}

		// Test whether the current request is the first request
		if ( ! $this->request->is_initial())
		{
			$this->_internal   = TRUE;
			$this->auto_render = FALSE;
		}

		// Test whether the current request is ajax request
		if ($this->request->is_ajax())
		{
			$this->_ajax       = TRUE;
			$this->auto_render = FALSE;
		}

		// Test whether the current request is datatables request
		if (Request::is_datatables())
		{
			$this->_ajax       = TRUE;
			$this->auto_render = FALSE;
		}

		$this->response->headers('X-Powered-By', Gleez::getVersion(TRUE, TRUE) . ' (' . Gleez::CODENAME . ')');

		$this->_config = Kohana::$config->load('site');
		$this->_auth   = Auth::instance();

		// Get desired response formats
		$accept_types = Request::accept_type();
		$accept_types = Arr::extract($accept_types, array_keys($this->_accept_formats));

		// Set response format to first matched element
		$this->_response_format = $this->request->headers()->preferred_accept(array_keys($this->_accept_formats));

		$site_name = Template::getSiteName();
		$url       =  URL::site(NULL, TRUE);

		View::bind_global('site_name', $site_name);
		View::bind_global('site_url',  $url);

		if ($this->auto_render)
		{
			// Throw exception if none of the accept-types are supported
			if ( ! $accept_types = array_filter($accept_types))
			{
				throw new Http_Exception_415('Unsupported accept-type', 415);
			}

			// Initiate a Format instance
			$this->_format = Format::instance();

			// Load the template
			$this->template         = View::factory($this->template);

			$this->title_separator  = $this->_config->get('title_separator', ' | ');
			$this->_widgets         = Widgets::instance();
			$this->template->_admin = Theme::$is_admin;

			// Set the destination & redirect url
			$this->_desti = array(
				'destination' => $this->request->uri()
			);

			$this->redirect = ($this->request->query('destination') !== NULL) ? $this->request->query('destination') : array();

			// Bind the generic page variables
			$this->template->set('site_name', Template::getSiteName())
				->set('site_slogan',   $this->_config->get('site_slogan', __('Innovate IT')))
				->set('site_url',      URL::site(NULL, TRUE))
				->set('site_logo',     $this->_config->get('site_logo', FALSE))
				->set('sidebar_left',  array())
				->set('sidebar_right', array())
				->set('column_class',  '')
				->set('main_column',   12)
				->set('head_title',    $this->title)
				->set('title',         $this->title)
				->set('subtitle',      $this->subtitle)
				->set('icon',          $this->icon)
				->set('front',         FALSE)
				->set('mission',       FALSE)
				->set('tabs',          FALSE)
				->set('subtabs',       FALSE)
				->set('actions',       FALSE)
				->set('_user',         $this->_auth->get_user())
				->bind('datatables',   $this->_datatables);

			// Page Title
			$this->title = ucwords($this->request->controller());

			// Assign the default css files
			$this->_set_default_css();

			// Assign the default js files
			$this->_set_default_js();

			// Set default server headers
			$this->_set_default_server_headers();

			// Set default meta data and media
			$this->_set_default_meta_links();
			$this->_set_default_meta_tags();

			/**
			 * Make your view template available to all your other views
			 * so easily you could access template variables
			 */
			View::bind_global('template', $this->template);
		}

		if (Kohana::$environment === Kohana::DEVELOPMENT)
		{
			Log::debug('Executing Controller [:controller] action [:action]',
				array(
					':controller' => $this->request->controller(),
					':action'     => $this->request->action()
			));
		}
	}

	/**
	 * If debugging is enabled, append profiler stats for non-production environments.
	 *
	 * @return  void
	 */
	public function after()
	{
		if ($this->auto_render)
		{
			// Controller name as the default page id if none set
			empty($this->_page_id) AND $this->_page_id = $this->request->controller();

			// Load left and right sidebars if available
			$this->_set_sidebars();

			// Set appropriate column css class
			$this->_set_column_class();

			// Do some CSS magic to page class
			$classes   = array(
				I18n::$lang,
				$this->request->controller(),
				$this->request->action(),
				$this->request->controller() . '-' . $this->request->action(),
				$this->template->column_class,
				$this->_page_class,
				($this->_auth->logged_in()) ? 'logged-in' : 'not-logged-in'
			);

			// Special check for frontpage and frontpage title
			if ($this->is_frontpage())
			{
				// Set front variable true for themers
				$this->template->front = TRUE;
				// Don't show title on homepage
				$this->template->title = FALSE;
				// Don't show title on homepage
				$this->title           = FALSE;

				$this->template->mission = __($this->_config->get('site_mission', ''));
			}

			View::set_global(array(
				'is_front' => $this->template->front,
				'is_admin' => $this->template->_admin
			));

			$classes[]  = $this->template->_admin ? 'backend' : 'frontend';
			$classes[]  = ($this->template->front) ? 'front' : 'not-front';
			$page_class = implode(' ', array_unique(array_map('trim', $classes)));

			// Construct Head Section Page title
			$this->_set_head_title();

			// Allow module and theme developers to override
			Module::event('template', $this);

			// Set primary menu
			$primary_menu = Menu::links('main-menu', array(
				'class' => 'menus nav'
			));

			// Bind the generic page variables
			$this->template->set('lang', I18n::$lang)
				->set('page_id',      $this->_page_id)
				->set('page_class',   $page_class)
				->set('primary_menu', $primary_menu)
				->set('title',        $this->title)
				->set('subtitle',     $this->subtitle)
				->set('icon',         $this->icon)
				->set('mission',      $this->template->mission)
				->set('content',      $this->response->body())
				->set('messages',     Message::display())
				->set('profiler',     FALSE);

			if (count($this->_tabs) > 0)
			{
				$this->template->tabs = View::factory('tabs')->set('tabs', $this->_tabs);
			}

			if (count($this->_subtabs) > 0)
			{
				$this->template->subtabs = View::factory('tabs')->set('tabs', $this->_subtabs);
			}
	
			if (count($this->_actions) > 0)
			{
				$this->template->actions = View::factory('actions')->set('actions', $this->_actions);
			}

			// And profiler if debug is true
			if (Kohana::$environment !== Kohana::PRODUCTION AND $this->debug)
			{
				$this->template->profiler = View::factory('profiler/stats');
			}

			// And finally the profiler stats
			$this->_set_profiler_stats();

			// Assign the template as the request response and render it
			$this->response->body($this->template);
		}
		elseif ($this->_ajax)
		{
			$output = $this->response->body();
			$this->process_ajax();

			if ($this->_response_format === 'application/json')
			{
				// Check for dataTables request
				if ($this->request->query('sEcho') !== NULL) return;

				$output = $this->_json['Data'];
			}

			$this->response->body($output);
		}
		elseif ($this->_internal)
		{
			$output = $this->response->body();
			$this->response->body($output);
		}

		if (isset($this->_benchmark))
		{
			// Stop the benchmark
			Profiler::stop($this->_benchmark);
		}

		// Set header content-type to response format with utf-8
		$this->response->headers('Content-Type', $this->_response_format . '; charset=' . Kohana::$charset);

		parent::after();
	}

	/**
	 * Set the page title
	 */
	protected function _set_head_title()
	{
		if ($this->title)
		{
			$head_title = array(
				strip_tags($this->title),
				$this->template->site_name
			);
		}
		else
		{
			$head_title = array($this->template->site_name);

			if ($this->template->site_slogan)
			{
				$head_title[] = $this->template->site_slogan;
			}
		}

		$this->template->head_title = implode($this->title_separator, $head_title);
	}

	/**
	 * Set the default server headers
	 */
	protected function _set_default_server_headers()
	{
		$headers = $this->_config->get('headers', array());
		$headers['X-Gleez-Version'] = Gleez::getVersion(TRUE, TRUE) . ' ('.Gleez::CODENAME.')';

		$xmlrpc = $this->_config->get('xmlrpc', NULL);

		if ( ! is_null($xmlrpc))
		{
			$headers['X-Pingback'] = URL::site($xmlrpc, TRUE);
		}

		$this->_set_server_headers($headers);
	}

	/**
	 * Set the server headers
	 *
	 * @param  array $headers  An associative array of server headers
	 */
	protected function _set_server_headers($headers)
	{
		if (is_array($headers) AND ! empty($headers))
		{
			$this->response->headers($headers);
		}
	}

	/**
	 * Set the default meta links
	 *
	 * Used configuration settings.
	 *
	 * @uses    Meta::links
	 * @uses    Arr::get
	 */
	protected function _set_default_meta_links()
	{
		$meta  = $this->_config->get('meta', array());
		$links = Arr::get($meta, 'links');

		if ($links)
		{
			foreach ($links as $url => $attributes)
			{
				Meta::links($url, $attributes);
			}
		}
	}

	/**
	 * Set the default meta tags
	 *
	 * Using configuration settings.
	 *
	 * @uses    Meta::tags
	 * @uses    Arr::get
	 * @uses    Arr::merge
	 */
	protected function _set_default_meta_tags()
	{
		$meta        = $this->_config->get('meta', array());
		$keywords    = $this->_config->get('keywords', '');
		$description = $this->_config->get('description', '');

		$tags = Arr::get($meta, 'tags');
		$tags = Arr::merge($tags, array('keywords' => $keywords), array('description' => $description));

		if ($tags)
		{
			foreach ($tags as $handle => $value)
			{
				$conditional = NULL;

				if (is_array($value))
				{
					$conditional = Arr::get($value, 'conditional');
					$value       = Arr::get($value, 'value', '');
				}

				$attrs = array();

				if (isset($conditional))
				{
					$attrs['conditional'] = $conditional;
				}

				Meta::tags($handle, $value, $attrs);
			}
		}
	}

	/**
	 * Add sidebars
	 *
	 * This method is chainable.
	 */
	protected function _set_sidebars()
	{
		if ($this->_sidebars !== FALSE)
		{
			$this->template->sidebar_left  = $this->_widgets->render('left');
			$this->template->sidebar_right = $this->_widgets->render('right');
		}

		return $this;
	}

	/**
	 * Add sidebar column class
	 *
	 * This method is chainable.
	 */
	protected function _set_column_class()
	{
		$sidebar_left  = $this->template->sidebar_left;
		$sidebar_right = $this->template->sidebar_right;

		if ( ! empty($sidebar_left) AND ! empty($sidebar_right))
		{
			$this->template->column_class = 'main-both';
			$this->template->main_column  = 6;
		}
		else
		{
			if ( ! empty($sidebar_left))
			{
				$this->template->column_class = 'main-left';
				$this->template->main_column  = 9;
			}
			if ( ! empty($sidebar_right))
			{
				$this->template->column_class = 'main-right';
				$this->template->main_column  = 9;
			}
		}

		return $this;
	}

	/**
	 * Set default CSS
	 *
	 * @uses  Assets::css
	 */
	protected function _set_default_css()
	{
		$theme = Theme::$active;
		Assets::css('bootstrap', 'media/css/bootstrap.min.css', NULL, array('weight' => -15));
		Assets::css('font-awesome', 'media/css/font-awesome.min.css',  array('bootstrap'), array('weight' => -13));
		Assets::css('default', 'media/css/default.css', NULL, array('weight' => 0));
		Assets::css('theme', "media/css/{$theme}.css", array('default'), array('weight' => 20));
	}

	/**
	 * Set default JavaScript
	 *
	 * @uses  Assets::js
	 */
	protected function _set_default_js()
	{
		Assets::js('bootstrap', 'media/js/bootstrap.min.js', array('jquery'), FALSE, array('weight' => -8));

		// Google js only in production and not in admin section
		if (Kohana::PRODUCTION === Kohana::$environment AND Theme::$is_admin === FALSE)
		{
			$ua = $this->_config->get('google_ua', NULL);

			if ( ! empty($ua) )
			{
				Assets::google_stats($ua, $this->_config->get('site_url'));
			}
		}
	}

	/**
	 * Returns TRUE if the POST has a valid CSRF
	 *
	 * Usage:<br>
	 * <code>
	 * 	if ($this->valid_post('upload_photo')) { ... }
	 * </code>
	 *
	 * @param   string|NULL  $submit Submit value [Optional]
	 * @return  boolean  Return TRUE if it's valid $_POST
	 *
	 * @uses    Request::is_post
	 * @uses    Request::post_max_size_exceeded
	 * @uses    Request::get_post_max_size
	 * @uses    Request::post
	 * @uses    Message::error
	 * @uses    CSRF::valid
	 * @uses    Captcha::valid
	 */
	public function valid_post($submit = NULL)
	{
		if ( ! $this->request->is_post())
		{
			return FALSE;
		}

		if (Request::post_max_size_exceeded())
		{
			$this->_errors = array('_action' => __('Max file size of :max Bytes exceeded!',
				array(':max' => Request::get_post_max_size())) );
			return FALSE;
		}

		if ( ! is_null($submit) )
		{
			if ( ! isset($_POST[$submit]))
			{
				$this->_errors = array('_action' => __('This form has altered. Please try submitting it again.'));
				return FALSE;
			}
		}

		$_token  = $this->request->post('_token');
		$_action = $this->request->post('_action');

		$has_csrf = ! empty($_token) AND ! empty($_action);
		$valid_csrf = CSRF::valid($_token, $_action);

		if ($has_csrf AND ! $valid_csrf)
		{
			// CSRF was submitted but expired
			$this->_errors = array('_token' => __('This form has expired. Please try submitting it again.'));
			return FALSE;
		}

		if (isset($_POST['_captcha']))
		{
			$captcha = $this->request->post('_captcha');
			if (empty($captcha))
			{
				// CSRF was not entered
				$this->_errors = array('_captcha' => __('The security code can\'t be empty.'));
				return FALSE;
			}
			elseif ( ! Captcha::valid($captcha))
			{
				$this->_errors = array('_captcha' => __('The security answer was wrong.'));
				return FALSE;
			}
		}

		return $has_csrf AND $valid_csrf;
	}

	/**
	 * Set the profiler stats into template
	 *
	 * @uses  Profiler::groups
	 *
	 * @link  http://php.net/manual/en/function.number-format.php  number_format
	 * @link  http://php.net/manual/en/function.get-included-files.php  get_included_files
	 */
	protected function _set_profiler_stats()
	{
		$queries = 0;

		if (Kohana::$profiling)
		{
			// DB queries
			foreach (Profiler::groups() as $group => $benchmarks)
			{
				if (strpos($group, 'database') === 0)
				{
					$queries += count($benchmarks);
				}
			}
		}

		// Get the total memory and execution time
		$total = array(
			'{memory_usage}'     => number_format((memory_get_peak_usage() - GLEEZ_START_MEMORY) / 1024 / 1024, 2) . '&nbsp;' . __('MB'),
			'{gleez_version}'    => Gleez::VERSION,
			'{execution_time}'   => number_format(microtime(TRUE) - GLEEZ_START_TIME, 3) . '&nbsp;' . __('seconds'),
			'{included_files}'   => count(get_included_files()),
			'{database_queries}' => $queries
		);

		// Insert the totals into the response
		$this->template = strtr((string) $this->template, $total);
	}

	/**
	 * Is frontpage?
	 *
	 * @return boolean
	 *
	 * @uses  Request::uri
	 */
	public function is_frontpage()
	{
		$uri = preg_replace("#(/p\d+)+$#uD", '', rtrim($this->request->uri(), '/'));

		return (empty($uri) OR ($uri === $this->_config->front_page));
	}

	/**
	 *  Process the response as JSON with some extra information about the
	 *  (success status of the form) so that jQuery knows what to do with the result.
	 */
	protected function process_ajax()
	{
		if ( $this->request->method() == HTTP_Request::POST )
		{
			// Allow for override. Set the form saved true for ajax request, if no errors
			if (empty($this->_errors))
			{
				$this->SetFormSaved(TRUE);
			}
			else
			{
				$this->SetFormSaved(FALSE);
			}

			if ($this->_response_format === 'application/json')
			{
				$this->SetJson('Body', FALSE);
			}
		}
		else
		{
			if ($this->_response_format === 'application/json')
			{
				$this->SetJson('Body', base64_encode($this->response->body()));
			}
		}

		if ($this->_response_format === 'application/json')
		{
			if ($this->request->query('sEcho') !== NULL) return;

			$scripts = Assets::js(FALSE, NULL, NULL, FALSE, NULL, Assets::FORMAT_AJAX);
			$styles  = Assets::css(FALSE, NULL, NULL, FALSE, Assets::FORMAT_AJAX);

			$this->SetJson('FormSaved',  $this->_formsaved);
			$this->SetJson('messages',   Message::get(NULL, NULL, TRUE));
			$this->SetJson('errors',     $this->_errors);
			$this->SetJson('redirect',   Request::$redirect_url);
			$this->SetJson('title',      $this->title);
			$this->SetJson('subtitle',   $this->subtitle);
			$this->SetJson('css',        $styles);
			$this->SetJson('js',         $scripts);

			if ( ! Valid::utf8($this->_json['Body']))
			{
				$this->_json['Body'] = utf8_encode($this->_json['Body']);
			}

			$this->_json['Data'] = JSON::encode($this->_json);
		}
	}

	/**
	 * If JSON is going to be sent to the client, this method allows you to add
	 * extra values to the JSON array.
	 *
	 * @param  string  $Key    The name of the array key to add.
	 * @param  string  $Value  The value to be added. If empty, nothing will be added [Optional]
	 */
	public function SetJson($Key, $Value = '')
	{
		$this->_json[$Key] = $Value;
	}

	/**
	 * Set $this->_FormSaved for JSON Renders.
	 *
	 * @param bool $Saved Whether form data was successfully saved.
	 */
	public function SetFormSaved($Saved = TRUE)
	{
		if ($Saved === '')
		{
			// Allow reset
			$this->_formsaved = NULL;
		}
		else
		{
			// Force true/false
			$this->_formsaved = ($Saved) ? TRUE : FALSE;
		}
	}

	/**
	 * Get site name
	 *
	 * It is just helper, which gets site name
	 *
	 * @since   1.2.0
	 *
	 * @param   mixed  $default  The return value if the site name isn't found [Optional]
	 *
	 * @return  mixed
	 */
	public static function getSiteName($default = 'Gleez CMS')
	{
		return Config::get('site.site_name', $default);
	}
}
