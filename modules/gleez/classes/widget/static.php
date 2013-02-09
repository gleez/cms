<?php defined('SYSPATH') OR die('No direct access allowed.');

class Widget_Static extends Widget {

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
		return View::factory('widgets/static')->set(array(
			'title'   => Text::plain($this->widget->title),
			'content' => Text::markup($this->widget->body, $this->widget->format)
		))->render();
	}

}