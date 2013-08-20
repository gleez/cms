<?php

return array(
	'pass' => array(
		'not_empty' => 'You must provide a password',
		'min_length' => 'Password must be at least :param2 characters long',
	),
	'pass_confirm' => array (
		'not_empty' => 'You must confirm password',
		'matches' => 'Password Confirm must be the same as Password',
	),
	'old_pass' => array(
		'not_empty' => 'You must provide old password',
		'check_password' => 'Old password is incorrect',
	),
);