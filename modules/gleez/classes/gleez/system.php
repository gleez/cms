<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Gleez Core Utils class
 *
 * @package   Gleez
 * @category  Core/Utils
 * @author    Sergey Yakovlev
 * @copyright (c) 2013 Gleez Technologies
 * @license   http://gleezcms.org/license
 */
class Gleez_System {

  /**
   * Get the server load averages (if possible)
   *
   * @return  string
   * @link    http://php.net/manual/en/function.sys-getloadavg.php sys-getloadavg()
   */
  public static function get_avg()
  {
    // Default return
    $not_available = __('Not available');

    if (function_exists('sys_getloadavg') && is_array(sys_getloadavg()))
    {
      $load_averages = sys_getloadavg();
      array_walk($load_averages, create_function('&$v', '$v = round($v, 3);'));
      $server_load = $load_averages[0].' '.$load_averages[1].' '.$load_averages[2];
    }
    elseif (@is_readable('/proc/loadavg'))
    {
      // We use @ just in case
      $fh = @fopen('/proc/loadavg', 'r');
      $load_averages = @fread($fh, 64);
      @fclose($fh);

      $load_averages = empty($load_averages) ? array() : explode(' ', $load_averages);

      $server_load = isset($load_averages[2]) ? $load_averages[0].' '.$load_averages[1].' '.$load_averages[2] : $not_available;
    }
    elseif (! in_array(PHP_OS, array('WINNT', 'WIN32')) && preg_match('/averages?: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/i', @exec('uptime'), $load_averages))
    {
      $server_load = $load_averages[1].' '.$load_averages[2].' '.$load_averages[3];
    }
    else
      $server_load = $not_available;

    return $server_load;
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
   * @link    http://php.net/manual/en/function.mkdir.php mkdir()
   */
  public static function mkdir($path, $mode = 0777, $recursive = TRUE)
  {
    $oldumask = umask(0);
    if(! is_dir($path))
    {
      return @mkdir($path, $mode, $recursive);
    }
    umask($oldumask);
  }

}
