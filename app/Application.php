<?php
/**
 * Gleez CMS (http://gleezcms.org)
 *
 * @link      https://github.com/gleez/cms Canonical source repository
 * @copyright Copyright (c) 2011-2015 Gleez Technologies
 * @license   http://gleezcms.org/license Gleez CMS License
 */

namespace Gleez;

use RuntimeException;

class Application
{
    // Release version and codename
    const VERSION  = '2.0.0-dev';
    const CODENAME = 'Bocydium Globulare';

    // Common environment type constants for consistency and convenience
    const PRODUCTION  = 'production';
    const STAGING     = 'staging';
    const TESTING     = 'testing';
    const DEVELOPMENT = 'development';

    /**
     * Current environment
     * @var int
     */
    protected $environment = Application::DEVELOPMENT;

    /**
     * Application constructor
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Runs the application performing all initializations
     *
     * @return Application
     */
    protected function init()
    {
        $initializers = [
            'environment'
        ];

        foreach($initializers as $service) {
            $function = 'init' . ucfirst($service);
            $this->$function();
        }

        return $this;
    }

    /**
     * Initializes the Environment
     * @throws \RuntimeException
     */
    protected function initEnvironment()
    {
        /**
         * @const APP_START_TIME The start time of the application, used for profiling
         */
        define('APP_START_TIME', microtime(TRUE));

        /**
         * @const APP_START_MEMORY The memory usage at the start of the application, used for profiling
         */
        define('APP_START_MEMORY', memory_get_usage(defined('HHVM_VERSION')));

        if (function_exists('mb_internal_encoding')) {
            // Set internal character encoding to UTF-8.
            mb_internal_encoding('UTF-8');
        }

        if (function_exists('mb_substitute_character')) {
            // Set the mb_substitute_character to "none"
            mb_substitute_character('none');
        }

        // Check environment variable
        if (isset($_SERVER['APP_ENV'])) {
            // Get environment variable from $_SERVER, .htaccess, apache.conf, nginx.conf, etc.
            $env = $_SERVER['APP_ENV'];
        } elseif (get_cfg_var('APP_ENV')) {
            // Get environment variable from php.ini or from ini_get('user_ini.filename')
            $env = get_cfg_var('APP_ENV');
        } elseif (getenv('APP_ENV')) {
            // Get environment variable from system environment
            $env = getenv('APP_ENV');
        } else {
            $env = Application::DEVELOPMENT;
        }

        $env = strtoupper($env);

        // Set environment variable if a 'APP_ENV' environment variable has been supplied.
        defined(__CLASS__.'::'.$env) && $this->environment = constant(__CLASS__.'::'.$env);

        // Enable xdebug parameter collection in development mode to improve fatal stack traces.
        if (Application::DEVELOPMENT ===  $this->environment && extension_loaded('xdebug')) {
            ini_set('xdebug.collect_params', 4);
        }

        if (Application::DEVELOPMENT ===  $this->environment) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL | E_STRICT);
        } else {
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            error_reporting(E_ALL ^ E_NOTICE);
        }

        if (!is_file(DOCROOT . '/vendor/autoload.php')) {
            throw new RuntimeException(sprintf(
                'Composer autoloader not found. Try to run "composer install" at :%s',
                DOCROOT
            ));
        }

        // Include Composer autoloader
        require_once(DOCROOT . '/vendor/autoload.php');
    }
}
