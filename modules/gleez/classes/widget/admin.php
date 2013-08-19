<?php
/**
 * Admin Widget class
 *
 * @package    Gleez\Widget
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2012 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Widget_Admin extends Widget {

	public function info() {}
	public function form() {}
	public function save(array $post) {}
	public function delete(array $post) {}

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
			case 'shortcut':
				return $this->shortcut();
			break;
			default:
				return;
			}
	}

	public function shortcut()
	{
		$menus = Menu::items('management')->get_items();
		unset($menus['administer']);
		return View::factory('widgets/shortcuts')
				->set(array( 'items' => $menus ))
				->render();
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
			'title' => __('Welcome'),
		))->render();
	}

	public function system_info()
	{
		return View::factory('widgets/systeminfo')->render();
	}
}