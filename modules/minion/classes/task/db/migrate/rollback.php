<?php
/**
 * Help task to display general instructons and list all tasks
 *
 * @package    Gleez\Minion\DB\Rollback
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Task_Db_Migrate_Rollback extends Task_Db_Migrate
{
	protected function __construct()
	{
		$this->_options['up'] 	= FALSE;
		$this->_options['down'] = TRUE;

		parent::__construct();
	}
}
