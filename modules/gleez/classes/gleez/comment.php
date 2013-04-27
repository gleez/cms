<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Comment Core Class
 *
 * @package	   Gleez\Comment
 * @author	   Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Gleez_Comment {
        
	// @todo our definitions for comment types and statuses
	const STATUS_UNAPPROVED = 0;
	const STATUS_APPROVED = 1;
	const STATUS_SPAM = 2;
	const STATUS_DELETED = 3;

	const COMMENT_HIDDEN = 0;
	const COMMENT_CLOSED = 1;
	const COMMENT_OPEN = 2;

	const COMMENT   = 0;
	const PINGBACK  = 1;
	const TRACKBACK = 2;
        
	/**
	 * List comments
	 */
	public function create_list($parent_id = 0, $state = 'publish', $url, $page, $config, $admin = FALSE)
	{
		// Get total number of comments
		if ($parent_id == 0)
		{
			Kohana::$log->add(LOG::DEBUG, 'Fetching all '.$state.' comments');
			$total = ORM::factory('comment', array(
				'status' => $state,
			))->count_all();
		}
		else
		{
			Kohana::$log->add(LOG::DEBUG, 'Fetching '.$state.' comments for parent id='.$parent_id);
			$total = ORM::factory('comment', array(
				'status'  => $state,
				'parent'  => $parent_id,
			))->count_all();
		}
	
		// Check if there are any comments to display
		if ($total == 0) return FALSE;
	
	}
	
        public static function form($controller, $item, $_action = FALSE, $captcha = FALSE)
        {
                $view = View::factory('comment/form')
                                ->set('use_captcha', $captcha)
                                ->set('action', Request::current()->uri())
				->set('is_edit', FALSE)
				->set('auth', Auth::instance())
				->set('destination', array())
				->set('item', $item)
				->bind('errors', $errors)
                                ->bind('post', $post);
        
		//set form action eitehr from model or action param
		if( $item->url ) $view->set('action', (string)$item->url);
                if( $_action ) 	 $view->set('action', $_action);
        
		//set if captcha necessary
                if( $captcha )
		{
			$captcha = Captcha::instance();
			$view->set('captcha', $captcha);
		}
        
		//Load the comment model
                $post = ORM::factory('comment');
                
		if( $controller->valid_post('comment') )
		{
			$values = Arr::merge( array('post_id' => $item->id, 'type' => $item->type), $_POST);
			try
			{
				$post->values($values)->save();
				if($post->status != 'publish')
				{
					Message::success(__('Your comment has been queued for review by
							    site administrators and will be published after approval.') );
				}
				else
				{
					Message::success(__('Your comment has been posted.', array(':title' => $post->title)) );
				}
			
				// Save the anonymous user information to a cookie for reuse.
				if (User::is_guest()) {
					User::cookie_save(array(
								'name'  => $post->guest_name,
								'email' => $post->guest_email,
								'url'   => $post->guest_url
								));
				}

				Kohana::$log->add(LOG::INFO, 'Comment: :title has posted.', array(':title' => $post->title) );
	
				//redirect to post page
				$controller->request->redirect( Request::current()->uri() );
			}
                        catch (ORM_Validation_Exception $e)
			{
				$errors =  $e->errors('models');
				Message::error(__('Please see the erros below!'));
			}
		}
	
                return $view;
        }
        
        /**
         * Make sure that the state is legal.
         */
        public static function valid_state($value)
        {
		return in_array( $value, array_keys( Comment::status() ) );
        }

	/**
	 *  list of status
         *  @return array statuses
         */
        public static function status()
        {
		$states = array(
                                'publish'   => __('Publish'),
                                'draft'     => __('Unpublish'),
                                'spam'      => __('Spam'),
				'delete'    => __('Delete'),
				);
	
		$values = Module::action('comment_status', $states);
                return $values;
        }

	/**
	 *  list of actions
	 *  @param 	boolean   true for dropdown for bult actions
         *  @return 	array 	  states
         */
        public static function bulk_actions( $list = FALSE )
        {
		$states = array(
				'publish'   => array(
						'label' => __('Publish the selected comments'),
						'callback' => 'Comment::bulk_update',
						'arguments' => array('updates' => array('status' => 'publish')),
						),
                                'draft' => array(
						'label' => __('Unpublish the selected comments'),
						'callback' => 'Comment::bulk_update',
						'arguments' => array('updates' => array('status' => 'draft')),
						),
                                'spam'   => array(
						'label' => __('Mark Selected Comments as Spam'),
						'callback' => 'Comment::bulk_update',
						'arguments' => array('updates' => array('status' => 'spam')),
						),
                                'delete'    => array(
						'label' => __('Delete the selected comments'),
						'callback' => NULL,
						)
				);
	
		//allow module developers to override
		$values = Module::action('comment_bulk_actions', $states);
	
		if($list)
		{
			$options = array();
			foreach ($values as $operation => $array) $options[$operation] = $array['label'];
		
			return $options;
		}
	
                return $values;
        }
	
	public static function bulk_update(array $ids, array $actions)
	{
		//Message::debug( Debug::vars($type));
		$posts = ORM::factory('comment')->where('id', 'IN', $ids)->find_all();
		foreach($posts as $post)
		{
			foreach ($actions as $name => $value)
			{
				$post->$name = $value;
			}
			$post->save();
		}
		
	}
}