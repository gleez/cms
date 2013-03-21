<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Core Menu Class
 *
 * This class can be used to easily build out a menu in the form
 * of an unordered list. You can add any attributes you'd like to
 * the list, and each list item has special classes to help you style it.
 *
 * @package		Gleez
 * @category	Menu
 * @author		Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2013 Gleez Technologies
 * @license		http://gleezcms.org/license
 */
class Gleez_Menu {

	// Associative array of list items
	protected $_items = array();
	
	// Associative array of attributes for list
	protected $_attrs = array();
	
	// Current URI
	protected $_current;
        
        /**
	 * Creates and returns a new menu object
	 *
	 * @chainable
	 * @param   array   Array of list items (instead of using add() method)
	 * @return  Menu
	 */
	public static function factory(array $items = NULL)
	{
		return new Menu($items);
	}
        
        /**
	 * Constructor, globally sets $items array
	 *
	 * @param   array   Array of list items (instead of using add() method)
	 * @return  void
	 */
	public function __construct( array $items = NULL )
	{
		$this->_items   = $items;
		//$this->_current = trim(URL::site(Request::current()->uri()), '/');
	}
       
        /**
	 * Add's a new list item to the menu. if parent_id is passed will add as child
	 *
	 * @chainable
	 * @param   string   Unique id
	 * @param   string   Title of link
	 * @param   string   URL (address) of link
	 * @param   string   Additional text of link
	 * @param   array    Params of the item to handle logic
	 * @param   string   Parent Id of the link
	 * @param   Menu     Instance of class that contain children
	 * @return  Menu
	 */
	public function add($id, $title, $url, $descp = FALSE, array $params = NULL, $image = NULL, $parent_id = FALSE, Menu $children = NULL)
	{
		if( $parent_id )
		{
			$this->_items = self::_add_child($parent_id, $this->_items, $id, $title, $url, $descp, $params, $image, $children);
		}
		else
		{
			$this->_items[$id] = array
			(
				'title'    => $title,
				'url'      => $url,
				'children' => ($children instanceof Menu) ? $children->get_items() : NULL,
				'access'   => TRUE,
				'descp'	   => $descp,
				'params'   => $params,
				'image'    => $image
			);
		}
	
		return $this;
	}

	/**
	 * Remove an item from the menu
	 *
	 * @param   string   Id of link
	 * @param   string   Parent Id of link
	 * @return  void
	 */
	public function remove($target_id, $parent_id = FALSE)
	{
		if( $parent_id )
		{
			$this->_items = self::_remove_child($target_id, $this->_items);
		}
		else if ( isset( $this->_items[$target_id] ) )
		{
			unset($this->_items[$target_id]);
		}
	
		return $this;
	}

	/**
	 * Change an item title of this menu
	 *
	 * @param   string   $target_id  Id of link item
	 * @param   string   $title      New Title for the item
	 * @param   booleen  $parent_id  true/false
	 * @return  void
	 */
	public function set_title($target_id, $title, $parent_id = FALSE)
	{
		if ( $parent_id )
		{
			$this->_items = self::_change_title_url($target_id, $this->_items, $title);
		}
		else if ( isset( $this->_items[$target_id] ) )
		{
			$this->_items[$target_id]['title'] = (string)$title;
		}
	
		return $this;
	}

	/**
	 * Change an item url of this menu
	 *
	 * @param   string   $target_id  Id of link
	 * @param   string   $url      	 New url of the item
	 * @param   booleen  $parent_id  true/false
	 * @return  void
	 */
	public function set_url($target_id, $url, $parent_id = FALSE)
	{
		if ( $parent_id )
		{
			$this->_items = self::_change_title_url($target_id, $this->_items, $url, 'url');
		}
		else if ( isset( $this->_items[$target_id] ) )
		{
			$this->_items[$target_id]['url'] = (string)$url;
		}
	
		return $this;
	}
	
