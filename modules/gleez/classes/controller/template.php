<?php
/**
 * Abstract controller class for automatic templating
 *
 * @package    Codebench\Controller
 * @author     Gleez Team
 * @version    1.1.0
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Template extends Controller {

    /**
     * Page template
     * @var View
     */
    public $template = 'template';

    /**
     * Auto render template?
     * @var boolean
     */
    public $auto_render = TRUE;

    /**
     * Loads the template [View] object.
     */
    public function before()
    {
        parent::before();

        if ($this->auto_render === TRUE)
        {
            // Load the template
            $this->template = View::factory($this->template);
        }
    }

    /**
     * Assigns the template [View] as the request response.
     */
    public function after()
    {
        if ($this->auto_render === TRUE)
        {
            $this->response->body($this->template->render());
        }

        parent::after();
    }

}