<?php defined("SYSPATH") or die("No direct script access.");

class Model_Module extends ORM {
        
        protected $_table_columns = array(
					'id'       => array('type'=>'int'),
					'name'     => array('type'=>'string'),
					'active'   => array('type'=>'int'),
					'weight'   => array('type'=>'int'),
					'version'  => array('type'=>'float'),
					'path'     => array('type'=>'string'),
					);

}
