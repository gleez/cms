<?php

return array(
	'author' => array(
	    'not_empty' => 'You must provide a username',
	    'invalid' => 'The username :param1 does not exist',
	),
	'created' => array(
	    'not_empty' => 'You must provide a date',
	    'invalid' => 'The date :param1 does not exist',
	),
	'pubdate' => array(
	    'not_empty' => 'You must provide a publish date',
	    'invalid' => 'The publish date :param1 does not exist',
	),
	'categories' => array(
	    'not_empty' => 'You must select at least one category',
	    'invalid' => 'You must select at least one category',
	),
);