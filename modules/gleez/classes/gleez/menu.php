<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Core Menu Class
 *
 * This class can be used to easily build out a menu in the form
 * of an unordered list. You can add any attributes you'd like to
 * the list, and each list item has special classes to help you style it.
 *
 * @package	Gleez
 * @category	Menu
 * @author	Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2012 Gleez Technologies
 * @license	http://gleezcms.org/license
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
	 * @param   array    Params of the item to handle logic
	 * @param   string   Parent Id of the link
	 * @param   Menu     Instance of class that contain children
	 * @return  Menu
	 */
	public function add($id, $title, $url, array $params = NULL, $parent_id = FALSE, Menu $children = NULL)
	{
		if( $parent_id )
		{
			$this->_items = self::_add_child($parent_id, $this->_items, $id, $title, $url, $params, $children);
		}
		else
		{
			$this->_items[$id] = array
			(
				'title'    => $title,
				'url'      => $url,
				'children' => ($children instanceof Menu) ? $children->_items : NULL,
				'access'   => TRUE,
				'params'   => $params
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
	public function set_title($target_id, $title, $parent = FALSE)
	{
		if ( $parent )
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
		if ( $parent )
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
		HTML::$current_route = Url::site(Request::current()->uri());
	

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
		
			$menu .= '<li'.$classes.'  ' .$id. '>'.HTML::anchor($item['url'], $item['title'].$caret, $attributes);
		
			if ( $has_children )
			{
				$menu .= $this->render(array('class' => 'dropdown-menu'),  $item['children']);
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
				$menu->add($item['name'], $item['title'], $item['url'], $item['params'], $stack[count($stack) - 1]['name']);
                        }
			else
			{
				//Kohana::$log->add(LOG::DEBUG, 'No parent for :title ', array( ':title' => $item['title']) );
				$menu->add($item['name'], $item['title'], $item['url'], $item['params']);
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
	 * @param   array    $params   The new params
	 * @param   menu     $children The new children
	 * @return  array
	 */
	private static function _add_child($needle, array $array, $id, $title, $url, array $params = NULL, Menu $children = NULL)
	{ 
		foreach ($array as $key => $value)
		{
			if ($key == $needle)
			{
				$array[$key]['children'][$id] = array
					(
						'title'    => $title,
						'url'      => $url,
						'children' => ($children instanceof Menu) ? $children->_items : NULL,
						'access'   => TRUE,
						'params'   => $params
					);
				
				return $array;
			}
	
			if (isset($value['children']))
			{
				$array[$key]['children'] = self::_add_child($needle, $value['children'], $id, $title, $url, $params, $children);
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
	
}