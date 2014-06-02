<?php
/**
 * Displays the current migration status of migrations in all groups
 *
 * @package    Gleez\Minion\DB\Migrate
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Task_Db_Migrate_Status extends Minion_Task
{
	/**
	 * Execute the task
	 *
	 * @param array $options Config for the task
	 */
	protected function _execute(array $options)
	{
		$model = new Model_Migration();
		$view = new View('minion/db/status');

		$view->groups = $model->get_group_statuses();

		echo $view;
	}
}
