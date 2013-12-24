<?php
/**
 * Private Message Helper
 *
 *
 * @package    Gleez\Helpers
 * @author     Gleez Team
 * @version    1.0.0
 * @copyright  (c) 2011-2013 Gleez Technologies
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
}