	/**
	 * Renders the HTML output for the menu
	 *
	 * @param   array   $attrs  Associative array of html attributes
	 * @param   array   $items  The parent item's array, only used internally
	 * 
	 * @return  string  HTML unordered list
	 */
	public function render(array $attrs = NULL, array $items = NULL)
	{
		static $i;
	
		$items = empty($items) ? $this->_items : $items;
		$attrs = empty($attrs) ? $this->_attrs : $attrs;
	
		if( empty( $items ) ) return;
	
		$i++;
		HTML::$current_route = URL::site(Request::current()->uri());
	

		$attrs['class'] = empty($attrs['class']) ? 'level-'.$i : $attrs['class'].' level-'.$i;
		$menu = '<ul'.HTML::attributes($attrs).'>';
		$num_items = count($items);
		$_i = 1;
	
		foreach ($items as $key => $item)
		{
			$has_children = count($item['children']);
			$classes = NULL;
			$attributes  = array();
			$caret = NULL;
		
			// Add first, last and parent classes to the list of links to help out themers.
			if ($_i == 1)          $classes[] = 'first';
			if ($_i == $num_items) $classes[] = 'last';
			if ( $has_children )   $classes[] = 'parent dropdown';
			if ( $has_children )   $attributes[] = 'dropdown-toggle';
		
			// Check if the menu item URI is or contains the current URI
			if (HTML::is_active($item['url']))
			{
				$classes[] = 'active';
				$attributes[] = 'active';
			}
	
			if ( ! empty($classes))
			{
				$classes = HTML::attributes(array('class' => implode(' ', $classes)));
			}

			if ( ! empty($attributes))
			{
				$attributes = array('class' => implode(' ', $attributes));
			}
	
			$id = HTML::attributes(array('id' => 'menu-'.$key));
			
			//Twitter bootstrap attributes
			if ( $has_children )   $attributes['data-toggle'] = 'dropdown';
			if ( $has_children )   $item['url'] = '#';
			if ( $has_children )   $caret = '<b class="caret"></b>';
			if ( $has_children )   $caret .= ' <span class="icon"></span>';

			//set title
			$title = (isset($item['image'])) ? '<i class="'.$item['image'].'"></i>' : '';
			$title .= Text::plain($item['title']).$caret;
			
			if($item['descp'] AND !empty($item['descp']))
			{
				$title .= '<span class="menu-descp">' . Text::plain($item['descp']) . '</span>';
			}
	
			$menu .= '<li'.$classes.'  ' .$id. '>'.HTML::anchor($item['url'], $title, $attributes);
		
			if ( $has_children )
			{
				$menu .= $this->render(array('class' => 'dropdown-menu sub-menu'),  $item['children']);
			}
			
			$_i++;
			$menu .= '</li> ';
		}
	
		$menu .= '</ul>';
		$i--;
	
		return $menu;
	}
	
	/**
	 * Renders the HTML output for menu without any attributes or active item
	 *
	 * @return   string
	 */
	public function __toString()
	{
		try
		{
			return $this->render();
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}
	}
	
	/**
	 * Nicely outputs contents of $this->items for debugging info
	 *
	 * @return   string
	 */
	public function debug()
	{
		return Debug::vars($this->_items);
	}

	/**
	 * Nicely outputs contents of $this->items as array
	 *
	 * @return   as array
	 */
	public function get_items()
	{
		return $this->_items;
	}
	
	/**
	 * Static method to display menu based on its unique name
	 *
	 * @param   string   $name The name of the menu
	 * @param   array    $attr The css class or id array
	 * @return  string
	 */
	public static function links( $name, $attr = array('class' =>'menus') )
	{
		$cache = Cache::instance('menus');
	
		if( ! $items = $cache->get($name) )
		{
			$_menu = DB::select()->from('menus')->where('name', '=', (string)$name )->execute()->current();
			if( ! $_menu) return;
		
			$items = DB::select()->from('menus')
					->where('lft', '>', $_menu['lft'])
					->where('rgt', '<', $_menu['rgt'])
					->where('scp', '=', $_menu['scp'])
					->where('active', '=', 1)
					->order_by('lft', 'ASC')
					->execute()
					->as_array();
		
			if ( count($items) === 0) return;

			//set the cache
			$cache->set($name, $items, DATE::DAY);
		}

		//Initiate Menu Object
		$menu = Menu::factory();
	
		// start with an empty $right stack
		$stack = array();

		foreach( $items as &$item)
		{
			// check if we should remove a node from the stack
			while(count($stack) > 0 AND $stack[count($stack) - 1]['rgt'] < $item['rgt'])
			{
				array_pop($stack);
			}
			
			if(count($stack) > 0)
			{
				//Kohana::$log->add(LOG::DEBUG, 'Adding :title to :parent', array( ':title' => $item['title'], ':parent' => $stack[count($stack) - 1]['title']) );
				$menu->add($item['name'], $item['title'], $item['url'], $item['descp'], $item['params'], $item['image'], $stack[count($stack) - 1]['name']);
                        }
			else
			{
				//Kohana::$log->add(LOG::DEBUG, 'No parent for :title ', array( ':title' => $item['title']) );
				$menu->add($item['name'], $item['title'], $item['url'], $item['descp'], $item['params'], $item['image']);
			}
		        
			$stack[] = &$item;
		}
	
		//unset the stack array to freeup memory
		unset( $stack );

		// Enable developers to override menu
		Module::event('menus', $menu);
		Module::event("menus_{$name}", $menu);
	
		return $menu->render( $attr );
	}

