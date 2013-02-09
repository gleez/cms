<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Feeds_Base extends Controller {
        
        protected $page_size = 30;
        
        protected $page;
        
        protected $limit;
        
        protected $offset;
        
        protected $id;
        
        protected $cache;
        
        protected $cache_key;
        
        protected $items = array();
        
        protected $info;
        
        public function before()
        {
                // Start at which page?
                $this->page = (int) Arr::get($_GET, 'p', 1);
        
                // How Many Items Should We Retrieve? Configurable page size between 1 and 200, default 25
                $this->limit = max(1, min(200, (int) Arr::get($_GET, 'l', $this->page_size) ) );
        
                $this->id     = (int) $this->request->param('id', 0); // ex: term id/ tag id
                $this->offset = ($this->page - 1) * $this->limit;
        
                $this->site_url = URL::site(null, TRUE);
        
                //inisiate cache
                $this->cache = Cache::instance('feeds');
                $this->cache_key = "feed-{$this->request->controller()}-{$this->request->action()}-{$this->limit}-{$this->page}-{$this->id}";
                $this->items = $this->cache->get($this->cache_key, array());

                $config = Kohana::$config->load('site');
                $this->info = array(
                        'title'       => $config->get('site_name', 'Gleez'),
                        'description' => $config->get('site_mission', __('Recently added posts') ),
                        'pubDate'     => time(),
                        'generator'   => 'GleezCMS (http://gleezcms.org)',
                        'link'        => URL::site(NULL, TRUE),
                        'copyright'   => date('Y') . ' ' . $config->get('site_name', 'Gleez'),
                        'language'    => substr(i18n::lang(), 0, 2),
			'image'	      => array(
					       'link'  => URL::site(NULL, TRUE),
					       'url'   => URL::site('/media/images/logo.png', TRUE),
					       'title' => $config->get('site_name', 'Gleez')
					       ),
                );
       
                parent::before(); 
        }
        
        public function action_index()
        {
                if ($this->items === NULL OR empty($this->items) )
                { 	// Cache is Empty so Re-Cache
                        $posts = DB::select( array('p.id', 'id'), 'p.title', 'p.format', 'p.type',
                                            array('p.teaser', 'description'),
                                            array('p.pubdate', 'pubDate'),
                                            array('a.alias', 'link')
                                            )
                                        ->from(array('posts', 'p'))
                                        ->join(array('paths', 'a'), 'LEFT')
                                                ->on('a.route_controller', '=', 'p.type')
                                                ->on('a.route_id', '=', 'p.id')
                                                ->join_and('a.route_action', '=', "index")
                                        ->where('p.type', '!=', 'post')
                                        ->where('p.status', '=', 'publish')
                                        ->where('p.promote', '=', 1)
                                        ->order_by('pubdate', 'DESC')
                                        ->limit($this->limit)
                                        ->offset($this->offset)
                                        ->execute()
                                        ->as_array();
                
                        // Encode HTML special characters in the description. and make link absolute
                        for ($i = 0, $n = count($posts); $i < $n; $i++)
                        {
                                $link = is_null($posts[$i]['link']) ? $posts[$i]['type'].'/'.$posts[$i]['id'] : $posts[$i]['link'];
                                $posts[$i]['description'] = Text::markup( $posts[$i]['description'], $posts[$i]['format'] );
                                $posts[$i]['link']        = URL::site($link, TRUE);
                                unset($posts[$i]['format'], $link );
                        }
		
                        $this->cache->set($this->cache_key, $posts, DATE::DAY); // 1 Hour
                        $this->items = $posts;
                }
                if( isset($this->items[0]))
		{
			$this->info['pubDate'] = $this->items[0]['pubDate'];
		}
        }

        public function action_view()
        {
		$id = (int) $this->request->param('id', 0);
                if ($this->items === NULL OR empty($this->items) )
                {
			// Cache is Empty so Re-Cache
                        $post = DB::select( array('p.id', 'id'), 'p.title', 'p.format', 'p.type',
                                            array('p.teaser', 'description'),
                                            array('p.pubdate', 'pubDate'),
                                            array('a.alias', 'link')
                                            )
                                        ->from(array('posts', 'p'))
                                        ->join(array('paths', 'a'), 'LEFT')
                                                ->on('a.route_controller', '=', 'p.type')
                                                ->on('a.route_id', '=', 'p.id')
                                                ->join_and('a.route_action', '=', "index")
                                        ->where('p.id', '=', $id)
                                        ->where('p.status', '=', 'publish')
                                        ->execute()
                                        ->as_array();
		
			if( isset($post[0]) )
			{
				// Encode HTML special characters in the description. and make link absolute
				$link = is_null($post[0]['link']) ? $post[0]['type'].'/'.$post[0]['id'] : $post[0]['link'];
				$post[0]['description'] = Text::markup( $post[0]['description'], $post[0]['format'] );
				$post[0]['link']        = URL::site($link, TRUE);
				unset($post[0]['format'], $link );

				$this->items = array($post[0]);
				$this->cache->set($this->cache_key, $this->items, DATE::HOUR); // 1 Hour
			}	
			else
			{
				// Return a 404 status
				//$this->response->status(404);
				$this->items = array();
			}
                }
                if( isset($this->items[0]))
		{
			$this->info['pubDate'] = $this->items[0]['pubDate'];
		}
        }
	
        public function after()
        {
                parent::after();
        
                if( isset($this->items['title'])) unset($this->items['title']);
                echo Feed::create($this->info, $this->items);
        }
        
}