<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package	Gleez
 * @category	Core
 * @author	Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2012 Gleez Technologies
 * @license	http://gleezcms.org/license
 */
class Gleez_Core {
        
        // Release version and codename
        const VERSION  = '0.9.8';
        const CODENAME = 'Turdus obscurus';

	/**
	 * @var  boolean  installed?
	 */
	public static $installed = FALSE;

	/**
	 * @var  string  theme name
	 */
	public static $theme = 'anytime';
	
        /**
	 * @var  boolean  Has [Gleez::init] been called?
	 */
	protected static $_ginit = FALSE;
        
        /**
	 * Runs the Gleez environment:
	 *
	 * @throws  Gleez_Exception
         */
        public static function ready()
        {
	        if (self::$_ginit)
		{
			// Do not allow execution twice
			return;
		}

		// Gleez is now initialized
		self::$_ginit = TRUE;

		//Set default session type & Cookie Salt
		Cookie::$salt = 'e41eb68d5605ebcc01424519da854';
	
		if( Kohana::$environment !== Kohana::DEVELOPMENT )
		{
			Kohana_Exception::$error_view = 'errors/stack';
		}
	
		//disable the kohana powred headers
		Kohana::$expose = FALSE;
	
		/**
		 * If database.php doesn't exist, then we assume that the Gleez is not
		 * properly installed and send to the installer.
		 */
		if (!file_exists(APPPATH.'config/database.php'))
		{
			Gleez::$installed = FALSE; //set system not installed
			Session::$default = 'cookie';
			Kohana_Exception::$error_view = 'kohana/error';
	
			// Static file serving (CSS, JS, images)
			Route::set('install/media', 'setup/media(/<file>)', array('file' => '.+'))
						->defaults(array(
								'controller' => 'install',
								'action'     => 'media',
								'file'       => NULL,
								'directory'  => 'install'
						));
		
			Route::set('install', '(install(/<action>))', array(
								'action' => 'index|systemcheck|database|install|finalize'))
					->defaults(array(
							 'controller' => 'install',
							 'action'     => 'index',
							 'directory'  => 'install'
					 ));

			return;
		}
	
		//set the default session type to db
		Session::$default = 'db';
	
		// Initialize Gleez modules
                Module::load_modules(FALSE);
	
		//database config reader and writer
		Kohana::$config->attach(new Config_Database);
	
		//Load the active theme(s)
		Theme::load_themes();
	
		//we're here means gleez installed and running, so set it
		Gleez::$installed = TRUE;
	}

	/**
	 * APC cache. Provides an opcode based cache.
	 * 
	 * @param   string   name of the cache
	 * @param   mixed    data to cache
	 * @param   integer  number of seconds the cache is valid for
	 * @return  mixed    for getting
	 * @return  boolean  for setting
	 */
	public static function cache($name, $data = NULL, $lifetime = 3600)
	{
		// enable cache only in production environment
		if( Kohana::$environment !== Kohana::PRODUCTION )
		{
			Kohana::$log->add(LOG::DEBUG, 'Gleez Caching only available in production');
			return FALSE;	
		}
	
		//Check for existence of the APC extension
		if ( ! extension_loaded('apc'))
		{
			//throw new Kohana_Exception('PHP APC extension is not available.');
			Kohana::$log->add(LOG::ERROR, 'PHP APC extension is not available');
			return FALSE;
		}
	
		if( isset($_SERVER['HTTP_HOST']) ) $name .= $_SERVER['HTTP_HOST'];
		
		if (is_null($data))
		{
			try
			{
				// Return the cache
				$c_data = apc_fetch(self::_sanitize_id($name), $success);
				return $success ? $c_data : FALSE;
			}
			catch (Exception $e)
			{
				// Cache is corrupt, let return happen normally.
				Kohana::$log->add(LOG::ERROR, "Cache name: {$name} is corrupt");
			}
		
			// Cache not found
			return FALSE;
		}
		else
		{
			try
			{
				return apc_store(self::_sanitize_id($name), $data, $lifetime);
			}
			catch (Exception $e)
			{
				// Failed to write cache
				return FALSE;
			}
		}
	}

	/**
	 * Delete all known cache's we set
	 */
	public static function cache_delete()
	{
		//clear any cache for sure
		Cache::instance('modules')->delete_all();
		Cache::instance('menus')->delete_all();
		Cache::instance('widgets')->delete_all();
		Cache::instance('feeds')->delete_all();
		Cache::instance('page')->delete_all();
		Cache::instance('blog')->delete_all();
	
		// For each cache instance
		foreach (Cache::$instances as $group => $instance)
		{
			$instance->delete_all();
		}
	}
	
	/**
	 * Replaces troublesome characters with underscores.
	 *
	 *     // Sanitize a cache id
	 *     $id = $this->_sanitize_id($id);
	 * 
	 * @param   string   id of cache to sanitize
	 * @return  string
	 */
	protected static function _sanitize_id($id)
	{
		// Change slashes and spaces to underscores
		return str_replace(array('/', '\\', ' '), '_', $id);
	}
	
	/**
	 *  list of route types (route name used for creating alias and term/tag routes)
         *  @return array types
         */
        public static function types()
        {
		$states = array(
				'post'   => __('Post'),
				'page'   => __('Page'),
                                'blog'   => __('Blog'),
                                'forum'  => __('Forum'),
                                'book'   => __('Book'),
                                'user'   => __('User'),
				);
	
		$values = Module::action('gleez_types', $states);
                return $values;
        }
	
