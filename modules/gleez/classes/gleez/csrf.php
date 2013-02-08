<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 *  Cross-Site Request Forgery helper.
 *      
 * @package	Gleez
 * @category	CSRF
 * @author	Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2012 Gleez Technologies
 * @license	http://gleezcms.org/license
 */
class Gleez_CSRF {
	
	/**
         * @var  integer  Token time to live in seconds, 30 minutes
         */
        public static $csrf_ttl = 1800;
    
        /**
         * Get CSRF token
         *
         * @param   string   $id      Custom token id, e.g. uid
         * @param   string   $action  Optional action
         * @param   integer  $time    Used only internally
         * @return  string
         */
        public static function token($id = '', $action = '', $time = 0)
        {
                // Get id string for token, could be uid or ip etc
                if (!$id) $id = Request::$client_ip;
        
                // Get time to live
                if (!$time) $time = ceil(time() / self::$csrf_ttl);
        
                return sha1($time . self::key() . $id . $action);
        }

        /**
         * Validate CSRF token
         *
         * @param   string   $token
         * @param   string   $id      Custom token id, e.g. uid
         * @param   string   $action  Optional action
         * @return  boolean
         */
        public static function valid($token = false, $action = '', $id = '')
        {
		//get token and action from Form POST
                if (!$token)  $token  = Arr::get($_REQUEST, '_token');
                if (!$action) $action = Arr::get($_REQUEST, '_action');
	
                // Get time to live
                $time = ceil(time() / self::$csrf_ttl);
        
                // Check token validity
                return ($token === self::token($id, $action, $time) || $token === self::token($id, $action, $time - 1));
        }
        
        /**
         * User specefic key used to generate unqiue tokens.
         *
         * @return
         *   The user specefic private key.
         */
        static function key()
        {
                $token  = Session::instance()->id();
                $secret = self::_private_key();
                return sha1($secret . $token);
        }
        
        /**
         * Ensure the private key variable used to generate tokens is set.
         *
         * @return
         *   The private key.
         */
        private static function _private_key()
        {
		$config = Kohana::$config->load('site');
		
                if ( !($key = $config->get('gleez_private_key')) )
                {
                        $key = sha1(uniqid(mt_rand(), true)) . md5(uniqid(mt_rand(), true));
                        $config->set('gleez_private_key', $key);
                }
                
                return $key;
        }
        
}