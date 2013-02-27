<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Admin Menu Controller
 *
 * @package   Gleez\Admin\Controller
 * @author    Sandeep Sangamreddi - Gleez
 * @copyright (c) 2011-2013 Gleez Technologies
 * @license   http://gleezcms.org/license
 */
class Controller_Admin_Menu extends Controller_Admin {

	/**
	 * The before() method is called before controller action.
	 */
	public function before()
	{
		ACL::required('administer menu');
		parent::before();
	}

	/**
	 * List menus
	 */
	public function action_list()
	{
		$view = View::factory('admin/menu/list')->bind('pagination', $pagination)->bind('menus', $menus);
		$this->title = __('Menus');

		$menus  = ORM::factory('menu')->where('lft', '=', 1);
		$total  = $menus->reset(FALSE)->count_all();

		if ($total == 0)
		{
			Kohana::$log->add(Log::INFO, 'No menus found');
			$this->response->body(View::factory('admin/menu/none'));
			return;
		}

		$pagination = Pagination::factory(array(
				'current_page'   => array('source'=>'route', 'key'=>'page'),
				'total_items'    => $total,
				'items_per_page' => 5,
				));

		$menus  = $menus->limit($pagination->items_per_page)->offset($pagination->offset)->find_all();

		$this->response->body($view);
	}

	/**
	 * Add menu
	 */
	public function action_add()
	{
		$this->title = __('Add Menu');
		$view = View::factory('admin/menu/form')->bind('post', $post)->bind('errors', $errors);
		$post = ORM::factory('menu');

		if ($this->valid_post('menu'))
		{
			$post->values($_POST);
			try
			{
				$post->make_root();
				DB::insert('widgets', array('name', 'title', 'module'))
					->values(array('menu/'.$post->name, $post->title, 'gleez'))->execute();

				Message::success(__('Menu: %name saved successful!', array('%name' => $post->name)));
				Cache::instance('menus')->delete($post->name);

				// Redirect to listing
				if ( ! $this->_internal)
				{
					$this->request->redirect(Route::get('admin/menu')->uri(), 200);
				}

			}
			catch (ORM_Validation_Exception $e)
			{
				$errors = $e->errors();
			}
		}

		$this->response->body($view);
	}

	/**
	 * Edit menu
	 */
	public function action_edit()
	{
		$id = (int) $this->request->param('id', 0);
		$post = ORM::factory('menu', $id);

		if (! $post->loaded())
		{
			Message::error(__('Menu: doesn\'t exists!'));
			Kohana::$log->add(Log::ERROR, 'Attempt to access non-existent Menu');

			// Redirect to listing
			if (! $this->_internal)
			{
				$this->request->redirect(Route::get('admin/menu')->uri(), 404);
			}
		}

		$this->title = __( 'Edit Menu: :name', array(':name' => $post->name) );
		$view = View::factory('admin/menu/form')->bind('post', $post)->bind('errors', $errors);

		if ($this->valid_post('menu'))
		{
			$post->values($_POST);
			try
			{
				$post->save();
				Message::success(__('Menu: %name saved successful!', array('%name' => $post->title)));
				Cache::instance('menus')->delete($post->name);

				// Redirect to listing
				if ( ! $this->_internal)
				{
					$this->request->redirect(Route::get('admin/menu')->uri(), 200);
				}

			}
			catch (ORM_Validation_Exception $e)
			{
				$errors = $e->errors();
			}
		}

		$this->response->body($view);
	}

	/**
	 * Delete menu
	 */
	public function action_delete()
	{
		$id = (int) $this->request->param('id', 0);
		$menu = ORM::factory('menu', $id);

		if (! $menu->loaded())
		{
			Message::error(__('Menu doesn\'t exists!'));
			Kohana::$log->add(Log::ERROR, 'Attempt to access non-existent menu');

			// Redirect to listing
			if ( ! $this->_internal)
			{
				$this->request->redirect(Route::get('admin/menu')->uri(), 403);
			}
		}

		$this->title = __('Delete Menu :title', array(':title' => $menu->name ));

		$view = View::factory('form/confirm')
									 ->set('action', Route::url('admin/menu', array('action' => 'delete', 'id' => $menu->id) ))
									 ->set('title', $menu->name);


		// If deletion is not desired, redirect to list
		if (isset($_POST['no']) AND $this->valid_post())
		{
			$this->request->redirect(Route::get('admin/menu')->uri());
		}

		// If deletion is confirmed
		if (isset($_POST['yes']) AND $this->valid_post())
		{
			try
			{
				$name = $menu->title;
				DB::delete('widgets')->where('name', '=', 'menu/'.$menu->name)->execute();
				Cache::instance('menus')->delete($menu->name);

				$menu->delete();
				Message::success(__('Menu: :name deleted successful!', array(':name' => $name)));

				if ( ! $this->_internal)
					$this->request->redirect(Route::get('admin/menu')->uri());
			}
			catch (Exception $e)
			{
				Message::error(__('An error occured deleting menu %menu', array('%menu' => $menu->name)));
				Kohana::$log->add(Log::ERROR, 'Error occured deleting menu :term, id: :id, :message',
					array(
						':id'      => $menu->id,
						':term'    => $menu->name,
						':message' => $e->getMessage()
					)
				);

				if ( ! $this->_internal)
				{
					$this->request->redirect(Route::get('admin/menu')->uri(), 400);
				}
			}
		}

		$this->response->body($view);
	}

}
