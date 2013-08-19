<?php

return array
(
	'default_format'        => 1,
	'allowed_protocols'     => '',
	'allowed_tags'          => '',
	'admin_allowed_tags'    => '',
	'formats'               => array(
		// #1
		1 => array(
			'name'    => __('Filtered HTML'),
			'weight'  => 0,
			'filters' => array(
				'html'    => array(
					'name'     => 'html',
				 	'weight'   => 0,
				 	'status'   => TRUE,
					'settings' => array(
						'html_nofollow' => TRUE,
						'allowed_html'  => '<h2> <h3> <h4> <h5> <h6> <a> <abbr> <address> <em> <strong> <b> <i> <br> <hr> <p> <cite> <blockquote> <q> <code> <ul> <ol> <li> <dl> <dt> <dd> <img> <sub> <sup> <s>',
						'url_length'    => 72,
					)
				),
				'htmlcorrector'	=> array(
					'name'        => 'htmlcorrector',
					'weight'      => 3,
					'status'      => TRUE,
					'settings'    => array()
				),
				'url'		    => array(
					'name'        => 'url',
					'weight'      => -2,
					'status'      => TRUE,
					'settings'    => array('url_length' => 72)
				),
				'autop'		     => array(
					'name'        => 'autop',
					'weight'      => 1,
					'status'      => TRUE,
					'settings'    => array()
				),
			),
			// Comma separated list
			'roles' => array(),
		),
		// #2
		2 => array(
			'name'    => __('Plain Text'),
			'weight'  => 1,
			'filters' => array(
				'plain'		=> array(
					'name'         =>'plain',
					'weight'       => 0,
					'status'       => TRUE,
					'settings'     => array()
				),
			),
			// Comma separated list
			'roles' => array(),
		),
		// #3
		3 => array(
			'name'    => __('Full HTML'),
			'weight'  => 1,
			'filters' => array(
				'htmlcorrector'	=> array(
					'name'         =>'htmlcorrector',
					'weight'       => 0,
					'status'       => TRUE,
					'settings'     => array()
				),
				'url'		=> array(
					'name'         => 'url',
					'weight'       => 0,
					'status'       => TRUE,
					'settings'     => array()
				),
				'autop'		=> array(
					'name'         => 'autop',
					'weight'       => 10,
					'status'       => TRUE,
					'settings'     => array()
				),
			),
			// Comma separated list
			'roles' => array('admin'),
		),
		// #4
		4 => array(
			'name'    => __('Markdown'),
			'weight'  => 1,
			'filters' => array(
				'markdown'	       => array(
					'name'         =>'markdown',
					'weight'       => 0,
					'status'       => TRUE,
					'settings'     => array()
				),
			),
			// Comma separated list
			'roles' => array(),
		),
	)
);
