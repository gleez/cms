<?php
/**
 * Admin Taxonomy Controller
 *
 * @package    Gleez\Controller\Admin
 * @author     Gleez Team
 * @version    1.0.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Admin_Taxonomy extends Controller_Admin {

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
	 * Lists Vocabularies
	 *
	 * @uses  Assets::popup
	 */
	public function action_list()
	{
		Assets::popup();

		$is_datatables = Request::is_datatables();
		$terms  = ORM::factory('term')->where('lft', '=', 1);

		if ($is_datatables)
		{
			$this->_datatables = $terms->dataTables(array('name', 'description'));

			foreach ($this->_datatables->result() as $term)
			{
				$this->_datatables->add_row(
					array(
						Text::plain($term->name).'<div class="description">'.Text::plain($term->description).'</div>',
						HTML::icon(Route::get('admin/term')->uri(array('action' => 'list', 'id' => $term->id)), 'icon-th-list', array('class'=>'action-list', 'title'=> __('List Terms'))),
						HTML::icon(Route::get('admin/term')->uri(array('action' => 'add', 'id' => $term->id)), 'icon-plus', array('class'=>'action-add', 'title'=> __('Add Term'))),
						HTML::icon(Route::get('admin/taxonomy')->uri(array('action' => 'edit', 'id' => $term->id)), 'icon-edit', array('class'=>'action-edit', 'title'=> __('Edit Vocab'))),
						HTML::icon(Route::get('admin/taxonomy')->uri(array('action' => 'delete', 'id' => $term->id)), 'icon-trash', array('class'=>'action-delete', 'title'=> __('Delete Vocab'), 'data-toggle' => 'popup', 'data-table' => '#admin-list-vocabs'))
					)
				);
			}
		}

		$this->title = __('Vocabulary');
		$add_url     = Route::get('admin/taxonomy')->uri(array('action' =>'add'));
		$url         = Route::url('admin/taxonomy', array('action' => 'list'), TRUE);

		$view = View::factory('admin/taxonomy/list')
				->bind('datatables',   $this->_datatables)
				->set('is_datatables', $is_datatables)
				->set('add_url',       $add_url)
				->set('url',           $url);

		$this->response->body($view);
	}

	public function action_add()
	{
		$this->title = __('Add Vocab');
		$view = View::factory('admin/taxonomy/form')->bind('post', $post)->bind('errors', $this->_errors);
		$post = ORM::factory('term');

		if ($this->valid_post('vocab'))
		{
			$post->values($_POST);
			try
			{
				$post->make_root();

				Message::success(__('Vocab: %name saved successful!', array('%name' => $post->name)));

				// Redirect to listing
				$this->request->redirect(Route::get('admin/taxonomy')->uri());
			}
			catch (ORM_Validation_Exception $e)
			{
				$this->_errors = $e->errors('models', TRUE);
			}
		}

		$this->response->body($view);
	}

	public function action_edit()
	{
		$id = (int) $this->request->param('id', 0);
		$post = ORM::factory('term', $id);

		if ( ! $post->loaded())
		{
			Message::error(__('Vocab: doesn\'t exists!'));
			Log::error('Attempt to access non-existent Vocab.');

			$this->request->redirect(Route::get('admin/taxonomy')->uri());
		}

		$this->title = __( 'Edit Vocab: :name', array(':name' => $post->name) );
		$view = View::factory('admin/taxonomy/form')->bind('post', $post)->bind('errors', $this->_errors);

		if ($this->valid_post('vocab'))
		{
			$post->values($_POST);
			try
			{
				$post->save();

				Message::success(__('Vocab: %name saved successful!', array('%name' => $post->name)));

				// Redirect to listing
				$this->request->redirect( Route::get('admin/taxonomy')->uri() );
			}
			catch (ORM_Validation_Exception $e)
			{
				$this->_errors = $e->errors('models', TRUE);
			}
		}

		$this->response->body($view);
	}

	public function action_delete()
	{
		$id = (int) $this->request->param('id', 0);
		$term = ORM::factory('term', $id);

		if ( ! $term->loaded())
		{
			Message::error(__('Taxonomy: doesn\'t exists!'));
			Log::error('Attempt to access non-existent taxonomy.');

			$this->request->redirect(Route::get('admin/taxonomy')->uri(array('action' => 'list')));
		}

		$this->title = __('Delete Taxonomy :title', array(':title' => $term->name ));

		$view = View::factory('form/confirm')
				->set('action', Route::url('admin/taxonomy', array('action' => 'delete', 'id' => $term->id) ))
				->set('title', $term->name);

		// If deletion is not desired, redirect to list
		if ( isset($_POST['no']) AND $this->valid_post() )
		{
			$this->request->redirect(Route::get('admin/taxonomy')->uri());
		}

		// If deletion is confirmed
		if ( isset($_POST['yes']) AND $this->valid_post() )
		{
			try
			{
				$term->delete();
				Message::success(__('Taxonomy: :name deleted successful!', array(':name' => $term->name)));

				$this->request->redirect(Route::get('admin/taxonomy')->uri(array('action' => 'list')));
			}
			catch (Exception $e)
			{
				Log::error('Error occurred deleting taxonomy id: :id, :message',
					array(':id' => $term->id, ':message' => $e->getMessage())
				);
				Message::error(__('An error occurred deleting taxonomy, :term.',array(':term' => $term->name)));
				$this->_errors = array(__('An error occurred deleting taxonomy, :term.',array(':term' => $term->name)));

				$this->request->redirect(Route::get('admin/taxonomy')->uri(array('action' => 'list')));
			}
		}

		$this->response->body($view);
	}

}
