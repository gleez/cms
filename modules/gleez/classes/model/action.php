<?php defined("SYSPATH") or die("No direct script access.");

class Model_action extends ORM {
        
        
        protected $_has_many = array(
		'roles'       => array('model' => 'role', 'through' => 'action_roles'),
	);
}
