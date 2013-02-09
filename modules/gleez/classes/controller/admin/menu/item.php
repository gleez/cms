<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Admin Menu Item Controller
 *
 * @package   Gleez\Admin\Controller
 * @author    Sandeep Sangamreddi - Gleez
 * @copyright (c) 2011-2013 Gleez Technologies
 * @license   http://gleezcms.org/license
 */
class Controller_Admin_Menu_Item extends Controller_Admin {

  /**
   * The before() method is called before controller action.
   */
  public function before()
  {
    ACL::Required('administer menu');
    parent::before();
  }

  /**
   * Lists all menu items
   */
  public function action_list()
  {
    $id = (int) $this->request->param('id');
    $menu  = ORM::factory('menu', array('id' => $id, 'lft' => 1));

    if (! $menu->loaded())
    {
      Message::error(__('Menu: doesn\'t exists!'));
      Kohana::$log->add(Log::ERROR, 'Attempt to access non-existent menu id: `:id`',
        array(
          ':id' => $id
        )
      );

      if (! $this->_internal)
      {
        $this->request->redirect(Route::get('admin/menu')->uri());
      }
    }

    $this->title  = __('Items for %vocab', array('%vocab' => $menu->title));
    $view = View::factory('admin/menu/item/list')
          ->bind('items', $items)
          ->bind('id', $id);

    $items  = DB::select()->from('menus')
            ->where('lft', '>', $menu->lft)
            ->where('rgt', '<', $menu->rgt)
            ->where('scp', '=', $menu->scp)
            ->order_by('lft', 'ASC')
            ->execute()
            ->as_array();

    if (count($items) == 0)
    {
      Message::info(__("Menu Items doesn't exists!"));
      $this->response->body( View::factory('admin/menu/item/none')->set('id', $id) );
    }

    $this->response->body($view);

    if (! $this->_internal)
    {
      Assets::tabledrag('menu-admin-list', 'match', 'parent', 'menu-plid', 'menu-plid', 'menu-mlid', TRUE, 15);
      Assets::tabledrag('menu-admin-list', 'order', 'sibling', 'menu-weight');
    }
  }

  /**
   * Adds menu item
   */
  public function action_add()
  {
    $id = (int) $this->request->param('id');
    $menu = ORM::factory('menu', array('id' => $id, 'lft' => 1));

    if (! $menu->loaded())
    {
      Message::error(__("Menu: doesn't exists!"));
      Kohana::$log->add(Log::ERROR, 'Attempt to access non-existent menu');

      if (! $this->_internal)
      {
        $this->request->redirect(Route::get('admin/menu')->uri());
      }
    }

    $this->title = __('Add Item for %menu', array('%menu' => $menu->title));
    $view = View::factory('admin/menu/item/form')
          ->bind('menu', $menu)
          ->bind('post', $post)
          ->bind('errors', $errors);

    $post 	  = ORM::factory('menu')->values($_POST);

    if ($this->valid_post('menu-item'))
    {
      try
      {
        $post->create_at($id, Arr::get($_POST, 'parent', 'last'));
        Message::success(__('Menu Item: %name saved successful!', array('%name' => $post->name)));
        Cache::instance('menus')->delete($menu->name);

        if (! $this->_internal)
        {
          $this->request->redirect(Route::get('admin/menu/item')->uri(array('action' => 'list', 'id' => $menu->id )));
        }
      }
      catch (ORM_Validation_Exception $e)
      {
        $errors = $e->errors('models');
      }
    }

    $this->response->body($view);
  }

  /**
   * Edit menu item
   */
  public function action_edit()
  {
    $id = (int) $this->request->param('id', 0);
    $menu = ORM::factory('menu', $id);

    if (! $menu->loaded())
    {
      Message::error(__("Menu: doesn't exists!"));
      Kohana::$log->add(LOG::ERROR, 'Attempt to access non-existent Menu');

      if (! $this->_internal)
      {
        $this->request->redirect( Route::get('admin/menu')->uri() );
      }
    }

    $this->title = __('Edit Item :name', array(':name' => $menu->name));
    $view = View::factory('admin/menu/item/form')
          ->bind('menu', $menu)
          ->bind('post', $menu)
          ->bind('errors', $errors);

    $post = ORM::factory('menu', $id)
          ->values($_POST);

    if ($this->valid_post('menu-item'))
    {
      try
      {
        $post->save();
        Message::success(__('Menu Item: %name updated successful!', array('%name' => $post->name)));
        Cache::instance('menus')->delete_all();

        if (! $this->_internal)
        {
          $this->request->redirect(Route::get('admin/menu/item')->uri(array('action' => 'list', 'id' => $menu->scp)));
        }
      }
      catch (ORM_Validation_Exception $e)
      {
        $errors = $e->errors('models');
      }
    }

    $this->response->body($view);
  }