	/**
	 * Static method to return menu object based on its unique name
	 *
	 * @param   string   $name The name of the menu
	 * @return  object   Menu
	 */
	public static function items( $name )
	{
		$cache = Cache::instance('menus');
	
		if( ! $items = $cache->get($name) )
		{
			$_menu = DB::select()->from('menus')->where('name', '=', (string)$name )->execute()->current();
			if( ! $_menu) return;
		
			$items = DB::select()->from('menus')
					->where('lft', '>', $_menu['lft'])
					->where('rgt', '<', $_menu['rgt'])
					->where('scp', '=', $_menu['scp'])
					->where('active', '=', 1)
					->order_by('lft', 'ASC')
					->execute()
					->as_array();
		
			if ( count($items) === 0) return;

			//set the cache
			$cache->set($name, $items, DATE::DAY);
		}

		//Initiate Menu Object
		$menu = Menu::factory();
	
		// start with an empty $right stack
		$stack = array();

		foreach( $items as &$item)
		{
			// check if we should remove a node from the stack
			while(count($stack) > 0 AND $stack[count($stack) - 1]['rgt'] < $item['rgt'])
			{
				array_pop($stack);
			}
			
			if(count($stack) > 0)
			{
				//Kohana::$log->add(LOG::DEBUG, 'Adding :title to :parent', array( ':title' => $item['title'], ':parent' => $stack[count($stack) - 1]['title']) );
				$menu->add($item['name'], $item['title'], $item['url'], $item['descp'], $item['params'], $item['image'], $stack[count($stack) - 1]['name']);
                        }
			else
			{
				//Kohana::$log->add(LOG::DEBUG, 'No parent for :title ', array( ':title' => $item['title']) );
				$menu->add($item['name'], $item['title'], $item['url'], $item['descp'], $item['params'], $item['image']);
			}
		        
			$stack[] = &$item;
		}
	
		//unset the stack array to freeup memory
		unset( $stack );

		// Enable developers to override menu
		Module::event('menus_items', $menu);
		Module::event("menus_items_{$name}", $menu);
	
		return $menu;
	}
	
	/**
	 * private method to change menu based on its unique name
	 *
	 * @param   string   $needle The name of the menu
	 * @param   array    $array  The array of items
	 * @param   string   $string The new value
	 * @param   string   $op     The action title/url to change
	 * @return  array
	 */
	private static function _change_title_url($needle, array $array, $string, $op = 'title')
	{
		foreach ($array as $key => $value)
		{
			# Check for val
			if ($key == $needle)
			{
				if($op == 'title') $array[$key]['title'] = (string)$string;
				if($op == 'url')   $array[$key]['url']   = (string)$string;
				
				return $array;
			}
	
			if (isset($value['children']))
			{
				$array[$key]['children'] = self::_change_title_url($needle, $value['children'], $string, $op);
			}
		}
	
		return $array;
	}

	/**
	 * private method to add menu based on its parent's unique name
	 *
	 * @param   string   $needle   The parent unique name of the menu
	 * @param   array    $array    The array of items
	 * @param   string   $id       The new id of menu
	 * @param   string   $title    The new title
	 * @param   string   $url      The new url
	 * @param   string   $descp    The additional text of url
	 * @param   array    $params   The new params
	 * @param   string   $image    The image or icon of url
	 * @param   menu     $children The new children
	 * @return  array
	 */
	private static function _add_child($needle, array $array, $id, $title, $url, $descp = FALSE, array $params = NULL, $image = NULL, Menu $children = NULL)
	{ 
		foreach ($array as $key => $value)
		{
			if ($key == $needle)
			{
				$array[$key]['children'][$id] = array
					(
						'title'    => $title,
						'url'      => $url,
						'children' => ($children instanceof Menu) ? $children->get_items() : NULL,
						'access'   => TRUE,
						'descp'	   => $descp,
						'params'   => $params,
						'image'    => $image
					);
				
				return $array;
			}
	
			if (isset($value['children']))
			{
				$array[$key]['children'] = self::_add_child($needle, $value['children'], $id, $title, $url, $descp, $params, $image, $children);
			}
		}
	
		return $array;
	}

	/**
	 * private method to remove a child menu based on its unique name
	 *
	 * @param   string   $needle The name of the menu
	 * @param   array    $array  The array of items
	 * @return  array
	 */
	private static function _remove_child($needle, array $array)
	{
		foreach ($array as $key => $value)
		{
			if ($key == $needle)
			{
				unset($array[$key]);
			
				return $array;
			}
	
			if (isset($value['children']))
			{
				$array[$key]['children'] = self::_remove_child($needle, $value['children']);
			}
		}
	
		return $array;
	}
	
