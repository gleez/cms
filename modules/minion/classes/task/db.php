<?php
/**
 * Help task to display general instructons and list all tasks
 *
 * @package    Gleez\Minion\DB
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Task_Db extends Minion_Task
{
	protected function _execute(array $options)
	{
		//Minion_CLI::write('Database Migrations');

		echo new View('minion/db/help');
	}
}
