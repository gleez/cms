<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Welcome extends Template {

	//public $debug = TRUE;
	public function action_index()
	{
		if( !Gleez::$installed )
		{
			$this->request->redirect(Route::get('install')->uri(array('action' => 'index')));
		}
	
		$content = View::factory('welcome');
		$this->title = 'Welcome!';
	
		$this->response->body($content);
	}

	public function action_welcome( )
	{
		//$user = ORM::factory('user', 2);
		//$user->pass = 'ELuC0HKi';
		//$user->save();
		//$this->response->body(  Debug::vars($_SERVER)  );
		$this->response->body(  ''  );
	}

	
} // End Welcome