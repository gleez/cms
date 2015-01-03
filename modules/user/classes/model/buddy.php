<?php
/**
 * Default Buddy Model
 *
 * @package    Gleez\User
 * @author     Gleez Team
 * @copyright  (c) 2011-2015 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Model_Buddy extends Model
{
	public function isFriend($user_id, $friend_id)
	{
		$total = DB::select(array(DB::expr('COUNT("id")'), 'total'))
			->from('buddies')
			->where_open()
				->where_open()
					->where('request_from', '=', $friend_id)
					->where('request_to', '=',$user_id)
				->where_close()
				->or_where_open()
					->where('request_from', '=', $user_id)
					->where('request_to', '=',$friend_id)
				->where_close()
			->where_close()
			->where('accepted','=','1')
			->execute()
			->get('total', false);

		return $total;
	}

	public function friends($user_id, $limit = 15, $offset = FALSE)
	{
		$sql = DB::select('request_from', 'request_to')
						->from('buddies')
						->where_open()
							->where('request_from', '=', $user_id)
							->or_where('request_to', '=',$user_id)
						->where_close()
						->where('accepted','=','1')
						->limit($limit);

		if($offset)
		{
			$sql->offset($offset);
		}

		$results = $sql->execute();

		$friends = array();
		foreach($results as $friend)
		{
			if($friend["request_from"] == $user_id)
			{
				$friends[] = $friend["request_to"];
			}
			else
			{
				$friends[] = $friend["request_from"];
			}
		}

		return $friends;
	}

	public function countFriends($user_id)
	{
		return DB::select(array(DB::expr('COUNT(*)'), 'total'))
						->from('buddies')
						->where_open()
							->where('request_from', '=', $user_id)
							->or_where('request_to', '=',$user_id)
						->where_close()
						->where('accepted','=','1')
						->execute()
						->get('total', FALSE);
	}

	public function isRequest($user_id, $friend_id)
	{
		$result = DB::select()->from('buddies')
					->where_open()
						->where_open()
							->where('request_from', '=', $friend_id)
							->where('request_to', '=',$user_id)
						->where_close()
						->or_where_open()
							->where('request_from', '=', $user_id)
							->where('request_to', '=',$friend_id)
						->where_close()
					->where_close()
					->where('accepted','=','0')
					->as_object()
					->execute()
					->current();

		return ($result != FALSE) ? $result->request_from : FALSE ;
	}

	public function addFriend($user_id, $friend_id)
	{
		return DB::insert('buddies',array('request_from', 'request_to', 'date_requested'))
					->values(array($user_id, $friend_id ,time()))
					->execute();
	}

	public function accept($id)
	{
		return DB::update('buddies')
					->set(array('accepted' => '1' , 'date_accepted' => time()))
					->where('request_to', '=', $id)
					->execute();
	}

	public function reject($friend_id)
	{
		return DB::delete('buddies')
					->where('request_from', '=', $friend_id)
					->where('accepted','=','0')
					->execute();
	}

	public function delete($friend_id,$user_id)
	{
		return DB::delete('buddies')
					->where_open()
						->where_open()
							->where('request_from', '=', $friend_id)
							->where('request_to', '=',$user_id)
						->where_close()
						->or_where_open()
							->where('request_from', '=', $user_id)
							->where('request_to', '=',$friend_id)
						->where_close()
					->where_close()
					->where('accepted','=','1')
					->execute();
	}

	public function sents($user_id, $limit = 15, $offset = FALSE)
	{
		$query = DB::select()
					->from('buddies')->where('request_from', '=', $user_id)
					->where('accepted', '=', '0')
					->limit($limit);

		if($offset)
		{
			$query->offset($offset);
		}

		return $query->execute();
	}
	
	public function pending($user_id, $limit = 15, $offset = FALSE)
	{
		$query = DB::select()
					->from('buddies')->where('request_to', '=', $user_id)
					->where('accepted', '=', '0')
					->limit($limit);

		if($offset)
		{
			$query->offset($offset);
		}

		return $query->execute();
	}

	public function countPending($user_id)
	{
		return DB::select(array(DB::expr('COUNT(*)'), 'total'))
						->from('buddies')
						->where('request_to', '=', $user_id)
						->where('accepted','=','0')
						->execute()
						->get('total', FALSE);
	}

	public function countSent($user_id)
	{
		return DB::select(array(DB::expr('COUNT(*)'), 'total'))
						->from('buddies')
						->where('request_from', '=', $user_id)
						->where('accepted','=','0')
						->execute()
						->get('total', FALSE);
	}
}