  /**
   * Delete menu item
   */
  public function action_delete()
  {
    $id = (int) $this->request->param('id', 0);
    $menu = ORM::factory('menu', $id);

    if (! $menu->loaded())
    {
      Message::error(__("Menu: doesn't exists!"));
      Kohana::$log->add(LOG::ERROR, 'Attempt to access non-existent Menu');

      if (! $this->_internal)
      {
        $this->request->redirect(Route::get('admin/menu')->uri());
      }
    }

    $action = Route::get('admin/menu/item')->uri(array('action' =>'delete', 'id' => $menu->id));
    $this->title = __('Delete Menu Item :name', array(':name' => $menu->title));
    $view = View::factory('form/confirm')->set('title', $menu->title)->set('action', $action);

    // If deletion is not desired, redirect to list
    if (isset( $_POST['no'] ) AND $this->valid_post())
    {
      $this->request->redirect(Route::get('admin/menu/item')->uri());
    }

    // If deletion is confirmed
    if (isset($_POST['yes']) AND $this->valid_post())
    {
      try
      {
        $name = $menu->name;
        $menu->delete();
        Cache::instance('menus')->delete_all();
        Message::success(__('Menu Item: :name deleted successful!', array(':name' => $name)));

        if (! $this->_internal)
        {
          $this->request->redirect(Route::get('admin/menu')->uri(array('action' =>'list')));
        }
      }
      catch (Exception $e)
      {
        Kohana::$log->add(LOG::ERROR, 'Error occured deleting menu item id: :id, :message',
          array(
            ':id'       => $menu->id,
            ':message'  => $e->getMessage()
          )
        );
        Message::error(__('An error occured deleting menu item :term.', array(':term' => $menu->name)));

        if (! $this->_internal)
        {
          $this->request->redirect(Route::get('admin/menu')->uri(array('action' =>'list', 'id' => $menu->scp)));
        }

      }
    }

  $this->response->body($view);
  }

  public function action_confirm()
  {
    $id = (int) $this->request->param('id', 0);

    if ($this->valid_post('menu-item-list') AND $id)
    {
      $updated_items = array();
      foreach ($_POST as $mlid => $val)
      {
        if (isset($_POST[$mlid]['mlid']) AND is_array($_POST[$mlid]) )
        {
          $updated_items[$val['mlid']] = $_POST[$mlid];
        }
      }
      $this->tree = array();
      $this->counter = 1;
      $this->level_zero = 1;
      $this->calculate_mptt( $this->generate_tree($updated_items) );
      unset($updated_items);

      if ($this->level_zero > 1)
      {
        Message::error(__('Menu Items order could not be saved.'));
        Kohana::$log->add(LOG::ERROR, 'Menu Items order could not be saved.');

        $this->request->redirect(Route::get('admin/menu/item')->uri(array('action'=>'list', 'id' => $id)));
      }

      try
      {
        foreach($this->tree as $node)
        {
          DB::update('menus')->set(
            array(
              'pid'     => $node['pid'],
              'active'  => $node['active'],
              'lvl'     => $node['lvl'], 'lft' => $node['lft'],
              'rgt'     => $node['rgt']
          ))
          ->where('id', '=', $node['id'])
          ->execute();
        }

        Message::success(__('Menu Items order has been saved.'));
      }
      catch(Exception $e)
      {
        Message::error(__('Menu Items order could not be saved.'));
      }

      Cache::instance('menus')->delete_all();
      $this->request->redirect(Route::get('admin/menu/item')->uri(array('action'=>'list', 'id' => $id)));
    }
  }

  /**
   * Private function to generate the tree with parent
   * for bulk update child relationship
   *
   * @param   array $tree Menu tree
   * @return  array Generated tree
   */
  private function generate_tree($tree)
  {
    $menu = array();
    $ref = array();

    foreach($tree as $d)
    {
      $d['children'] = array();

      if(isset($ref[$d['plid']]))
      {
        // we have a reference on its parent
        $ref[ $d['plid'] ]['children'][ $d['mlid'] ] = $d;
        $ref[ $d['mlid'] ] =& $ref[ $d['plid'] ]['children'][ $d['mlid'] ];
      }
      else
      {
        // we don't have a reference on its parent => put it a root level
        $menu[$d['mlid']] = $d;
        $ref[$d['mlid']] =& $menu[$d['mlid']];
      }
    }

    return $menu;
  }

  /**
   * Private function to calculate and generate the new ordered left,
   * right and level values for bulk update.
   *
   * @param   array   $tree
   * @param   integr  $level
   */
  private function calculate_mptt($tree, $level = 2)
  {
    foreach ($tree as $id => $val)
    {
      $left = ++$this->counter;

      if (! empty($val['children']))
      {
        $this->calculate_mptt($val['children'], $id, $level+1);
      }

      $right = ++$this->counter;

      if ($level === 1)
      {
        $this->level_zero++;
      }

      $this->tree[] = array(
        'id'      => $id,
        'pid'     => (int) $val['plid'],
        'active'  => isset($val['hidden']) ? 1 : 0,
        'lvl'     => $level,
        'lft'     => $left,
        'rgt'     => $right
      );
    }
  }
}
