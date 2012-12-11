<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Extending Kohana Upload helper
 */
class Gleez_Upload extends Kohana_Upload {

  /**
   * Returns PHP max upload filesize
   *
   * @return  integer
   */
  public static function get_max_size()
  {
    $max_size = ini_get('upload_max_filesize');
    $mul = substr($max_size, -1);
    $mul = ($mul == 'M' ? 1048576 : ($mul == 'K' ? 1024 : ($mul == 'G' ? 1073741824 : 1)));
    return $mul * (int) $max_size;
  }

}
