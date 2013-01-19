<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * This class wraps the functionality of Mongo (connection)
 * and MongoDB (database object) into one class.
 *
 * When used with Gleez it can be instantiated simply by:
 *  $db = Mango::instance();
 *
 * The above will assume the `default` configuration from the
 * APPPATH/config/mongo.php file (or MODPATH/config/mongo.php by default).
 *
 * Alternatively it may be instantiated with the name and
 * configuration specified as arguments:
 *  $db = Mango::instance('mongo', array('database' => 'test'));
 *
 * ### System Requirements
 *
 * - PHP 5.3 or higher
 * - PHP-extension MongoDB 1.3 or higher
 *
 * @package   Mango
 * @category  Database
 * @author    Sergey Yakovlev
 * @version   0.1.1.1
 * @copyright (c) 2013 Gleez Technologies
 * @license   http://gleezcms.org/license
 * @link      http://php.net/manual/ru/book.mongo.php MongoDB Native Driver
 *
 * @todo Divide this class into the following three:
 *  - Mango_Database (Database and connection manage)
 *  - Mango_Collection (Collection manage)
 *  - Mango_Document (Document manage)
 *
 * @todo Implement profiling
 */

class Mango_Database {

  /** @var array Mango_Database instances */
  public static $instances = array();

  /** @var string Config group */
  public static $default = 'default';

  /** @var string Mango_Database instance name */
  protected $_name;

  /** @var array Configuration */
  protected $_config;

  /** @var boolean Connection state */
  protected $_connected = FALSE;

  /** @var object The raw Mongo server connection */
  protected $_connection;

  /** @var MongoDB The database instance for the database name chosen by the config */
  protected $_db;

  /** @var string Database name by default */
  const MANGO_DB_NAME = 'Gleez';

  /** @var string Module version */
  const MANGO_VERSION = '0.1.1.1';

  /**
   * Get an instance of Mango_Database
   *
   * @param   string    $name   Config group name [Optional]
   * @param   array     $config MongoDB config [Optional]
   * @return  Mango_Database    Database instance
   */
  public static function instance($name = NULL, array $config = NULL)
  {
    if (is_null($name))
    {
      $name = self::$default;
    }
    if (! isset(self::$instances[$name]))
    {
      if (is_null($config))
      {
        // Load the configuration for this database
        $config = Kohana::$config->load('mango')->$name;
      }

      new self($name,$config);
    }

    return self::$instances[$name];
  }

  /**
   * Class constructor
   *
   * @param   string    $name   Database instance name
   * @param   array     $config MongoDB config
   * @throws  Exception         In the absence of the php-mongo extension
   * @throws  Kohana_Exception  In the absence of of mandatory configuration settings
   */
  protected function __construct($name, array $config)
  {
    if (! extension_loaded('mongo'))
    {
      throw new Exception('The php-mongo extension is not installed or is disabled.');
    }

    $this->_name = $name;
    $this->_config = $config;

    $this->_db = isset($this->_config['connection.database'])
      ? $this->_config['connection.database']
      : self::MANGO_DB_NAME;

    $host = isset($this->_config['connection.hostname'])
      ? $this->_config['connection.hostname']
      : NULL;

    $user = isset($this->_config['connection.username'])
      ? $this->_config['connection.username']
      : NULL;

    $passwd = isset($this->_config['connection.password'])
      ? $this->_config['connection.password']
      : NULL;

    $opt = Arr::get($this->_config['connection'], 'options', array());

    $prepared = $this->_prepare_connection($host, $user, $passwd);

    $this->_connection = new MongoClient($prepared, $opt);

    unset($host, $user, $passwd, $opt, $prepared);

    // Store the database instance
    self::$instances[$name] = $this;
  }

  final public function __destruct()
  {
    try
    {
      $this->disconect();
      $this->_connection = NULL;
      $this->_connected = FALSE;
    }
    catch(Exception $e)
    {
      // can't throw exceptions in __destruct
    }
  }

  final public function __toString()
  {
    return $this->_name;
  }

  /**
   * Execute Command
   *
   * @param   string  $cmd    Command
   * @param   array   $args   Arguments [Optional]
   * @param   array   $values The values passed to the command [Optional]
   * @return  mixed   Responce the result of the method by passing in a `$cmd`
   */
  public function _call($cmd, array $args = array(), $values = NULL)
  {
    // If there is no connection - we execute connect()
    $this->_connected OR $this->connect();

    if (isset($args['collection']))
    {
      $c = $this->_db->selectCollection($args['collection']);
    }

    switch ($cmd)
    {
      case 'batch_insert':
        $responce = $c->batchInsert($values, array('continueOnError' => TRUE));
      break;
      case 'count':
        $responce = $c->count($args['query']);
      break;
      case 'find':
        $responce = $c->find($args['query'], $args['fields']);
      break;
      case 'find_one':
        $responce = $c->findOne($args['query'], $args['fields']);
      break;
      case 'remove':
        $responce = $c->remove($args['criteria'], $args['options']);
      break;
      case 'drop':
        $responce = $c->drop();
      break;
    }

    return $responce;
  }

