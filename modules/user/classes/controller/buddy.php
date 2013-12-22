<?php
/**
 * Controller Buddy
 *
 * @package    Gleez\User
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
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
		$id 	  = (int) $this->request->param('id');
		$user     = ORM::factory('user', $id);
		$is_owner = FALSE;
		$account  = Auth::instance()->get_user();

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
					->set('pagination', $pagination);

		$this->title = __('Friends');
		$this->response->body($view);
	}

}