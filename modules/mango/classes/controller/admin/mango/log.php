<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * The controller control logging
 *
 * @package   Mango
 * @category  Controller
 * @author    Sergey Yakovlev
 * @copyright (c) 2013 Gleez Technologies
 * @license   http://gleezcms.org/license
 */

class Controller_Admin_Mango_Log extends Controller_Admin {

  public function before()
  {
    // required privilege
    ACL::Required('view logs');

    // Loading module specific styles
    Assets::css('user', 'media/css/mango.css', NULL, array('weight' => 0));

    parent::before();
  }

  /** List of events */
  public function action_list()
  {
    $this->title = 'System log';

    $db = Mango::instance();

    $view = View::factory('admin/mango/log/list')
                ->bind('pagination',  $pagination)
                ->bind('logs',        $logs);

    $pagination = Pagination::factory(
      array
      (
        'current_page'    => array('source'=>'cms', 'key'=>'page'),
        'total_items'     => $db->count('Logs'),
        'items_per_page'  => 10,
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
      Message::alert('Event #' . $id . ' not found!');

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
    // required privilege
    ACL::Required('delete logs');

    $id = $this->request->param('id', 0);

    $log = Mango::instance()->find_one('Logs', array('_id' => new MongoId($id)));

    if(is_null($log))
    {
      Message::alert('Event #' . $id . ' not found!');

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

        Message::notice('Message from the log has been removed');

        if (! $this->_internal)
        {
          $this->request->redirect(Route::get('admin/log')->uri(), 200);
        }

      }
      catch (Exception $e)
      {
        Message::error('An error occurred when deleting the message: '.$e->getMessage());

        if (! $this->_internal)
        {
          $this->request->redirect(Route::get('admin/log')->uri(), 500);
        }
      }
    }

    $this->response->body($view);
  }
}