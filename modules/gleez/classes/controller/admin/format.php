<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Admin Format Controller
 *
 * @package   Gleez\Admin\Controller
 * @author    Sandeep Sangamreddi - Gleez
 * @copyright (c) 2011-2013 Gleez Technologies
 * @license   http://gleezcms.org/license
 */
class Controller_Admin_Format extends Controller_Admin {

	/**
	 * Formats list
	 *
	 * @uses  View::factory
	 * @uses  Format::get_all
	 * @uses  Assets::tabledrag
	 */
	public function action_list()
	{
		$this->title = __('Text formats');

		$formats = $this->_format->get_all();

		$view = View::factory('admin/format/list')
					->set('formats', $formats);

		$this->response->body($view);

		if ( ! $this->_internal)
		{
			Assets::tabledrag('text-format-order', 'order', 'sibling', 'text-format-order-weight');
		}
	}

	/**
	 * Formats setting
	 */
	public function action_configure()
	{
		$id = $this->request->param('id');

		// Get required format
		$format = $this->_format->get($id, FALSE);

		if ( ! $format)
		{
			Message::error(__('Text Format doesn\'t exists!'));
			Kohana::$log->add(LOG::ERROR, 'Attempt to access non-existent format id :id', array(':id' => $id));

			if ( ! $this->_internal)
			{
				$this->request->redirect( Route::get('admin/format')->uri(), 404);
			}
		}

		$all_roles = ORM::factory('role')
						->find_all()
						->as_array('id', 'name');

		$filters = InputFilter::filters();

		$enabled_filters = $format['filters'];

		// Form attributes
		$params = array('id' => $id, 'action' => 'configure');

		//Message::error(Kohana::debug($formats[$id]['filters']));

		$this->title = __('Configure %name format', array('%name' => $format['name']));

		$view = View::factory('admin/format/form')
					->set('roles', $all_roles)
					->set('filters', $filters)
					->set('enabled_filters', $enabled_filters)
					->set('format', $format)
					->set('params', $params);

		if ($this->valid_post('filter'))
		{
			unset($_POST['filter'], $_POST['_token'], $_POST['_action']);
			Message::info(__('Not implemented yet!'));
		}

		$this->response->body($view);

		if ( ! $this->_internal)
		{
			Assets::tabledrag('filter-order', 'order', 'sibling', 'filter-order-weight', NULL, NULL, TRUE);
		}
	}
}
