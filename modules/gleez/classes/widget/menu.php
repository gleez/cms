<?php
/**
 * Menu Widget class
 *
 * @package    Gleez\Widget
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Widget_Menu extends Widget {

	public function info(){}
	public function form(){}
	public function save(array $post){}
	public function delete(array $post){}
	
	public function render()
	{
		return Menu::links($this->name, $attr = array('class' =>'menus', 'widget' => TRUE));
	}

}