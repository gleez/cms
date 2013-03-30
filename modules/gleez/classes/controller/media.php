<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Media Controller
 *
 * @package    Gleez\Media\Controller
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License 
 */
class Controller_Media extends Controller {
	
	/** @var Kohana_Config The configuration settings */
	public $config;
	
	/**
	 * The before() method is called before controller action.
	 */
	public function before()
	{
		parent::before();
		
		// Load config
		$this->config = Kohana::$config->load('media');
		
		if ($this->request->param('type', FALSE))
		{
			Theme::set_admin_theme();
		}
	}
	
	/**
	 * Static file serving (CSS, JS, images)
	 */
	public function action_serve()
	{
		// Get the file path from the request
		$file = $this->request->param('file');
		
		// Find the file extension
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		
		// Remove the extension from the filename
		$file = substr($file, 0, -(strlen($ext) + 1));
		
		if ($file_name = Kohana::find_file('media', $file, $ext))
		{
			// Check if the browser sent an "if-none-match: <etag>" header, and tell if the file hasn't changed
			$this->response->check_cache(sha1($this->request->uri()) . filemtime($file_name), $this->request);
			
			// Send the file content as the response
			$this->response->body(file_get_contents($file_name));
			
			// Set the proper headers to allow caching
			$this->response->headers('content-type', File::mime_by_ext($ext));
			$this->response->headers('last-modified', date('r', filemtime($file_name)));
			//this is ignored by check_cache
			$this->response->headers('cache-control', 'public, max-age=2592000');
			
			if ($this->config->get('cache', FALSE))
			{
				//set base path
				$path = $this->config->get('public_dir', 'media');
			
				//override path if we're in admin
				if ($this->request->param('type', FALSE))
				{
					$path = $path.DIRECTORY_SEPARATOR.'admin';
				}
				
				// Save the contents to the public directory for future requests
				$public_path = $path.DIRECTORY_SEPARATOR. $file . '.' . $ext;
				$directory   = dirname($public_path);
				
				if (!is_dir($directory))
				{
					// Recursively create the directories needed for the file
					System::mkdir($directory, 0777, TRUE);
				}
				
				file_put_contents($public_path, $this->response->body());
			}
		}
		else
		{
			Kohana::$log->add(LOG::ERROR, 'Media controller error while loading file: `:file`', array(
				':file' => $file
			));
			// Return a 404 status
			$this->response->status(404);
		}
	}
	
}
