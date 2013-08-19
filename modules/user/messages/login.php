<?php

return array(
	'name' => array(
	    'not_empty' => 'You must provide a username',
	    'min_length' => 'The username must be at least :param2 characters long',
	    'max_length' => 'The username must be less than :param2 characters long',
	    'invalid' => 'Password or username is incorrect',
	    'blocked' => 'This account is blocked',
	),
	'pass' => array(
	    'not_empty' => 'You must provide a password',
	),
	'mail' => array(
	    'not_empty' => 'You must provide an email address',
	    'invalid' => 'Password or username is incorrect',
	),
);