<?php
/**
 * Tag Controller
 *
 * @package    Gleez\Controller
 * @author     Gleez Team
 * @version    1.0.2
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Tag extends Template {

	/**
	 * The before() method is called before controller action
	 *
	 * @uses    ACL::required
	 */
	public function before()
	{
		ACL::required('access content');

		parent::before();
	}

	public function action_list()
	{
		// @todo
		Log::error('Attempt to access disabled feature.');
	}

	/**
	 * List of pages (blogs/posts/etc.) with a specific tag
	 *
	 * @throws  HTTP_Exception_404
	 *
	 * @uses    Log::add
	 * @uses    Text::ucfirst
	 * @uses    ACL::check
	 * @uses    Meta::links
	 * @uses    URL::canonical
	 * @uses    Route::url
	 */
	public function action_view()
	{
		$id = (int) $this->request->param('id', 0);
		$tag = ORM::factory('tag', $id);

		if ( ! $tag->loaded())
		{
			throw HTTP_Exception::factory(404, 'Tag :tag not found!', array(':tag' => $id));
		}

		$this->title = __(':title', array(':title' => Text::ucfirst($tag->name)));

		$view = View::factory('tag/view')
				->set('teaser',      TRUE)
				->bind('pagination', $pagination)
				->bind('posts',      $posts);

		$posts = $tag->posts;

		if ( ! ACL::check('administer tags') AND ! ACL::check('administer content'))
		{
			$posts->where('status', '=', 'publish');
		}

		$total = $posts->reset(FALSE)->count_all();

		if ($total == 0)
		{
			Log::info('No posts found.');
			$this->response->body(View::factory('page/none'));
			return;
		}

		$pagination = Pagination::factory(array(
			'current_page'   => array('source'=>'cms', 'key'=>'page'),
			'total_items'    => $total,
			'items_per_page' => 15,
			'uri'            => $tag->url,
		));

		$posts = $posts->order_by('created', 'DESC')
					->limit($pagination->items_per_page)
					->offset($pagination->offset)
					->find_all();
                
		$this->response->body($view);

		// Set the canonical and shortlink for search engines
		if ($this->auto_render === TRUE)
		{
			Meta::links(URL::canonical($tag->url, $pagination), array('rel' => 'canonical'));
			Meta::links(Route::url('tag', array('action' => 'view', 'id' => $tag->id)), array('rel' => 'shortlink'));
		}
	}
}