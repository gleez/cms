<?php
/**
 * Gleez Installer
 *
 * @package    Gleez\Install
 * @author     Gleez Team
 * @version    1.3.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Install_Install extends Controller_Template {

	/**
	 * Page template
	 * @var View
	 */
	public $template = 'install/template';

	/**
	 * All routes
	 * @var Route
	 */
	protected $_media;

	/**
	 * Current session data
	 * @var Session
	 */
	protected $_session;

	/**
	 * The before() method is called before controller action
	 *
	 * @uses  Route::get
	 * @uses  Session::destroy
	 * @uses  Session::instance
	 */
	public function before()
	{
		if ($this->request->action() === 'media')
		{
			// Do not template media files
			$this->auto_render = FALSE;
		}
		else
		{
			// Grab the necessary routes
			$this->_media = Route::get('install/media');
		}

		parent::before();

		if ($this->request->action() === 'index')
		{
			Session::instance('cookie')->destroy();
		}

		if ($this->auto_render)
		{
			$this->_session = Session::instance('cookie');
			$this->template->menu = array(
				__('Welcome'),
				__('System Check'),
				__('Database'),
				__('Install'),
				__('Finish')
			);
		}
	}

	/**
	 * The after() method is called after controller action
	 *
	 * @uses  Route::uri
	 */
	public function after()
	{
		if ($this->auto_render)
		{
			// Add styles
			$this->template->styles = array(
				$this->_media->uri(array('file' => 'css/bootstrap.css')) => 'screen',
				$this->_media->uri(array('file' => 'css/install.css')) => 'screen',
			);

			$this->template->logo = $this->_media->uri(array('file' => 'logo.png'));
			$this->template->link = $this->_media->uri(array('file' => 'favicon.ico'));

			// Do some CSS magic to page class
			$classes   = array();
			$classes[] = I18n::$lang;
			$classes[] = $this->request->controller();
			$classes[] = $this->request->action();

			$page_class = implode(' ', array_unique(array_map('trim', $classes)));

			// Bind the generic page variables
			$this->template->set('lang', I18n::$lang)
						->set('page_class', $page_class);

		}

		parent::after();
	}

	/**
	 * ## First step of installation
	 *
	 * Welcome page
	 *
	 * @uses  HTML::anchor
	 * @uses  Route::get
	 * @uses  Route::uri
	 */
	public function action_index()
	{
		$this->template->title = __('Install');

		$this->template->_activity = __('20');
		$this->template->menu = array(
			HTML::anchor(Route::get('install')->uri(), __('Welcome')),
			__('System Check'),
			__('Database'),
			__('Install'),
			__('Finish')
		);

		$this->template->content = new View('install/welcome');
	}

	public function action_systemcheck()
	{
		$this->template->title =  __('System Check');
		$this->template->_activity = __('40');
		$this->template->menu = array(
			HTML::anchor(Route::get('install')->uri(), __('Welcome')),
			HTML::anchor(Route::get('install')->uri(array('action' => 'systemcheck')), __('System Check')),
			__('Database'),
			__('Install'),
			__('Finish')
		);

		!file_exists(APPPATH . "cache") && System::mkdir(APPPATH . "cache");
		!file_exists(APPPATH . "config") && System::mkdir(APPPATH . "config");
		!file_exists(APPPATH . "logs") && System::mkdir(APPPATH . "logs");
		!file_exists(APPPATH . "uploads") && System::mkdir(APPPATH . "uploads");
		!file_exists(APPPATH . "media") && System::mkdir(APPPATH . "media");
		!file_exists(APPPATH . "media/pictures") && System::mkdir(APPPATH . "media/pictures");
		!file_exists(DOCROOT . "media") && System::mkdir(DOCROOT . "media");
		!file_exists(DOCROOT . "media/css") && System::mkdir(DOCROOT . "media/css");
		!file_exists(DOCROOT . "media/js") && System::mkdir(DOCROOT . "media/js");

		$view = View::factory('install/systemcheck', System::check());

		if (	$view->php_version
			AND $view->mysql
			AND $view->system_directory
			AND $view->application_directory
			AND $view->modules_directory
			AND $view->config_writable
			AND $view->cache_writable
			AND $view->pcre_utf8
			AND $view->pcre_unicode
			AND $view->reflection_enabled
			AND $view->filters_enabled
			AND $view->iconv_loaded
			AND $view->spl_autoload_register
			AND $view->simplexml
			AND $view->json_encode
			AND $view->mbstring
			AND $view->ctype_digit
			AND $view->uri_determination
			AND $view->gd_info)
			$this->request->redirect(Route::get('install')->uri(array('action' => 'database')));

		else
		{
			$this->template->error = __('Gleez may not work correctly with your environment.');
		}

		$this->template->content = $view;
	}

	public function action_database()
	{
		$action = Route::get('install')->uri(array('action' => 'database'));
		$view = View::factory('install/database')
					->bind('form', $form)
					->set('action', $action);

		$this->template->content = $view;

		$this->template->title = __('Database Configuration');
		$this->template->_activity = __('60');
		$this->template->menu = array(
			HTML::anchor(Route::get('install')->uri(), __('Welcome')),
			HTML::anchor(Route::get('install')->uri(array('action' => 'systemcheck')), __('System Check')),
			HTML::anchor(Route::get('install')->uri(array('action' => 'database')), __('Database')),
			__('Install'),
			__('Finish')
		);

		$form = array(
			'user' => '',
			'pass' => '',
			'hostname' => 'localhost',
			'database' => 'gleezcms',
			'table_prefix' => 'gl_'
		);

		if (isset($_POST['db']))
		{
			$data = array(
				'user' => $username = $_POST['user'],
				'pass' => $password = $_POST['pass'],
				'hostname' => $hostname = $_POST['hostname'],
				'database' => $database = $_POST['database'],
				'table_prefix' => $table_prefix = $_POST['table_prefix']
			);

			try
			{
				$this->check_database($username, $password, $hostname, $database);

				$this->_session->set('database_data', $data);

				$this->request->redirect(Route::get('install')->uri(array('action' => 'install')));
			}
			catch (Exception $e)
			{
				$form = Arr::overwrite($form, $_POST);
				$error = $e->getMessage();

				// TODO create better error messages
				// Try to use mysql_errno.
				// Error message of East Asian character sets will display garbled text on utf-8 web page
				switch ($error)
				{
					case 'access':
						$this->template->error = __('Wrong username or password');
					break;
					case 'unknown_host':
						$this->template->error = __('Could not find the host');
					break;
					case 'connect_to_host':
						$this->template->error = __('Could not connect to host');
					break;
					case 'select':
						$this->template->error = __('Could not select the database');
					break;
					case 'version':
						$this->template->error = __('Gleez requires at least MySQL version 5.0.0. You\'re using version :version',
								array( ':version' => $this->mysql_version(1) )
							);
					break;
					default:
						$this->template->error = $error;
				}
			}
		}
	}

	public function action_install()
	{
		$config = $this->_session->get('database_data');

		$this->template->title = __('Install');
		$this->template->content = '';
		$this->template->_activity = __('80');
		$this->template->menu = array(
			HTML::anchor(Route::get('install')->uri(), __('Welcome')),
			HTML::anchor(Route::get('install')->uri(array('action' => 'systemcheck')), __('System Check')),
			HTML::anchor(Route::get('install')->uri(array('action' => 'database')), __('Database')),
			HTML::anchor(Route::get('install')->uri(array('action' => 'install')), __('Install')),
				__('Finish')
			);

		try
		{
			$this->unpack_sql($config);
			$this->request->redirect(Route::get('install')->uri(array('action' => 'finalize')));
		}
		catch (Exception $e)
		{
			$this->template->error = $e->getMessage();
			$this->template->content = __('Please fix the errors!');
		}

	}

	public function action_finalize()
	{
		$data = $this->_session->get('database_data');
		$this->template->_activity = __('80');
		$this->template->menu = array(
			HTML::anchor(Route::get('install')->uri(), __('Welcome')),
			HTML::anchor(Route::get('install')->uri(array('action' => 'systemcheck')), __('System Check')),
			HTML::anchor(Route::get('install')->uri(array('action' => 'database')), __('Database')),
			HTML::anchor(Route::get('install')->uri(array('action' => 'install')), __('Install')),
			HTML::anchor(Route::get('install')->uri(array('action' => 'finalize')), __('Finish')),
			);

		if(isset($data))
		{
			if( ! $this->create_database_config($data['user'], $data['pass'], $data['hostname'], $data['database'], $data['table_prefix']))
			{
				$this->template->error = __('Couldn\'t create application/config/database.php');
			}

			$password = $this->add_user();
			chmod(APPPATH.'config/database.php', 0444);

			$admin_user = Route::get('admin/user')->uri( array('action' => 'edit', 'id' => 2));
			$admin_url = Route::get('user')->uri( array('action' => 'login')).URL::query(array('destination' => $admin_user));

			$this->template->title = __('Success!');
			$this->template->_activity = __('100');
			$this->template->content = View::factory('install/finalize', array('password' => $password, 'admin_url' => $admin_url) );
		}
		else
		{
			$this->request->redirect(Route::get('install')->uri());
		}
	}

	public function action_media()
	{
		// Get the file path from the request
		$file = $this->request->param('file');

		// Find the file extension
		$ext = pathinfo($file, PATHINFO_EXTENSION);

		// Remove the extension from the filename
		$file = substr($file, 0, -(strlen($ext) + 1));

		if ($file = Kohana::find_file('media', $file, $ext))
		{
			// Check if the browser sent an "if-none-match: <etag>" header, and tell if the file hasn't changed
			$this->response->check_cache(sha1($this->request->uri()).filemtime($file), $this->request);

			// Send the file content as the response
			$this->response->body(file_get_contents($file));

			// Set the proper headers to allow caching
			$this->response->headers('content-type',  File::mime_by_ext($ext));
			$this->response->headers('last-modified', date('r', filemtime($file)));
		}
		else
		{
			// Return a 404 status
			$this->response->status(404);
		}
	}

	public function check_database($username, $password, $hostname, $database)
	{
		if(function_exists('mysqli_query'))
		{
			return $this->_mysqli_check_database($username, $password, $hostname, $database);
		}
		else
		{
			return $this->_mysql_check_database($username, $password, $hostname, $database);
		}
	}

	private function _mysql_check_database($username, $password, $hostname, $database)
	{
		if ( ! $link = @mysql_connect($hostname, $username, $password))
		{
			if (strpos(mysql_error(), 'Access denied'))
			{
				throw new Exception('access');
			}
			elseif (strpos(mysql_error(), 'server host'))
			{
				throw new Exception('unknown_host');
			}
			elseif (strpos(mysql_error(), 'connect to'))
			{
				throw new Exception('connect_to_host');
			}
			else
			{
				throw new Exception(mysql_error());
			}
		}

		if (! version_compare($this->mysql_version($link), "5.0.0", ">=") ) {
				throw new Exception('version');
		}

		if ($select = mysql_select_db($database, $link)) {
			return TRUE;
		}
		else {
			mysql_query("CREATE DATABASE `{$database}`");

			if (! $select = mysql_select_db($database, $link)) {
				throw new Exception('select');
			}
		}

		return TRUE;
	}

	private function _mysqli_check_database($username, $password, $hostname, $database)
	{
		if ( ! $link = @mysqli_connect($hostname, $username, $password))
		{
			if (strpos(mysqli_error(), 'Access denied'))
			{
				throw new Exception('access');
			}
			elseif (strpos(mysqli_error(), 'server host'))
			{
				throw new Exception('unknown_host');
			}
			elseif (strpos(mysqli_error(), 'connect to'))
			{
				throw new Exception('connect_to_host');
			}
			else
			{
				throw new Exception(mysqli_error());
			}
		}

		if (! version_compare($this->mysql_version($link), "5.0.0", ">=") ) {
				throw new Exception('version');
		}

		if ($select = mysqli_select_db($link, $database)) {
			return TRUE;
		}
		else {
			mysqli_query($link, "CREATE DATABASE `{$database}`");

			if (! $select = mysqli_select_db($link, $database)) {
				throw new Exception('select');
			}
		}

		return TRUE;
	}

	public function create_database_config($username, $password, $hostname, $database, $table_prefix)
	{
		$config = new View('install/config');

		$config->type	  = function_exists('mysqli_query') ? 'mysqli' : 'mysql';
		$config->user     = $username;
		$config->password = $password;
		$config->host     = $hostname;
		$config->dbname   = $database;
		$config->prefix   = $table_prefix;
		$config->port     = '';

		return file_put_contents(APPPATH.'config/database.php', $config) !== false;
	}

	private function mysql_version($link)
	{
		if(function_exists('mysqli_query'))
		{
			$result = mysqli_query($link, "SHOW VARIABLES WHERE variable_name = \"version\"");
			$row = mysqli_fetch_object($result);
		}
		else
		{
			$result = mysql_query("SHOW VARIABLES WHERE variable_name = \"version\"");
			$row = mysql_fetch_object($result);
		}

		return $row->Value;
	}

	private function unpack_sql($config)
	{
		if(function_exists('mysqli_query'))
		{
			return $this->_mysqli_unpack_sql($config);
		}
		else
		{
			return $this->_mysql_unpack_sql($config);
		}
	}

	private function _mysqli_unpack_sql($config)
	{
		$prefix = $config["table_prefix"];
		$buf = null;

		$link = mysqli_connect($config["hostname"], $config["user"], $config["pass"]);
		mysqli_select_db($link, $config["database"]);

		$sql_file = MODPATH . "gleez/views/install/install.sql";

		foreach (file($sql_file) as $line)
		{
			$buf .= trim($line);
			if (preg_match("/;$/", $buf))
			{
				if (!mysqli_query($link, $this->prepend_prefix($prefix, $buf)))
				{
					throw new Exception(mysqli_error());
				}
				$buf = "";
			}
		}
		return true;
	}

	private function _mysql_unpack_sql($config)
	{
		$prefix = $config["table_prefix"];
		$buf = null;

		mysql_connect($config["hostname"], $config["user"], $config["pass"]);
		mysql_select_db($config["database"]);

		$sql_file = MODPATH . "gleez/views/install/install.sql";

		foreach (file($sql_file) as $line)
		{
			$buf .= trim($line);
			if (preg_match("/;$/", $buf))
			{
				if (!mysql_query($this->prepend_prefix($prefix, $buf)))
				{
					throw new Exception(mysql_error());
				}
				$buf = "";
			}
		}
		return true;
	}

	private function prepend_prefix($prefix, $sql)
	{
		return  preg_replace("#{([a-zA-Z0-9_]+)}#", "{$prefix}$1", $sql);
	}

	private function add_user()
	{
		if(function_exists('mysqli_query'))
		{
			return $this->_mysqli_add_user();
		}
		else
		{
			return $this->_mysql_add_user();
		}
	}

	private function _mysqli_add_user()
	{
		$config = $this->_session->get('database_data');
		$link = mysqli_connect($config["hostname"], $config["user"], $config["pass"]);
		mysqli_select_db($link, $config["database"]);
		$prefix = trim($config["table_prefix"]);

		$key = sha1(uniqid(mt_rand(), true)) . md5(uniqid(mt_rand(), true));
		$skey = serialize($key);
		$sql = "UPDATE `{$prefix}config` SET `config_value` = '$skey' WHERE `group_name` = 'site' AND `config_key` = 'gleez_private_key'";
		mysqli_query($link, $sql);

		$password = Text::random('alnum', 8);
		$pass = hash_hmac('sha1', $password, 'e41eb68d5605ebcc01424519da854c00cf52c342e81de4f88fd336b1d31ff430');
		mysqli_query($link, "UPDATE `{$prefix}users` SET `pass` = '$pass' WHERE `id` = 2");

		return $password;
	}

	private function _mysql_add_user()
	{
		$config = $this->_session->get('database_data');
		mysql_connect($config["hostname"], $config["user"], $config["pass"]);
		mysql_select_db($config["database"]);
		$prefix = trim($config["table_prefix"]);

		$key = sha1(uniqid(mt_rand(), true)) . md5(uniqid(mt_rand(), true));
		$skey = serialize($key);
		$sql = "UPDATE `{$prefix}config` SET `config_value` = '$skey' WHERE `group_name` = 'site' AND `config_key` = 'gleez_private_key'";
		mysql_query($sql);

		$password = Text::random('alnum', 8);
		$pass = hash_hmac('sha1', $password, 'e41eb68d5605ebcc01424519da854c00cf52c342e81de4f88fd336b1d31ff430');
		mysql_query("UPDATE `{$prefix}users` SET `pass` = '$pass' WHERE `id` = 2");

		return $password;
	}

}
