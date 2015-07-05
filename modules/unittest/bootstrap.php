<?php

$application = 'application';
$modules     = 'modules';
$gleez       = 'modules/gleez';
$system      = 'system';
$themes      = 'themes';

define('EXT', '.php');
define('DS', DIRECTORY_SEPARATOR);
define('DOCROOT', realpath(dirname(__FILE__).'/../../').DS);

error_reporting(E_ALL | E_STRICT);

// Make the application relative to the docroot, for symlink'd index.php
if (!is_dir($application) && is_dir(DOCROOT.$application)) {
    $application = DOCROOT.$application;
}

// Make the modules relative to the docroot, for symlink'd index.php
if (!is_dir($modules) && is_dir(DOCROOT.$modules)) {
    $modules = DOCROOT.$modules;
}

// Make the gleez relative to the docroot, for symlink'd index.php
if (!is_dir($gleez) && is_dir(DOCROOT.$gleez)) {
    $gleez = DOCROOT.$gleez;
}

// Make the system relative to the docroot, for symlink'd index.php
if (!is_dir($system) && is_dir(DOCROOT.$system)) {
    $system = DOCROOT.$system;
}

// Make the themes relative to the docroot
if (!is_dir($themes) && is_dir(DOCROOT.$themes)) {
    $themes = DOCROOT.$themes;
}

// Define the absolute paths for configured directories
define('APPPATH', realpath($application).DS);
define('MODPATH', realpath($modules).DS);
define('GLZPATH', realpath($gleez).DS);
define('SYSPATH', realpath($system).DS);
define('THEMEPATH', realpath($themes).DS);

// Clean up the configuration vars
unset($application, $modules, $system, $themes);

defined('GLEEZ_START_TIME') || define('GLEEZ_START_TIME', microtime(true));
defined('GLEEZ_START_MEMORY') || define('GLEEZ_START_MEMORY', memory_get_usage());

// Bootstrap the application
require APPPATH.'bootstrap'.EXT;

// Disable output buffering
if (false !== ($ob_len = ob_get_length())) {
    // flush_end on an empty buffer causes headers to be sent. Only flush if needed.
    if ($ob_len > 0) {
        ob_end_flush();
    } else {
        ob_end_clean();
    }
}

// Enable the unittest module
Kohana::modules(Kohana::modules() + ['unittest' => MODPATH.'unittest']);
