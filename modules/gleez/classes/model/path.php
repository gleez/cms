<?php
/**
 * An adaptation of handle path aliasing
 *
 * @package    Gleez\ORM\Path
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Model_Path extends ORM {

	/**
	 * Table columns
	 * @var array
	 */
	protected $_table_columns = array(
		'id'               => array( 'type' => 'int' ),
		'source'           => array( 'type' => 'string' ),
		'alias'            => array( 'type' => 'string' ),
		'lang'             => array( 'type' => 'string' ),
		'route_name' 	   => array( 'type' => 'string' ),
		'route_directory'  => array( 'type' => 'string' ),
		'route_controller' => array( 'type' => 'string' ),
		'route_action'     => array( 'type' => 'string' ),
		'route_id'         => array( 'type' => 'string' ),
	);

	/**
	 * Ignored columns
	 * @var array
	 */
	protected $_ignored_columns = array(
		'type',
		'action'
	);

 	/**
	 * The language code used when no language is explicitly assigned.
	 * Defined by ISO639-2 for "Undetermined".
	 * @link  http://www.loc.gov/standards/iso639-2/php/code_list.php
	 * @var string
	 */
	protected $_language_none = 'und';

	/**
	 * Labels for fields in this model
	 *
	 * @return  array  Array of labels
	 */
	public function labels()
	{
		return array(
			'source' => __('URL Path'),
			'alias'  => __('Alias'),
			'lang'   => __('Language'),
		);
	}

	/**
	 * Rules for the post model
	 *
	 * @return  array  Rules
	 */
	public function rules()
	{
		return array(
			'source' => array(
				array('not_empty'),
			),
			'alias' => array(
				array('not_empty'),
				array(array($this, 'process_alias'), array(':validation', ':field')),
			),
			'lang' => array(
				array('min_length', array(':value', 2)),
				array('max_length', array(':value', 3)),
				array('regex', array(':value', '/^[a-z]{2,3}/')),
			),
		);
	}

	/**
	 * Check by triggering error if process
	 *
	 * Validation callback.
	 *
	 * @param   Validation  $validation  Object for validation
	 * @param   string      $field       Field name
	 * @return  void
	 *
	 * @uses    Module::event
	 */
	public function process_alias(Validation $validation, $field)
	{
		// always set unique alias if its set
		$alias  = $this->_unique_slug(trim($this->alias));

		// make sure only one alias exists for home page <front>
		if ($this->alias === Path::FRONT_ALIAS)
		{
			$alias = Path::FRONT_ALIAS;
		}

		$this->type = empty($this->type) ? NULL : $this->type;

		// allow other modules to interact with alias
		Module::event('path_aliases', $this);

		$source     = trim($this->source, '/');
		$this->lang = empty($this->lang) ? $this->_language_none : $this->lang;

		if ($params = $this->_process_uri($source))
		{
			if (isset($params['directory']))
			{
				$this->route_directory  = $params['directory'];
			}
			if (isset($params['controller']))
			{
				$this->route_controller = $params['controller'];
			}
			if (isset($params['action']))
			{
				$this->route_action = $params['action'];
			}
			if (isset($params['id']))
			{
				$this->route_id  = $params['id'];
			}
			if (isset($params['route']))
			{
				$this->route_name  = $params['route'];
			}
			$this->alias = $alias;
		}
		elseif ( ! isset($params['controller']) OR ! isset($params) OR empty($params['controller']) OR empty($params))
		{
			$validation->error($field, 'invalid_source', array($validation[$field]));
		}
	}

	private function _unique_slug($str)
	{
		$slug   = $str;
		$suffix = 0;
		
		while( $path = ORM::factory('path', array('alias' => $str) )
			AND $path->loaded()
			AND $path->source != $this->source
			)
		{
			$str = substr($slug, 0, 200 - (strlen($suffix) + 1)) . "-$suffix";
			$suffix++;
		}

		return $str;
	}

	/**
	 * Process URI
	 *
	 * @param   string  $uri  URI
	 * @return  array|boolean
	 * @uses    Route::all
	 */
	private function _process_uri($uri)
	{
		// Load routes
		$routes = Route::all();
		$params = NULL;

		foreach ($routes as $name => $route)
		{
			// We found something suitable
			if ($params = $route->matches($uri))
			{
				$params['route'] = (string)$name;
				return $params;
			}
		}

		return FALSE;
	}

	/**
	 * Reading data from inaccessible properties
	 *
	 * @param   string  $field
	 * @return  mixed
	 *
	 * @uses  Route::get
	 * @uses  Route::uri
	 */
	public function __get($field)
	{
		switch ($field)
		{
			case 'edit_url':
				return Route::get('admin/path')->uri(array('action' => 'edit', 'id' => $this->id));
			break;
			case 'delete_url':
				return Route::get('admin/path')->uri(array('action' => 'delete', 'id' => $this->id));
			break;
		}

		return parent::__get($field);
	}
}