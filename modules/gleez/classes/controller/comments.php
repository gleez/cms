<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Comment controller
 *
 * @package    Gleez
 * @category   Comment
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Controller_Comments extends Controller {

	// Supported return formats
	protected $supported_formats = array(
		'.xhtml',
		'.json',
		'.xml',
		'.rss',
	);

	// Comment model to use (based on group)
	protected $model = 'comment';

	// Pagination per-page setting (based on group)
	protected $per_page = 10;

	// View folder (based on group)
	protected $view = 'comment';

	// Group name
	protected $group = NULL;
	
	// Group config
	protected $config = NULL;
        
	/**
	 * Perform format check
	 */
	public function before()
        {
		// Make sure request is an internal request
		if ($this->request === Request::initial())
		{
			Kohana::$log->add(LOG::ERROR, 'Attempt was made to access comments controller externally');
			$this->request->redirect('');
		}

		// Test to ensure the format requested is supported
		//if ( ! in_array($this->request->param('format'), $this->supported_formats))
		//	throw new Kohana_Exception('File not found');

                // Get group settings
		$group = $this->request->param('group');
		$config = Kohana::$config->load($group);
		$this->per_page = $config['comments_per_page'];
		//$this->view     = $config['view'];
		$this->config   = $config;
		$this->group   = $group;

		return parent::before();
	}

	/**
	 * Retrieve public list of good comments
	 */
	public function action_public()
        {
		$id = $this->request->param('id', 0);

		// Comment must have a parent
		if ($id == 0)
		{
			Kohana::$log->add(LOG::INFO, 'Attempt to load all public comments without a defined parent');
			//$this->request->response = FALSE;
			return;
		}
		else
		{
			$this->create_list('publish', FALSE);
		}
	}
        
	/**
	 * List comments
	 */
	protected function create_list($state = 'publish', $admin = FALSE, $uri = '')
	{
                // Get parent id
                $parent_id = (int) $this->request->param('id', 0);
        
                $posts = ORM::factory('comment')->where('status', '=', $state);
	
                if ( $parent_id )
		{
			$posts->where('post_id', '=', $parent_id);
		
			$uri = $source = $this->group.'/'.$parent_id;
			$uri = Path::alias($source);
		}
        
                // Get total number of comments
                $total  = $posts->reset(FALSE)->count_all();
	
		// Check if there are any comments to display
		if ($total == 0) return;

                // Determine pagination offset
		$page   = $this->request->param('page', 1);
		$offset = ($page - 1) * $this->per_page;
	
		// Create pagination
		$pagination = Pagination::factory(array(
			'current_page'   => array('source'=>'cms', 'key'=>'page'),
			'total_items'    => $total,
			'items_per_page' => $this->per_page,
			'uri'		 => $uri,
		));
        
                // Execute query               
                $comments = $posts->order_by('created', 'ASC')->limit($this->per_page)->offset($offset)->find_all();
        
                // If no comments found (bad offset/page)
		if (count($comments) == 0)
		{
			Kohana::$log->add(LOG::INFO, 'No comments found for state='.$state.', page='.$page);
			//$this->response->body = FALSE;
			return;
		}

                /*// Setup admin view
		$admin_view = View::factory($this->view.'/admin')
			->set('is_ham', ($state == 'ham'))
			->set('is_spam', ($state == 'spam'));
                */
        
		// Setup view with data
		$list = View::factory($this->view.'/list')
                                ->set('count', $total)
                                ->set('pagination', $pagination)
                                ->set('comments', $comments);

		// Set request response
		$this->response->body( $list );
	}
        
}