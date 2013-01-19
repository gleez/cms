<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Controller Class for control logging
 *
 * ### System Requirements
 *
 * - PHP 5.3 or higher
 * - PHP-extension Mongodb 1.3 or higher
 * - Mango Reader module 0.1.1.1 or higher
 * - ACL [Optional]
 *
 * @package   Mango
 * @category  Controller
 * @author    Sergey Yakovlev
 * @copyright (c) 2013 Gleez Technologies
 * @license   http://gleezcms.org/license
 */

class Controller_Admin_Mango_Log extends Controller_Admin {

  /** The before() method is called before controller action. */
  public function before()
  {
    // Required privilege
    if (class_exists('ACL'))
    {
      ACL::Required('view logs');
    }

    // Loading module specific styles
    Assets::css('user', 'media/css/mango.css', NULL, array('weight' => 0));

    parent::before();
  }

  /** Shows list of events */
  public function action_list()
  {
    $this->title = __('System log');

    $db = Mango::instance();

    $view = View::factory('admin/mango/log/list')
                ->bind('pagination',  $pagination)
                ->bind('logs',        $logs);

    $pagination = Pagination::factory(
      array
      (
        'current_page'    => array('source'=>'cms', 'key'=>'page'),
        'total_items'     => $db->count('Logs'),
        'items_per_page'  => 50,
        'uri'             => Route::get('admin/log')->uri(),
      )
    );

    $logs = $db->find('Logs')
               ->skip($pagination->offset)
               ->sort(array('time'=> -1))
               ->limit($pagination->items_per_page);

    $this->response->body($view);
  }

  /** View a particular event */
  public function action_view()
  {
    $id = $this->request->param('id', 0);

    $log = Mango::instance()->find_one('Logs', array('_id' => new MongoId($id)));

    if(is_null($log))
    {
      Message::alert(__('Event #:id not found!', array(':id' => $id)));

      Kohana::$log->add(Log::WARNING, 'An attempt to get the log event id: `:id`, which is not found!',
        array(
          ':id' => $id
        )
      );

      if (! $this->_internal)
      {
        $this->request->redirect(Route::get('admin/log')->uri(), 404);
      }
    }

    $user = User::lookup((int) $log['user']);

    $log['user'] = $user->nick;

    $this->title  = __('View event');

    $view = View::factory('admin/mango/log/view')
                ->set('log', $log);

    $this->response->body($view);
  }

  /** Delete the message from log */
  public function action_delete()
  {
    // Required privilege
    if (class_exists('ACL'))
    {
      ACL::Required('delete logs');
    }

    $id = $this->request->param('id', 0);

    $log = Mango::instance()->find_one('Logs', array('_id' => new MongoId($id)));

    if(is_null($log))
    {
      Message::alert(__('Event #:id not found!', array(':id' => $id)));

      Kohana::$log->add(Log::WARNING, 'An attempt to delete the log event id: `:id`, which is not found!',
        array(
          ':id' => $id
        )
      );

      if (! $this->_internal)
      {
        $this->request->redirect(Route::get('admin/log')->uri(), 404);
      }
    }

    $this->title = __('Deleting log records');

    $view = View::factory('form/confirm')
                ->set('action', Route::url('admin/log', array('action' => 'delete', 'id' => $id)))
                ->set('title', 'Event #'.$id);

    // If deletion is not desired, redirect to list
    if (isset($_POST['no']) AND $this->valid_post())
    {
      $this->request->redirect(Route::get('admin/log')->uri(), 200);
    }

    // If deletion is confirmed
    if (isset($_POST['yes']) AND $this->valid_post())
    {
      try
      {
        Mango::instance()->remove('Logs', array('_id' => new MongoId($id)));
        Mango::instance()->remove(
          'Logs',                           // Collection Name
          array('_id' => new MongoId($id)), // Event ID
          array("justOne" => TRUE)          // Remove at most one record
        );

        Message::notice(__('Message from the log has been removed.'));

        if (! $this->_internal)
        {
          $this->request->redirect(Route::get('admin/log')->uri(), 200);
        }

      }
      catch (Exception $e)
      {
        Message::error(__('An error occurred when deleting the message: :msg',
          array(
            ':msg' => $e->getMessage()
          )
        ));

        if (! $this->_internal)
        {
          $this->request->redirect(Route::get('admin/log')->uri(), 500);
        }
      }
    }

    $this->response->body($view);
  }

  /** Drop collection */
  public function action_clear()
  {
    // Required privilege
    if (class_exists('ACL'))
    {
      ACL::Required('delete logs');
    }

    $this->title = __('Drop system log');

    $view = View::factory('form/confirm')
                ->set('action', Route::url('admin/log', array('action' => 'clear')))
                ->set('title', 'All log events');

    // If deletion is not desired, redirect to list
    if (isset($_POST['no']) AND $this->valid_post())
    {
      $this->request->redirect(Route::get('admin/log')->uri(), 200);
    }

    // If deletion is confirmed
    if (isset($_POST['yes']) AND $this->valid_post())
    {
      try
      {
        $responce = Mango::instance()->drop('Logs');

        Message::notice(__('System log successfully cleared. Database message: :msg',
          array(
            ':msg' => $responce['msg']
          )
        ));

        if (! $this->_internal)
        {
          $this->request->redirect(Route::get('admin/log')->uri(), 200);
        }

      }
      catch (Exception $e)
      {
        Message::error(__('An error occurred when clearing the system log: :msg',
          array(
            ':msg' => $e->getMessage()
          )
        ));

        if (! $this->_internal)
        {
          $this->request->redirect(Route::get('admin/log')->uri(), 500);
        }
      }
    }

    $this->response->body($view);
  }

}