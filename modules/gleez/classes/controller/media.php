<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Media Controller
 *
 * @package   Gleez
 * @category  Controller
 * @author    Sandeep Sangamreddi - Gleez
 * @copyright (c) 2012 Gleez Technologies
 * @license   http://gleezcms.org/license
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

    if ($file_name = Kohana::find_file($this->config->get('public_dir', 'media'), $file, $ext))
    {
      // Check if the browser sent an "if-none-match: <etag>" header, and tell if the file hasn't changed
      $this->response->check_cache(sha1($this->request->uri()).filemtime($file_name), $this->request);

      // Send the file content as the response
      $this->response->body(file_get_contents($file_name));

      // Set the proper headers to allow caching
      $this->response->headers('content-type',  File::mime_by_ext($ext));
      $this->response->headers('last-modified', date('r', filemtime($file_name)));
      //this is ignored by check_cache
      $this->response->headers('cache-control', 'public, max-age=2592000');

      if ($this->config->get('cache', FALSE))
      {
        // Save the contents to the public directory for future requests
        $public_path = $this->config->get('public_dir', 'media').DIRECTORY_SEPARATOR.$file.'.'.$ext;
        $directory = dirname($public_path);

        if (! is_dir($directory))
        {
          // Recursively create the directories needed for the file
          $this->mkdir($directory, 0777, TRUE);
        }

        file_put_contents($public_path, $this->response->body());
      }
    }
    else
    {
      Kohana::$log->add(LOG::ERROR, 'Media controller error while loading file: `:file`',
        array(
          ':file' => $file
        )
      );
      // Return a 404 status
      $this->response->status(404);
    }
  }

  /**
   * Attempts to create the directory specified by `$path`
   *
   * To create the nested structure, the `$recursive` parameter
   * to mkdir() must be specified.
   *
   * @param   string  $path       The directory path
   * @param   integer $mode       Set permission mode (as in chmod) [Optional]
   * @param   boolean $recursive  Create directories recursively if necessary [Optional]
   * @return  boolean             Returns TRUE on success or FALSE on failure
   *
   * @see     http://php.net/manual/en/function.mkdir.php
   * @todo    Overload is not an elegant by design
   */
  private function mkdir($path, $mode = 0777, $recursive = TRUE)
  {
    $oldumask = umask(0);
    if(! is_dir($path))
    {
      return @mkdir($path, $mode, $recursive);
    }
    umask($oldumask);
  }

}
