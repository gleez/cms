<?php
/**
 * Comments controller
 *
 * @package    Gleez\Controller
 * @author     Gleez Team
 * @version    1.0.1
 * @copyright  (c) 2011-20133 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Comments extends Controller {

	/**
	 * Supported return formats
	 * @var array
	 */
	protected $supported_formats = array(
		'.xhtml',
		'.json',
		'.xml',
		'.rss',
	);

	/**
	 * Comment model to use (based on group)
	 * @var string
	 */
	protected $model = 'comment';

	/**
	 * Pagination per-page setting (based on group)
	 * @var integer
	 */
	protected $per_page = 10;

	/**
	 * View folder (based on group)
	 * @var string
	 */
	protected $view = 'comment';

	/**
	 * Group name
	 * @var string
	 */
	protected $group = NULL;

	/**
	 * Config object
	 * @var Config
	 */
	protected $config = NULL;

	/**
	 * Perform format check
	 */
	public function before()
	{
		// Make sure request is an internal request
		if ($this->request === Request::initial())
		{
			Log::error('Attempt was made to access comments controller externally.');
			$this->request->redirect('');
		}

		// Get group settings
		$group  = $this->request->param('group');
		$config = Kohana::$config->load($group);

		$this->per_page = $config['comments_per_page'];
		$this->config   = $config;
		$this->group    = $group;

		parent::before();
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
			Log::info('Attempt to load all public comments without a defined parent.');
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

		if ($parent_id)
		{
			$posts->where('post_id', '=', $parent_id);

			$source = $this->group.'/'.$parent_id;
			$uri = Path::alias($source);
		}

		// Get total number of comments
		$total  = $posts->reset(FALSE)->count_all();

		// Check if there are any comments to display
		if ($total == 0)
		{
			// @todo
			return;
		}

		// Determine pagination offset
		$page   = $this->request->param('page', 1);
		$offset = ($page - 1) * $this->per_page;

		// Create pagination
		$pagination = Pagination::factory(array(
			'current_page'   => array('source'=>'cms', 'key'=>'page'),
			'total_items'    => $total,
			'items_per_page' => $this->per_page,
			'uri'            => $uri,
		));

		// Execute query
		$comments = $posts->order_by('created', 'ASC')
						->limit($this->per_page)
						->offset($offset)
						->find_all();

		// If no comments found (bad offset/page)
		if (count($comments) == 0)
		{
			Log::info('No comments found for state: :state, page: :page',
				array(':state' => $state, ':page' => $page)
			);
			return;
		}

		// Setup view with data
		$list = View::factory($this->view.'/list')
					->set('count',      $total)
					->set('pagination', $pagination)
					->set('comments',   $comments);

		// Set request response
		$this->response->body($list);
	}
}