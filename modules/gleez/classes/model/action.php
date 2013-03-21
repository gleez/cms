<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Action Model Class
 *
 * @package    Gleez\Action
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Model_Action extends ORM {

	/**
	 * "Has many" relationships
	 * @var array
	 */
	protected $_has_many = array(
		'roles' => array(
			'model' => 'role',
			'through' => 'action_roles'
		),
	);
}
