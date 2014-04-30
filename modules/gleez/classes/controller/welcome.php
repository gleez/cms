<?php
/**
 * Welcome Controller
 *
 * @package    Gleez\Controller
 * @author     Gleez Team
 * @version    1.0.1
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Welcome extends Template {

	/**
	 * Page template
	 * @var string
	 */
	public $template = 'layouts/welcome';

	/**
	 * The before() method is called before controller action.
	 */
	public function before()
	{
		// The action_index() is default
		if ($this->request->action() == 'index')
		{
			$this->request->action('welcome');
		}

		parent::before();
	}

	/**
	 * Prepare welcome page
	 */
	public function action_welcome()
	{
		// If Gleez CMS don't installed
		if ( ! Gleez::$installed)
		{
			// Send to the installer with server status
			$this->request->redirect(Route::get('install')->uri(array('action' => 'index')), 200);
		}

		Assets::css('welcome', "media/css/welcome.css", array('default'), array('weight' => 30));

		$this->title = __('Welcome!');
		$this->schemaType = 'WebPage';
		$content = View::factory('welcome');

		// Disable sidebars on welcome page
		$this->_sidebars = FALSE;

		$this->response->body($content);
	}

}
