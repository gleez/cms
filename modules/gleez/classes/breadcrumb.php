<?php
/**
 * Breadcrumb Class
 *
 * Automatically create breadcrumb links.
 *
 * @package    Gleez\HTML
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 * @see        https://github.com/xavividal/Breadcrumbs
 */
class Breadcrumb
{
	/**
	 * Default view
	 * @var string
	 */
	protected $_view = 'breadcrumb';

	/**
	 * Singleton instance
	 * @var \Breadcrumb
	 */
	protected static $_instance;

	/**
	 * Stack of breadcrumb items
	 * @var array
	 */
	protected $_items = array();

	/**
	 * Constructor
	 * @return \Breadcrumb
	 */
	protected function __construct(){}

	/**
	 * Get the unique instance
	 *
	 * @return  \Breadcrumb
	 */
	public static function factory()
	{
		if (self::$_instance === NULL)
		{
			self::$_instance = new Breadcrumb;
		}
		return self::$_instance;
	}

	/**
	 * Set the template name
	 *
	 * @param  string  $view
	 */
	public function setView($view)
	{
		$this->_view = $view;
	}

	/**
	 * Add a new item to the breadcrumb stack
	 *
	 * @param  string  $label
	 * @param  string  $url
	 */
	public function addItem($label, $url = NULL)
	{
		$this->_items[] = array(
			'label' => $label,
			'url'   => $url
		);
	}

	/**
	 * Render the breadcrumb
	 *
	 * @return  string
	 */
	public function render()
	{
		$view              = View::factory($this->_view);
		$view->items       = $this->_items;
		$view->items_count = count($this->_items);

		$view->separator     = Config::get('breadcrumb.separator', '&nbsp;&gt;&nbsp;');
		$view->last_linkable = Config::get('breadcrumb.last_linkable', FALSE);

		return $view->render();
	}
}