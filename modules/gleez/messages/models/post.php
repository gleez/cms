<?php defined('SYSPATH') or die('No direct script access.');

return array(
	'title' => array(
		'not_empty' => ':field must not be empty',
	),
	'body' => array(
		'not_empty'  => ':field must not be empty',
		'min_length' => '',
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
		'not_empty'  => '',
	),
	'categories' => array(
		'not_empty'  => ':field must not be empty',
		'invalid'    => '',
	),
	'pubdate' => array(
		'not_empty'  => ':field must not be empty',
		'invalid'    => 'The publish date :param1 is invalid',
	),
);