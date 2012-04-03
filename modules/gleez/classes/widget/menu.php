<?php defined('SYSPATH') OR die('No direct access allowed.');

class Widget_Menu extends Widget {

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
                //Message::debug( Debug::vars($this->name) );
                
		return Menu::links($this->name);
	}

}