<?php

return array(
	'title' => array(
		'not_empty' => ':field must not be empty',
	),
	'body' => array(
		'not_empty'  => ':field must not be empty',
		'min_length' => 'Body must be at least :param2 characters long',
	),
	'author' => array(
		'not_empty'  => ':field must not be empty',
		'invalid'    => 'The username :param1 does not exist',
	),
	'created' => array(
		'not_empty'  => ':field must not be empty',
		'invalid'    => 'The date :param1 is invalid',
	),
	'status' => array(
		'not_empty'  => ':field must not be empty',
	),
	'categories' => array(
		'not_empty'  => ':field must not be empty',
		'invalid'    => 'You must select at least one category',
	),
	'pubdate' => array(
		'not_empty'  => ':field must not be empty',
		'invalid'    => 'The publish date :param1 is invalid',
	),
);