<?php

return array(
	/**
	 * Default Page Status (eg: draft, review, publish, etc)
	 * @var string
	 */
	'default_status' => 'draft',

	/**
	 * Pages per page (eg: 5, 10, 15, etc)
	 * @var integer
	 */
	'items_per_page' => 15,

	/**
	 * Enable captcha
	 * @var boolean
	 */
	'use_captcha' => FALSE,

	/**
	 * Enable to set page author
	 * @var boolean
	 */
	'use_authors' => TRUE,

	/**
	 * Enable teaser
	 * @var boolean
	 */
	'use_excerpt' => FALSE,

	/**
	 * Enable comments
	 * @var boolean
	 */
	'use_comment' => TRUE,

	/**
	 * View submitted info in views
	 * @var boolean
	 */
	'use_submitted' => TRUE,

	/**
	 * Enable taxonomy. Array of term id's for sets or FALSE to disable
	 * @var mixed
	 */
	'use_category' => FALSE,


	/** @var boolean Enable tags */
	'use_tags' => TRUE,

	/**
	 * Enable login buttons above comment form
	 * @var boolean
	 */
	'use_provider_buttons' => TRUE,

	/**
	 * Enable per page caching for performance
	 * @var boolean
	 */
	'use_cache' => FALSE,
        
	/**
	 * Allow people to post Comment(s): 0 - disabled, 1 - read, 2 - read/write
	 * @var integer
	 */
	'comment' => 0,

	/**
	 * Comment display mode
	 * @var integer
	 */
	'comment_default_mode' => 0,

	/**
	 * Allow anonymous commenting (with contact information)
	 * @var boolean
	 */
	'comment_anonymous' => FALSE,

	/**
	 * Comments per page
	 * @var integer
	 */
	'comments_per_page' => 20,

	/**
	 * Comments displayed with the older/new comments ('asc' OR 'desc')
	 * @var string
	 */
	'comment_order' => 'asc',

	/**
	 * Use primary image?
	 * @var boolean
	 */
	'primary_image' => TRUE,
);
