<?php
/**
 * Action Model Class
 *
 * @package    Gleez\ORM\Action
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
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
