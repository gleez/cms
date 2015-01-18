<?php
/**
 * Gleez CMS (http://gleezcms.org)
 *
 * @link      https://github.com/gleez/cms Canonical source repository
 * @copyright Copyright (c) 2011-2015 Gleez Technologies
 * @license   http://gleezcms.org/license Gleez CMS License
 */

namespace Gleez\Loader;

use Traversable;

// If already registered
if (interface_exists(__NAMESPACE__ . '\Autoloadable')) return;

/**
 * Autoloader interface
 *
 * Defining an interface for classes that can be registered using spl_autoload.
 *
 * @package  Gleez\Loader
 * @author   Gleez Team
 * @version  1.0.1
 */
interface Autoloadable
{
    /**
     * Class constructor
     *
     * Allows to configure the autoloader during creation of the object.
     *
     * @param null|array|\Traversable $config Autoloader configuration [Optional]
     */
    public function __construct($config = null);

    /**
     * Sets configuration for Autoloader
     *
     * @param array|\Traversable $config Autoloader configuration
     */
    public function setConfig($config);

    /**
     * Autoload classes
     *
     * @param  string $class Class name
     *
     * @return bool   false if unable to load $class
     * @return string Class name if $class is successfully loaded
     */
    public function autoload($class);

    /**
     * Registers this instance as an autoloader
     *
     * Typical contents of this method might look like this:
     * <code>
     * spl_autoload_register(array($this, 'autoload'), true, $prepend);
     * </code>
     *
     * @param bool $prepend Whether to prepend the autoloader or not
     */
    public function register($prepend = false);

    /**
     * Unregisters this instance as an autoloader
     *
     * Typical contents of this method might look like this:
     * <code>
     * spl_autoload_unregister(array($this, 'autoload'));
     * </code>
     */
    public function unregister();
}