  /**
   * Prepare connection
   *
   * @param   string  $host   Database host
   * @param   string  $user   Database user name
   * @param   string  $passwd Database user password
   * @return  string
   */
  protected function _prepare_connection($host, $user, $passwd)
  {
    if (is_null($host))
    {
      $host = ini_get('mongo.default_host').':'.ini_get('mongo.default_port');
    }

    if (! is_null($user) AND ! is_null($passwd))
    {
      return 'mongodb://' . $user . ':' . $passwd . '@' . $host . '/' . $this->_db;
    }

    return 'mongodb://' . $host . '/' . $this->_db;
  }

  /**
   * Get an instance of MongoDB directly
   *
   * @return MongoDB
   */
  public function db()
  {
    $this->_connected OR $this->connect();

    return $this->_db;
  }

  /**
   * Database connection
   *
   * @return  TRUE              When the connection is successful
   * @throws  Kohana_Exception  When a connection error
   */
  public function connect()
  {
    // If no connection
    if(! $this->_connected)
    {
      try
      {
        // Connecting to the server
        $this->_connected = $this->_connection->connect();
      }
      catch (MongoConnectionException $e)
      {
        // Unable to connect to database server
        throw new Kohana_Exception('Unable to connect to MongoDB server. MongoDB said :message',
          array(
            ':message' => $e->getMessage()
          )
        );
      }

      $this->_db = $this->_connection->selectDB("$this->_db");
    }

    return $this->_connected;
  }

  /**
   * Disconnecting from the database
   *
   * @return boolean TRUE if successful, FALSE uf it fails
   */
  protected function disconect()
  {
    if ($this->_connected)
    {
      $this->_connected = $this->_connection->close();
      $this->_db = "$this->_db";
    }

    return $this->_connected;
  }

  /**
   * Counting documents in a collection
   *
   * @param   string  $collection Collection Name
   * @param   array   $query      NoSQL query [Optional]
   * @return  integer Amount of documents
   *
   * @link    http://php.net/manual/en/mongocollection.count.php MongoCollection::count()
   */
  public function count($collection, array $query = array())
  {
    return $this->_call('count', array(
      'collection' => $collection,
      'query'      => $query
    ));
  }

  /**
   * Receives documents from the collection
   *
   * @param   string  $collection Collection Name
   * @param   array   $query      NoSQL query [Optional]
   * @param   array   $fields     Fields which are looking for in the request [Optional]
   * @return  MongoCursor
   *
   * @link    http://php.net/manual/en/mongocollection.find.php MongoCollection::find()
   */
  public function find($collection, array $query = array(), array $fields = array())
  {
    return $this->_call('find', array(
      'collection'  => $collection,
      'query'       => $query,
      'fields'      => $fields
    ));
  }

  /**
   * Gets 1 document from the collection
   *
   * @param   string  $collection Collection Name
   * @param   array   $query      NoSQL query [Optional]
   * @param   array   $fields     Fields which are looking for in the request [Optional]
   * @return  MongoCursor
   *
   * @link    http://php.net/manual/en/mongocollection.findone.php MongoCollection::findOne()
   */
  public function find_one($collection, array $query = array(), array $fields = array())
  {
    return $this->_call('find_one', array(
      'collection'  => $collection,
      'query'       => $query,
      'fields'      => $fields,
    ));
  }

  /**
   * Deleting a document from a collection
   *
   * @param   string  $collection Collection Name
   * @param   array   $criteria   The search criteria
   * @param   array   $options    Additional options [Optional]
   * @return  boolean|array
   *
   * @link    http://php.net/manual/en/mongocollection.remove.php MongoCollection::remove()
   */
  public function remove($collection, array $criteria, $options = array())
  {
    return $this->_call('remove', array(
      'collection'  => $collection,
      'criteria'    => $criteria,
      'options'     => $options
    ));
  }

  /**
   * Drop collection
   *
   * @param   string  $collection Collection Name
   * @return  array   The database response as array
   *
   * @link    http://php.net/manual/en/mongocollection.drop.php MongoCollection::drop()
   */
  public function drop($collection)
  {
    return $this->_call('drop', array(
      'collection'  => $collection
    ));
  }

  /**
   * Bulk insert multiple documents in a collection
   *
   * Note: If in the array `$a` pass the objects,
   * they should not have the properties of `protected` or `private`
   *
   * @param   string      $collection Collection Name
   * @param   array       $a          An array of arrays or objects
   * @return  mixed
   *
   * @link    http://php.net/manual/en/mongocollection.batchinsert.php MongoCollection::batchInsert()
   */
  public function batch_insert($collection, array $a)
  {
    return $this->_call('batch_insert', array('collection' => $collection), $a);
  }

}