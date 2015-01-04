<?php
/**
 * Breadcrumb Class
 *
 * Automatically create breadcrumb links.
 *
 * @package    Gleez\Helpers
 * @author     Gleez Team
 * @version    2.0.0
 * @copyright  (c) 2011-2015 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Breadcrumb
{
	/**
	 * Default view
	 * @var string
	 */
	public static $default = 'breadcrumb';

	/**
	 * Singleton instance
	 * @var \Breadcrumb
	 */
	protected static $instance;

	/**
	 * Stack of breadcrumb items
	 * @var array
	 */
	protected $items = array();

	/**
	 * Constructor
	 * @return \Breadcrumb
	 */
	protected function __construct(){}

	/**
	 * Render the breadcrumb
	 *
	 * @since   2.0.0
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Get the unique instance
	 *
	 * @since   2.0.0
	 *
	 * @return  \Breadcrumb
	 */
	public static function instance()
	{
		if (is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Set the template name
	 *
	 * @param  string  $view
	 */
	public function setView($view)
	{
		static::$default = $view;
	}

	/**
	 * Add a new item to the breadcrumb stack
	 *
	 * @since   2.0.0  Initial implementation
	 * @since   2.0.0  Return \Breadcrumb
	 *
	 * @param  string  $label  Item label
	 * @param  string  $url    Item url [Optional]
	 */
	public function addItem($label, $url = NULL)
	{
		$this->items[] = array(
			'label' => $label,
			'url'   => $url
		);

		return $this;
	}

	/**
	 * Clear all items from breadcrumb stack
	 *
	 * @since   2.0.0
	 *
	 * @return  \Breadcrumb
	 */
	public function clear()
	{
		$this->items[] = array();

		return $this;
	}

	/**
	 * Get all items from breadcrumb stack
	 *
	 * @since   2.0.0
	 *
	 * @return array
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * Render the breadcrumb
	 *
	 * @return  string
	 * @uses    Config::get
	 */
	public function render()
	{
		$view = View::factory(static::$default)
					->set('items',         $this->items)
					->set('items_count',   count($this->items))
					->set('separator',     Config::get('breadcrumb.separator', '&nbsp;&gt;&nbsp;'))
					->set('last_linkable', Config::get('breadcrumb.last_linkable', false));

		return $view->render();
	}
}