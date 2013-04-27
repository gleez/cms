<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Gleez Views Class
 *
 * @package    Gleez\Base
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Gleez_View extends Kohana_View {

	/**
	 * Override set_filename in order to search our view directories for the view
	 * instead of just the 'views' directory.
	 */
	public function set_filename($file)
	{
		$path = Kohana::find_file('themes', $file);

		// Otherwise, revert to the "standard" Kohana method.
		if ($path === FALSE)
		{
			// Otherwise, revert to the "standard" Kohana method.
			return parent::set_filename($file);
		}

		// Store the file path locally
		$this->_file = $path;

		return $this;
	}

}