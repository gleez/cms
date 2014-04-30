<?php
/**
 * Help task to display general instructons and list all tasks
 *
 * @package    Gleez\Minion\DB\Migrate
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Task_Db_Migrate_Reset extends Minion_Task
{
	protected function _execute(array $options)
	{

		Minion_CLI::write('db:migrate:reset runs db:drop db:create db:migrate');
	}
}
