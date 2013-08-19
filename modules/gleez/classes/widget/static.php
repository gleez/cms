<?php
/**
 * Menu Widget class
 *
 * @package    Gleez\Widget
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2012 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Widget_Static extends Widget {

	public function info(){}
	public function form(){}
	public function save(array $post){}
	public function delete(array $post){}
	
	public function render()
	{
		return View::factory('widgets/static')
			->set(array(
					'title' => Text::plain($this->widget->title),
					'content' => Text::markup($this->widget->body, $this->widget->format)
			))
			->render();
	}

}