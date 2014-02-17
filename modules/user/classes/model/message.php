<?php
/**
 * Message Model Class
 *
 * @package    Gleez\ORM\Message
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2014 Gleez Technologies
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
	 * Ignored columns
	 * @var array
	 */
	protected $_ignored_columns = array('draft');

	/**
	 * Sets the rules for Contact form
	 *
	 * @return  array
	 *
	 * @uses    Config::get
	 */
	public function rules()
	{
		return array(
			'recipient' => array(
				array(array($this, 'toExists'), array(':validation', ':field')),
			),
			'subject' => array(
				array('max_length', array(':value', 128)),
			),
			'body' => array(
				array('not_empty'),
				array('min_length', array(':value', 2)),
			)
		);
	}

	/**
	 * Sets the labels for Message form
	 *
	 * @return array
	 */
	public function labels()
	{
		return array(
			'recipient' => __('Recipient'),
			'subject'   => __('Subject'),
			'body'      => __('Body'),
			'format'    => __('Format'),
			'draft'     => __('Draft')
		);
	}

	/**
	 * Reading data from inaccessible properties
	 *
	 * @param   string  $field
	 * @return  mixed
	 *
	 * @uses  Text::plain
	 * @uses  Text::markup
	 * @uses  Route::get
	 * @uses  Route::uri
	 */
	public function __get($field)
	{
		switch ($field)
		{
			case 'subject':
				return Text::plain(parent::__get('subject'));
			case 'body':
				return Text::markup($this->rawbody, $this->format);
			case 'rawsubject':
				// Raw fields without markup. Usage: during edit or etc!
				return parent::__get('subject');
			case 'rawbody':
				// Raw fields without markup. Usage: during edit or etc!
				return parent::__get('body');
			case 'url':
				return Route::get('user/message')->uri(array( 'id' => $this->id, 'action' => 'view'));
			case 'delete_url':
				return Route::get('user/message')->uri(array( 'id' => $this->id, 'action' => 'delete'));
			default:
				return parent::__get($field);
		}
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

	/**
	 * Checks whether recipient user exists with the specified name
	 *
	 * Validation callback.
	 *
	 * @param   Validation  $validation An validation object
	 * @param   string      $field      Field name
	 * @return  boolean
	 */
	public function toExists(Validation $validation, $field)
	{
		if ( $this->status != PM::STATUS_DRAFT AND empty($validation[$field]))
		{
			$validation->error($field, 'not_empty', array($validation[$field]));
		}
		elseif ( $this->status != PM::STATUS_DRAFT AND $this->exists($validation[$field]))
		{
			$validation->error($field, 'exists', array($validation[$field]));
		}
	}

	/**
	 * Checks whether user exists with the specified name
	 *
	 * @param  string $recipient User name
	 * @return bool
	 */
	public function exists($recipient)
	{
		$result = ORM::factory('user')
				->where('name', '=', $recipient)
				->and_where('name', '!=', 'guest')
				->find();

		return (bool) $result->loaded();
	}
}
