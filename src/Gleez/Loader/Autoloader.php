<?php
/**
 * Gleez CMS (http://gleezcms.org)
 *
 * @link      https://github.com/gleez/cms Canonical source repository
 * @copyright Copyright (c) 2011-2015 Gleez Technologies
 * @license   http://gleezcms.org/license Gleez CMS License
 */

namespace Gleez\Loader;

// Grab Autoloadable interface
require_once __DIR__ . '/Autoloadable.php';

/**
 * PSR-4 compliant autoloader
 *
 * Provides autoloading classes by checking their name and search files in the
 * file system. Supports the following strategies:
 *
 * - Load from include_path (fallback-mode)
 * - Load from a list of 'Namespace => Path' pairs
 * - Load from a list of 'Prefix => Path' pairs
 *
 * Search in the include_path (fallback-mode) is slowest and is used as a last
 * resort. By default, this search is disabled. To use it call
 * Autoloader::setFallback and pass true as parameter.
 *
 * Examples:
 * <code>
 * // Creating an Autoloader object
 * $autoloader = new Autoloader();
 *
 * // Creating an Autoloader object with some configuration
 * $autoloader = new Autoloader($config);
 *
 * // Creating an Autoloader object with autoregister Gleez lib
 * $autoloader = new Autoloader(array('autoregister' => true));
 *
 * // Simple way with one namespace
 * $autoloader->setNamespaces('Zend', '/usr/includes/Zend');
 *
 * // Setting multiple namespaces
 * $autoloader->setNamespaces(array(
 *     'Aura\Web' => '/path/to/aura-web/src',
 *     'Gleez'    => '/path/to/Gleez'
 * ));
 *
 * // Enabling fallback-mode
 * $autoloader->setFallback(true);
 *
 * // Enabling search classes with prefixes
 * $autoloader->setPrefixes(array(
 *     'Phly_'  => '/path/to/Phly',
 *     'Gleez_' => '/path/to/Gleez',
 * ));
 *
 * // Register this instance as an autoloader
 * $autoloader->register();
 * </code>
 *
 * @package  Gleez\Loader
 * @author   Gleez Team
 * @version  1.0.2
 */
class Autoloader implements Autoloadable
{
    /**
     * Namespace separator
     * @type string
     */
    const NS_SEPARATOR = '\\';

    /**
     * Prefix separator
     * @type string
     */
    const PR_SEPARATOR = '_';

    /**
     * Label to indicate 'Namespace => Path' pairs
     * @type string
     */
    const LOAD_NS = 'namespaces';

    /**
     * Label to indicate 'Prefix => Path' pairs
     * @type string
     */
    const LOAD_PR = 'prefixes';

    /**
     * Label to indicate fallback-mode
     * @type string
     */
    const FALLBACK = 'fallback';

    /**
     * Label to indicate autoregister Gleez lib
     * @type string
     */
    const AUTOREGISTER = 'autoregister';

    /**
     * Autoloader version
     * @type string
     */
    const VERSION = '1.0.2';

    /**
     * List of 'Namespace => Path' pairs for search
     * @var array
     */
    protected $namespaces = array();

    /**
     * List of 'Prefix => Path' pairs for search
     * @var array
     */
    protected $prefixes = array();

    /**
     * Use fallback-mode?
     *
     * Using fallback-mode in the first place means that the search for classes
     * must be made in the include_path
     * @var bool
     */
    protected $fallback = false;

    /**
     * Class constructor
     *
     * Defined by Autoloadable. Allows to configure the autoloader during
     * creation of the object.
     *
     * @param null|array|\Traversable $config Autoloader configuration [Optional]
     */
    public function __construct($config = null)
    {
        if (!is_null($config)) {
            $this->setConfig($config);
        }
    }

    /**
     * Autoload classes
     *
     * Defined by Autoloadable.
     *
     * @param  string $class Class name
     *
     * @return bool   false if unable to load $class
     * @return string Class name if $class is successfully loaded
     */
    public function autoload($class)
    {
        if (false !== strpos($class, self::NS_SEPARATOR)) {
            if ($this->loadClass($class, self::LOAD_NS)) {
                return $class;
            } elseif ($this->isFallback()) {
                return $this->loadClass($class, self::FALLBACK);
            }

            return false;
        }

        if (false !== strpos($class, self::PR_SEPARATOR)) {
            if ($this->loadClass($class, self::LOAD_PR)) {
                return $class;
            } elseif ($this->isFallback()) {
                return $this->loadClass($class, self::FALLBACK);
            }

            return false;
        }

        if ($this->isFallback()) {
            return $this->loadClass($class, self::FALLBACK);
        }

        return false;
    }

