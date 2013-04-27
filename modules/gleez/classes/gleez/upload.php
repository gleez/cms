<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Upload Class Helper
 *
 * @package    Gleez\Helpers
 * @author     Sandeep Sangamreddi - Gleez
 * @author     Sergey Yakovlev - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
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