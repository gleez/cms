<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * The template class for automatic templating.
 *
 * @package   Gleez
 * @category  Template
 * @author    Sandeep Sangamreddi - Gleez
 * @copyright (c) 2013 Gleez Technologies
 * @license   http://gleezcms.org/license
 */
class Gleez_Template extends Controller{

        /** @var string Page template */
        public $template = 'template';

        /** @var boolean Auto render template */
        public $auto_render = TRUE;

        /** @var boolean Turn debugging on? */
        public $debug = FALSE;

        /** @var string Doctype declaration */
        public $doctype = FALSE;

        /** @var string The site name */
        public $_site_name;

        /** @var string The page title */
        public $title = NULL;

        /** @var string The delimiter page header and site name */
        public $_title_separator;

        /** @var array The sidebar content */
        protected $_regions = array();

        /** @var boolean Is ajax request */
        protected $_ajax = FALSE;

        /** @var  boolean  is internal request */
        protected $_internal = FALSE;

        /** @var Kohana_Config The configuration settings */
        protected $_config;

        /** @var object The Auth Object */
        protected $_auth;

        /** @var object The Widgets Object */
        protected $_widgets;

        /** @var array The destination url */
        protected $dest;

        /** @var array The destination url */
        protected $redirect;

        /** @var string Current page class */
        protected $_page_class;

        /** @var string Current page id, defaults to controller name */
        protected $_page_id;

        /** @var array Tabs navigation */
        protected $_tabs;

        /** @var string Unique token for profiling */
        protected $_benchmark;

        /** @var string Hold the response format for this request */
        protected $_response_format;

        /**
         * @var array Supported output formats for this controller
         * (accept-type => path to format template)
         */
        protected $_accept_formats = array(
          'text/html' => '',
          'application/xhtml+xml' => '',
          'application/json' => 'json',
          '*/*' => '' //ie7 ie8
        );

        /** @var bool Enable/Disable sidebars for this request, ex: add or edit page requires no sidebars */
        protected $_sidebars = TRUE;
        
        /**
         * Loads the template View object, if it is direct request.
         *
         * This method is called before controller action.
         *
         * @throws  HTTP_Exception_415  If none of the accept-types are supported
         */
        public function before()
        {
                // Execute parent::before first
                parent::before();

                if (Kohana::$profiling === TRUE)
                {
                        // Start a new benchmark
                        $this->_benchmark = Profiler::start('Gleez', 'Gleez Controller');
                }

                // Test whether the current request is the first request
                if (!$this->request->is_initial())
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

                $this->response->headers('X-Powered-By', 'Gleez CMS ' . Gleez::VERSION . ' (' . Gleez::CODENAME . ')');

                $this->_config = Kohana::$config->load('site');
                $this->_auth   = Auth::instance();

                // Get desired response formats
                $accept_types = Request::accept_type();
                $accept_types = Arr::extract($accept_types, array_keys($this->_accept_formats));

                // Set response format to first matched element
                $this->_response_format = key($accept_types);

                if ($this->auto_render)
                {
                        // Throw exception if none of the accept-types are supported
                        if ( ! array_filter($accept_types))
                        {
                                throw new HTTP_Exception_415('Unsupported accept-type');
                        }

                        // Load the template
                        $this->template         = View::factory($this->template);
                        $this->_title_separator = $this->_config->get('title_separator', ' | ');
                        $this->_widgets         = Widgets::instance();
                        $this->template->_admin = Theme::$is_admin;

                        //set the destination & redirect url
                        $this->desti    = array(
                                'destination' => $this->request->uri()
                        );
                        $this->redirect = ($this->request->query('destination') !== NULL) ? $this->request->query('destination') : array();

                        // Bind the generic page variables
                        $this->template->set('site_name', $this->_config->get('site_name', __('Gleez CMS')))
                                        ->set('site_slogan', $this->_config->get('site_slogan', __('Innovate IT')))
                                        ->set('site_url', URL::site(null, TRUE))
                                        ->set('site_logo', $this->_config->get('site_logo', false))
                                        ->set('sidebar_left', array())->set('sidebar_right', array())
                                        ->set('column_class', '')->set('main_column', 12)
                                        ->set('head_title', $this->title)
                                        ->set('title', $this->title)
                                        ->set('front', FALSE)
                                        ->set('mission', FALSE)
                                        ->set('tabs', FALSE)
                                        ->set('_user', $this->_auth->get_user());

                        // Page Title
                        $this->title = ucwords($this->request->controller());

                        //Default Doctype declaration to xhtml strict
                        $this->doctype = 4;

                        // Assign the default css files
                        Assets::css('bootstrap', 'media/css/bootstrap.css', NULL, array('weight' => -15));
                        Assets::css('font-awesome', 'media/css/font-awesome.css', array('weight' => -13));
                        Assets::css('default', 'media/css/default.css', NULL, array('weight' => 0));
                        Assets::css('style', 'media/css/style.css', array('default'), array('weight' => 1));
                        Assets::js('bootstrap', 'media/js/bootstrap.js', array('jquery'), FALSE, array('weight' => 5));

                        // Set default server headers
                        $this->_set_default_server_headers();

                        // Set default meta data and media
                        $this->_set_default_meta_links();
                        $this->_set_default_meta_tags();

                        /**
                         * Make your view template available to all your other views
                         * so easily you could access template variables
                         */
                        View::bind_global('site_name', $this->template->site_name);
                        View::bind_global('site_url', $this->template->site_url);
                        View::bind_global('template', $this->template);

                        if (Kohana::$environment === Kohana::DEVELOPMENT)
                        {
                                Kohana::$log->add(LOG::DEBUG, 'Executing Controller `:controller` action `:action`', array(
                                        ':controller' => $this->request->controller(),
                                        ':action' => $this->request->action()
                                ));
                        }
                }
        }