    /**
     * Registers this instance as an autoloader
     *
     * Defined by Autoloadable.
     *
     * @param bool $prepend Whether to prepend the autoloader or not
     */
    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'autoload'), false, $prepend);
    }

    /**
     * Unregisters this instance as an autoloader
     *
     * Defined by Autoloadable.
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'autoload'));
    }

    /**
     * Loads the given class or interface, based on its type
     *
     * @param  string $class Class name
     * @param  string $type  Load mode (namespace, prefix, fallback-mode)
     *
     * @return bool|string
     */
    protected function loadClass($class, $type)
    {
        // Autoload in fallback-mode
        if ($type === self::FALLBACK) {
            $filename      = $this->getFilenameFromClassname($class, '');
            $resolved_path = stream_resolve_include_path($filename);

            if (false !== $resolved_path && file_exists($resolved_path)) {
                return include $resolved_path;
            }

            return false;
        }

        // Autoload with using namespaces and/or prefixes
        foreach ($this->$type as $leader => $path) {
            if (0 === strpos($class, $leader)) {
                // Cut namespace or prefix
                $trimmed = substr($class, strlen($leader));

                // Create filename
                $filename = $this->getFilenameFromClassname($trimmed, $path);

                if (file_exists($filename)) {
                    return include $filename;
                }

                return false;
            }
        }

        return false;
    }

    /**
     * Checks whether the current mode is fallback
     * (loading for include_path strategy)
     *
     * @return bool
     */
    public function isFallback()
    {
        return (bool) $this->fallback;
    }

    /**
     * Sets fallback mode
     * (loading for include_path strategy)
     *
     * @param  bool $flag true/false or, for example 1/0
     *
     * @return Autoloader
     */
    public function setFallback($flag)
    {
        $this->fallback = (bool) $flag;

        return $this;
    }

    /**
     * Sets configuration for Autoloader
     *
     * Defined by Autoloadable.
     *
     * Allows to define a namespaces (LOAD_NS) and prefixes (LOAD_PR)
     * using the following structure:
     * <code>
     * array(
     *     'namespaces' => array(
     *       'MyLib1' => '/path/to/MyLib1/lib',
     *       'MyLib2' => '/path/to/MyLib2/lib',
     *       'MyLib3' => '/path/to/MyLib3/lib',
     *     ),
     *     'prefixes' => array(
     *       'Gleez_' => '/path/to/Gleez',
     *       'Zend_'  => '/path/to/Zend',
     *       'Phly_'  => '/path/to/Phly'
     *     ),
     *     'fallback' => true,
     * )
     * </code>
     *
     * @param array|\Traversable $config Autoloader configuration
     *
     * @return Autoloader
     */
    public function setConfig($config)
    {
        if (is_array($config) || $config instanceof \Traversable) {
            foreach ($config as $type => $pairs) {
                switch ($type) {
                    case self::AUTOREGISTER:
                        if ($pairs) {
                            $this->setNamespaces('Gleez', dirname(__DIR__));
                        }
                    case self::LOAD_NS:
                        if (is_array($pairs) || $pairs instanceof \Traversable) {
                            $this->setNamespaces($pairs);
                        }
                        break;
                    case self::LOAD_PR:
                        if (is_array($pairs) || $pairs instanceof \Traversable) {
                            $this->setPrefixes($pairs);
                        }
                        break;
                    case self::FALLBACK:
                        $this->setFallback($pairs);
                        break;
                    default:
                        // Ignore
                }
            }
        }

        return $this;
    }

    /**
     * Sets prefix/path pair(s)
     *
     * System of prefixes is used for classes that do not use the namespace,
     * ie for those classes for which directories are separated by underscores.
     * For example Kohana_Request_Client_HTTP.
     *
     * Example:
     * <code>
     * // Set one prefix
     * $this->setPrefixes('Gleez_', '/path/to/Gleez');
     *
     * // Set multiple prefixes
     * $this->setPrefixes(array(
     *     'Gleez_' => '/path/to/Gleez',
     *     'Zend_'  => '/path/to/Zend',
     *     'Phly_'  => '/path/to/Phly'
     * ));
     * </code>
     *
     * @param  mixed  $pr   Prefix or an array of pairs
     * @param  string $path Path, if $pr is string [Optional]
     *
     * @return Autoloader
     */
    public function setPrefixes($pr, $path = null)
    {
        if (is_null($path)) {
            if (is_array($pr) || $pr instanceof \Traversable) {
                foreach ($pr as $key => $value) {
                    $this->setPrefixes($key, $value);
                }
            }
        } else {
            // Remove any trailing slashes (protection from repetitions)
            // and add one to the end
            $pr = rtrim($pr, self::PR_SEPARATOR) . self::PR_SEPARATOR;

            // Save pair
            $this->prefixes[$pr] = rtrim($path, '/\\').DIRECTORY_SEPARATOR;
        }

        return $this;
    }

    /**
     * Gets current prefixes
     *
     * Gets current prefixes and corresponding registered paths
     * like 'Prefix => Path'. System of prefixes is used for classes that
     * do not use the namespace, ie for those classes for which directories
     * are separated by underscores. For example Kohana_Request_Client_HTTP.
     *
     * Example:
     * <code>
     * $this->getPrefixes();
     * </code>
     *
     * @return array
     */
    public function getPrefixes()
    {
        return $this->prefixes;
    }

    /**
     * Sets namespace/path pair(s)
     *
     * Example:
     * <code>
     * // Set one namespace
     * $this->setNamespaces('MyLib', '/path/to/MyLib/lib');
     *
     * // Set multiple namespaces
     * $this->setNamespaces(array(
     *     'MyLib1' => '/path/to/MyLib1/lib',
     *     'MyLib2' => '/path/to/MyLib2/lib',
     *     'MyLib3' => '/path/to/MyLib3/lib'
     * ));
     * </code>
     *
     * @param  mixed  $ns   Namespace or array of namespace
     * @param  string $path Path, if $ns is string [Optional]
     *
     * @return Autoloader
     */
    public function setNamespaces($ns, $path = null)
    {
        if (is_null($path)) {
            if (is_array($ns) || $ns instanceof \Traversable) {
                foreach ($ns as $key => $value) {
                    $this->setNamespaces($key, $value);
                }
            }
        } else {
            // Remove any trailing slashes (protection from repetitions)
            // and add one to the end
            $ns = rtrim($ns, self::NS_SEPARATOR) . self::NS_SEPARATOR;

            // Save pair
            $this->namespaces[$ns] = rtrim($path, '/\\').DIRECTORY_SEPARATOR;
        }

        return $this;
    }

    /**
     * Gets current namespaces
     *
     * Gets current namespaces and corresponding registered paths
     * like 'Namespace => Path'
     *
     * Example:
     * <code>
     * $this->getNamespaces();
     * </code>
     *
     * @return array
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }


    /**
     * Gets filename from classname
     *
     * Example:
     * <code>
     * $class = '\Class\My\One_World';
     * $dir   = 'Gleez';
     *
     * $this->getFilenameFromClassname($class, $dir);
     * // Returns 'Gleez/Class/My/One/World.php'
     * </code>
     *
     * @param  string $class Class name
     * @param  string $dir   Directory
     *
     * @return string
     */
    protected function getFilenameFromClassname($class, $dir)
    {
        // $class may contain a namespace portion, in  which case we need
        // to preserve any underscores in that portion.

        $matches = array();

        preg_match('#(?P<namespace>.+\\\)?(?P<class>[^\\\]+$)#', $class, $matches);

        $class     = (isset($matches['class']))     ? $matches['class']     : '';
        $namespace = (isset($matches['namespace'])) ? $matches['namespace'] : '';

        return $dir
            . str_replace(self::NS_SEPARATOR, DIRECTORY_SEPARATOR, $namespace)
            . str_replace(self::PR_SEPARATOR, DIRECTORY_SEPARATOR, $class)
            . '.php';
    }
}
