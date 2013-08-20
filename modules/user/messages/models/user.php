<?php

return array(
	'name' => array(
		'not_empty' => 'You must provide a username',
		'min_length' => 'The username must be at least :param2 characters long',
		'max_length' => 'The username must be less than :param2 characters long',
		'username_available' => 'This username is not available',
		'invalid' => 'This username or password is not valid',
		'unique' => 'This username already exists',
	),
	'pass' => array(
		'not_empty' => 'You must provide a password',
	),
	'mail' => array(
		'not_empty' => 'You must provide an email address',
		'email_available' => 'This email already exists',
		'unique' => 'This email already exists',
	),
	'_external' => array(
		'pass_confirm' => 'The values you entered in the password fields did not match',
		'old_pass' => array(
			'check_password' => 'Old password is incorrect',
		),
	),
	'homepage' => array (
		'url' => ':field must be a valid address with the http:// or https:// prefix',
	),
	'bio' => array (
		'max_length' => 'Bio must be less than :param2 characters long',
	),
);