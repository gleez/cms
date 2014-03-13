<?php
/**
 * Admin Tools Controller
 *
 * @package    Gleez\Controller\Admin
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Admin_Tool extends Controller_Admin {

	public function action_index()
	{
		$this->title = __('Administer Tools');
	}

	public function action_php()
	{
		//getting the php info clean!
		ob_start();
		phpinfo();
		$phpinfo = ob_get_contents();
		ob_end_clean();

		//strip the body html
		$phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);

		//format the data
		$phpinfo = str_replace('<table', '<table class="table table-striped table-bordered"', $phpinfo);
		$phpinfo = str_replace('</table><br />', '</table>', $phpinfo);
		$phpinfo = str_replace(',', '<br>', $phpinfo);

		$view = View::factory('admin/tools/phpinfo')->set('phpinfo', $phpinfo);

		$this->title = __('PHP Info');
		$this->response->body($view);
	}

	public function action_db()
	{
		$db = Database::instance('default');

		//get tables names and the size and the index
		$total_space = 0;
		$tables_info = array();

		$tables = $db->query(Database::SELECT, 'SHOW TABLE STATUS');

		foreach ($tables as $table)
		{
			$tot_data = $table['Data_length'];
			$tot_idx = $table['Index_length'];
			$tot_free = $table['Data_free'];

			$tables_info[] = array( 'name' => $table['Name'],
									'rows' => $table['Rows'],
									'space' => round (($tot_data + $tot_idx) / 1024,3),
									);

			$total_space += ($tot_data + $tot_idx) / 1024;
		}

		$view = View::factory('admin/tools/dbinfo')
				->set('tables', $tables_info)
				->set('space', $total_space);

		$this->title = __('Database Stats');
		$this->response->body($view);
	}
}