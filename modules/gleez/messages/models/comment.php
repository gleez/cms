<?php

return array(
	'author' => array(
		'not_empty' => 'You must provide a username',
		'invalid' => 'The username :param1 does not exist',
	),
	'guest_name' => array(
		'not_empty' => 'You must provide a name',
		'invalid' => 'The name :param1 does not exist',
		'registered_user' => 'The name :param1 you used belongs to a registered user',
	),
	'guest_email' => array(
		'not_empty' => 'You must provide a valid email',
		'invalid' => 'The email :param1 you specified is not valid',
	),
	'post_id' => array(
		'not_empty' => 'You must provide a post id',
		'invalid' => 'The post id :param1 you specified is not valid',
	),
	'created' => array(
		'not_empty' => 'You must provide a publish date',
		'invalid' => 'The publish date :param1 you specified is not valid',
	),
);