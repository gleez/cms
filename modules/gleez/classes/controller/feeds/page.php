<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Feeds_Page extends Controller_Feeds_Base {
        
        public function action_index()
        {
                if ($this->items === NULL OR empty($this->items) )
                { // Cache is Empty so Re-Cache
                        $pages = ORM::factory('page')
                                        ->where('status', '=', 'publish')
                                        //->order_by('sticky', 'DESC')
					->order_by('pubdate', 'DESC')
					->limit($this->limit)
					->offset($this->offset)
                                        ->find_all();
                        
                        $items = array();
                        foreach($pages as $page)
                        {
                                $item = array();
                                $item['id'] = $page->id;
                                $item['title'] = $page->title;
                                $item['link'] = URL::site($page->url, TRUE);
                                $item['description'] = $page->teaser;
                                $item['pubDate'] = $page->pubdate;
                        
                                $items[] = $item;
                        }
                
                        $this->cache->set($this->cache_key, $items, DATE::HOUR); // 1 Hour
                        $this->items = $items;
                }
                
		if( isset($this->items[0]))
		{
			$this->info['title']   = __('Pages - Recent updates');
			$this->info['pubDate'] = $this->items[0]['pubDate'];
		}
                
        }

	public function action_term()
        {
		if ($this->items === NULL OR empty($this->items) )
                { 	// Cache is Empty so Re-Cache
			$id = (int) $this->request->param('id', 0);
			$term = ORM::factory('term')
					->where('id', '=', $id)
					->where('type', '=', 'page')
					->where('lvl', '!=', 1)
					->find();
		
			if( ! $term->loaded() )
			{
				Kohana::$log->add(LOG::ERROR, 'Attempt to access non-existent page term feed');
				throw new HTTP_Exception_404( __('Term ":term" Not Found'), array(':term'=>$id));
			}

			$posts = $term->posts
                                        ->where('status', '=', 'publish')
                                        //->order_by('sticky', 'DESC')
					->order_by('pubdate', 'DESC')
					->limit($this->limit)
					->offset($this->offset)
                                        ->find_all();
     
                        $items = array();
                        foreach($posts as $page)
                        {
                                $item = array();
                                $item['id'] = $page->id;
                                $item['title'] = $page->title;
                                $item['link'] = URL::site($page->url, TRUE);
                                $item['description'] = $page->teaser;
                                $item['pubDate'] = $page->pubdate;
                        
                                $items[] = $item;
                        }
                
			$items['title'] = $term->name;
                        $this->cache->set($this->cache_key, $items, 3600); // 1 Hour
                        $this->items = $items;
		}
	
		if( isset($this->items[0]))
		{
			$this->info['title']   = __(':term - Recent updates',
						    array(':term' => ucfirst($this->items['title']) ) );
			$this->info['pubDate'] = $this->items[0]['pubDate'];
		}
	}
	
	public function action_tag()
        {
		if ($this->items === NULL OR empty($this->items) )
                { 	// Cache is Empty so Re-Cache
			$id = (int) $this->request->param('id', 0);
			$tag = ORM::factory('tag', array('id' => $id, 'type' => 'page') );
		
			if( ! $tag->loaded() )
			{
				Kohana::$log->add(LOG::ERROR, 'Attempt to access non-existent page tag feed');
				throw new HTTP_Exception_404( __('Tag ":tag" Not Found'), array(':tag'=>$id));
			}

			$posts = $tag->posts
                                        ->where('status', '=', 'publish')
                                        //->order_by('sticky', 'DESC')
					->order_by('pubdate', 'DESC')
					->limit($this->limit)
					->offset($this->offset)
                                        ->find_all();
     
                        $items = array();
                        foreach($posts as $page)
                        {
                                $item = array();
                                $item['id'] = $page->id;
                                $item['title'] = $page->title;
                                $item['link'] = URL::site($page->url, TRUE);
                                $item['description'] = $page->teaser;
                                $item['pubDate'] = $page->pubdate;
                        
                                $items[] = $item;
                        }
                
			$items['title'] = $tag->name;
                        $this->cache->set($this->cache_key, $items, 3600); // 1 Hour
                        $this->items = $items;
		}
	
		if( isset($this->items[0]))
		{
			$this->info['title']   = __(':tag - Recent updates',
						    array(':tag' => ucfirst($this->items['title']) ) );
			$this->info['pubDate'] = $this->items[0]['pubDate'];
		}
	}
}