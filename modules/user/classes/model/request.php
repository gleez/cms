<?php
/**
 * Default Buddy Request Model
 *
 * @package    Gleez\User
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Model_Request extends Model {

        public function get_pending_requests_to_me($user_id)
        {
                $query = DB::select()
                        ->from('buddy_requests')
                        ->join(array('users', 'friends'))
                        ->on('requests.request_from', '=', 'friends.id')
                        ->where('request_to', '=', $user_id)
                        ->where('requests.accepted', '=', FALSE)
                        ->as_object();

                return $query;
        }

        public function get_pending_requests_from_me($user_id)
        {
                $query = DB::select()
                        ->from('buddy_requests')
                        ->join(array('users', 'friends'))
                        ->on('requests.request_to', '=', 'friends.id')
                        ->where('request_from', '=', $user_id)
                        ->where('requests.accepted', '=', FALSE)
                        ->as_object();

                return $query;
        }

        public function get_request($user_id, $friend_id)
        {
                $query = DB::select()
                        ->from('buddy_requests')
                        ->where('request_from', '=', $friend_id)
                        ->where('request_to', '=',$user_id)
                        ->as_object();

                return $query;
        }

}