<?php
/**
 * Message Model Class
 *
 * @package    Gleez\ORM\Message
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Model_Message extends ORM {

	/**
	 * Sort mode of messages - ascending
	 * @type string
	 */
	const ASC = 'ASC';

	/**
	 * Sort mode of messages - descending
	 * @type string
	 */
	const DESC = 'DESC';

	/**
	 * Message config
	 * @var Config_Group
	 */
	protected $_config;

	/**
	 * Table columns
	 * @var array
	 */
	protected $_table_columns = array(
		'id'        => array( 'type' => 'int' ),
		'sender'    => array( 'type' => 'int' ),
		'recipient' => array( 'type' => 'int' ),
		'subject'   => array( 'type' => 'string' ),
		'body'      => array( 'type' => 'string' ),
		'status'    => array( 'type' => 'string' ),
		'format'    => array( 'type' => 'int' ),
		'created'   => array( 'type' => 'int' ),
		'sent'      => array( 'type' => 'int' ),
		'lang'      => array( 'type' => 'string' ),
	);

	/**
	 * Auto fill created column
	 * @var array
	 */
	protected $_created_column = array(
		'column' => 'created',
		'format' => TRUE
	);

	/**
	 * "Belongs to" relationships
	 * @var array
	 */
	protected $_belongs_to = array(
		'user' => array(
			'foreign_key' => 'sender'
		)
	);

	/**
	 * Class constructor
	 *
	 * @param  mixed $id  Parameter for find or object to load [Optional]
	 */
	public function __construct($id = NULL)
	{
		$this->_config = Config::load('message');

		parent::__construct($id);
	}

	/**
	 * Reading data from inaccessible properties
	 *
	 * @param   string  $field
	 * @return  mixed
	 *
	 * @uses  Text::plain
	 * @uses  Route::get
	 * @uses  Route::uri
	 */
	public function __get($field)
	{
		switch ($field)
		{
			case 'subject':
				return Text::plain(parent::__get('subject'));
			case 'url':
				return Route::get('user/message')->uri(array( 'id' => $this->id, 'action' => 'view'));
			case 'delete_url':
				return Route::get('user/message')->uri(array( 'id' => $this->id, 'action' => 'delete'));
			default:
				return parent::__get($field);
		}
	}

	/**
	 * Deletes a single message or multiple messages, ignoring relationships
	 *
	 * @return  Model_Message
	 * @throws  Gleez_Exception
	 *
	 * @uses    Cache::delete
	 */
	public function delete()
	{
		if ( ! $this->_loaded)
		{
			throw new Gleez_Exception('Cannot delete :model model because it is not loaded.',
				array(':model' => $this->_object_name)
			);
		}

		Cache::instance('message')->delete($this->id);

		parent::delete();

		return $this;
	}

	/**
	 * Load messages list
	 *
	 * Example:
	 * ~~~
	 * // Get all messages from inbox. Sorting mode is ascending
	 * ORM::factory('message')->load(PM::INBOX, 'asc');
	 *
	 * // Get all messages from outbox. Sorting mode is descending
	 * ORM::factory('message')->load(PM::OUTBOX);
	 *
	 * // Get all draft messages. Sorting mode is descending
	 * ORM::factory('message')->load(PM::DRAFTS);
	 *
	 * // Get all messages from inbox, outbox and drafts
	 * // Sorting mode is descending
	 * ORM::factory('message')->load();
	 * ~~~
	 *
	 * [!!] Note: The $direction may be 'asc' for ascending sort mode,
	 *            or 'desc' for descending sort mode.
	 *
	 * For message type constants see [PM] class
	 *
	 * @param    integer $type       Message type, eg. PM::INBOX, PM::OUTBOX, PM::DRAFTS [Optional]
	 * @param    string  $direction  Sort mode of messages [Optional]
	 *
	 * @return  Model_Message
	 *
	 * @todo    Cache
	 */
	public function load($type = 0, $direction = self::DESC)
	{
		if ( ! $this->loaded())
		{
			$this->order_by('created', $direction);

			$user = User::active_user();

			switch ($type)
			{
				case PM::INBOX:
					$this->where_open()
						->where('recipient', '=', $user->id)
						->and_where('status', '!=', PM::STATUS_DRAFT)
						->where_close();
				break;
				case PM::OUTBOX:
					$this->where_open()
						->where('sender', '=', $user->id)
						->and_where('status', '!=', PM::STATUS_DRAFT)
						->where_close();
				break;
				case PM::DRAFTS:
					$this->where_open()
						->where('sender', '=', $user->id)
						->and_where('status', '=', PM::STATUS_DRAFT)
						->where_close();
				break;
				default:
					$this->where_open()
						->where('sender', '=', $user->id)
						->or_where('recipient', '=', $user->id)
						->where_close();
			}
		}

		return $this;
	}

	/**
	 * Load inbox messages
	 *
	 * Example:
	 * ~~~
	 * ORM::factory('message')->loadInbox();
	 * ~~~
	 *
	 * [!!] Note: The $direction may be 'asc' for ascending sort mode,
	 *            or 'desc' for descending sort mode.
	 *
	 * @param    string  $direction  Sort mode of messages [Optional]
	 *
	 * @return  Model_Message
	 */
	public function loadInbox($direction = self::DESC)
	{
		return $this->load(PM::INBOX, $direction);
	}

	/**
	 * Load outbox messages
	 *
	 * Example:
	 * ~~~
	 * ORM::factory('message')->loadInbox();
	 * ~~~
	 *
	 * [!!] Note: The $direction may be 'asc' for ascending sort mode,
	 *            or 'desc' for descending sort mode.
	 *
	 * @param    string  $direction  Sort mode of messages [Optional]
	 *
	 * @return  Model_Message
	 */
	public function loadOutbox($direction = self::DESC)
	{
		return $this->load(PM::OUTBOX, $direction);
	}

	/**
	 * Load draft messages
	 *
	 * Example:
	 * ~~~
	 * ORM::factory('message')->loadDrafts();
	 * ~~~
	 *
	 * [!!] Note: The $direction may be 'asc' for ascending sort mode,
	 *            or 'desc' for descending sort mode.
	 *
	 * @param    string  $direction  Sort mode of messages [Optional]
	 *
	 * @return  Model_Message
	 */
	public function loadDrafts($direction = self::DESC)
	{
		return $this->load(PM::DRAFTS, $direction);
	}

	/**
	 * Get one message
	 *
	 * When receiving a message changes its status if message is unread
	 *
	 * Example:
	 * ~~~
	 * ORM::factory('message', $id)->getOne();
	 * ~~~
	 *
	 * @return  Model_Message
	 *
	 * @throws  HTTP_Exception_404
	 */
	public function getOne()
	{
		if ( ! $this->loaded())
		{
			throw new HTTP_Exception_404('Message not found!');
		}

		return $this;
	}

	public static function bulk_actions($list = FALSE)
	{
		$states = array(
			'read'    => array(
				'label'     => __('Mark as read'),
				'callback'  => NULL,
			),
			'unread'  => array(
				'label'     => __('Mark as unread'),
				'callback'  => NULL,
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
	 * Bulk delete messages
	 *
	 * Example:
	 * ~~~
	 * Model_Message::bulk_delete(array(1, 2, 3, ...));
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