	/**
         * If Gleez is in maintenance mode, then force all non-admins to get routed
         * to a "This site is down for maintenance" page.
         * 
         */
        public static function maintenance_mode()
        {
                $maintenance_mode = Kohana::$config->load('site.maintenance_mode', false);
                $request          = Request::initial();

                if ( $maintenance_mode AND ($request instanceof Request)
		    AND( $request->controller() != 'user' AND $request->action() != 'login' )
		    AND !ACL::check('administer site') AND $request->controller() != 'media')
                {
                        Kohana::$log->add(LOG::INFO, 'Site running in Maintenance Mode');
                        throw new HTTP_Exception_503('Site running in Maintenance Mode');
                }
        }
	
        /**
         * Return a unix timestamp in a user specified format including date and time.
         * @param $timestamp unix timestamp
         * @return string
         */
        static function date_time($timestamp)
        {
                return date(Kohana::$config->load('site.date_time_format'), $timestamp);
        }
        
        /**
         * Return a unix timestamp in a user specified format that's just the date.
         * @param $timestamp unix timestamp
         * @return string
         */
        static function date($timestamp)
        {
                return date(Kohana::$config->load('site.date_format'), $timestamp);
        }
        
        /**
         * Return a unix timestamp in a user specified format that's just the time.
         * 
         * @param $timestamp unix timestamp
         * @return string
         */
        static function time($timestamp)
        {
                return date(Kohana::$config->load('site.time_format'), $timestamp);
        }

	/**
	 * This function searches for the file that first matches the specified file
	 * name and returns its path.
	 *
	 * @access protected
	 * @param string $file                          the file name
	 * @return string                               the file path
	 * @throws Kohana_Exception        indicates that the file does not exist
	 */
	protected static function find_file_custom($file) {
		if (file_exists($file)) {
			return $file;
		}

		$uri = THEMEPATH . $file;
		if (file_exists($uri)) {
			return $uri;
		}

		$uri = APPPATH . $file;
		if (file_exists($uri)) {
			return $uri;
		}

		$modules = Kohana::modules();
		foreach($modules as $module) {
			$uri = $module . $file;
			if (file_exists($uri)) {
				return $uri;
			}
		}

		$uri = SYSPATH . $file;
		if (file_exists($uri)) {
			return $uri;
		}

		throw new Kohana_Exception('Message: Unable to locate file. Reason: No file exists with the specified file name.', array(':file', $file));
	}
	
	/**
	 * create a image tag for sprite images
	 * 
	 * @access public
	 * @static
	 * @param mixed $name
	 * @param mixed $title. (default: null)
	 * @param mixed $extra_class. (default: null)
	 * @return void
	 */
	public static function spriteImg($class, $title = null)
	{
		$attr = array();
		$attr['width']  = 16;
		$attr['height'] = 16;
		$attr['class'] = 'icon ' . $class;
		
		if ($title)
		{
			$attr['title'] = $title;
		}
		
		return Html::image(Route::get('media')->uri(array('file' => 'images/spacer.gif')), $attr);
	}
	
	/**
	 * Check the supplied integer in given range
	 * 
	 * @access public
	 * @static
	 * @param int $min
	 * @param int $max
	 * @param int $from_user supplied intiger
	 * @return bool
	 */
	public static function check_in_range($min, $max, $from_user)
	{
		// Convert to int
		$start = (int) $min;
		$end   = (int) $max;
		$user  = (int) $from_user;

		// Check that user data is between start & end
		return (($user > $start) AND ($user < $end));
	}
	
	public static function ping_o_matic()
	{
		$urls = array();
		$urls[] = 'http://pingomatic.com/ping/?title=Gleez&blogurl=http%3A%2F%2Fgleez.com&rssurl=http%3A%2F%2Fgleez.com%2Frss.xml&chk_weblogscom=on&chk_blogs=on&chk_feedburner=on&chk_syndic8=on&chk_newsgator=on&chk_myyahoo=on&chk_pubsubcom=on&chk_blogdigger=on&chk_blogstreet=on&chk_weblogalot=on&chk_newsisfree=on&chk_topicexchange=on&chk_google=on&chk_tailrank=on&chk_postrank=on&chk_skygrid=on&chk_collecta=on&chk_superfeedr=on';
	
		$urls[] = 'http://pingomatic.com/ping/?title=Gleez&blogurl=http%3A%2F%2Fgleez.com&rssurl=http%3A%2F%2Fgleez.com%2Frss%2Fblog&chk_weblogscom=on&chk_blogs=on&chk_feedburner=on&chk_syndic8=on&chk_newsgator=on&chk_myyahoo=on&chk_pubsubcom=on&chk_blogdigger=on&chk_blogstreet=on&chk_weblogalot=on&chk_newsisfree=on&chk_topicexchange=on&chk_google=on&chk_tailrank=on&chk_postrank=on&chk_skygrid=on&chk_collecta=on&chk_superfeedr=on';
		
		$urls[] = 'http://pingomatic.com/ping/?title=Gleez&blogurl=http%3A%2F%2Fgleez.com&rssurl=http%3A%2F%2Fgleez.com%2Frss%2Fforum&chk_weblogscom=on&chk_blogs=on&chk_feedburner=on&chk_syndic8=on&chk_newsgator=on&chk_myyahoo=on&chk_pubsubcom=on&chk_blogdigger=on&chk_blogstreet=on&chk_weblogalot=on&chk_newsisfree=on&chk_topicexchange=on&chk_google=on&chk_tailrank=on&chk_postrank=on&chk_skygrid=on&chk_collecta=on&chk_superfeedr=on';
		
	}
	
}