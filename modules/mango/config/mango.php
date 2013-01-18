<?php defined('SYSPATH') OR die('No direct access allowed.');

return array(

  /**
   * Configuration Name
   *
   * You use this name when initializing a new MongoDB instance
   *
   *  // Example:
   *  $db = MongoDB::instance('default');
   */
  'default' => array(

    /** @var array Connection Setup */
    'connection' => array(

      /** @var string Database host */
      //'hostname' => '192.168.0.1',

      /** @var string Database name */
      //'database'  => 'Cerber',

      /** @var string Auth params */
      //'username'  => 'username',
      //'password'  => 'password',

      /** @var array Additional options */
      //'options'   => array(
      //  'persist'    => 'persist_id',
      //  'timeout'    => 1000,
      //  'replicaSet' => TRUE
      //)
    ),
  )
);