<?php
/**
 * Setting the Routes
 *
 * @package    Gleez\Routing
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
if ( ! Route::cache())
{
// -- Gleez media routes -------------------------------------------------------

	// Image resize
	Route::set('resize', 'media/imagecache/<type>/<dimensions>(/<file>)', array(
		'dimensions' => '\d+x\d+',
		'type'       => 'crop|ratio|resize',
		'file'       => '.+'
	))
	->defaults(array(
		'controller' => 'resize',
		'action'     => 'image',
		'type'       => 'resize'
	));

	// Static file serving (CSS, JS, images)
	Route::set('media', 'media(/<theme>)/<file>', array('file' => '.+', 'theme' => Theme::route_list()))
	->defaults(array(
		'controller' => 'media',
		'action'     => 'serve',
		'file'       => NULL,
	));

// -- Gleez backend routes -----------------------------------------------------

	Route::set('admin/autocomplete', 'admin/autocomplete/<action>(/<string>)', array(
		'string'     => '.+',
		'action'     => 'index|links',
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'autocomplete',
		'action'     => 'index',
	));
        
	Route::set('admin/module', 'admin/modules(/<action>)')
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'modules',
		'action'     => 'list',
	));

	Route::set('admin/page', 'admin/pages(/<action>(/<id>))', array(
		'id'         => '\d+',
		'action'     => 'index|list|settings|bulk'
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'page',
		'action'     => 'list',
	));

	Route::set('admin/comment', 'admin/comments(/<action>(/<id>))(/p<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'action'     => 'index|list|process|view|delete|spam|pending'
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'comment',
		'action'     => 'list',
	));

	Route::set('admin/menu', 'admin/menus(/<action>(/<id>))(/p/<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'action'     => 'list|add|edit|delete|confirm'
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'menu',
		'action'     => 'list',
	));

	Route::set('admin/menu/item', 'admin/menu/manage/<id>(/<action>)(/p/<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'action'     => 'list|add|edit|delete|confirm',
		'slug'       => '[A-Za-z0-9-]+'
	))
	->defaults(array(
		'directory'  => 'admin/menu',
		'controller' => 'item',
		'action'     => 'list',
	));

	Route::set('admin/path', 'admin/paths(/<action>(/<id>))', array(
		'id'         => '\d+',
		'action'     => 'list|add|edit|delete'
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'path',
		'action'     => 'list',
	));

	Route::set('admin/tag', 'admin/tags(/<action>(/<id>))', array(
		'id'         => '\d+',
		'action'     => 'list|add|edit|delete'
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'tag',
		'action'     => 'list',
	));

	Route::set('admin/taxonomy', 'admin/taxonomy(/<action>(/<id>))(/p<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'action'     => 'list|add|edit|delete'
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'taxonomy',
		'action'     => 'list',
	));

	Route::set('admin/term', 'admin/terms(/<action>)/<id>(/p<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'action'     => 'list|add|edit|delete|confirm'
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'term',
		'action'     => 'list',
	));

	Route::set('admin/widget', 'admin/widgets(/<action>(/<id>))(/p<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'action'     => 'index|list|view|add|edit|delete|reset|confirm|clone'
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'widget',
	));

	Route::set('admin/format', 'admin/formats(/<action>(/<id>))', array(
		'id'         => '\d+',
		'action'     => 'list|view|add|edit|delete|configure|reset'
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'format',
		'action'     => 'list'
	));

	Route::set('admin/blog', 'admin/blogs(/<action>(/<id>))', array(
		'id'         => '\d+',
		'action'     => 'index|list|settings|bulk'
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'blog',
		'action'     => 'list',
	));

	Route::set('admin/setting', 'admin/settings(/<action>)')
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'setting',
	));

	Route::set('admin', 'admin(/<controller>)(/<action>)(/<id>)(/p<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+'
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'dashboard',
	));

// -- Gleez frontend routes ----------------------------------------------------

	Route::set('autocomplete', 'autocomplete/<action>(/<type>)(/<string>)', array(
		'string'     => '(.*)',
		'action'     => 'index|user|nick|tag',
		'type'       => 'page|blog|forum'
	))
	->defaults(array(
		'controller' => 'autocomplete',
		'type'       => 'blog',
	));

	Route::set('page', 'page(/<action>)(/<id>)(/p<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'action'     => 'index|list|view|add|edit|delete|term|tag'
	))
	->defaults(array(
		'controller' => 'page',
		'action'     => 'index'
	));

	Route::set('comment', 'comment(/<action>(/<id>))(/p<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'action'     => 'list|process|add|view|edit|delete'
	))
	->defaults(array(
		'controller' => 'comment',
		'action'     => 'list',
	));

	Route::set('comments', 'comments/<group>/<action>(/<id>)(/p<page>)(<format>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'format'     => '\.\w+',
	))
	->defaults(array(
		'controller' => 'comments',
		'group'      => 'page',
		'action'     => 'public',
		'format'     => '.xhtml',
	));

	Route::set('rss', 'rss(/<controller>)(/<action>)(/<id>)(/p<page>)(/l<limit>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'limit'      => '\d+'
	))
	->defaults(array(
		'directory'  => 'feeds',
		'controller' => 'base',
		'action'     => 'list',
	));

	Route::set('blog', 'blog(/<action>)(/<id>)(/p<page>)', array(
		'id'         => '\d+',
		'page'       => '\d+',
		'action'     => 'index|list|view|add|edit|delete|tag|term'
	))
	->defaults(array(
		'controller' => 'blog',
		'action'     => 'index'
	));

	Route::set('contact', 'contact(/<action>)')
	->defaults(array(
		'controller' => 'contact',
		'action'     => 'mail',
	));

	Route::set('welcome', 'welcome(/<action>)(/<id>)')
	->defaults(array(
		'controller' => 'welcome'
	));
}

/**
 * Define Module specific Permissions
 *
 * Definition of user privileges by default if the ACL is present in the system.
 * Note: Parameter `restrict access` indicates that these privileges have serious
 * implications for safety.
 *
 * @uses  ACL::cache
 * @uses  ACL::set
 */
