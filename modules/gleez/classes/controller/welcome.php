<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Welcome Controller
 *
 * @package   Gleez
 * @category  Controller
 * @author    Sandeep Sangamreddi - Gleez
 * @copyright (c) 2013 Gleez Technologies
 * @license   http://gleezcms.org/license
 */
class Controller_Welcome extends Template {

        /** Prepare welcome page */
        public function action_index()
        {
                // If Gleez CMS don't installed
                if(! Gleez::$installed)
                {
                        // Send to the installer with server status
                        $this->request->redirect(Route::get('install')->uri(array('action' => 'index')), 200);
                }
            
                $this->title = __('Welcome!');
                $content = View::factory('welcome');
                $this->response->body($content);
        }

}
