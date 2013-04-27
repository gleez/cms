<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Breadcrumb Class
 *
 * Automatically create breadcrumb links.
 *
 * @package    Gleez\HTML
 * @author	   Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 * @see	  	   https://github.com/xavividal/Breadcrumbs
 */
class Gleez_Breadcrumb
{
        /**
         * Default view 
         * @var string
         */
        protected $view = 'breadcrumb';
        
        /**
         * Singleton instance
         * @var Breadcrumb
         */
        protected static $instance;
        
        /**
         * Stack of breadcrumb items
         * @var array
         */
        protected $items = array();
        
        /**
         * Constructor
         * @return Breadcrumb
         */
        private function __construct()
        {
        }
        
        /**
         * Get the unique instance
         * @return Breadcrumb
         */
        public static function factory()
        {
                if (self::$instance === null)
                {
                        self::$instance = new Breadcrumb;
                }
                return self::$instance;
        }
        
        /**
         * Set the template name
         * @param string $view 
         */
        public function setView($view)
        {
                $this->view = $view;
        }
        
        /**
         * Add a new item to the breadcrumb stack
         * @param string $label
         * @param string $url
         */
        public function addItem($label, $url = null)
        {
                $this->items[] = array(
                        'label' => $label,
                        'url' => $url
                );
        }
        
        /**
         * Render the breadcrumb
         * @return string
         */
        public function render()
        {
                $view              = View::factory($this->view);
                $view->items       = $this->items;
                $view->items_count = count($this->items);
                
                $config = Kohana::$config->load('breadcrumb');
                
                $view->separator     = $config['separator'];
                $view->last_linkable = $config['last_linkable'];
                
                
                return $view->render();
        }
}