<?php
/**
 * OAuth Live Controller
 *
 * @package    Gleez\OAuth\Controller
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Controller_OAuth_Live extends Controller_OAuth_Base {

	public function action_index()
	{
                //Message::debug( Debug::vars($this) );
                $url = $this->route->uri(array('controller' => 'live', 'action' => 'login'));
		$img = HTML::image('media/images/live.jpg', array('title' => __('Sign in with Windows Live')) );

                $this->content = HTML::anchor($url, $img, array('title' => __('Sign in with Windows Live') ) );
	}

	protected function response_process($response)
	{
		$data = array();

		//make sure the response is valid
		if ( $response AND !array_key_exists('error', $response) )
		{
			if( $response['emails'] )
			{
				$data['id'] = $response['id'];
				$data['email'] = $response['emails']['account']; //only account email is used
				$data['nick'] = $response['name'];
				$data['link'] = $response['link'];
				$data['gender'] = ($response['gender'] != NULL) ? $response['gender'] : FALSE;
			}
		}

		return $data;
        }

}