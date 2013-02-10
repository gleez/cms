<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Admin Setting Controller
 *
 * @package   Gleez\Admin\Controller
 * @author    Sandeep Sangamreddi - Gleez
 * @copyright (c) 2011-2013 Gleez Technologies
 * @license   http://gleezcms.org/license
 */
class Controller_Admin_Setting extends Controller_Admin {

  public function action_index()
  {
    $this->title = __('General Settings');
    $config = Kohana::$config->load('site');

    if(isset($config['maintenance_mode']) AND $config['maintenance_mode'] == 1)
    {
      Message::success(__('Site running in maintenance mode!'));
    }

    $view = View::factory('admin/settings')
                ->set('date_time_formats',  Date::date_time_formats(1))
                ->set('date_formats',       Date::date_formats(1))
                ->set('time_formats',       Date::time_formats(1))
                ->set('date_weekdays',      Date::weeekdays())
                ->set('timezones',          Date::timezones())
                ->bind('title',             $this->title)
                ->set('post',               $config);

    if ($this->valid_post('settings'))
    {
      unset($_POST['settings'], $_POST['_token'], $_POST['_action']);

      foreach($_POST as $key => $value)
      {
        $config->set($key, $value);

        if($key == 'front_page' )
        {
          $this->_set_front_page($value);
        }
      }

      Message::success(__('Site configuration updated!'));

      $this->request->redirect(Route::get('admin/setting')->uri());
    }

    $this->response->body($view);
  }

  /**
   * Sets Front page route
   *
   * @param   string  $source Path for alias
   * @return  ORM Model_Path
   */
  private function _set_front_page($source)
  {
    // Delete previous alias if any
    Path::delete( array( 'alias' => '<front>' ) );

    // Create and save alias
    $values = array();
    $values['source'] = $source;
    $values['alias']  = '<front>' ;

    return Path::save($values);
  }
}
