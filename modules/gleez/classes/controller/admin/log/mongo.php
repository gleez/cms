<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Log_Mongo extends Controller_Admin {

	public function before()
	{
		ACL::Required('administer logs');
		parent::before();
	}

	public function action_index()
	{
		$mangodb = Mangodb::instance();
                $this->title  = __('Logs');
	
		$view   = View::factory('admin/log/list')
					->bind('pagination', $pagination)
					->bind('logs', $logs);

		$pagination = Pagination::factory(array(
				'current_page'   => array('source'=>'cms', 'key'=>'page'),
				'total_items'    => $mangodb->count('Logs'),
				'items_per_page' => 50,
				'uri'		 => Route::get('admin/log')->uri(),
				));
        
		$logs = $mangodb->find('Logs')->skip($pagination->offset)
					->sort(array('time'=> -1))
					->limit($pagination->items_per_page);

                $this->response->body($view);
        }

	public function action_view()
	{
		$id = $this->request->param('id', 0);
	
		$this->title  = __('Log : :id', array(':id' => $id));
		$view = View::factory('admin/log/view')->bind('log', $log);
	
		$log = Mangodb::instance()->find_one('Logs', array('_id' => new MongoId($id) ) );

		if($log)
		{
			$user = User::lookup((int) $log['user']);
			$log['user'] = $user->nick;
		}
	
		$this->response->body($view);
	}
	
}