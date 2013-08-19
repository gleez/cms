<?php

return array(

	/**
	 * Default blog status (eg: draft, review, publish, etc)
	 * @var string
	 */
	'default_status' => 'draft',

	/**
	 * Pages per page (ex: 5, 10, 15, etc)
	 * @var integer
	 */
	'items_per_page' => 15,

	/**
	 * Enable captcha?
	 * @var boolean
	 */
	'use_captcha' => FALSE,

	/**
	 * Enable to set page author?
	 * @var boolean
	 */
	'use_authors' => TRUE,

	/**
	 * Enable teaser?
	 * @var boolean
	 */
	'use_excerpt' => FALSE,

	/**
	 * Enable comment?
	 * @var boolean
	 */
	'use_comment' => TRUE,

	/**
	 * Enable tags?
	 * @var boolean
	 */
	'use_tags' => FALSE,

	/**
	 * Show submitted info in views?
	 * @var boolean
	 */
	'use_submitted' => TRUE,

	/**
	 * Enable terms?
	 * @var boolean
	 */
	'use_category' => FALSE,

	/**
	 * Enable login buttons above comment form?
	 * @var boolean
	 */
	'use_provider_buttons' => TRUE,

	/**
	 * Enable per page caching for performance
	 * @var boolean
	 */
	'use_cache' => FALSE,

	/**
	 * Allow people to post Comments (0: disabled, 1: read, 2: read/write)
	 * @var integer
	 */
	'comment' => 1,

	/**
	 * Comment display mode
	 * @var boolean
	 */
	'comment_default_mode' => FALSE,

	/**
	 * Allow anonymous commenting (with contact information)?
	 */
	'comment_anonymous' => FALSE,

	/**
	 * Comments per page
	 * @var integer
	 */
	'comments_per_page' => 20,

	/**
	 * Comments displayed with the older/new comments (asc||desc)
	 * @var string
	 */
	'comment_order' => 'asc',

	/**
	 * Use primary image?
	 * @var boolean
	 */
	'primary_image' => TRUE,
);
