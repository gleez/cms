<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * An adaptation of handle path aliasing.
 *
 * @package    Gleez
 * @category   Path
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Model_Path extends ORM {       

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
	
	protected $_ignored_columns = array('type', 'action');
	
        /**
         * The language code used when no language is explicitly assigned.
         *
         * Defined by ISO639-2 for "Undetermined".
         *
         * @access  protected
	 * @var     string  language
         */
        protected $_language_none = 'und';

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
				array('regex', array(':value', '/^[a-z]{2}/')),
			),
                        'route_controller' => array(
				//array('not_empty'),
			),
		);
	}

	/**
	 * Check by triggering error if process.
	 * Validation callback.
	 *
	 * @param   Validation  Validation object
	 * @param   string      Field name
	 * @return  void
	 */
	public function process_alias(Validation $validation, $field)
	{
		//always set unique alias if its set
		$alias  = $this->_unique_slug( trim($this->alias) );
	
		//make sure only one alias exists for home page <front>
		if($this->alias === '<front>') $alias = '<front>';
	
		$this->type   = empty($this->type) ? NULL : $this->type;
	
		//allow other modules to interact with alias
		Module::event('path_aliases', $this);
	
		$source = trim($this->source, '/');
		$this->lang   = empty($this->lang) ? $this->_language_none : $this->lang;
	
		if( $params = $this->_process_uri($source) )
                {
			if(isset($params['directory']))
				$this->route_directory  = $params['directory'];
			
			if(isset($params['controller']))
				$this->route_controller = $params['controller'];
			
			if(isset($params['action']))
				$this->route_action = $params['action'];
			
			if(isset($params['id']))
				$this->route_id  = $params['id'];
			
			if(isset($params['route']))
				$this->route_name  = $params['route'];
			
			$this->alias = $alias;
                }
		elseif( !isset($params['controller']) OR !isset($params) OR empty($params['controller']) OR empty($params) )
		{
			$validation->error($field, 'invalid_source', array($validation[$field]));
		}
	}

	private function _unique_slug($str)
	{
		$slug = $str;
		$suffix = 0;

		while( $path = ORM::factory('path', array('alias' => $str) )
			AND $path->loaded()
			AND $path->source != $this->source
		      )
		{
			$str = substr( $slug, 0, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
			$suffix++;
		}
	
		return $str;
	}
	
	/**
	 * Process URI
	 *
	 * @param   string  $uri     URI
	 * @return  array
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
	
}