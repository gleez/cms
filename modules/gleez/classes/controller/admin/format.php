<?php
/**
 * Admin Format Controller
 *
 * @package    Gleez\Controller\Admin
 * @author     Gleez Team
 * @version    1.0.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Admin_Format extends Controller_Admin {

	/**
	 * The before() method is called before controller action.
	 */
	public function before()
	{
		ACL::required('administer formats');
		parent::before();
	}

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

		$total = $this->_format->count_all();

		if ($total == 0)
		{
			Log::info('No formats found.');
			$this->response->body(View::factory('admin/format/none'));

			return;
		}

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
	 *
	 * @uses  InputFilter::filters
	 * @uses  Assets::tabledrag
	 * @uses  Config::load
	 * @uses  Message::error
	 * @uses  Filter::all
	 */
	public function action_configure()
	{
		$id = $this->request->param('id', NULL);

		// Get required format
		$format = $this->_format->get($id);
		$config = Config::load('inputfilter');

		if (is_null($format))
		{
			Log::error('Attempt to access non-existent format id :id', array(':id' => $id));
			Message::error(__('Text Format doesn\'t exists!'));

			$this->request->redirect(Route::get('admin/format')->uri(), 404);
		}

		$fallback_format = (int) $config->default_format;
		$formats = $this->_format->get_all();
		$formats[$id]['id'] = $id;

		$all_roles = ORM::factory('role')->find_all()->as_array('id', 'name');
		$filters = Filter::all();
		$enabled_filters = $formats[$id]['filters'];

		// Form attributes
		$params = array('id' => $id, 'action' => 'configure');

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
