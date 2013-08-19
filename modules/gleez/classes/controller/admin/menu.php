<?php
/**
 * Admin Menu Controller
 *
 * @package    Gleez\Controller\Admin
 * @author     Gleez Team
 * @version    1.0.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Admin_Menu extends Controller_Admin {

	/**
	 * The before() method is called before controller action
	 *
	 * @uses  ACL::required
	 */
	public function before()
	{
		ACL::required('administer menu');

		parent::before();
	}

	/**
	 * List menus
	 *
	 * @uses  ORM::reset
	 * @uses  ORM::dataTables
	 * @uses  Route::get
	 * @uses  Route::uri
	 * @uses  Route::url
	 * @uses  Assets::popup
	 * @uses  Request::is_datatables
	 * @uses  Text::plain
	 * @uses  HTML::icon
	 */
	public function action_list()
	{
		Assets::popup();

		$is_datatables = Request::is_datatables();
		$menus         = ORM::factory('menu')->where('lft', '=', 1);

		if ($is_datatables)
		{
			$this->_datatables = $menus->dataTables(array('title', 'descp'));

			foreach ($this->_datatables->result() as $menu)
			{
				$this->_datatables->add_row(
					array(
						Text::plain($menu->title).'<div class="description">'.Text::plain($menu->descp).'</div>',
						HTML::icon($menu->list_items_url, 'icon-th-list', array('class'=>'action-list', 'title'=> __('List Links'))),
						HTML::icon($menu->add_item_url, 'icon-plus', array('class'=>'action-add', 'title'=> __('Add Link'))),
						HTML::icon($menu->edit_url, 'icon-edit', array('class'=>'action-edit', 'title'=> __('Edit Menu'))),
						HTML::icon($menu->delete_url, 'icon-trash', array('class'=>'action-delete', 'title'=> __('Delete Menu'), 'data-toggle' => 'popup', 'data-table' => '#admin-list-menus'))
					)
				);
			}
		}

		$this->title = __('Menus');
		$add_url     = Route::get('admin/menu')->uri(array('action' =>'add'));
		$url         = Route::url('admin/menu', array('action' => 'list'), TRUE);

		$view = View::factory('admin/menu/list')
				->bind('datatables',   $this->_datatables)
				->set('is_datatables', $is_datatables)
				->set('add_url',       $add_url)
				->set('url',           $url);

		$this->response->body($view);
	}

	/**
	 * Add menu
	 *
	 * @uses  Request::redirect
	 * @uses  Route::get
	 * @uses  Route::uri
	 * @uses  DB::insert
	 * @uses  ORM::save
	 * @uses  ORM::make_root
	 * @uses  Message::success
	 * @uses  Cache::delete
	 * @uses  Message::success
	 */
	public function action_add()
	{
		$post = ORM::factory('menu');
		$action = Route::get('admin/menu')->uri(array('action' => 'add'));

		if ($this->valid_post('menu'))
		{
			$post->values($_POST);
			try
			{
				$post->make_root();
				DB::insert('widgets', array('name', 'title', 'module'))
					->values(array('menu/'.$post->name, $post->title, 'gleez'))
					->execute();

				Message::success(__('Menu %name created successful!', array('%name' => $post->title)));
				Cache::instance('menus')->delete($post->name);

				// Redirect to listing
				$this->request->redirect(Route::get('admin/menu')->uri(), 200);
			}
			catch (ORM_Validation_Exception $e)
			{
				$this->_errors = $e->errors('models', TRUE);
			}
		}
		$this->title = __('Creating a Menu');

		$view = View::factory('admin/menu/form')
				->bind('post', $post)
				->bind('action', $action)
				->bind('errors', $this->_errors);

		$this->response->body($view);
	}

	/**
	 * Edit menu
	 *
	 * @uses  Message::error
	 * @uses  Message::success
	 * @uses  Log::add
	 * @uses  Request::redirect
	 * @uses  Route::get
	 * @uses  Route::uri
	 * @uses  Cache::delete
	 * @uses  ORM::save
	 */
	public function action_edit()
	{
		$id = (int) $this->request->param('id', 0);
		$post = ORM::factory('menu', $id);

		if ( ! $post->loaded())
		{
			Log::error('Attempt to access non-existent Menu.');
			Message::error(__('Menu doesn\'t exists!'));

			// Redirect to listing
			$this->request->redirect(Route::get('admin/menu')->uri(), 404);
		}

		$this->title = __('Edit %name menu', array('%name' => $post->title));
		$action = Route::get('admin/menu')->uri(array('action' => 'edit', 'id' => $id));

		if ($this->valid_post('menu'))
		{
			$post->values($_POST);
			try
			{
				$post->save();
				Message::success(__('Menu %name saved successful!', array('%name' => $post->title)));
				Cache::instance('menus')->delete($post->name);

				// Redirect to listing
				$this->request->redirect(Route::get('admin/menu')->uri(), 200);
			}
			catch (ORM_Validation_Exception $e)
			{
				$this->_errors = $e->errors('models', TRUE);
			}
		}

		$view = View::factory('admin/menu/form')
					->bind('post',    $post)
					->bind('action',  $action)
					->bind('errors',  $this->_errors);

		$this->response->body($view);
	}

	/**
	 * Delete menu
	 *
	 * @uses  Message::error
	 * @uses  Message::success
	 * @uses  Request::redirect
	 * @uses  Request::uri
	 * @uses  Route::get
	 * @uses  Route::uri
	 * @uses  Route::url
	 * @uses  Cache::delete
	 * @uses  ORM::delete
	 * @uses  DB::delete
	 * @uses  Log::add
	 */
	public function action_delete()
	{
		$id = (int) $this->request->param('id', 0);
		$menu = ORM::factory('menu', $id);

		if ( ! $menu->loaded())
		{
			Log::error('Attempt to access non-existent menu.');
			Message::error(__("Menu doesn't exists!"));

			// Redirect to listing
			$this->request->redirect(Route::get('admin/menu')->uri(), 404);
		}
		// If it is an external request and id == 2
		elseif ($menu->id == 2)
		{
			Log::error('Attempt to delete system menu.');
			Message::error(__("You can't delete system menu!"));

			// Redirect to listing
			$this->request->redirect(Route::get('admin/menu')->uri(), 403);
		}

		$this->title = __('Delete Menu :title', array(':title' => $menu->title));

		$view = View::factory('form/confirm')
			->set('action', $menu->delete_url)
			->set('title',  $menu->title);


		// If deletion is not desired, redirect to list
		if (isset($_POST['no']) AND $this->valid_post())
		{
			$this->request->redirect(Route::get('admin/menu')->uri());
		}

		// If deletion is confirmed
		if (isset($_POST['yes']) AND $this->valid_post())
		{
			// If it is an internal request (eg. popup dialog) and id < 3
			if ($menu->id == 2)
			{
				Log::error('Attempt to delete system menu.');
				$this->_errors = array(__("You can't delete system menu!"));
			}
			else
			{
				try
				{
					$name = $menu->title;
					DB::delete('widgets')->where('name', '=', 'menu/'.$menu->name)->execute();
					Cache::instance('menus')->delete($menu->name);

					$menu->delete();
					Message::success(__('Menu %name deleted successful!', array('%name' => $name)));
				}
				catch (Exception $e)
				{
					Log::error('Error occurred deleting menu :term, id: :id, :msg',
						array(':id' => $menu->id, ':term' => $menu->name, ':msg' => $e->getMessage()
						)
					);
					$this->_errors = array(__('An error occurred deleting menu %menu: :message',
						array(
							'%menu'    => $menu->name,
							':message' => $e->getMessage()
						)
					));
				}
			}

			$this->request->redirect(Route::get('admin/menu')->uri());
		}

		$this->response->body($view);
	}

}
