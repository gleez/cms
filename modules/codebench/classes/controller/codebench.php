<?php
/**
 * Codebench controller
 *
 * @package    Codebench\Controller
 * @author     Gleez Team
 * @version    1.1.0
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Codebench extends Controller_Template {

	/**
	 * Page template
	 * @var View
	 */
	public $template = 'codebench';

	public function action_index()
	{
		$class = $this->request->param('class');

		// Convert submitted class name to URI segment
		if (isset($_POST['class']))
		{
			$this->request->redirect('codebench/'.trim($_POST['class']));
		}

		// Pass the class name on to the view
		$this->template->class = (string) $class;

		// Try to load the class, then run it
		if (Kohana::auto_load($class) === TRUE)
		{
			$codebench = new $class;
			$this->template->codebench = $codebench->run();
		}
	}
}
