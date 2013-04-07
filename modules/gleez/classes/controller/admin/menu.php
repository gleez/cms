<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Admin Menu Controller
 *
 * @package    Gleez\Admin\Controller
 * @author     Sandeep Sangamreddi - Gleez
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
	 * @uses  ORM::count_all
	 * @uses  ORM::limit
	 * @uses  Log::add
	 */
	public function action_list()
	{
		$view = View::factory('admin/menu/list')
			->bind('pagination', $pagination)
			->bind('menus', $menus);

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
			'items_per_page' => 15,
		));

		$menus  = $menus->limit($pagination->items_per_page)->offset($pagination->offset)->find_all();

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
				if ( ! $this->_internal)
				{
					$this->request->redirect(Route::get('admin/menu')->uri(), 200);
				}
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

		if (! $post->loaded())
		{
			Message::error(__('Menu doesn\'t exists!'));
			Kohana::$log->add(Log::ERROR, 'Attempt to access non-existent Menu');

			// Redirect to listing
			if (! $this->_internal)
			{
				$this->request->redirect(Route::get('admin/menu')->uri(), 404);
			}
		}

		$this->title = __('Edit :name menu', array(':name' => $post->title));
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
				if ( ! $this->_internal)
				{
					$this->request->redirect(Route::get('admin/menu')->uri(), 200);
				}

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
			Message::error(__('Menu doesn\'t exists!'));
			Kohana::$log->add(Log::ERROR, 'Attempt to access non-existent menu');

			// Redirect to listing
			if ( ! $this->_internal)
			{
				$this->request->redirect(Route::get('admin/menu')->uri(), 403);
			}
		}

		$this->title = __('Delete Menu :title', array(':title' => $menu->title));
		$action =  Route::url('admin/menu', array('action' => 'delete', 'id' => $menu->id));

		$view = View::factory('form/confirm')
			->set('action',$action)
			->set('title', $menu->title);


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
				Message::success(__('Menu %name deleted successful!', array('%name' => $name)));

				if ( ! $this->_internal)
					$this->request->redirect(Route::get('admin/menu')->uri(), 200);
			}
			catch (Exception $e)
			{
				Message::error(__('An error occurred deleting menu %menu', array('%menu' => $menu->name)));
				Kohana::$log->add(Log::ERROR, 'Error occurred deleting menu :term, id: :id, :message',
					array(
						':id'      => $menu->id,
						':term'    => $menu->name,
						':message' => $e->getMessage()
					)
				);

				if ( ! $this->_internal)
				{
					$this->request->redirect(Route::get('admin/menu')->uri(), 503);
				}
			}
		}

		$this->response->body($view);
	}

}
