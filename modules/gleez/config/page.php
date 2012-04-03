<?php defined('SYSPATH') or die('No direct script access.');

return array(
                //Default Page Status. draft,review,publish etc
                'default_status'        => 'draft',
                
                //Pages per page; ex: 5, 10, 15 etc
                'items_per_page'        => 15,
                
                //Enable captcha; true/false
                'use_captcha'           => 0,
                
                // Enable/disbale to set page author; true/false
                'use_authors'           => 1,
                
                //Enable teaser; true/false
                'use_excerpt'           => 0,
                
                //Enable comment; true/false
                'use_comment'           => 1,
                
                //Enable tags; true/false
                'use_tags'              => 1,
                
                //Enable book support; true/false
                'use_book'              => 0,
                
                //View submitted info in views; true/false
                'use_submitted'         => 1,
                
                //Enable terms; array/false array of term id's or false to disable
                'use_category'          => 1,
                
                //Enable login buttons above comment form; true or false to disable
                'use_provider_buttons'  => 1,
                
                //Allow people to post Comment(s); 0 => disabled, 1 => read, 2 => read/write
                'comment'               => 0,
                
                //Comment display mode 
                'comment_default_mode'  => 0,
                
                //Allow anonymous commenting (with contact information); true/false
                'comment_anonymous'     => 0,
                
                //Comments per page
                'comments_per_page'     => 20,
                
                //Comments displayed with the older/new comments; asc/desc
                'comment_order'         => 'asc',
        );