<?php defined('SYSPATH') or die('No direct script access.');

class Controller_OAuth_Facebook extends Controller_OAuth_Base {
        
	public function action_index()
	{
                //Message::debug( Debug::vars($this) );
                $url = $this->route->uri(array('controller' => 'facebook', 'action' => 'login'));
		$img = HTML::image('media/images/facebook.jpg', array('title' => __('Sign in with Facebook')) );
		
                $this->content = HTML::anchor($url, $img, array('title' => __('Sign in with Facebook') ) );
	}

	protected function response_process($response)
	{
		$data = array();
		
		if( isset($response['email']) )
		{
			$data['id'] = $response['id'];
			$data['email'] = $response['email'];
			$data['nick'] = $response['name'];
			$data['link'] = $response['link'];
			$data['gender'] = ($response['gender'] != NULL) ? $response['gender'] : FALSE;
		}
	
		return $data;
	}
        
}