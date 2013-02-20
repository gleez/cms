<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Resize images
 *
 * @package    Gleez\Media\Controller
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Controller_Resize extends Controller {

	/** @var integer Image width */
	public $width;

	/** @var integer Image height */
	public $height;

	/** @var string Resize type */
	public $resize_type;

	/** @var string Image folder */
	public $image_folder;

	/** @var string Image path */
	public $image_src;

	/** @var string Path to resized image */
	public $resized_image;

	/** @var string Image type */
	public $resized_image_type;

	public function before()
	{
		$this->image_folder = DOCROOT . 'media';

		if ( class_exists('ACL') )
		{
			ACL::Required('access content');
		}

		parent::before();
	}

	public function action_image()
	{
		$this->resize_type = $this->request->param('type', 'crop');
		$dimensions  	   = $this->request->param('dimensions', '80x80');

		list($this->width, $this->height) = explode('x', $dimensions);

		$image_src  	   = $this->request->param('file', NULL);
		$this->image_src   = (isset($_REQUEST['s']) AND ! empty($_REQUEST['s'])) ? $_REQUEST['s'] : $image_src;

		$this->cache();

		if( ! $this->resized_image)
		{
			return;
		}

		// Check if the browser sent an "if-none-match: <etag>" header, and tell if the file hasn't changed
		$this->response->check_cache(sha1($this->request->uri()).filemtime($this->resized_image), $this->request);

		$this->response->headers('content-type',  $this->resized_image_type);
		$this->response->body( Image::factory($this->resized_image)->render() );
		$this->response->headers('last-modified', date('r', filemtime($this->resized_image)));

	}

	private function cache()
	{
		// is it a remote image?
		if(URL::is_remote($this->image_src))
		{
			$path = $this->image_folder . '/imagecache/original';
			$image_original_name = "$path/".preg_replace('/\W/i', '-', $this->image_src);

			if( ! file_exists($image_original_name))
			{
				// make sure the directory(s) exist
				System::mkdir($path);

				// download image
				copy($this->image_src, $image_original_name);
			}

			unset($path);
		}
		else
		{
			// Find the file extension
			$ext = pathinfo($this->image_src, PATHINFO_EXTENSION);

			// Remove the extension from the filename
			$file = substr($this->image_src, 0, -(strlen($ext) + 1));

			// $image_original_name = Route::get('media')->uri(array('file' => $this->image_src));
			$image_original_name = Kohana::find_file('media', $file, $ext);
		}

		// if image file not found stop here
		if( ! $this->is_valid($image_original_name))
		{
			return FALSE;
		}

		$this->resized_image = "$this->image_folder/imagecache/$this->resize_type/{$this->width}x{$this->height}/$this->image_src";

		if( ! file_exists($this->resized_image))
		{
			// make sure the directory(s) exist
			$path = pathinfo($this->resized_image, PATHINFO_DIRNAME);
			System::mkdir($path);

			// Save the resized image to the public directory for future requests
			$image_function = ($this->resize_type === 'crop') ? 'crop' : 'resize';
			Image::factory($image_original_name)->$image_function($this->width, $this->height)->save($this->resized_image, 85);
		}

		return TRUE;

	}

	private function is_valid($image_path)
	{
		try
		{
			// get the size and MIME type of the requested image
			$size = GetImageSize($image_path);
		}
		catch(Exception $e)
		{}

		// make sure that the requested file is actually an image
		if( ! isset($size) OR ! is_array($size) OR substr($size['mime'], 0, 6) != 'image/')
		{
			if(URL::is_remote($image_path))
		{
			unlink($image_path);
		}

			$this->response->status(404);
			$this->response->body('Error: requested file is not an accepted type: ' . $this->image_src);
			return FALSE;
		}

		$this->resized_image_type = $size['mime'];

		return TRUE;
	}

}
