<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Class event logging using the database MongoDB
 *
 * ### System Requirements
 *
 * - PHP 5.3 or higher
 * - PHP-extension Mongodb 1.3 or higher
 * - Mango Reader module 0.1.1.1 or higher
 *
 * @package   Mango\Logging
 * @author    Sergey Yakovlev - Gleez
 * @copyright (c) 2013 Gleez Technologies
 * @license   http://gleezcms.org/license
 */

class Mango_Log_Mango extends Log_Writer {

  /**
   * @var string Collection for log
   *
   * Use capped collection to support high-bandwidth inserts
   * @link http://docs.mongodb.org/manual/core/capped-collections/ Capped Collections
   */
  protected $_collection;

  /** @var string Database instace name */
  protected $_name;

  /**
   * Constructor
   *
   * Example:<br>
   * <code>
   *   $writer = new Log_Mango($collection);
   * </code>
   *
   * @param   string  $collection Collection Name [Optional]
   * @param   string  $name       Database instance name [Optional]
   */
  public function __construct($collection = 'Logs', $name = 'default')
  {
    $this->_collection  = $collection;
    $this->_name        = $name;
  }

  /**
   * Save messages to MongoDB collection
   *
   * @param   array   $messages   An array of messages
   */
  public function write(array $messages)
  {
    // Descriptive array
    $info = array
    (
      'host'  => Request::$client_ip,
      'agent' => Request::$user_agent,
      'user'  => User::active_user()->id,
      'url'   => Text::plain(Request::initial()->uri()),
    );

    // Message to log
    $logs = array();

    foreach ($messages as $message)
    {
      if(isset($message))
      {
        $message['type']  = $this->_log_levels[$message['level']];
        $message['time']  = new MongoDate(strtotime($message['time']));

        // Merging descriptive array and the current message
        $logs[] = array_merge($info, $message);
      }
    }

    if(! empty($logs))
    {
      // Record to a collection
      Mango::instance($this->_name)->batch_insert($this->_collection, $logs);
    }
  }

}