<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Autocomplete extends Controller {

	public function before()
	{
		// Ajax request only!
		if ( !$this->request->is_ajax() )
		{
			throw new HTTP_Exception_404('Accessing an ajax request <small>:type</small> externally', array(
                                     ':type' => $this->request->uri(),
                              ));
		}
		
		parent::before();
	}
	
	/**
	* Retrieve a JSON object containing autocomplete suggestions for existing aliases.
	*/
        public function action_links()
        {
                ACL::Required('administer menu');
                
                $string = $this->request->param('string', FALSE);
        
                $matches = array();
                if ($string)
                {
                        $result  = DB::select('alias')->from('paths')->where('alias', 'LIKE', $string.'%')
                                        ->limit('10')->execute();
                        
                        foreach ($result as $link)
                        {
                                $matches[$link['alias']] = Text::plain($link['alias']);
                        }
                }

		$this->response->body( JSON::encode( $matches ) );
        }
	
	public function after()
	{
                if ( $this->request->is_ajax() )
		{
                        $this->response->headers('content-type',  'application/json; charset='.Kohana::$charset);
                }
        
		parent::after();
	}

}