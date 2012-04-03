<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Media extends Controller {

	public $config;

	public function before()
	{
		parent::before();

		$this->config = Kohana::$config->load('media');
	}

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
			$this->response->check_cache(sha1($this->request->uri()).filemtime($file_name), $this->request);
			
			// Send the file content as the response
			$this->response->body(file_get_contents($file_name));

			// Set the proper headers to allow caching
			$this->response->headers('content-type',  File::mime_by_ext($ext));
			$this->response->headers('last-modified', date('r', filemtime($file_name)));
			$this->response->headers('cache-control', 'public, max-age=2592000'); //this is ignored by check_cache
	
			if ($this->config['cache'])
			{
				// Save the contents to the public directory for future requests
				$public_path = $this->config['public_dir'].'/'.$file.'.'.$ext;
				$directory = dirname($public_path);

				if ( ! is_dir($directory))
				{
					// Recursively create the directories needed for the file
					mkdir($directory.'/', 0777, TRUE);
				}

				file_put_contents($public_path, $this->response->body());
			}
		}
		else
		{
			// Return a 404 status
			$this->response->status(404);
		}
	}

	private function mkdir($path, $mode = 0777, $recursive = TRUE)
	{
		$oldumask = umask(0);
		if( !is_dir($path) ) mkdir($path, $mode, $recursive);
		umask($oldumask);
	}
	
}
