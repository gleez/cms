<?php
/**
 * Controller Buddy
 *
 * @package    Gleez\User
 * @author     Gleez Team
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Controller_Buddy extends Template {

	protected $user;

	public function before()
	{
		parent::before();

		if ( $this->_auth->logged_in() == false )
		{
			// No user is currently logged in
			$this->request->redirect('user/login');
		}

		$this->user = $this->_auth->get_user();
	}

	public function action_index()
	{
		$account  = Auth::instance()->get_user();
		$id 	  = (int) $this->request->param('id', $account->id);
		$is_owner = FALSE;

		$user     = ORM::factory('user', $id);

		if ( ! $user->loaded())
		{
			Log::error('Attempt to access non-existent user.');
			// No user is currently logged in
			$this->request->redirect(Route::get('user')->uri(array('action' => 'login')), 401);
		}

		if ($account AND ($user->id === $account->id))
		{
			$is_owner = TRUE;
		}

		$model 	  = Model::factory('buddy');
		$total    = $model->countFriends($id);

		$url = Route::get('user/buddy')->uri( array('action' => 'list','id' => $id) );
		$pagination = Pagination::factory(array(
			'current_page'   => array('source'=>'cms', 'key'=>'page'),
			'total_items' 	 => $total,
			'items_per_page' => 15,
			'uri'  			 => $url,
		));

		$friends  = $model->friends($id, $pagination->items_per_page, $pagination->offset);

		$view = View::factory('user/buddy')
					->set('total',      $total)
					->set('is_owner',   $is_owner)
					->set('friends',    $friends)
					->set('id', $id)
					->set('pagination', $pagination);

		$this->title = __('Friends');
		$this->response->body($view);
	}

	public function action_sent()
	{
		$id       = (int) $this->request->param('id');
		$user 	  = ORM::factory('user', $id);
		$account  = FALSE;

		if ( ! $user->loaded())
		{
			Log::error('Attempt to access non-existent user.');
			// No user is currently logged in
			$this->request->redirect(Route::get('user')->uri(array('action' => 'login')), 401);
		}

		if ($this->_auth->logged_in())
		{
			$account = Auth::instance()->get_user();
		}

		if ($account AND ($user->id === $account->id))
		{
			$is_owner = TRUE;
		}
		else
		{
			throw HTTP_Exception::factory(403, 'Attempt to access without required privileges.');
		}

		$model = Model::factory('buddy');
		$total = $model->countSent($id);
		
		$url = Route::get('user/buddy')->uri(array('action'=>'sent','id'=>$id));
		$pagination = Pagination::factory(array(
			'current_page'		=> array('source'=>'cms', 'key'=>'page'),
			'total_items'		=> $total,
			'items_per_page'	=> 15,
			'uri'				=> $url,
		));
		
		$sents  = $model->sents($id, $pagination->items_per_page, $pagination->offset);
			
		$view = View::factory('user/buddy/sent')
					->set('id',$id)
					->set('total',$total)
					->set('sents',$sents)
					->set('pagination',$pagination);
		
		$this->title = __('Sent Requests');
		$this->response->body($view);
	}
	
	public function action_pending()
	{
		$id 	  = (int) $this->request->param('id');
		$user     = ORM::factory('user', $id);
		$is_owner = FALSE;
		$account  = FALSE;
		
		if ( ! $user->loaded())
			{
				Log::error('Attempt to access non-existent user.');
				// No user is currently logged in
				$this->request->redirect(Route::get('user')->uri(array('action' => 'login')), 401);
			}
		
		if ($this->_auth->logged_in())
		{
			$account = Auth::instance()->get_user();
		}
		if ($account AND ($user->id === $account->id))
		{
			$is_owner = TRUE;
		}
		else
		{
			throw HTTP_Exception::factory(403, 'Attempt to access without required privileges.');
		}
		
		$model = Model::factory('buddy');
		$total = $model->countPending($id);
		
		$url = Route::get('user/buddy')->uri(array('action'=>'pending','id'=>$id));
		$pagination = Pagination::factory(array(
			'current_page'		=> array('source'=>'cms', 'key'=>'page'),
			'total_items'		=> $total,
			'items_per_page'	=> 15,
			'uri'				=> $url,
		));
		
		$pending  = $model->pending($id, $pagination->items_per_page, $pagination->offset);
			
		$view = View::factory('user/buddy/pending')
					->set('total',$total)
					->set('id',$id)
					->set('pendings',$pending)
					->set('is_owner',$is_owner)
					->set('pagination',$pagination);
		
		$this->title = __('Pending Requests');
		$this->response->body($view);
	}

	public function action_add()
	{
		$id      = (int) $this->request->param('id');
		$invitee = ORM::factory('user', $id);
		$account = Auth::instance()->get_user();

		if ( ! $invitee->loaded() )
		{
			Log::error('Attempt to access non-existent user.');
			// No user is currently logged in
			$this->request->redirect(Route::get('user')->uri(array('action' => 'login')), 401);
		}

		$model = Model::factory('buddy')->addFriend($account->id, $invitee->id);
		 
		Message::success(__("Buddy request sent to %title", array('%title' => $invitee->nick)));
		$this->request->redirect(Route::get('user')->uri(array('action' => 'profile', 'id' => $id)));
	}

	public function action_accept()
	{
		$id     = (int) $this->request->param('id');
		$friend = ORM::factory('user', $id);

		if ( ! $friend->loaded())
		{
			Log::error('Attempt to access non-existent user.');
			// No user is currently logged in
			$this->request->redirect(Route::get('user')->uri(array('action' => 'login')), 401);
		}

		$model = Model::factory('buddy');

		if ( $model->isFriend($this->user->id, $friend->id))
		{
			// Already friend
			$this->request->redirect(Route::get('user')->uri(array('action' => 'profile', 'id' => $friend->id)));
		}

		if ( $model->isRequest($this->user->id, $friend->id))
		{
			$model->accept($this->user->id);		
			Message::success(__("Buddy request: %title accepted", array('%title' => $friend->nick)));
		}

		$this->request->redirect(Route::get('user')->uri(array('action' => 'view', 'id' => $id)));
	}

	public function action_reject()
	{
		$id 	= (int) $this->request->param('id');
		$friend = ORM::factory('user', $id);

		if ( ! $friend->loaded())
		{
			Log::error('Attempt to access non-existent user.');
			// No user is currently logged in
			$this->request->redirect(Route::get('user')->uri(array('action' => 'login')), 401);
		}

		$model = Model::factory('buddy');

		if ( $model->isFriend($this->user->id, $friend->id))
		{
			// Already friend
			$this->request->redirect(Route::get('user')->uri(array('action' => 'profile', 'id' => $id)));
		}

		if ( $model->isRequest($this->user->id, $friend->id))
		{
			$model->reject($id);
			Message::success(__("Buddy %title rejected", array('%title' => $friend->nick)));
		}

		$this->request->redirect(Route::get('user')->uri(array('action' => 'profile', 'id' => $id)));
	}

	public function action_delete()
	{
		$id      = (int) $this->request->param('id');
		$friend  = ORM::factory('user', $id);
		$account = Auth::instance()->get_user();

		if ( ! $friend->loaded())
		{
			Log::error('Attempt to access non-existent user.');
			// No user is currently logged in
			$this->request->redirect(Route::get('user')->uri(array('action' => 'login')), 401);
		}

		Model::factory('buddy')->delete($id, $account->id);
		Message::success( __("Buddy %title deleted", array('%title' => $friend->nick)) );

		$this->request->redirect(Route::get('user')->uri(array('action' => 'profile', 'id' => $this->user->id)));
	}
}