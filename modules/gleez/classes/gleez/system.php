<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Gleez System class helper
 *
 * @package   Gleez
 * @category  Core/Utils
 * @version   0.9.8.1
 * @author    Sergey Yakovlev (me@klay.me)
 * @copyright (c) 2012 Gleez Technologies
 * @license   http://gleezcms.org/license
 */
class Gleez_System {

  /**
   * Get the server load averages (if possible)
   *
   * @return  string
   * @see     http://php.net/manual/en/function.sys-getloadavg.php
   */
  public static function get_avg()
  {
    if (function_exists('sys_getloadavg') && is_array(sys_getloadavg()))
    {
      $load_averages = sys_getloadavg();
      array_walk($load_averages, create_function('&$v', '$v = round($v, 3);'));
      $server_load = $load_averages[0].' '.$load_averages[1].' '.$load_averages[2];
    }
    else if (@is_readable('/proc/loadavg'))
    {
      // We use @ just in case
      $fh = @fopen('/proc/loadavg', 'r');
      $load_averages = @fread($fh, 64);
      @fclose($fh);

      $load_averages = empty($load_averages) ? array() : explode(' ', $load_averages);

      $server_load = isset($load_averages[2]) ? $load_averages[0].' '.$load_averages[1].' '.$load_averages[2] : 'Not available';
    }
    else if (! in_array(PHP_OS, array('WINNT', 'WIN32')) && preg_match('/averages?: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/i', @exec('uptime'), $load_averages))
    {
      $server_load = $load_averages[1].' '.$load_averages[2].' '.$load_averages[3];
    }

    else
      $server_load = $lang_admin_index['Not available'];

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
   * @see     http://php.net/manual/en/function.mkdir.php
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