if ( ! ACL::cache())
{
	ACL::set('comment', array(
		'administer comment' => array(
			'title' => __('Administer Comments'),
			'restrict access' => TRUE,
			'description' => __('Administer comments and comments settings'),
		),
		'access comment' => array(
			'title' => __('Access comments'),
			'restrict access' => FALSE,
			'description' => __('Access to any published comments'),
		),
		'post comment' =>  array(
			'title' => __('Post comments'),
			'restrict access' => FALSE,
			'description' => __('Ability to publish comments'),
		),
		'skip comment approval' =>  array(
			'title' => __('Skip comment approval'),
			'restrict access' => FALSE,
			'description' => __('Ability to publish comments without approval by the moderator'),
		),
		'edit own comment' =>  array(
			'title' => __('Edit own comments'),
			'restrict access' => FALSE,
			'description' => __('Ability to editing own comments'),
		),
	));

	ACL::set('content', array(
		'administer content' => array(
			'title' => __('Administer content'),
			'restrict access' => TRUE,
			'description' => __('Most of the tasks associated with the administration of the contents of this website associated with this permission'),
		),
		'access content' => array(
			'title' => __('Access content'),
			'restrict access' => FALSE,
			'description' => __(''),
		),
		'view own unpublished content' => array(
			'title' => __('View own unpublished content'),
			'restrict access' => FALSE,
			'description' => __(''),
		),
		'administer page' => array(
			'title' => __('Administer pages'),
			'restrict access' => TRUE,
			'description' => __(''),
		),
		'create page' => array(
			'title' => __('Create pages'),
			'restrict access' => FALSE,
			'description' => __('The ability to create pages'),
		),
		'edit own page' => array(
			'title' => __('Edit own pages'),
			'restrict access' => FALSE,
			'description' => __(''),
		),
		'edit any page' => array(
			'title' => __('Edit any pages'),
			'restrict access' => FALSE,
			'description' => __(''),
		),
		'delete own page' => array(
			'title' => __('Delete own pages'),
			'restrict access' => FALSE,
			'description' => __(''),
		),
		'delete any page' => array(
			'title' => __('Delete any pages'),
			'restrict access' => FALSE,
			'description' => __(''),
		),
	));

	ACL::set('site', array(
		'administer menu' => array(
			'title' => __('Administer Menus'),
			'restrict access' => TRUE,
			'description' => __(''),
		),
		'administer paths' => array(
			'title' => __('Administer Paths'),
			'restrict access' => FALSE,
			'description' => __(''),
		),
		'administer site' => array(
			'title' => __('Administer Site'),
			'restrict access' => TRUE,
			'description' => __(''),
		),
		'administer tags' => array(
			'title' => __('Administer Tags'),
			'restrict access' => FALSE,
			'description' => __(''),
		),
		'administer terms' => array(
			'title' => __('Administer Terms'),
			'restrict access' => FALSE,
			'description' => __(''),
		),
		'administer formats' => array(
			'title' => __('Administer Formats'),
			'restrict access' => TRUE,
			'description' => __('Managing the text formats of editor'),
		),
	));

	ACL::set('contact', array(
		'sending mail' => array(
			'title' => __('Sending Mails'),
			'restrict access' => FALSE,
			'description' => __('Ability to send messages for administrators from your site'),
		),
	));

	ACL::set('blog', array(
		'administer blog' =>  array(
			'title' => __('Administer Blog'),
			'restrict access' => TRUE,
			'description' => __('Administer Blog and Blog settings'),
		),
		'create blog' => array(
			'title' => __('Create Blog post'),
			'restrict access' => FALSE,
			'description' => '',
		),
		'edit own blog' =>  array(
			'title' => __('Edit own Blog post'),
			'restrict access' => FALSE,
			'description' => '',
		),
		'edit any blog' => array(
			'title' => __('Edit any Blog posts'),
			'restrict access' => FALSE,
			'description' => '',
		),
		'delete own blog' => array(
			'title' => __('Delete own Blog post'),
			'restrict access' => FALSE,
			'description' => '',
		),
		'delete any blog' => array(
			'title' => __('Delete any Blog posts'),
			'restrict access' => FALSE,
			'description' => '',
		),
	));

	/** Cache the module specific permissions in production */
	ACL::cache(Kohana::$environment === Kohana::PRODUCTION);
}

