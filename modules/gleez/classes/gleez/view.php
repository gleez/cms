<?php defined('SYSPATH') or die('No direct script access.');

class Gleez_View extends Kohana_View {
        
	/**
	 * Override set_filename in order to search our view directories for the view
	 * instead of just the 'views' directory.
	 */
	public function set_filename($file)
	{
                $path = Kohana::find_file('themes', $file);
  
                // Otherwise, revert to the "standard" Kohana method.
                if ( $path === false )
                {
        		// Otherwise, revert to the "standard" Kohana method.
			return parent::set_filename($file);
		}
	
		// Store the file path locally
		$this->_file = $path;

		return $this;
	}
	
}
