<?php

return array(
	'name' => array(
		'not_empty' => 'Role name must not be empty',
		'min_length' => 'Name of Role must be at least :param2 characters long',
		'max_length' => 'Name of Role must be less than :param2 characters long',
	),
	'description' => array(
		'max_length' => 'Description of Role must be less than :param2 characters long',
	),
);