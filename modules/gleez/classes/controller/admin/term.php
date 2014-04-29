<?php
/**
 * Admin Term Controller
 *
 * @package    Gleez\Controller\Admin
 * @author     Gleez Team
 * @version    1.1.0
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Admin_Term extends Controller_Admin {

	/**
	 * The before() method is called before controller action
	 *
	 * @uses  ACL::required
	 */
	public function before()
	{
		ACL::required('administer terms');

		parent::before();
	}

	/**
	 * List of terms for vocabulary
	 *
	 * @uses  Message::error
	 * @uses  Message::info
	 * @uses  Request::redirect
	 * @uses  Route::get
	 * @uses  Route::uri
	 * @uses  DB::select
	 */
	public function action_list()
	{
		$id    = (int) $this->request->param('id', 0);
		$vocab = ORM::factory('term', array('id' => $id, 'lft' => 1));

		if ( ! $vocab->loaded())
		{
			Log::error('Attempt to access non-existent category group.');
			Message::error(__("Category Group doesn't exists!"));

			$this->request->redirect(Route::get('admin/taxonomy')->uri(), 404);
		}

		$this->title = __('Categories of Group %vocab', array('%vocab' => $vocab->name));
		$params = array('action' => 'add', 'id' => $id);

		$view = View::factory('admin/term/list')
					->bind('terms',  $terms)
					->bind('id',     $id)
					->bind('params', $params);

		$terms = DB::select()->from('terms')
					->where('lft', '>', $vocab->lft)
					->where('rgt', '<', $vocab->rgt)
					->where('scp', '=', $vocab->scp)
					->order_by('lft', 'ASC')
					->execute()
					->as_array();

		if (count($terms) == 0)
		{
			Message::info(__('There are no Categories that have been created for %vocab.', array('%vocab' => $vocab->name)));

			$view = View::factory('admin/term/none')->bind('params', $params);
		}

		$this->response->body($view);
		Assets::tabledrag();
	}

	/**
	 * Add Term for vocabulary
	 *
	 * @uses  Message::error
	 * @uses  Message::info
	 * @uses  Request::redirect
	 * @uses  Route::get
	 * @uses  Route::uri
	 * @uses  Arr::get
	 */
	public function action_add()
	{
		$id    = (int) $this->request->param('id', 0);
		/** @var $vocab Model_Term */
		$vocab = ORM::factory('term', array('id' => $id, 'lft' => 1));

		if ( ! $vocab->loaded())
		{
			Log::error('Attempt to access non-existent category group.');
			Message::error(__("Category Group doesn't exists!"));

			$this->request->redirect(Route::get('admin/taxonomy')->uri());
		}

		$this->title = __('Add Category for %vocab', array('%vocab' => $vocab->name));

		$terms = $vocab->select_list('id', 'name', '--');
		$action = Route::get('admin/term')->uri(array('action' =>'add', 'id' => $vocab->id));
		$allowed_types = Config::get('media.supported_image_formats', array('jpg', 'png', 'gif'));

		$view = View::factory('admin/term/form')
					->bind('vocab',  $vocab)
					->bind('post',   $post)
					->set('action',  $action)
					->set('terms',   $terms)
					->set('path',    FALSE)
					->bind('errors', $this->_errors)
					->set('allowed_types', $allowed_types);

		/** @var $post Model_Term */
		$post = ORM::factory('term')->values(Arr::merge($this->request->post(), $_FILES));

		if ($this->valid_post('term'))
		{
			try
			{
				$post->type = $vocab->type;
				$post->create_at($id, Arr::get($_POST, 'parent', 'last'));

				Message::success(__('Category %name saved successful!', array('%name' => $post->name)));

				$this->request->redirect(Route::get('admin/term')->uri(array('action' => 'list', 'id' => $vocab->id)), 200);
			}
			catch (ORM_Validation_Exception $e)
			{
				$this->_errors = $e->errors('models', TRUE);
			}
		}

		$this->response->body($view);
	}

	/**
	 * Edit Term for vocabulary
	 *
	 * @uses  Message::error
	 * @uses  Message::success
	 * @uses  Request::redirect
	 * @uses  Request::uri
	 * @uses  Route::get
	 * @uses  Route::uri
	 */
	public function action_edit()
	{
		$id   = (int) $this->request->param('id', 0);
		/** @var $term Model_Term */
		$term = ORM::factory('term', $id);

		if ( ! $term->loaded())
		{
			Log::error('Attempt to access non-existent category.');
			Message::error(__("Category doesn't exists!"));

			$this->request->redirect(Route::get('admin/taxonomy')->uri());
		}

		$this->title = __('Edit Category %name', array('%name' => $term->name));

		$action = Route::get('admin/term')->uri(array('action' =>'edit', 'id' => $term->id));
		$terms = $term->select_list('id', 'name', '--');
		$allowed_types = Config::get('media.supported_image_formats', array('jpg', 'png', 'gif'));

		$view = View::factory('admin/term/form')
				->bind('vocab',  $term)
				->bind('post',   $term)
				->bind('errors', $this->_errors)
				->set('terms',   $terms)
				->set('path',    $term->url)
				->set('action',  $action)
				->set('allowed_types', $allowed_types);


		if ($this->valid_post('term'))
		{

			try
			{
				$post = Arr::merge($this->request->post(), $_FILES);
				$term->values($post)->save();

				Message::success(__('Category %name saved successful!', array('%name' => $term->name)));

				// Redirect to listing
				$this->request->redirect(Route::get('admin/term')->uri( array('id'=> $term->root())));
			}
			catch (ORM_Validation_Exception $e)
			{
				$this->_errors = $e->errors('models', TRUE);
			}
		}

		$this->response->body($view);
	}

	/**
	 * Deleting terms
	 *
	 * @uses  Message::error
	 * @uses  Message::success
	 * @uses  Request::redirect
	 * @uses  Request::uri
	 * @uses  Route::get
	 * @uses  Route::uri
	 */
	public function action_delete()
	{
		$id   = (int) $this->request->param('id', 0);
		$term = ORM::factory('term', $id);

		if ( ! $term->loaded())
		{
			Log::error('Attempt to access non-existent category.');
			Message::error(__("Category doesn't exists!"));

			$this->request->redirect(Route::get('admin/taxonomy')->uri(), 404);
		}

		$action = Route::get('admin/term')->uri(array('action' =>'delete', 'id' => $term->id));

		$this->title = __('Deleting Category %name', array('%name' => $term->name));
		$view = View::factory('form/confirm')
					->set('title', $term->name)
					->set('action', $action);

		// If deletion is not desired, redirect to list
		if (isset( $_POST['no']) AND $this->valid_post())
		{
			$this->request->redirect(Route::get('admin/taxonomy')->uri());
		}

		// If deletion is confirmed
		if (isset($_POST['yes']) AND $this->valid_post())
		{
			try
			{
				$name = $term->name;
				$term->delete();

				Log::info('Category :name deleted successful.', array(':name' => $name));
				Message::success(__('Category %name deleted successful!', array('%name' => $name)));

				$this->request->redirect(Route::get('admin/taxonomy')->uri(array('action' =>'list')));
			}
			catch (Exception $e)
			{
				Log::error('Error occurred deleting category id: :id, :msg',
					array(':id' => $term->id, ':msg' => $e->getMessage())
				);
				Message::error(__('An error occurred deleting category %term.', array('%term' => $term->name)));

				$this->request->redirect(Route::get('admin/term')->uri(array('action' =>'list', 'id' => $term->id )), 500);
			}
		}

		$this->response->body($view);
	}

	/**
	 * Confirm form
	 *
	 * @todo Fix me!
	 */
	public function action_confirm()
	{
		$id = $this->request->param('id', NULL);

		if ($this->valid_post('term-list') AND ! is_null($id))
		{
			$updated_items = array();

			foreach ($_POST as $mlid => $val)
			{
				if (isset($_POST[$mlid]['tid']) AND is_array($_POST[$mlid]) )
				{
					$updated_items[$val['tid']] = $_POST[$mlid];
				}

			}

			$this->tree = array();
			$this->counter = 1;
			$this->level_zero = 1;

			$this->calculate_mptt($this->generate_tree($updated_items));
			unset($updated_items);

			if ($this->level_zero > 1)
			{
				Log::error('Category order could not be saved.');
				Message::error(__('Order of the categories could not be saved.'));

				$this->request->redirect(
					Route::get('admin/term')->uri( array( 'action'=>'list', 'id' => $id ) )
				);
			}

			try
			{
				foreach($this->tree as $node)
				{
					DB::update('terms')
						->set(array(
								'pid' => $node['pid'],
								'lvl' => $node['lvl'],
								'lft' => $node['lft'],
								'rgt' => $node['rgt'])
						)
						->where('id', '=', $node['id'])
						->execute();
				}

				Message::success(__('Order of the categories has been saved.'));
			}
			catch(Exception $e)
			{
				Message::error(__('Order of the categories could not be saved.'));
			}

			$this->request->redirect(Route::get('admin/term')->uri(array('action'=>'list', 'id' => $id )));
		}

	}

	/*
	 * Private function to generate the tree with parent child relationship for bulk update
	 *
	 * @param  array  $tree
	 *
	 * @todo Move to Model
	 */
	private function generate_tree($tree)
	{
		$menu = array();
		$ref = array();

		foreach ($tree as $d)
		{
			$d['children'] = array();

			if (isset($ref[$d['pid']]))
			{
				// we have a reference on its parent
				$ref[$d['pid']]['children'][$d['tid']] = $d;
				$ref[$d['tid']] =& $ref[ $d['pid']]['children'][$d['tid']];
			}
			else
			{
				// we don't have a reference on its parent => put it a root level
				$menu[$d['tid']] = $d;
				$ref[$d['tid']] =& $menu[$d['tid']];
			}
		}

		return $menu;
	}

	/*
	 * Private function to calculate and generate the new ordered left,
	 * right and level values for bulk update
	 *
	 * @param  array    $tree
	 * @param  integer  $parent [Optional]
	 * @param  array    $level [Optional]
	 *
	 * @todo Move to Model
	 */
	private function calculate_mptt($tree, $parent = 0, $level = 2)
	{
		foreach ($tree as $id => $val)
		{
			$left = ++$this->counter;

			if ( ! empty($val['children']))
			{
				$this->calculate_mptt($val['children'], $id, $level+1);
			}

			$right = ++$this->counter;

			if ($level === 1)
			{
				$this->level_zero++;
			}

			$this->tree[] = array(
				'id'  => $id,
				'pid' => (int) $val['pid'],
				'lvl' => $level,
				'lft' => $left,
				'rgt' => $right
			);
		}
	}

}
