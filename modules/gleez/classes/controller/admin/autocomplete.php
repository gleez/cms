<?php
/**
 * Admin Autocomplete Controller
 *
 * @package    Gleez\Controller\Admin
 * @author     Gleez Team
 * @version    1.0.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Admin_Autocomplete extends Controller {

	/**
	 * The before() method is called before controller action
	 *
	 * @uses    Request::is_ajax
	 * @uses    Request::uri
	 * @throws  HTTP_Exception_404
	 */
	public function before()
	{
		// Ajax request only!
		if ( ! $this->request->is_ajax())
		{
			throw HTTP_Exception::factory(404, 'Accessing an ajax request :type externally',
				array(':type' => '<small>'.$this->request->uri().'</small>')
			);
		}

		parent::before();
	}

	/**
	 * The after() method is called after controller action
	 *
	 * @uses  Request::is_ajax
	 * @uses  Response::headers
	 */
	public function after()
	{
		if ($this->request->is_ajax())
		{
			$this->response->headers('content-type',  'application/json; charset='.Kohana::$charset);
		}

		parent::after();
	}

	/**
	 * Retrieve a JSON object containing autocomplete suggestions for existing aliases
	 *
	 * @uses  ACL::required
	 * @uses  DB::select
	 * @uses  Text::plain
	 * @uses  JSON::encode
	 */
	public function action_links()
	{
		ACL::required('administer menu');

		$string  = $this->request->param('string', FALSE);
		$matches = array();

		if ($string)
		{
			$result  = DB::select('alias')
						->from('paths')
						->where('alias', 'LIKE', $string.'%')
						->limit('10')
						->execute();

			foreach ($result as $link)
			{
				$matches[$link['alias']] = Text::plain($link['alias']);
			}
		}

		$this->response->body(JSON::encode($matches));
	}



}