<?php defined("SYSPATH") or die("No direct script access.");
/**
 * @package	Gleez
 * @category	Event
 * @author	Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2012 Gleez Technologies
 * @license	http://gleezcms.org/license
 * 
 */
class Gleez_Event {
        
	static function Post_Save($post)
	{
		//Message::warn( Debug::vars($post) );
	}
	
	static function filter_info($filters)
        {
		$filters->list['html'] 		=  array('title' => __('Limit allowed HTML tags'),
								'prepare callback' => FALSE,
								'process callback' => 'Text::html',
								'settings'         => array(
									'html_nofollow' => true,
									'allowed_html'  =>
						'<a> <em> <strong> <cite> <blockquote> <code> <ul> <ol> <li> <dl> <dt> <dd>'),
								 'description' => 'Allowed HTML tags ',
                                                        );
		
		$filters->list['htmlcorrector']	= array('title' => __('Correct faulty and chopped off HTML'),
                                                                 'prepare callback' => FALSE,
                                                                 'process callback' => 'Text::htmlcorrector',
                                                                 'settings'         => array()
                                                        );
		
		$filters->list['url']		= array('title' => __('Convert URLs into links'),
                                                                 'prepare callback' => FALSE,
                                                                 'process callback' => 'Text::autolink',
                                                                 'settings'         => array('url_length' => 72,),
								 'description' => 'Web page addresses and e-mail addresses turn into links automatically.'
                                                        );
		
		$filters->list['autop']		= array('title' => __('Convert line breaks into HTML'),
                                                                 'prepare callback' => FALSE,
                                                                 'process callback' => 'Text::autop',
                                                                 'settings'         => array(),
								 'description'	=> 'Lines and paragraphs break automatically.'
                                                        );
		
		$filters->list['plain']		= array('title' => __('Display any HTML as plain text'),
                                                                 'prepare callback' => FALSE,
                                                                 'process callback' => 'Text::plain',
                                                                 'settings'         => array(),
								 'description' => 'No HTML tags allowed.'
                                                        );
		
		$filters->list['initialcaps']	= array('title' => __('Adds Initialcaps'),
                                                                 'prepare callback' => FALSE,
                                                                 'process callback' => 'Text::initialcaps',
                                                                 'settings'         => array(),
								 'description' => 'Adds <span class="initial"> tag around the initial letter of each paragraph'
                                                        );
		
		$filters->list['fractions']	= array('title' => __('Convert Fractions'),
                                                                 'prepare callback' => FALSE,
                                                                 'process callback' => 'Text::fractions',
                                                                 'settings'         => array(),
								 'description' => 'Converts fractions to their html equivalent'
                                                        );
		
		$filters->list['ordinals']	= array('title' => __('Convert Ordinals'),
                                                                 'prepare callback' => FALSE,
                                                                 'process callback' => 'Text::ordinals',
                                                                 'settings'         => array(),
								 'description' => 'Adds <span class="ordinal"> tags around any ordinals (nd / st / th / rd)'
                                                        );
		
		$filters->list['accented']	= array('title' => __('Convert accented characters'),
                                                                 'prepare callback' => FALSE,
                                                                 'process callback' => 'Text::convert_accented_characters',
                                                                 'settings'         => array(),
								 'description' => 'Convert accented characters'
                                                        );
		
		$filters->list['links_end']	= array('title' => __('Move Links to end of the block'),
                                                                 'prepare callback' => FALSE,
                                                                 'process callback' => 'Text::move_links_to_end',
                                                                 'settings'         => array(),
								 'description' => 'Move Links to end of the block'
                                                        );
        }
        
}
