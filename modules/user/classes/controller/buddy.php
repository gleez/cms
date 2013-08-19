<?php
/**
 * Controller Buddy
 *
 * @package    Gleez\User
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Controller_Buddy extends Controller {

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

        public function action_add()
        {
                $invitee_id = (int) $this->request->param('friend_id');
                $invitee = ORM::factory('user')->where('id', '=', $invitee_id)->find();

                if ( ! $user->has('friends', $invitee) AND ! $this->user->has('requests', $invitee))
                {
                        $user->add('requests', $invitee);
                }
        }

        public function action_accept()
        {
                $friend_id = (int) $this->request->param('friend_id');
                $friend = ORM::factory('user')->where('id', $friend_id)->find();

                if ( ! $this->user->has('friends', $friend))
                {
                        $this->user->add('friends', $friend);
                }

                $obj = new Model_Request();
                $request = $obj->get_request($this->user->id, $friend_id)->execute()->current();

                $values = array(
                        'accepted' => true,
                        'date_accepted' => date('Y-m-d H:i:s'),
                );

                DB::update('buddy_requests')->set($values)->where('id', '=', $request->id)->execute();
        }

        public function action_reject()
        {
                $friend_id = (int) $this->request->param('friend_id');
                $friend = ORM::factory('user')->where('id', '=', $friend_id)->find();

                if ($friend->loaded() AND $friend->has('requests', $this->user))
                {
                        $friend->remove('requests', $this->user);
                }
        }

        public function action_delete()
        {
                $friend_id = (int) $this->request->param('friend_id');
                $friend = ORM::factory('user')->where('id', '=', $friend_id)->find();

                if ($friend->loaded() AND $this->user->has('friends', $friend))
                {
                        $this->user->remove('friends', $friend);
                }
        }

}