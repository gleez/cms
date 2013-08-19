<?php
/**
 * Comment Core Class
 *
 * @package	   Gleez\Comment
 * @author	   Gleez Team
 * @version    1.0.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Gleez_Comment {

	// @todo our definitions for comment types and statuses
	const STATUS_UNAPPROVED = 0;
	const STATUS_APPROVED   = 1;
	const STATUS_SPAM       = 2;
	const STATUS_DELETED    = 3;

	const COMMENT_HIDDEN    = 0;
	const COMMENT_CLOSED    = 1;
	const COMMENT_OPEN      = 2;

	const COMMENT           = 0;
	const PINGBACK          = 1;
	const TRACKBACK         = 2;

	/**
	 * List comments
	 */
	public function create_list($parent_id = 0, $state = 'publish', $url, $page, $config, $admin = FALSE)
	{
		// Get total number of comments
		if ($parent_id == 0)
		{
			Log::debug('Fetching all :state comments', array(':state' => $state));
			$total = ORM::factory('comment', array('status' => $state))->count_all();
		}
		else
		{
			Log::debug('Fetching :state comments for parent id: :id',
				array(':state' => $state, ':id' => $parent_id)
			);
			$total = ORM::factory('comment', array(
				'status'  => $state,
				'parent'  => $parent_id,
			))->count_all();
		}

		// Check if there are any comments to display
		if ($total == 0)
		{
			return FALSE;
		}
	}

	public static function form($controller, $item, $_action = FALSE, $captcha = FALSE)
	{
		// Set default comment form action
		$action = Request::current()->uri();

		$view = View::factory('comment/form')
					->set('use_captcha', $captcha)
					->set('action',      $action)
					->set('is_edit',     FALSE)
					->set('auth',        Auth::instance())
					->set('destination', array())
					->set('item',        $item)
					->bind('errors',     $errors)
					->bind('post',       $post);

		// Set form action either from model or action param
		if ($item->url)
		{
			$action = (string)$item->url;
		}
		elseif($_action)
		{
			$action = $_action;
		}

		// Set if captcha necessary
		if ($captcha)
		{
			$captcha = Captcha::instance();
			$view->set('captcha', $captcha);
		}

		// Load the comment model
		$post = ORM::factory('comment');

		if ($controller->valid_post('comment'))
		{
			$values = Arr::merge(array('post_id' => $item->id, 'type' => $item->type), $_POST);
			try
			{
				$post->values($values)->save();
				if($post->status != 'publish')
				{
					Message::success(__('Your comment has been queued for review by site administrators and will be published after approval.') );
				}
				else
				{
					Message::success(__('Your comment has been posted.', array(':title' => $post->title)) );
				}

				// Save the anonymous user information to a cookie for reuse.
				if (User::is_guest())
				{
					User::cookie_save(array(
						'name'  => $post->guest_name,
						'email' => $post->guest_email,
						'url'   => $post->guest_url
					));
				}

				Log::info('Comment: :title has posted.', array(':title' => $post->title));

				// Redirect to post page
				$controller->request->redirect(Request::current()->uri());
			}
			catch (ORM_Validation_Exception $e)
			{
				// @todo Add messages
				$errors = $e->errors('models', TRUE);
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
	 * List of status
	 *
     * @return array statuses
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
	 * List of actions
	 *
	 * @param   boolean  $list  TRUE for dropdown for bult actions
	 * @return  array
	 */
	public static function bulk_actions( $list = FALSE )
	{
		$states = array(
			'publish' => array(
				'label' => __('Publish the selected comments'),
				'callback' => 'Comment::bulk_update',
				'arguments' => array(
					'updates' => array('status' => 'publish')
				),
			),
			'draft' => array(
				'label' => __('Unpublish the selected comments'),
				'callback' => 'Comment::bulk_update',
				'arguments' => array(
					'updates' => array('status' => 'draft')
				),
			),
			'spam' => array(
				'label' => __('Mark the selected comments as Spam'),
				'callback' => 'Comment::bulk_update',
				'arguments' => array(
					'updates' => array('status' => 'spam')
				),
			),
			'delete' => array(
				'label' => __('Delete the selected comments'),
				'callback' => NULL,
			)
		);

		// Allow module developers to override
		$values = Module::action('comment_bulk_actions', $states);

		if ($list)
		{
			$options = array();
			foreach ($values as $operation => $array)
			{
				$options[$operation] = $array['label'];
			}

			return $options;
		}

		return $values;
	}

	public static function bulk_update(array $ids, array $actions)
	{
		$posts = ORM::factory('comment')->where('id', 'IN', $ids)->find_all();

		foreach ($posts as $post)
		{
			foreach ($actions as $name => $value)
			{
				$post->$name = $value;
			}
			$post->save();
		}

	}
}