	public static function icons()
	{
		return array(
			"icon-none" => 'none',
			"icon-beaker" => '<i class="icon-beaker"></i> icon-beaker',
			"icon-bell" => '<i class="icon-bell"></i> icon-bell',
			"icon-bolt" => '<i class="icon-bolt"></i> icon-bolt',
			"icon-bookmark-empty" => '<i class="icon-bookmark-empty"></i> icon-bookmark-empty',
			"icon-briefcase" => '<i class="icon-briefcase"></i> icon-briefcase',
			"icon-bullhorn" => '<i class="icon-bullhorn"></i> icon-bullhorn',
			"icon-caret-down" => '<i class="icon-caret-down"></i> icon-caret-down',
			"icon-caret-left" =>'<i class="icon-caret-left"></i> icon-caret-left',
			"icon-caret-right" => '<i class="icon-caret-right"></i> icon-caret-right',
			"icon-caret-up" => '<i class="icon-caret-up"></i> icon-caret-up',
			"icon-certificate" => '<i class="icon-certificate"></i> icon-certificate',
			"icon-check-empty" => '<i class="icon-check-empty"></i> icon-check-empty',
			"icon-circle-arrow-down" => '<i class="icon-circle-arrow-down"></i> icon-circle-arrow-down',
			"icon-circle-arrow-left" => '<i class="icon-circle-arrow-left"></i> icon-circle-arrow-left',
			"icon-circle-arrow-right" => '<i class="icon-circle-arrow-right"></i> icon-circle-arrow-right',
			"icon-circle-arrow-up" => '<i class="icon-circle-arrow-up"></i> icon-circle-arrow-up',
			"icon-cloud" => '<i class="icon-cloud"></i> icon-cloud',
			"icon-columns" => '<i class="icon-columns"></i> icon-columns',
			
			"icon-comment-alt" => '<i class="icon-comment-alt"></i> icon-comment-alt',
			"icon-comments-alt" => '<i class="icon-comments-alt"></i> icon-comments-alt',
			"icon-copy" => '<i class="icon-copy"></i> icon-copy',
			"icon-credit-card" => '<i class="icon-credit-card"></i> icon-credit-card',
			"icon-cut" => '<i class="icon-cut"></i> icon-cut',
			"icon-dashboard" => '<i class="icon-dashboard"></i> icon-dashboard',
			"icon-envelope-alt" => '<i class="icon-envelope-alt"></i> icon-envelope-alt',
			"icon-facebook" => '<i class="icon-facebook"></i> icon-facebook',
			"icon-filter" => '<i class="icon-filter"></i> icon-filter',
			"icon-fullscreen" => '<i class="icon-fullscreen"></i> icon-fullscreen',
			"icon-github" => '<i class="icon-github"></i> icon-github',
			"icon-globe" => '<i class="icon-globe"></i> icon-globe',
			"icon-google-plus-sign" => '<i class="icon-google-plus-sign"></i> icon-google-plus-sign',
			"icon-google-plus" => '<i class="icon-google-plus"></i> icon-google-plus',
			"icon-group" => '<i class="icon-group"></i> icon-group',
			"icon-hand-down" => '<i class="icon-hand-down"></i> icon-hand-down',
			"icon-hand-left" => '<i class="icon-hand-left"></i> icon-hand-left',
			"icon-hand-right" => '<i class="icon-hand-right"></i> icon-hand-right',
			"icon-hand-up" => '<i class="icon-hand-up"></i> icon-hand-up',
			"icon-hdd" => '<i class="icon-hdd"></i> icon-hdd',
			"icon-legal" => '<i class="icon-legal"></i> icon-legal',
			"icon-link" => '<i class="icon-link"></i> icon-link',
			"icon-linkedin" => '<i class="icon-linkedin"></i> icon-linkedin',
			"icon-list-ol" => '<i class="icon-list-ol"></i> icon-list-ol',
			"icon-list-ul" => '<i class="icon-list-ul"></i> icon-list-ul',
			"icon-magic" => '<i class="icon-magic"></i> icon-magic',
			"icon-money" => '<i class="icon-money"></i> icon-money',
			"icon-paper-clip" => '<i class="icon-paper-clip"></i> icon-paper-clip',
			"icon-paste" => '<i class="icon-paste"></i> icon-paste',
			"icon-phone-sign" => '<i class="icon-phone-sign"></i> icon-phone-sign',
			"icon-phone" => '<i class="icon-phone"></i> icon-phone',
			"icon-pinterest-sign" => '<i class="icon-pinterest-sign"></i> icon-pinterest-sign',
			"icon-pinterest" => '<i class="icon-pinterest"></i> icon-pinterest',
			"icon-reorder" => '<i class="icon-reorder"></i> icon-reorder',
			"icon-rss" => '<i class="icon-rss"></i> icon-rss',
			"icon-save" => '<i class="icon-save"></i> icon-save',
			"icon-sign-blank" => '<i class="icon-sign-blank"></i> icon-sign-blank',
			"icon-sitemap" => '<i class="icon-sitemap"></i> icon-sitemap',
			"icon-sort-down" => '<i class="icon-sort-down"></i> icon-sort-down',
			"icon-sort-up" => '<i class="icon-sort-up"></i> icon-sort-up',
			"icon-sort" => '<i class="icon-sort"></i> icon-sort',
			"icon-strikethrough" => '<i class="icon-strikethrough"></i> icon-strikethrough',
			"icon-table" => '<i class="icon-table"></i> icon-table',
			"icon-tasks" => '<i class="icon-tasks"></i> icon-tasks',
			"icon-truck" => '<i class="icon-truck"></i> icon-truck',
			"icon-twitter" => '<i class="icon-twitter"></i> icon-twitter',
			"icon-umbrella" => '<i class="icon-umbrella"></i> icon-umbrella',
			"icon-underline" => '<i class="icon-underline"></i> icon-underline',
			"icon-undo" => '<i class="icon-undo"></i> icon-undo',
			"icon-unlock" => '<i class="icon-unlock"></i> icon-unlock',
			"icon-user-md" => '<i class="icon-user-md"></i> icon-user-md',
			"icon-wrench" => '<i class="icon-wrench"></i> icon-wrench',
			"icon-adjust" => '<i class="icon-adjust"></i> icon-adjust',
			"icon-asterisk" => '<i class="icon-asterisk"></i> icon-asterisk',
			"icon-ban-circle" => '<i class="icon-ban-circle"></i> icon-ban-circle',
			"icon-bar-chart" => '<i class="icon-bar-chart"></i> icon-bar-chart',
			"icon-barcode" => '<i class="icon-barcode"></i> icon-barcode',
			"icon-beaker" => '<i class="icon-beaker"></i> icon-beaker',
			"icon-bell" => '<i class="icon-bell"></i> icon-bell',
			"icon-bolt" => '<i class="icon-bolt"></i> icon-bolt',
			"icon-book" => '<i class="icon-book"></i> icon-book',
			"icon-bookmark" => '<i class="icon-bookmark"></i> icon-bookmark',
			"icon-bookmark-empty" => '<i class="icon-bookmark-empty"></i> icon-bookmark-empty',
			"icon-briefcase" => '<i class="icon-briefcase"></i> icon-briefcase',
			"icon-bullhorn" => '<i class="icon-bullhorn"></i> icon-bullhorn',
			"icon-calendar" => '<i class="icon-calendar"></i> icon-calendar',
			"icon-camera" => '<i class="icon-camera"></i> icon-camera',
			"icon-camera-retro" => '<i class="icon-camera-retro"></i> icon-camera-retro',
			"icon-certificate" => '<i class="icon-certificate"></i> icon-certificate',
			"icon-check" => '<i class="icon-check"></i> icon-check',
			"icon-check-empty" => '<i class="icon-check-empty"></i> icon-check-empty',
			"icon-cloud" => '<i class="icon-cloud"></i> icon-cloud',
			"icon-cog" => '<i class="icon-cog"></i> icon-cog',
			"icon-cogs" => '<i class="icon-cogs"></i> icon-cogs',
			"icon-comment" => '<i class="icon-comment"></i> icon-comment',
			"icon-comment-alt" => '<i class="icon-comment-alt"></i> icon-comment-alt',
			"icon-comments" => '<i class="icon-comments"></i> icon-comments',
			"icon-comments-alt" => '<i class="icon-comments-alt"></i> icon-comments-alt',
			"icon-credit-card" => '<i class="icon-credit-card"></i> icon-credit-card',
			"icon-dashboard" => '<i class="icon-dashboard"></i> icon-dashboard',
			"icon-download" => '<i class="icon-download"></i> icon-download',
			"icon-download-alt" => '<i class="icon-download-alt"></i> icon-download-alt',
			"icon-edit" => '<i class="icon-edit"></i> icon-edit',
			"icon-envelope" => '<i class="icon-envelope"></i> icon-envelope',
			"icon-envelope-alt" => '<i class="icon-envelope-alt"></i> icon-envelope-alt',
			"icon-exclamation-sign" => '<i class="icon-exclamation-sign"></i> icon-exclamation-sign',
			"icon-external-link" => '<i class="icon-external-link"></i> icon-external-link',
			"icon-eye-close" => '<i class="icon-eye-close"></i> icon-eye-close',
			"icon-eye-open" => '<i class="icon-eye-open"></i> icon-eye-open',
			"icon-facetime-video" => '<i class="icon-facetime-video"></i> icon-facetime-video',
			"icon-film" => '<i class="icon-film"></i> icon-film',
			"icon-filter" => '<i class="icon-filter"></i> icon-filter',
			"icon-fire" => '<i class="icon-fire"></i> icon-fire',
			"icon-flag" => '<i class="icon-flag"></i> icon-flag',
			"icon-folder-close" => '<i class="icon-folder-close"></i> icon-folder-close',
			"icon-folder-open" => '<i class="icon-folder-open"></i> icon-folder-open',
			"icon-gift" => '<i class="icon-gift"></i> icon-gift',
			"icon-glass" => '<i class="icon-glass"></i> icon-glass',
			"icon-globe" => '<i class="icon-globe"></i> icon-globe',
			"icon-group" => '<i class="icon-group"></i> icon-group',
			"icon-hdd" => '<i class="icon-hdd"></i> icon-hdd',
			"icon-headphones" => '<i class="icon-headphones"></i> icon-headphones',
			"icon-heart" => '<i class="icon-heart"></i> icon-heart',
			"icon-heart-empty" => '<i class="icon-heart-empty"></i> icon-heart-empty',
			"icon-home" => '<i class="icon-home"></i> icon-home',
			"icon-inbox" => '<i class="icon-inbox"></i> icon-inbox',
			"icon-info-sign" => '<i class="icon-info-sign"></i> icon-info-sign',
			"icon-key" => '<i class="icon-key"></i> icon-key',
			"icon-leaf" => '<i class="icon-leaf"></i> icon-leaf',
			"icon-legal" => '<i class="icon-legal"></i> icon-legal',
			"icon-lemon" => '<i class="icon-lemon"></i> icon-lemon',
			"icon-lock" => '<i class="icon-lock"></i> icon-lock',
			"icon-unlock" => '<i class="icon-unlock"></i> icon-unlock',
			"icon-magic" => '<i class="icon-magic"></i> icon-magic',
			"icon-magnet" => '<i class="icon-magnet"></i> icon-magnet',
			"icon-map-marker" => '<i class="icon-map-marker"></i> icon-map-marker',
			"icon-minus" => '<i class="icon-minus"></i> icon-minus',
			"icon-minus-sign" => '<i class="icon-minus-sign"></i> icon-minus-sign',
			"icon-money" => '<i class="icon-money"></i> icon-money',
			"icon-move" => '<i class="icon-move"></i> icon-move',
			"icon-music" => '<i class="icon-music"></i> icon-music',
			"icon-off" => '<i class="icon-off"></i> icon-off',
			"icon-ok" => '<i class="icon-ok"></i> icon-ok',
			"icon-ok-circle" => '<i class="icon-ok-circle"></i> icon-ok-circle',
			"icon-ok-sign" => '<i class="icon-ok-sign"></i> icon-ok-sign',
			"icon-pencil" => '<i class="icon-pencil"></i> icon-pencil',
			"icon-picture" => '<i class="icon-picture"></i> icon-picture',
			"icon-plane" => '<i class="icon-plane"></i> icon-plane',
			"icon-plus" => '<i class="icon-plus"></i> icon-plus',
			"icon-plus-sign" => '<i class="icon-plus-sign"></i> icon-plus-sign',
			"icon-print" => '<i class="icon-print"></i> icon-print',
			"icon-pushpin" => '<i class="icon-pushpin"></i> icon-pushpin',
			"icon-qrcode" => '<i class="icon-qrcode"></i> icon-qrcode',
			"icon-question-sign" => '<i class="icon-question-sign"></i> icon-question-sign',
			"icon-random" => '<i class="icon-random"></i> icon-random',
			"icon-refresh" => '<i class="icon-refresh"></i> icon-refresh',
			"icon-remove" => '<i class="icon-remove"></i> icon-remove',
			"icon-remove-circle" => '<i class="icon-remove-circle"></i> icon-remove-circle',
			"icon-remove-sign" => '<i class="icon-remove-sign"></i> icon-remove-sign',
			"icon-reorder" => '<i class="icon-reorder"></i> icon-reorder',
			"icon-resize-horizontal" => '<i class="icon-resize-horizontal"></i> icon-resize-horizontal',
			"icon-resize-vertical" => '<i class="icon-resize-vertical"></i> icon-resize-vertical',
			"icon-retweet" => '<i class="icon-retweet"></i> icon-retweet',
			"icon-road" => '<i class="icon-road"></i> icon-road',
			"icon-rss" => '<i class="icon-rss"></i> icon-rss',
			"icon-screenshot" => '<i class="icon-screenshot"></i> icon-screenshot',
			"icon-search" => '<i class="icon-search"></i> icon-search',
			"icon-share" => '<i class="icon-share"></i> icon-share',
			"icon-share-alt" => '<i class="icon-share-alt"></i> icon-share-alt',
			"icon-shopping-cart" => '<i class="icon-shopping-cart"></i> icon-shopping-cart',
			"icon-signal" => '<i class="icon-signal"></i> icon-signal',
			"icon-signin" => '<i class="icon-signin"></i> icon-signin',
			"icon-signout" => '<i class="icon-signout"></i> icon-signout',
			"icon-sitemap" => '<i class="icon-sitemap"></i> icon-sitemap',
			"icon-sort" => '<i class="icon-sort"></i> icon-sort',
			"icon-sort-down" => '<i class="icon-sort-down"></i> icon-sort-down',
			"icon-sort-up" => '<i class="icon-sort-up"></i> icon-sort-up',
			"icon-star" => '<i class="icon-star"></i> icon-star',
			"icon-star-empty" => '<i class="icon-star-empty"></i> icon-star-empty',
			"icon-star-half" => '<i class="icon-star-half"></i> icon-star-half',
			"icon-tag" => '<i class="icon-tag"></i> icon-tag',
			"icon-tags" => '<i class="icon-tags"></i> icon-tags',
			"icon-tasks" => '<i class="icon-tasks"></i> icon-tasks',
			"icon-thumbs-down" => '<i class="icon-thumbs-down"></i> icon-thumbs-down',
			"icon-thumbs-up" => '<i class="icon-thumbs-up"></i> icon-thumbs-up',
			"icon-time" => '<i class="icon-time"></i> icon-time',
			"icon-tint" => '<i class="icon-tint"></i> icon-tint',
			"icon-trash" => '<i class="icon-trash"></i> icon-trash',
			"icon-trophy" => '<i class="icon-trophy"></i> icon-trophy',
			"icon-truck" => '<i class="icon-truck"></i> icon-truck',
			"icon-umbrella" => '<i class="icon-umbrella"></i> icon-umbrella',
			"icon-upload" => '<i class="icon-upload"></i> icon-upload',
			"icon-upload-alt" => '<i class="icon-upload-alt"></i> icon-upload-alt',
			"icon-user" => '<i class="icon-user"></i> icon-user',
			"icon-user-md" => '<i class="icon-user-md"></i> icon-user-md',
			"icon-volume-off" => '<i class="icon-volume-off"></i> icon-volume-off',
			"icon-volume-down" => '<i class="icon-volume-down"></i> icon-volume-down',
			"icon-volume-up" => '<i class="icon-volume-up"></i> icon-volume-up',
			"icon-warning-sign" => '<i class="icon-warning-sign"></i> icon-warning-sign',
			"icon-wrench" => '<i class="icon-wrench"></i> icon-wrench',
			"icon-zoom-in" => '<i class="icon-zoom-in"></i> icon-zoom-in',
			"icon-zoom-out" => '<i class="icon-zoom-out"></i> icon-zoom-out',
			"icon-file" => '<i class="icon-file"></i> icon-file',
			"icon-cut" => '<i class="icon-cut"></i> icon-cut',
			"icon-copy" => '<i class="icon-copy"></i> icon-copy',
			"icon-paste" => '<i class="icon-paste"></i> icon-paste',
			"icon-save" => '<i class="icon-save"></i> icon-save',
			"icon-undo" => '<i class="icon-undo"></i> icon-undo',
			"icon-repeat" => '<i class="icon-repeat"></i> icon-repeat',
			"icon-paper-clip" => '<i class="icon-paper-clip"></i> icon-paper-clip',
			"icon-text-height" => '<i class="icon-text-height"></i> icon-text-height',
			"icon-text-width" => '<i class="icon-text-width"></i> icon-text-width',
			"icon-align-left" => '<i class="icon-align-left"></i> icon-align-left',
			"icon-align-center" => '<i class="icon-align-center"></i> icon-align-center',
			"icon-align-right" => '<i class="icon-align-right"></i> icon-align-right',
			"icon-align-justify" => '<i class="icon-align-justify"></i> icon-align-justify',
			"icon-indent-left" => '<i class="icon-indent-left"></i> icon-indent-left',
			"icon-indent-right" => '<i class="icon-indent-right"></i> icon-indent-right',
			"icon-font" => '<i class="icon-font"></i> icon-font',
			"icon-bold" => '<i class="icon-bold"></i> icon-bold',
			"icon-italic" => '<i class="icon-italic"></i> icon-italic',
			"icon-strikethrough" => '<i class="icon-strikethrough"></i> icon-strikethrough',
			"icon-underline" => '<i class="icon-underline"></i> icon-underline',
			"icon-link" => '<i class="icon-link"></i> icon-link',
			"icon-columns" => '<i class="icon-columns"></i> icon-columns',
			"icon-table" => '<i class="icon-table"></i> icon-table',
			"icon-th-large" => '<i class="icon-th-large"></i> icon-th-large',
			"icon-th" => '<i class="icon-th"></i> icon-th',
			"icon-th-list" => '<i class="icon-th-list"></i> icon-th-list',
			"icon-list" => '<i class="icon-list"></i> icon-list',
			"icon-list-ol" => '<i class="icon-list-ol"></i> icon-list-ol',
			"icon-list-ul" => '<i class="icon-list-ul"></i> icon-list-ul',
			"icon-list-alt" => '<i class="icon-list-alt"></i> icon-list-alt',
			"icon-arrow-down" => '<i class="icon-arrow-down"></i> icon-arrow-down',
			"icon-arrow-left" => '<i class="icon-arrow-left"></i> icon-arrow-left',
			"icon-arrow-right" => '<i class="icon-arrow-right"></i> icon-arrow-right',
			"icon-arrow-up" => '<i class="icon-arrow-up"></i> icon-arrow-up',
			"icon-chevron-down" => '<i class="icon-chevron-down"></i> icon-chevron-down',
			"icon-circle-arrow-down" => '<i class="icon-circle-arrow-down"></i> icon-circle-arrow-down',
			"icon-circle-arrow-left" => '<i class="icon-circle-arrow-left"></i> icon-circle-arrow-left',
			"icon-circle-arrow-right" => '<i class="icon-circle-arrow-right"></i> icon-circle-arrow-right',
			"icon-circle-arrow-up" => '<i class="icon-circle-arrow-up"></i> icon-circle-arrow-up',
			"icon-chevron-left" => '<i class="icon-chevron-left"></i> icon-chevron-left',
			"icon-caret-down" => '<i class="icon-caret-down"></i> icon-caret-down',
			"icon-caret-left" => '<i class="icon-caret-left"></i> icon-caret-left',
			"icon-caret-right" => '<i class="icon-caret-right"></i> icon-caret-right',
			"icon-caret-up" => '<i class="icon-caret-up"></i> icon-caret-up',
			"icon-chevron-right" => '<i class="icon-chevron-right"></i> icon-chevron-right',
			"icon-hand-down" => '<i class="icon-hand-down"></i> icon-hand-down',
			"icon-hand-left" => '<i class="icon-hand-left"></i> icon-hand-left',
			"icon-hand-right" => '<i class="icon-hand-right"></i> icon-hand-right',
			"icon-hand-up" => '<i class="icon-hand-up"></i> icon-hand-up',
			"icon-chevron-up" => '<i class="icon-chevron-up"></i> icon-chevron-up',
			"icon-play-circle" => '<i class="icon-play-circle"></i> icon-play-circle',
			"icon-play" => '<i class="icon-play"></i> icon-play',
			"icon-pause" => '<i class="icon-pause"></i> icon-pause',
			"icon-stop" => '<i class="icon-stop"></i> icon-stop',
			"icon-step-backward" => '<i class="icon-step-backward"></i> icon-step-backward',
			"icon-fast-backward" => '<i class="icon-fast-backward"></i> icon-fast-backward',
			"icon-backward" => '<i class="icon-backward"></i> icon-backward',
			"icon-forward" => '<i class="icon-forward"></i> icon-forward',
			"icon-fast-forward" => '<i class="icon-fast-forward"></i> icon-fast-forward',
			"icon-step-forward" => '<i class="icon-step-forward"></i> icon-step-forward',
			"icon-eject" => '<i class="icon-eject"></i> icon-eject',
			"icon-fullscreen" => '<i class="icon-fullscreen"></i> icon-fullscreen',
			"icon-resize-full" => '<i class="icon-resize-full"></i> icon-resize-full',
			"icon-resize-small" => '<i class="icon-resize-small"></i> icon-resize-small',
			"icon-phone" => '<i class="icon-phone"></i> icon-phone',
			"icon-phone-sign" => '<i class="icon-phone-sign"></i> icon-phone-sign',
			"icon-facebook" => '<i class="icon-facebook"></i> icon-facebook',
			"icon-facebook-sign" => '<i class="icon-facebook-sign"></i> icon-facebook-sign',
			"icon-twitter" => '<i class="icon-twitter"></i> icon-twitter',
			"icon-twitter-sign" => '<i class="icon-twitter-sign"></i> icon-twitter-sign',
			"icon-github" => '<i class="icon-github"></i> icon-github',
			"icon-github-sign" => '<i class="icon-github-sign"></i> icon-github-sign',
			"icon-linkedin" => '<i class="icon-linkedin"></i> icon-linkedin',
			"icon-linkedin-sign" => '<i class="icon-linkedin-sign"></i> icon-linkedin-sign',
			"icon-pinterest" => '<i class="icon-pinterest"></i> icon-pinterest',
			"icon-pinterest-sign" => '<i class="icon-pinterest-sign"></i> icon-pinterest-sign',
			"icon-google-plus" => '<i class="icon-google-plus"></i> icon-google-plus',
			"icon-google-plus-sign" => '<i class="icon-google-plus-sign"></i> icon-google-plus-sign',
			"icon-sign-blank" => '<i class="icon-sign-blank"></i> icon-sign-blank',
		);
	}
	
}