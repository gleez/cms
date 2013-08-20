<?php

// Catch-all route for Codebench classes to run
Route::set('codebench', 'codebench(/<class>)')
	->defaults(array(
		'controller' => 'codebench',
		'action' => 'index',
		'class' => NULL));