        /**
         * If debugging is enabled, append profiler stats for non-production environments.
         *
         * @return  void
         */
        public function after()
        {
                if ($this->auto_render === TRUE)
                {
                        // Controller name as the default page id if none set
                        empty($this->_page_id) AND $this->_page_id = $this->request->controller();

                        // Load left and right sidebars if available
                        $this->_set_sidebars();

                        // set appropriate column css class
                        $this->_set_column_class();

                        // Do some CSS magic to page class
                        $classes   = array();
                        $classes[] = Gleez::$locale;
                        $classes[] = $this->request->controller();
                        $classes[] = $this->request->action();
                        $classes[] = $this->request->controller() . '-' . $this->request->action();
                        $classes[] = $this->template->column_class;
                        $classes[] = $this->_page_class;
                        $classes[] = ($this->_auth->logged_in()) ? 'logged-in' : 'not-logged-in';

                        // Special check for frontpage and frontpage title
                        if (!$uri = @preg_replace("#(/p\d+)+$#uD", '', rtrim($this->request->uri(), '/')) OR $uri === $this->_config->front_page)
                        {
                                // Set front variable true for themers
                                $this->template->front = TRUE;
                                // Dont show title on homepage
                                $this->template->title = FALSE;
                                // Dont show title on homepage
                                $this->title           = FALSE;

                                $this->template->mission = $this->_config->get('site_mission', FALSE);
                        }

                        View::set_global('is_front', $this->template->front);
                        View::set_global('is_admin', $this->template->_admin);

                        $classes[]  = ($this->template->front) ? 'front' : 'not-front';
                        $page_class = implode(' ', array_unique(array_map('trim', $classes)));

                        // Construct Head Section Page title
                        $this->_set_head_title();

                        // Allow module and theme developers to override
                        Module::event('template', $this);

                        // Set pimary menu
                        $pimary_menu = Menu::links('main-menu', array(
                                'class' => 'menus nav'
                        ));

                        // Bind the generic page variables
                        $this->template->set('lang', Gleez::$locale)
                                        ->set('page_id', $this->_page_id)
                                        ->set('page_class', $page_class)
                                        ->set('doctype', '<!DOCTYPE HTML>')
                                        ->set('primary_menu', $pimary_menu)
                                        ->set('title', $this->title)
                                        ->set('mission', $this->template->mission)
                                        ->set('content', $this->response->body())
                                        ->set('messages', Message::display())
                                        ->set('profiler', FALSE);

                        if (count($this->_tabs) > 0)
                        {
                                $this->template->tabs = View::factory('tabs')->set('tabs', $this->_tabs);
                        }


                        // And profiler if debug is true
                        if (Kohana::$environment !== Kohana::PRODUCTION AND $this->debug)
                        {
                                $this->template->profiler = View::factory('profiler/stats');
                        }

                        // And finally the profiler stats
                        $this->_set_profiler_stats();

                        // Set header content-type to response format with utf-8
                        $this->response->headers('Content-Type', $this->_response_format . '; charset=' . Kohana::$charset);

                        // Assign the template as the request response and render it
                        $this->response->body($this->template);
                }
                elseif ($this->_ajax)
                {
                        // Set header content-type to response format with utf-8
                        $this->response->headers('Content-Type', $this->_response_format . '; charset=' . Kohana::$charset);

                        $output = $this->response->body();

                        if ($this->_response_format === 'application/json')
                        {
                                $output = JSON::encode($output);
                        }

                        $this->response->body($output);
                }
                elseif ($this->_internal)
                {
                        // Set header content-type to response format with utf-8
                        $this->response->headers('Content-Type', $this->_response_format . '; charset=' . Kohana::$charset);

                        $output = $this->response->body();
                        $this->response->body($output);
                }

                if (isset($this->_benchmark))
                {
                        // Stop the benchmark
                        Profiler::stop($this->_benchmark);
                }

                parent::after();
        }

        /**
         * Set the page title.
         *
         * @return  void
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
                        $head_title = array(
                                $this->template->site_name
                        );
                        if ($this->template->site_slogan)
                        {
                                $head_title[] = $this->template->site_slogan;
                        }
                }

                $this->template->head_title = implode($this->_title_separator, $head_title);
        }

        /**
         * Set the default server headers.
         *
         * @return  void
         */
        protected function _set_default_server_headers()
        {
                $headers                    = $this->_config->get('headers', array());
                $headers['X-Gleez-Version'] = 'Gleez CMS v ' . Gleez::VERSION . ' (' . Gleez::CODENAME . ')';

                $xmlrpc = $this->_config->get('xmlrpc', NULL);
                if (!is_null($xmlrpc))
                {
                        $headers['X-Pingback'] = URL::site($xmlrpc, TRUE);
                }

                $this->_set_server_headers($headers);
        }

