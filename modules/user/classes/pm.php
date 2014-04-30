<?php
/**
 * Private Message Helper
 *
 *
 * @package    Gleez\Helpers
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class PM {
	/**
	 * Inbox virtual folder name.
	 * Can be used when determining the type of messages received.
	 * @type integer
	 */
	const INBOX = 0x01;

	/**
	 * Outbox virtual folder name.
	 * Can be used when determining the type of messages received.
	 * @type integer
	 */
	const OUTBOX = 0x02;

	/**
	 * Drafts virtual folder name.
	 * Can be used when determining the type of messages received.
	 * @type integer
	 */
	const DRAFTS = 0x03;

	/**
	 * Message status - read
	 * @type string
	 */
	const STATUS_READ = 'read';

	/**
	 * Message status - unread
	 * @type string
	 */
	const STATUS_UNREAD = 'unread';

	/**
	 * Message status - draft
	 * @type string
	 */
	const STATUS_DRAFT = 'draft';

	/**
	 * Bulk Actions
	 *
	 * @param   boolean  $list  TRUE for dropdown for bulk actions [Optional]
	 *
	 * @return  mixed
	 * @uses    Module::action
	 */
	public static function bulk_actions($list = FALSE)
	{
		$states = array(
			'read'    => array(
				'label'     => __('Mark as read'),
				'callback'  => 'PM::bulk_update',
				'arguments' => array('updates' => array('status' => self::STATUS_READ)),
			),
			'unread'  => array(
				'label'     => __('Mark as unread'),
				'callback'  => 'PM::bulk_update',
				'arguments' => array('updates' => array('status' => self::STATUS_UNREAD)),
			),
			'delete'  => array(
				'label'     => __('Delete'),
				'callback'  => NULL,
			)
		);

		// Allow module developers to override
		$values = Module::action('message_bulk_actions', $states);

		if ($list)
		{
			$options = array('' => __('Bulk Actions'));

			foreach ($values as $operation => $array)
			{
				$options[$operation] = $array['label'];
			}

			return $options;
		}

		return $values;
	}

	/**
	 * Bulk update messages
	 *
	 * Usage:
	 * ~~~
	 * PM::bulk_update(array(1, 2, 3, ...), array('status' => 'read'));
	 * ~~~
	 *
	 * @param  array  $ids      Array of mesage ids
	 * @param  array  $actions  Array of message actions
	 */
	public static function bulk_update(array $ids, array $actions)
	{
		$messages = ORM::factory('message')
			->where('id', 'IN', $ids)
			->find_all();

		foreach($messages as $message)
		{
			foreach ($actions as $name => $value)
			{
				if (property_exists($message, $name))
				{
					$message->$name = $value;
				}
			}

			$message->save();
		}
	}

	/**
	 * Bulk delete messages
	 *
	 * Example:
	 * ~~~
	 * PM::bulk_delete(array(1, 2, 3, ...));
	 * ~~~
	 *
	 * @param  array  $ids  Array of post id's
	 */
	public static function bulk_delete(array $ids)
	{
		$messages = ORM::factory('message')
			->where('id', 'IN', $ids)
			->find_all();

		foreach($messages as $message)
		{
			$message->delete();
		}
	}
}
