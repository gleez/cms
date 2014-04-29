<?php
/**
 * Controller Message
 *
 * @package    Gleez\User
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Controller_Message extends Template {

	/**
	 * The before() method is called before controller action
	 *
	 * @throws  HTTP_Exception
	 *
	 * @uses    Assets::css
	 * @uses    User::is_guest
	 */
	public function before()
	{
		if (User::is_guest())
		{
			throw HTTP_Exception::factory(403, 'Permission denied! You must login!');
		}

		$id = $this->request->param('id', FALSE);

		if ($id AND 'index' == $this->request->action())
		{
			$this->request->action('view');
		}
		if ( ! $id AND 'index' == $this->request->action())
		{
			$this->request->action('inbox');
		}

		Assets::css('user', 'media/css/user.css', array('theme'), array('weight' => 60));

		parent::before();
	}

	/**
	 * The after() method is called after controller action
	 *
	 * @uses  Route::get
	 * @uses  Route::uri
	 * @uses  Request::action
	 * @uses  Assets::editor
	 */
	public function after()
	{
		if ($this->request->action() == 'compose' OR $this->request->action() == 'edit')
		{
			// Add RichText Support
			Assets::editor('.textarea', I18n::$lang);
		}
		else
		{
			// Tabs
			$this->_tabs =  array(
				array('link' => Route::get('user/message')->uri(array('action' =>'inbox')), 'text' => __('Inbox')),
				array('link' => Route::get('user/message')->uri(array('action' =>'outbox')), 'text' => __('Sent Messages')),
				array('link' => Route::get('user/message')->uri(array('action' =>'drafts')), 'text' => __('Drafts')),
				array('link' => Route::get('user/message')->uri(array('action' =>'list')), 'text' => __('All Messages'))
			);

			// Disable sidebars on message pages except compose and edit
			$this->_sidebars = FALSE;
		}

		parent::after();
	}

	/**
	 * Display a list of incoming messages
	 *
	 * @uses  Assets::popup
	 * @uses  Route::url
	 * @uses  Route::get
	 * @uses  Route::uri
	 * @uses  Request::is_datatables
	 * @uses  Form::checkbox
	 * @uses  HTML::anchor
	 * @uses  Date::formatted_time
	 * @uses  HTML::icon
	 * @uses  Text::limit_chars
	 */
	public function action_inbox()
	{
		Assets::popup();

		$url         = Route::url('user/message', array('action' => 'inbox'), TRUE);
		$redirect    = Route::get('user/message')->uri(array('action' => 'inbox'));
		$form_action = Route::get('user/message')->uri(array('action' => 'bulk', 'id' => PM::INBOX));
		$destination = '?destination='.$redirect;

		$is_datatables = Request::is_datatables();

		/** @var $messages Model_Message */
		$messages = ORM::factory('message')->loadInbox();

		if ($is_datatables)
		{
			$this->_datatables = $messages->dataTables(array('id', 'subject', 'sender', 'sent'));

			foreach ($this->_datatables->result() as $message)
			{
				$this->_datatables->add_row(
					array(
						Form::checkbox('messages['.$message->id.']', $message->id, isset($_POST['messages'][$message->id])),
						HTML::anchor($message->user->url, $message->user->nick, array('class' => 'message-'.$message->status)),
						HTML::anchor($message->url, Text::limit_chars($message->subject, 20), array('class' => 'message-'.$message->status))
						.' '.
						HTML::anchor($message->url, Text::limit_chars(strip_tags($message->body), 80)),
						Date::formatted_time($message->sent, 'M d, Y'),
						HTML::icon($message->delete_url.$destination, 'fa-trash-o', array('title'=> __('Delete Message'), 'data-toggle' => 'popup', 'data-table' => '#user-message-inbox'))
					)
				);
			}
		}

		$this->title = __('Inbox');

		$view = View::factory('message/inbox')
					->bind('datatables',   $this->_datatables)
					->set('is_datatables', $is_datatables)
					->set('action',        $form_action)
					->set('url',           $url);

		$this->response->body($view);
	}

	public function action_outbox()
	{
		$this->title = __('Sent Messages');

		$view = View::factory('message/outbox');

		$this->response->body($view);
	}

	public function action_drafts()
	{
		$this->title = __('Drafts');

		$view = View::factory('message/drafts');

		$this->response->body($view);
	}

	public function action_list()
	{
		$this->title = __('All Messages');

		$view = View::factory('message/list');

		$this->response->body($view);
	}

	public function action_view()
	{
		$this->title = __('View Message');

		$view = View::factory('message/view');

		$this->response->body($view);
	}

	public function action_edit()
	{
		$this->title = __('Edit Message');

		$view = View::factory('message/form');

		$this->response->body($view);
	}

	/**
	 * Compose message
	 */
	public function action_compose()
	{
		$this->title = __('New Message');

		// Set form destination
		$destination = ( ! is_null($this->request->query('destination'))) ? array('destination' => $this->request->query('destination')) : array();
		// Set form action
		$action = Route::get('user/message')->uri(array('action' => 'compose')).URL::query($destination);

		$view = View::factory('message/form')
				->bind('message',    $message)
				->bind('errors',     $this->_errors)
				->set('destination', $destination)
				->set('action',      $action)
				->set('recipient',   FALSE);

		$message = ORM::factory('message');

		if ($this->valid_post('message'))
		{
			$sent   = (isset($_POST['draft']) AND $_POST['draft']) ? 0 : time();
			$sender = Auth::instance()->get_user();
			$status = $sent == 0 ? PM::STATUS_DRAFT : PM::STATUS_UNREAD;
			$act    = $sent == 0 ? __('saved') : __('sent');

			try
			{
				$message->values(array(
					'sender'    => $sender->id,
					'recipient' => User::lookup_by_name($_POST['recipient']),
					'subject'   => $_POST['subject'],
					'body'      => $_POST['body'],
					'status'    => $status,
					'format'    => $_POST['format'],
					'sent'      => $sent
				))->save();

				Log::info('Message :id successfully :act.', array(':id' => $message->id, ':act' => $act));
				Message::success(__('Message successfully :act.', array(':act' => $act)));

				// Redirect to Inbox
				$this->request->redirect(Route::get('user/message')->uri());
			}
			catch (ORM_Validation_Exception $e)
			{
				$this->_errors = $e->errors('models', TRUE);
			}
		}

		$this->response->body($view);
	}

	/**
	 * Delete message
	 *
	 * @uses  Request::query
	 * @uses  Request::redirect
	 * @uses  Route::get
	 * @uses  Route::uri
	 * @uses  URL::query
	 * @uses  PM::delete
	 * @uses  Message::success
	 * @uses  Message::error
	 * @uses  Log::add
	 */
	public function action_delete()
	{
		$id = (int) $this->request->param('id', 0);

		/** @var $message Model_Message */
		$message = ORM::factory('message', $id);

		$this->title = __('Delete Message');

		$destination = ($this->request->query('destination') !== NULL)
			? array('destination' => $this->request->query('destination'))
			: array();

		$redirect = empty($destination)
			? Route::get('user/message')->uri()
			: $this->request->query('destination');

		$view = View::factory('form/confirm')
			->set('action', $message->delete_url.URL::query($destination))
			->set('title',  $message->subject);

		// If deletion is not desired, redirect
		if (isset($_POST['no']) AND $this->valid_post())
		{
			$this->request->redirect($redirect);
		}

		// If deletion is confirmed
		if ( isset($_POST['yes']) AND $this->valid_post() )
		{
			try
			{
				$title = $message->subject;
				$id    = $message->id;
				$message->delete();

				Log::info('Message :id deleted.', array(':id' => $id));
				Message::success(__('Message %title deleted successful!', array('%title' => $title)));
			}
			catch (Exception $e)
			{
				Log::error('Error occurred deleting message id: :id, :msg',
					array(':id' => $message->id, ':msg' => $e->getMessage())
				);
				Message::error(__('An error occurred deleting message %title',array('%title' => $message->subject)));
			}

			$this->request->redirect($redirect);
		}

		$this->response->body($view);
	}

	/**
	 * Perform bulk actions
	 *
	 * @uses  Route::get
	 * @uses  Route::uri
	 * @uses  Request::post
	 * @uses  Request::redirect
	 * @uses  PM::bulk_delete
	 * @uses  Message::success
	 * @uses  Message::error
	 * @uses  DB::select
	 */
	public function action_bulk()
	{
		$id = (int) $this->request->param('id', 0);

		switch ($id)
		{
			case PM::INBOX:
				$destination = 'inbox';
			break;
			case PM::OUTBOX:
				$destination = 'outbox';
			break;
			case PM::DRAFTS:
				$destination = 'drafts';
			break;
			default:
				$destination = 'list';
		}

		$redirect    = Route::get('user/message')->uri(array('action' => $destination));
		$post        = $this->request->post();
		$this->title = __('Bulk Actions');

		// If deletion is not desired, redirect to list
		if (isset($post['no']) AND $this->valid_post())
		{
			$this->request->redirect($redirect);
		}

		// If deletion is confirmed
		if (isset($post['yes']) AND $this->valid_post())
		{
			$ids = array_filter($post['items']);

			PM::bulk_delete($ids);

			Message::success(__('The delete has been performed!'));

			$this->request->redirect($redirect);
		}

		if ($this->valid_post('message-bulk-actions'))
		{
			if (isset($post['operation']) AND empty($post['operation']))
			{
				Message::error(__('No bulk operation selected.'));
				$this->request->redirect($redirect);
			}

			if ( ! isset($post['messages']) OR ( ! is_array($post['messages']) OR ! count(array_filter($post['messages']))))
			{
				Message::error(__('No messages selected.'));
				$this->request->redirect($redirect);
			}

			try
			{
				if ($post['operation'] == 'delete')
				{
					$ids = array_filter($post['messages']); // Filter out unchecked messages
					$this->title = __('Delete Messages');

					$items = DB::select('id', 'subject')
						->from('messages')
						->where('id', 'IN', $ids)
						->execute()
						->as_array('id', 'subject');

					$view = View::factory('form/confirm_multi')
						->set('action', '')
						->set('items', $items);

					$this->response->body($view);
					return;
				}

				$this->_bulk_update($post);

				Message::success(__('The update has been performed!'));
				$this->request->redirect($redirect);
			}
			catch( Exception $e)
			{
				Message::error(__('The update has not been performed!'));
				Log::error('Message updates failed: '.$e->getMessage());
			}
		}

		// always redirect to list, if no action performed
		$this->request->redirect($redirect);
	}

	/**
	 * Bulk updates
	 *
	 * @param  array  $post
	 *
	 * @uses   PM::bulk_actions
	 * @uses   Arr::callback
	 */
	private function _bulk_update($post)
	{
		$operations = PM::bulk_actions(FALSE);
		$operation  = $operations[$post['operation']];
		$messages   = array_filter($post['messages']); // Filter out unchecked pages

		if ($operation['callback'])
		{
			list($func, $params) = Arr::callback($operation['callback']);
			if (isset($operation['arguments']))
			{
				$args = array_merge(array($messages), $operation['arguments']);
			}
			else
			{
				$args = array($messages);
			}

			// set model name
			$args['type'] = 'message';

			// execute the bulk operation
			call_user_func_array($func, $args);
		}
	}
}
