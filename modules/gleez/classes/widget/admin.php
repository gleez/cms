<?php defined('SYSPATH') OR die('No direct access allowed.');

class Widget_Admin extends Widget {

	public function info()
	{
	}
	
	public function form()
	{
	}

	public function save( array $post )
	{
	}

	public function delete( array $post )
	{
	}
	
	public function render()
	{
		switch($this->name)
                {
                        case 'donate':
                                return $this->donate();
                        break;
                        case 'welcome':
                                return $this->welcome();
                        break;
                        case 'info':
                                return $this->system_info();
                        break;
                        default:
                                return;
                }
	}

	public function donate()
	{
		return View::factory('widgets/static')->set(array(
			'title' => __('Donate'),
			'content' => __('If you use Gleez, we ask that you donate to ensure future development is possible.')
		))->render();
	}
        
	public function welcome()
	{
		return View::factory('widgets/welcome')->set(array(
			'title' => __('welcome'),
                        ))->render();
	}
        
        public function system_info()
	{
		return View::factory('widgets/systeminfo')->render();
	}
}