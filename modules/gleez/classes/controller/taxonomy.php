<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Taxonomy Controller
 *
 * @package    Gleez\Controller
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Taxonomy extends Template {

	public function before()
	{
		// Internal request only!
		if ($this->request->is_initial())
		{
			throw new HTTP_Exception_404('Accessing an internal request <small>:type</small> externally',
				array(':type' => $this->request->uri())
			);
		}

		ACL::required('access content');
		parent::before();
	}

	public function action_term()
	{
		$id = (int) $this->request->param('id', 0);
		$term = ORM::factory('term', $id);

		if( ! $term->loaded() )
		{
			Kohana::$log->add(LOG::ERROR, 'Attempt to access non-existent term');
			throw new HTTP_Exception_404('Term ":term" Not Found', array(':term'=>$id));
		}

			$this->title    = __(':title', array(':title' => $term->name) );
			$view = View::factory('taxonomy/term')
					->set('teaser', TRUE)
					->bind('pagination', $pagination)
					->bind('posts', $posts);

			$posts = $term->posts;

		if( ! ACL::check('administer terms') AND !ACL::check('administer content'))
		{
			$posts->where('status', '=', 'publish');
		}

		$total = $posts->reset(FALSE)->count_all();

		if ($total == 0)
		{
			Kohana::$log->add(Log::INFO, 'No posts found');
			$this->response->body( View::factory('page/none') );
			return;
		}

		$pagination = Pagination::factory(array(
			'current_page'   => array('source'=>'cms', 'key'=>'page'),
			'total_items'    => $total,
			'items_per_page' => 5,
			'uri'		 => $term->url
		));

		$posts  = $posts->order_by('created', 'DESC')
				->limit($pagination->items_per_page)
				->offset($pagination->offset)
				->find_all();

		$this->response->body($view);

		//Set the canonical and shortlink for search engines
		if ( $this->auto_render === TRUE )
		{
			Meta::links(URL::canonical($term->url, $pagination), array('rel' => 'canonical'));
			Meta::links(Route::url('taxonomy', array('action' => 'term', 'id' => $term->id)), array('rel' => 'shortlink'));
		}
	}

}