        /**
         * Set the server headers.
         *
         * @param   array $headers  An associative array of server headers
         * @return  void
         */
        protected function _set_server_headers($headers)
        {
                if (is_array($headers) AND !empty($headers))
                {
                        $this->response->headers($headers);
                }
        }

        /**
         * Set the default meta links (using configuration settings).
         *
         * @return  void
         * @uses    Meta::links
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
         * Set the default meta tags (using configuration settings).
         *
         * @return  void
         * @uses    Meta::tags
         */
        protected function _set_default_meta_tags()
        {
                $meta = $this->_config->get('meta', array());
                $tags = Arr::get($meta, 'tags');

                if ($tags)
                {
                        foreach ($tags as $name => $value)
                        {
                                $conditional = NULL;
                                if (is_array($value))
                                {
                                        $conditional = Arr::get($value, 'conditional');
                                        $value       = Arr::get($value, 'value', '');
                                }
                                $attributes = array();
                                if (isset($conditional))
                                {
                                        $attributes['conditional'] = $conditional;
                                }
                                Meta::tags($name, $value, $attributes);
                        }
                }
        }

        /**
         * Add sidebars.
         * This method is chainable.
         *
         * @return Gleez_Template
         */
        protected function _set_sidebars()
        {
                if( $this->_sidebars !== FALSE )
                {
                        $this->template->sidebar_left  = $this->_widgets->render('left');
                        $this->template->sidebar_right = $this->_widgets->render('right');
                }

          return $this;
        }

        /**
         * Add sidebar column class.
         * This method is chainable.
         */
        protected function _set_column_class()
        {
                $sidebar_left  = $this->template->sidebar_left;
                $sidebar_right = $this->template->sidebar_right;

                if (!empty($sidebar_left) AND !empty($sidebar_right))
                {
                        $this->template->column_class = 'main-both';
                        $this->template->main_column  = 6;
                }
                else
                {
                        if (!empty($sidebar_left))
                        {
                                $this->template->column_class = 'main-left';
                                $this->template->main_column  = 9;
                        }
                        if (!empty($sidebar_right))
                        {
                                $this->template->column_class = 'main-right';
                                $this->template->main_column  = 9;
                        }
                }

                return $this;
        }

        /**
         * Returns true if the post has a valid CSRF
         *
         * @return  bool
         */
        public function valid_post($submit = FALSE)
        {
                if ($this->request->method() !== HTTP_Request::POST)
                {
                        return FALSE;
                }

                if (Request::post_max_size_exceeded())
                {
                        Message::error(__('Max filesize of :max exceeded.', array(
                                ':max' => ini_get('post_max_size') . 'B'
                        )));
                        return FALSE;
                }

                // @todo use $this->request->post()
                if ($submit)
                {
                        if (!isset($_POST[$submit]))
                        {
                                Message::error(__('This form has altered. Please try submitting it again.'));
                                return FALSE;
                        }
                }

                $_token  = $this->request->post('_token');
                $_action = $this->request->post('_action');

                $has_csrf = !empty($_token) AND !empty($_action);
                $valid_csrf = $has_csrf AND CSRF::valid($_token, $_action);

                if ($has_csrf AND !$valid_csrf)
                {
                        // CSRF was submitted but expired
                        Message::error(__('This form has expired. Please try submitting it again.'));
                        return FALSE;
                }

                if (isset($_POST['_captcha']))
                {
                        $captcha = $this->request->post('_captcha');
                        if (empty($captcha))
                        {
                                // CSRF was not entered
                                Message::error(__('The security field can\'t be empty.'));
                                return FALSE;
                        }
                        else if (!Captcha::valid($captcha))
                        {
                                Message::error(__('The security answer was wrong.'));
                                return FALSE;
                        }
                }

                return $has_csrf AND $valid_csrf;
        }

        /**
         * Set the profiler stats into template.
         */
        protected function _set_profiler_stats()
        {
                $queries = 0;

                // DB queries
                foreach (Profiler::groups() as $group => $benchmarks)
                {
                        if (strpos($group, 'database') === 0)
                        {
                                $queries += count($benchmarks);
                        }
                }

                // Get the total memory and execution time
                $total = array(
                        '{memory_usage}' => number_format((memory_get_peak_usage() - KOHANA_START_MEMORY) / 1024 / 1024, 2) . 'MB',
                        '{gleez_version}' => Gleez::VERSION,
                        '{execution_time}' => number_format(microtime(TRUE) - KOHANA_START_TIME, 3) . ' seconds',
                        '{included_files}' => count(get_included_files()),
                        '{database_queries}' => $queries
                );

                // Insert the totals into the response
                $this->template = strtr((string) $this->template, $total);
        }

}