/**
 * Load the filter cache
 *
 * @uses  Filter::cache
 * @uses  Filter::set
 * @uses  Text::html
 * @uses  Text::htmlcorrector
 * @uses  Text::autop
 * @uses  Text::plain
 * @uses  Text::autolink
 * @uses  Text::initialcaps
 * @uses  Text::markdown
 */
if ( ! Filter::cache())
{
		Filter::set('html',  array(
			'prepare callback' => FALSE,
			'process callback' => 'Text::html'
		))
		->title(__('Limit allowed HTML tags'))
		->description(__('Limit Allowed HTML tags'))
		->settings(array(
			'html_nofollow' => TRUE,
			'allowed_html'  => '<a> <em> <strong> <cite> <blockquote> <code> <ul> <ol> <li> <dl> <dt> <dd>'
		));

		Filter::set('htmlcorrector',  array(
			'prepare callback' => FALSE,
			'process callback' => 'Text::htmlcorrector'
		))
		->title(__('Correct faulty and chopped off HTML'));

		Filter::set('autop',  array(
			'prepare callback' => FALSE,
			'process callback' => 'Text::autop'
		))
		->title(__('Convert line breaks into HTML'))
		->description(__('Lines and paragraphs break automatically.'));

		Filter::set('plain',  array(
			'prepare callback' => FALSE,
			'process callback' => 'Text::plain'
		))
		->title(__('Display any HTML as plain text'))
		->description(__('No HTML tags allowed.'));

		Filter::set('url',  array(
			'prepare callback' => FALSE,
			'process callback' => 'Text::autolink'
		))
		->title(__('Convert URLs into links'))
		->description(__('Web page addresses and e-mail addresses turn into links automatically.'))
		->settings( array(
			'url_length' => 72
		));

		Filter::set('initialcaps',  array(
			'prepare callback' => FALSE,
			'process callback' => 'Text::initialcaps'
		))
		->title(__('Adds Initialcaps'))
		->description(__('Adds <span class="initial"> tag around the initial letter of each paragraph'));

		Filter::set('markdown',  array(
			'prepare callback' => FALSE,
			'process callback' => 'Text::markdown'
		))
		->title(__('Markdown'))
		->description(__('Allows content to be submitted using Markdown, a simple plain-text syntax that is filtered into valid HTML.'));

	// Cache the Filters in production
	Filter::cache(Kohana::$environment === Kohana::PRODUCTION);
}
