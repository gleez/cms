<?php
/**
 * Widget base class
 *
 * All widgets should extend this class.
 *
 * @package    Gleez\Widget
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 *
 * @todo       Add docs
 */
abstract class Widget {

	/**
	 * Widget Name
	 * @var string
	 */
	protected $name;

	/**
	 * Widget Object
	 * @var Widget
	 */
	protected $widget;

	/**
	 * Create a new widget instance
	 *
	 * Example:
	 * ~~~
	 * $widget = Widget::factory($name, $widget);
	 * ~~~
	 *
	 * @param   string  $name    Widget name
	 * @param   Widget  $widget  Widget object
	 * @return  Widget
	 */
	public static function factory($name, $widget)
	{
		// get class name if it has slash for multiple widgets, ex menu/management or static/donate
		$split_name = explode('/', $name);
		$name = array_shift($split_name);

		// Set class name
		$widget_class = 'Widget_'.ucfirst($name);
		$name = isset($split_name[0]) ? $split_name[0] : $name;

		return new $widget_class($name, $widget);
	}

	public function __construct($name, $widget)
	{
		$this->name   = $name;
		$this->widget = $widget;
	}

	public function __toString()
	{
		return $this->render();
	}

	abstract public function info();

	abstract public function form();

	abstract public function save(array $post);

	abstract public function delete(array $post);

	abstract public function render();
}