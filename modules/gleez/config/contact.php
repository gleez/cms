<?php

return array(
	/**
	 * Subject length
	 * @var integer
	 */
	'subject_length' => 80,

	/**
	 * Body length
	 * @var integer
	 */
	'body_length' => 400,

	/**
	 * Use captcha?
	 * @var integer
	 */
	'use_captcha' => TRUE,

	/**
	 * Mail type
	 * @var array
	 */
	'types' => array(
		''          => __('Please Choose Category'),
		'advertise' => __('Advertise'),
		'feedback'  => __('Feedback'),
		'info'      => __('Info'),
		'privacy'   => __('Privacy'),
		'other'     => __('Other'),
	),
);
