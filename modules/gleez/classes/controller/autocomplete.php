<?php
/**
 * Autocomplete Controller
 *
 * @package    Gleez\Controller
 * @author     Gleez Team
 * @version    1.0.2
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_AutoComplete extends Controller {

	public function before()
	{
		// Ajax request only!
		if ( ! $this->request->is_ajax())
		{
			throw HTTP_Exception::factory(404, 'Accessing an ajax request :type externally',
				array(':type' => '<small>'.$this->request->uri().'</small>'));
		}

		ACL::required('access content');
		parent::before();
	}

	/**
	 * Retrieve a JSON object containing autocomplete suggestions for existing users.
	 */
	public function action_user()
	{
		$string  = $this->request->param('string', FALSE);
		$matches = array();

		if ($string)
		{
			$result  = DB::select('name')
				->from('users')
				->where('name', 'LIKE', $string.'%')
				->limit('10')
				->execute();

			foreach ($result as $user)
			{
				$matches[$user['name']] = Text::plain($user['name']);
			}
		}

		$this->response->body( JSON::encode( $matches ) );
	}

	/**
	 * Retrieve a JSON object containing autocomplete suggestions for existing users.
	 */
	public function action_nick()
	{
		$string = $this->request->param('string', FALSE);
		$matches = array();

		if ($string)
		{
			$result  = DB::select('name')
				->from('users')
				->where('nick', 'LIKE', $string.'%')
				->limit('10')
				->execute();

			foreach ($result as $user)
			{
				$matches[$user['name']] = Text::plain($user['name']);
			}
		}

		$this->response->body( JSON::encode( $matches ) );
	}

	/**
	 * Retrieve a JSON object containing autocomplete suggestions for existing users.
	 */
	public function action_tag()
	{
		$string = $this->request->param('string', FALSE);
		$type   = $this->request->param('type', 'blog');

		// The user enters a comma-separated list of tags. We only autocomplete the last tag.
		$tags_typed = Tags::explode($string);
		$tag_last   = UTF8::strtolower(array_pop($tags_typed));
		$matches    = array();

		if ( ! empty($tag_last))
		{
			$query  = DB::select('name')->from('tags')
				->where('name', 'LIKE', $tag_last.'%')
				->where('type', '=', $type);

			// Do not select already entered terms.
			if ( ! empty($tags_typed))
			{
				$query->where('name', 'NOT IN', $tags_typed);
			}

			$result = $query->limit('10')->execute();

			$prefix = count($tags_typed) ? implode(', ', $tags_typed) . ', ' : '';

			foreach ($result as $tag)
			{
				$n = $tag['name'];
				// Tag names containing commas or quotes must be wrapped in quotes.
				if (strpos($tag['name'], ',') !== FALSE OR strpos($tag['name'], '"') !== FALSE)
				{
					$n = '"' . str_replace('"', '""', $tag['name']) . '"';
				}
				else
				{
					$matches[$prefix . $n] = Text::plain($tag['name']);
				}
			}
		}

		$this->response->body(JSON::encode($matches));
	}

	public function after()
	{
		if ($this->request->is_ajax())
		{
			$this->response->headers('content-type',  'application/json; charset='.Kohana::$charset);
		}

		parent::after();
	}

}
