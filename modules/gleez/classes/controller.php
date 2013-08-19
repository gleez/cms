<?php
/**
 * Abstract controller class
 *
 * Controllers should only be created using a [Request].
 *
 * Controllers methods will be automatically called in the following
 * order by the request:
 * ~~~
 * $controller = new Controller_Foo($request);
 * $controller->before();
 * $controller->action_bar();
 * $controller->after();
 * ~~~
 *
 * The controller action should add the output it creates to
 * `$this->response->body($output)`, typically in the form of a [View], during the
 * "action" part of execution.
 *
 * @package    Gleez\Controller
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
abstract class Controller {

	/**
	 * Request that created the controller
	 * @var Request
	 */
	public $request;

	/**
	 * The response that will be returned from controller
	 * @var Response
	 */
	public $response;

	/**
	 * Creates a new controller instance
	 *
	 * Each controller must be constructed with the request object that created it.
	 *
	 * @param  Request   $request   Request that created the controller
	 * @param  Response  $response  The request's response
	 */
	public function __construct(Request $request, Response $response)
	{
		// Assign the request to the controller
		$this->request = $request;

		// Assign a response to the controller
		$this->response = $response;
	}

	/**
	 * Automatically executed before the controller action
	 *
	 * Can be used to set class properties, do authorization checks,
	 * and execute other custom code.
	 */
	public function before()
	{
		// Nothing by default
	}

	/**
	 * Automatically executed after the controller action
	 *
	 * Can be used to apply transformation to the request response,
	 * add extra output, and execute other custom code.
	 */
	public function after()
	{
		// Nothing by default
	}

}
