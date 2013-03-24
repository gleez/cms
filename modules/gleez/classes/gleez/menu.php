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
			"icon-none" => __('none'),
			"icon-cloud-download" => '<i class="icon-cloud-download"></i> cloud-download',
			"icon-cloud-upload" => '<i class="icon-cloud-upload"></i> cloud-upload',
			"icon-lightbulb" => '<i class="icon-lightbulb"></i> lightbulb',
			"icon-exchange" => '<i class="icon-exchange"></i> exchange',
			"icon-bell-alt" => '<i class="icon-bell-alt"></i> bell-alt',
			"icon-file-alt" => '<i class="icon-file-alt"></i> file-alt',
			"icon-beer" => '<i class="icon-beer"></i> beer',
			"icon-coffee" => '<i class="icon-coffee"></i> coffee',
			"icon-food" => '<i class="icon-food"></i> food',
			"icon-fighter-jet" => '<i class="icon-fighter-jet"></i> fighter-jet',
			
			"icon-user-md" => '<i class="icon-user-md"></i> user-md',
			"icon-stethoscope" => '<i class="icon-stethoscope"></i> stethoscope',
			"icon-suitcase" => '<i class="icon-suitcase"></i> suitcase',
			"icon-building" => '<i class="icon-building"></i> building',
			"icon-hospital" => '<i class="icon-hospital"></i> hospital',
			"icon-ambulance" => '<i class="icon-ambulance"></i> ambulance',
			"icon-medkit" => '<i class="icon-medkit"></i> medkit',
			"icon-h-sign" => '<i class="icon-h-sign"></i> h-sign',
			"icon-plus-sign-alt" => '<i class="icon-plus-sign-alt"></i> plus-sign-alt',
			"icon-spinner" => '<i class="icon-spinner"></i> spinner',
			
			"icon-angle-left" => '<i class="icon-angle-left"></i> angle-left',
			"icon-angle-right" => '<i class="icon-angle-right"></i> angle-right',
			"icon-angle-up" => '<i class="icon-angle-up"></i> angle-up',
			"icon-angle-down" => '<i class="icon-angle-down"></i> angle-down',
			"icon-double-angle-left" => '<i class="icon-double-angle-left"></i> double-angle-left',
			"icon-double-angle-right" => '<i class="icon-double-angle-right"></i> double-angle-right',
			"icon-double-angle-up" => '<i class="icon-double-angle-up"></i> double-angle-up',
			"icon-double-angle-down" => '<i class="icon-double-angle-down"></i> double-angle-down',
			"icon-circle-blank" => '<i class="icon-circle-blank"></i> circle-blank',
			"icon-circle" => '<i class="icon-circle"></i> circle',
			
			"icon-desktop" => '<i class="icon-desktop"></i> desktop',
			"icon-laptop" => '<i class="icon-laptop"></i> laptop',
			"icon-tablet" => '<i class="icon-tablet"></i> tablet',
			"icon-mobile-phone" => '<i class="icon-mobile-phone"></i> mobile-phone',
			"icon-quote-left" => '<i class="icon-quote-left"></i> quote-left',
			"icon-quote-right" => '<i class="icon-quote-right"></i> quote-right',
			"icon-reply" => '<i class="icon-reply"></i> reply',
			"icon-github-alt" => '<i class="icon-github-alt"></i> github-alt',
			"icon-folder-close-alt" => '<i class="icon-folder-close-alt"></i> folder-close-alt',
			"icon-folder-open-alt" => '<i class="icon-folder-open-alt"></i> folder-open-alt',
			
			"icon-adjust" => '<i class="icon-adjust"></i> adjust',
			"icon-asterisk" => '<i class="icon-asterisk"></i> asterisk',
			"icon-ban-circle" => '<i class="icon-ban-circle"></i> ban-circle',
			"icon-bar-chart" => '<i class="icon-bar-chart"></i> bar-chart',
			"icon-barcode" => '<i class="icon-barcode"></i> barcode',
			"icon-beaker" => '<i class="icon-beaker"></i> beaker',
			"icon-beer" => '<i class="icon-beer"></i> beer',
			"icon-bell" => '<i class="icon-bell"></i> bell',
			"icon-bell-alt" => '<i class="icon-bell-alt"></i> bell-alt',
			"icon-bolt" => '<i class="icon-bolt"></i> bolt',
			"icon-book" => '<i class="icon-book"></i> book',
			"icon-bookmark" => '<i class="icon-bookmark"></i> bookmark',
			"icon-bookmark-empty" => '<i class="icon-bookmark-empty"></i> bookmark-empty',
			"icon-briefcase" => '<i class="icon-briefcase"></i> briefcase',
			"icon-bullhorn" => '<i class="icon-bullhorn"></i> bullhorn',
			"icon-calendar" => '<i class="icon-calendar"></i> calendar',
			"icon-camera" => '<i class="icon-camera"></i> camera',
			"icon-camera-retro" => '<i class="icon-camera-retro"></i> camera-retro',
			"icon-certificate" => '<i class="icon-certificate"></i> certificate',
			"icon-check" => '<i class="icon-check"></i> check',
			"icon-check-empty" => '<i class="icon-check-empty"></i> check-empty',
			"icon-circle" => '<i class="icon-circle"></i> circle',
			"icon-circle-blank" => '<i class="icon-circle-blank"></i> circle-blank',
			"icon-cloud" => '<i class="icon-cloud"></i> cloud',
			"icon-cloud-download" => '<i class="icon-cloud-download"></i> cloud-download',
			"icon-cloud-upload" => '<i class="icon-cloud-upload"></i> cloud-upload',
			"icon-coffee" => '<i class="icon-coffee"></i> coffee',
			"icon-cog" => '<i class="icon-cog"></i> cog',
			"icon-cogs" => '<i class="icon-cogs"></i> cogs',
			"icon-comment" => '<i class="icon-comment"></i> comment',
			"icon-comment-alt" => '<i class="icon-comment-alt"></i> comment-alt',
			"icon-comments" => '<i class="icon-comments"></i> comments',
			"icon-comments-alt" => '<i class="icon-comments-alt"></i> comments-alt',
			"icon-credit-card" => '<i class="icon-credit-card"></i> credit-card',
			"icon-dashboard" => '<i class="icon-dashboard"></i> dashboard',
			"icon-desktop" => '<i class="icon-desktop"></i> desktop',
			"icon-download" => '<i class="icon-download"></i> download',
			"icon-download-alt" => '<i class="icon-download-alt"></i> download-alt',
			
			"icon-edit" => '<i class="icon-edit"></i> edit',
			"icon-envelope" => '<i class="icon-envelope"></i> envelope',
			"icon-envelope-alt" => '<i class="icon-envelope-alt"></i> envelope-alt',
			"icon-exchange" => '<i class="icon-exchange"></i> exchange',
			"icon-exclamation-sign" => '<i class="icon-exclamation-sign"></i> exclamation-sign',
			"icon-external-link" => '<i class="icon-external-link"></i> external-link',
			"icon-eye-close" => '<i class="icon-eye-close"></i> eye-close',
			"icon-eye-open" => '<i class="icon-eye-open"></i> eye-open',
			"icon-facetime-video" => '<i class="icon-facetime-video"></i> facetime-video',
			"icon-fighter-jet" => '<i class="icon-fighter-jet"></i> fighter-jet',
			"icon-film" => '<i class="icon-film"></i> film',
			"icon-filter" => '<i class="icon-filter"></i> filter',
			"icon-fire" => '<i class="icon-fire"></i> fire',
			"icon-flag" => '<i class="icon-flag"></i> flag',
			"icon-folder-close" => '<i class="icon-folder-close"></i> folder-close',
			"icon-folder-open" => '<i class="icon-folder-open"></i> folder-open',
			"icon-folder-close-alt" => '<i class="icon-folder-close-alt"></i> folder-close-alt',
			"icon-folder-open-alt" => '<i class="icon-folder-open-alt"></i> folder-open-alt',
			"icon-food" => '<i class="icon-food"></i> food',
			"icon-gift" => '<i class="icon-gift"></i> gift',
			"icon-glass" => '<i class="icon-glass"></i> glass',
			"icon-globe" => '<i class="icon-globe"></i> globe',
			"icon-group" => '<i class="icon-group"></i> group',
			"icon-hdd" => '<i class="icon-hdd"></i> hdd',
			"icon-headphones" => '<i class="icon-headphones"></i> headphones',
			"icon-heart" => '<i class="icon-heart"></i> heart',
			"icon-heart-empty" => '<i class="icon-heart-empty"></i> heart-empty',
			"icon-home" => '<i class="icon-home"></i> home',
			"icon-inbox" => '<i class="icon-inbox"></i> inbox',
			"icon-info-sign" => '<i class="icon-info-sign"></i> info-sign',
			"icon-key" => '<i class="icon-key"></i> key',
			"icon-leaf" => '<i class="icon-leaf"></i> leaf',
			"icon-laptop" => '<i class="icon-laptop"></i> laptop',
			"icon-legal" => '<i class="icon-legal"></i> legal',
			"icon-lemon" => '<i class="icon-lemon"></i> lemon',
			"icon-lightbulb" => '<i class="icon-lightbulb"></i> lightbulb',
			"icon-lock" => '<i class="icon-lock"></i> lock',
			"icon-unlock" => '<i class="icon-unlock"></i> unlock',
			
			"icon-magic" => '<i class="icon-magic"></i> magic',
			"icon-magnet" => '<i class="icon-magnet"></i> magnet',
			"icon-marker" => '<i class="icon-map-marker"></i> map-marker',
			"icon-minus" => '<i class="icon-minus"></i> minus',
			"icon-minus-sign" => '<i class="icon-minus-sign"></i> minus-sign',
			"icon-mobile-phone" => '<i class="icon-mobile-phone"></i> mobile-phone',
			"icon-money" => '<i class="icon-money"></i> money',
			"icon-move" => '<i class="icon-move"></i> move',
			"icon-music" => '<i class="icon-music"></i> music',
			"icon-off" => '<i class="icon-off"></i> off',
			"icon-ok" => '<i class="icon-ok"></i> ok',
			"icon-ok-circle" => '<i class="icon-ok-circle"></i> ok-circle',
			"icon-ok-sign" => '<i class="icon-ok-sign"></i> ok-sign',
			"icon-pencil" => '<i class="icon-pencil"></i> pencil',
			"icon-picture" => '<i class="icon-picture"></i> picture',
			"icon-plane" => '<i class="icon-plane"></i> plane',
			"icon-plus" => '<i class="icon-plus"></i> plus',
			"icon-plus-sign" => '<i class="icon-plus-sign"></i> plus-sign',
			"icon-print" => '<i class="icon-print"></i> print',
			"icon-pushpin" => '<i class="icon-pushpin"></i> pushpin',
			"icon-qrcode" => '<i class="icon-qrcode"></i> qrcode',
			"icon-question-sign" => '<i class="icon-question-sign"></i> question-sign',
			"icon-quote-left" => '<i class="icon-quote-left"></i> quote-left',
			"icon-quote-right" => '<i class="icon-quote-right"></i> quote-right',
			"icon-random" => '<i class="icon-random"></i> random',
			"icon-refresh" => '<i class="icon-refresh"></i> refresh',
			"icon-remove" => '<i class="icon-remove"></i> remove',
			"icon-remove-circle" => '<i class="icon-remove-circle"></i> remove-circle',
			"icon-remove-sign" => '<i class="icon-remove-sign"></i> remove-sign',
			"icon-reorder" => '<i class="icon-reorder"></i> reorder',
			"icon-reply" => '<i class="icon-reply"></i> reply',
			"icon-resize-horizontal" => '<i class="icon-resize-horizontal"></i> resize-horizontal',
			"icon-resize-vertical" => '<i class="icon-resize-vertical"></i> resize-vertical',
			"icon-retweet" => '<i class="icon-retweet"></i> retweet',
			"icon-road" => '<i class="icon-road"></i> road',
			"icon-rss" => '<i class="icon-rss"></i> rss',
			"icon-screenshot" => '<i class="icon-screenshot"></i> screenshot',
			"icon-search" => '<i class="icon-search"></i> search',
			
			"icon-share" => '<i class="icon-share"></i> share',
			"icon-share-alt" => '<i class="icon-share-alt"></i> share-alt',
			"icon-shopping-cart" => '<i class="icon-shopping-cart"></i> shopping-cart',
			"icon-signal" => '<i class="icon-signal"></i> signal',
			"icon-signin" => '<i class="icon-signin"></i> signin',
			"icon-signout" => '<i class="icon-signout"></i> signout',
			"icon-sitemap" => '<i class="icon-sitemap"></i> sitemap',
			"icon-sort" => '<i class="icon-sort"></i> sort',
			"icon-sort-down" => '<i class="icon-sort-down"></i> sort-down',
			"icon-sort-up" => '<i class="icon-sort-up"></i> sort-up',
			"icon-spinner" => '<i class="icon-spinner"></i> spinner',
			"icon-star" => '<i class="icon-star"></i> star',
			"icon-star-empty" => '<i class="icon-star-empty"></i> star-empty',
			"icon-star-half" => '<i class="icon-star-half"></i> star-half',
			"icon-tablet" => '<i class="icon-tablet"></i> tablet',
			"icon-tag" => '<i class="icon-tag"></i> tag',
			"icon-tags" => '<i class="icon-tags"></i> tags',
			"icon-tasks" => '<i class="icon-tasks"></i> tasks',
			"icon-thumbs-down" => '<i class="icon-thumbs-down"></i> thumbs-down',
			"icon-thumbs-up" => '<i class="icon-thumbs-up"></i> thumbs-up',
			"icon-time" => '<i class="icon-time"></i> time',
			"icon-tint" => '<i class="icon-tint"></i> tint',
			"icon-trash" => '<i class="icon-trash"></i> trash',
			"icon-trophy" => '<i class="icon-trophy"></i> trophy',
			"icon-truck" => '<i class="icon-truck"></i> truck',
			"icon-umbrella" => '<i class="icon-umbrella"></i> umbrella',
			"icon-upload" => '<i class="icon-upload"></i> upload',
			"icon-upload-alt" => '<i class="icon-upload-alt"></i> upload-alt',
			"icon-user" => '<i class="icon-user"></i> user',
			"icon-user-md" => '<i class="icon-user-md"></i> user-md',
			"icon-volume-off" => '<i class="icon-volume-off"></i> volume-off',
			"icon-volume-down" => '<i class="icon-volume-down"></i> volume-down',
			"icon-volume-up" => '<i class="icon-volume-up"></i> volume-up',
			"icon-warning-sign" => '<i class="icon-warning-sign"></i> warning-sign',
			"icon-wrench" => '<i class="icon-wrench"></i> wrench',
			"icon-zoom-in" => '<i class="icon-zoom-in"></i> zoom-in',
			"icon-zoom-out" => '<i class="icon-zoom-out"></i> zoom-out',
			
			"icon-file" => '<i class="icon-file"></i> file',
			"icon-file-alt" => '<i class="icon-file-alt"></i> file-alt',
			"icon-cut" => '<i class="icon-cut"></i> cut',
			"icon-copy" => '<i class="icon-copy"></i> copy',
			"icon-paste" => '<i class="icon-paste"></i> paste',
			"icon-save" => '<i class="icon-save"></i> save',
			"icon-undo" => '<i class="icon-undo"></i> undo',
			"icon-repeat" => '<i class="icon-repeat"></i> repeat',
			
			"icon-text-height" => '<i class="icon-text-height"></i> text-height',
			"icon-text-width" => '<i class="icon-text-width"></i> text-width',
			"icon-align-left" => '<i class="icon-align-left"></i> align-left',
			"icon-align-center" => '<i class="icon-align-center"></i> align-center',
			"icon-align-right" => '<i class="icon-align-right"></i> align-right',
			"icon-justify" => '<i class="icon-align-justify"></i> align-justify',
			"icon-indent-left" => '<i class="icon-indent-left"></i> indent-left',
			"icon-indent-right" => '<i class="icon-indent-right"></i> indent-right',
			
			"icon-font" => '<i class="icon-font"></i> font',
			"icon-bold" => '<i class="icon-bold"></i> bold',
			"icon-italic" => '<i class="icon-italic"></i> italic',
			"icon-strikethrough" => '<i class="icon-strikethrough"></i> strikethrough',
			"icon-underline" => '<i class="icon-underline"></i> underline',
			"icon-link" => '<i class="icon-link"></i> link',
			"icon-paper-clip" => '<i class="icon-paper-clip"></i> paper-clip',
			"icon-columns" => '<i class="icon-columns"></i> columns',
			
			"icon-table" => '<i class="icon-table"></i> table',
			"icon-th-large" => '<i class="icon-th-large"></i> th-large',
			"icon-th" => '<i class="icon-th"></i> th',
			"icon-th-list" => '<i class="icon-th-list"></i> th-list',
			"icon-list" => '<i class="icon-list"></i> list',
			"icon-list-ol" => '<i class="icon-list-ol"></i> list-ol',
			"icon-list-ul" => '<i class="icon-list-ul"></i> list-ul',
			"icon-list-alt" => '<i class="icon-list-alt"></i> list-alt',
			
			"icon-angle-left" => '<i class="icon-angle-left"></i> angle-left',
			"icon-angle-right" => '<i class="icon-angle-right"></i> angle-right',
			"icon-angle-up" => '<i class="icon-angle-up"></i> angle-up',
			"icon-angle-down" => '<i class="icon-angle-down"></i> angle-down',
			"icon-arrow-down" => '<i class="icon-arrow-down"></i> arrow-down',
			"icon-arrow-left" => '<i class="icon-arrow-left"></i> arrow-left',
			"icon-arrow-right" => '<i class="icon-arrow-right"></i> arrow-right',
			"icon-arrow-up" => '<i class="icon-arrow-up"></i> arrow-up',
			
			"icon-caret-down" => '<i class="icon-caret-down"></i> caret-down',
			"icon-caret-left" => '<i class="icon-caret-left"></i> caret-left',
			"icon-caret-right" => '<i class="icon-caret-right"></i> caret-right',
			"icon-caret-up" => '<i class="icon-caret-up"></i> caret-up',
			"icon-chevron-down" => '<i class="icon-chevron-down"></i> chevron-down',
			"icon-chevron-left" => '<i class="icon-chevron-left"></i> chevron-left',
			"icon-chevron-right" => '<i class="icon-chevron-right"></i> chevron-right',
			"icon-chevron-up" => '<i class="icon-chevron-up"></i> chevron-up',
			
			"icon-circle-arrow-down" => '<i class="icon-circle-arrow-down"></i> circle-arrow-down',
			"icon-circle-arrow-left" => '<i class="icon-circle-arrow-left"></i> circle-arrow-left',
			"icon-circle-arrow-right" => '<i class="icon-circle-arrow-right"></i> circle-arrow-right',
			"icon-circle-arrow-up" => '<i class="icon-circle-arrow-up"></i> circle-arrow-up',
			"icon-double-angle-left" => '<i class="icon-double-angle-left"></i> double-angle-left',
			"icon-double-angle-right" => '<i class="icon-double-angle-right"></i> double-angle-right',
			"icon-double-angle-up" => '<i class="icon-double-angle-up"></i> double-angle-up',
			"icon-double-angle-down" => '<i class="icon-double-angle-down"></i> double-angle-down',
			
			"icon-hand-down" => '<i class="icon-hand-down"></i> hand-down',
			"icon-hand-left" => '<i class="icon-hand-left"></i> hand-left',
			"icon-hand-right" => '<i class="icon-hand-right"></i> hand-right',
			"icon-hand-up" => '<i class="icon-hand-up"></i> hand-up',
			"icon-circle" => '<i class="icon-circle"></i> circle',
			"icon-circle-blank" => '<i class="icon-circle-blank"></i> circle-blank',
			
			"icon-play-circle" => '<i class="icon-play-circle"></i> play-circle',
			"icon-play" => '<i class="icon-play"></i> play',
			"icon-pause" => '<i class="icon-pause"></i> pause',
			"icon-stop" => '<i class="icon-stop"></i> stop',
			
			"icon-step-backward" => '<i class="icon-step-backward"></i> step-backward',
			"icon-fast-backward" => '<i class="icon-fast-backward"></i> fast-backward',
			"icon-backward" => '<i class="icon-backward"></i> backward',
			"icon-forward" => '<i class="icon-forward"></i> forward',
			
			"icon-fast-forward" => '<i class="icon-fast-forward"></i> fast-forward',
			"icon-step-forward" => '<i class="icon-step-forward"></i> step-forward',
			"icon-eject" => '<i class="icon-eject"></i> eject',
			
			"icon-fullscreen" => '<i class="icon-fullscreen"></i> fullscreen',
			"icon-resize-full" => '<i class="icon-resize-full"></i> resize-full',
			"icon-resize-small" => '<i class="icon-resize-small"></i> resize-small',
			
			"icon-phone" => '<i class="icon-phone"></i> phone',
			"icon-sign" => '<i class="icon-phone-sign"></i> phone-sign',
			"icon-facebook" => '<i class="icon-facebook"></i> facebook',
			"icon-facebook-sign" => '<i class="icon-facebook-sign"></i> facebook-sign',
			
			"icon-twitter" => '<i class="icon-twitter"></i> twitter',
			"icon-twitter-sign" => '<i class="icon-twitter-sign"></i> twitter-sign',
			"icon-github" => '<i class="icon-github"></i> github',
			"icon-github-alt" => '<i class="icon-github-alt"></i> github-alt',
			
			"icon-github-sign" => '<i class="icon-github-sign"></i> github-sign',
			"icon-linkedin" => '<i class="icon-linkedin"></i> linkedin',
			"icon-linkedin-sign" => '<i class="icon-linkedin-sign"></i> linkedin-sign',
			"icon-pinterest" => '<i class="icon-pinterest"></i> pinterest',
			
			"icon-pinterest-sign" => '<i class="icon-pinterest-sign"></i> pinterest-sign',
			"icon-google-plus" => '<i class="icon-google-plus"></i> google-plus',
			"icon-google-plus-sign" => '<i class="icon-google-plus-sign"></i> google-plus-sign',
			"icon-sign-blank" => '<i class="icon-sign-blank"></i> sign-blank',
			
			"icon-ambulance" => '<i class="icon-ambulance"></i> ambulance',
			"icon-beaker" => '<i class="icon-beaker"></i> beaker',
			
			"icon-h-sign" => '<i class="icon-h-sign"></i> h-sign',
			"icon-hospital" => '<i class="icon-hospital"></i> hospital',
			
			"icon-medkit" => '<i class="icon-medkit"></i> medkit',
			"icon-plus-sign-alt" => '<i class="icon-plus-sign-alt"></i> plus-sign-alt',
			
			"icon-stethoscope" => '<i class="icon-stethoscope"></i> stethoscope',
			"icon-user-md" => '<i class="icon-user-md"></i> user-md',
		);
	}
	
}