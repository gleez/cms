<?php
/**
 * Gleez CMS (http://gleezcms.org)
 *
 * @link      https://github.com/gleez/cms Canonical source repository
 * @copyright Copyright (c) 2011-2015 Gleez Technologies
 * @license   http://gleezcms.org/license Gleez CMS License
 */

namespace Gleez\Tests\Loader;

use Gleez\Loader\Autoloader;
use ReflectionClass;

/**
 * Gleez Autoloader Test
 *
 * @package  Gleez\Loader\UnitTest
 * @author   Gleez Team
 * @version  1.0.2
 */
class AutoloaderTest extends \PHPUnit_Framework_TestCase
{
    protected $loaders;
    protected $includePath;

    public function setUp()
    {
        // Store original autoloaders
        $this->loaders = spl_autoload_functions();

        if (!is_array($this->loaders)) {
            // spl_autoload_functions does not return empty array when no
            // autoloaders registered...
            $this->loaders = array();
        }

        // Store original include_path
        $this->includePath = get_include_path();
    }

    public function tearDown()
    {
        // Restore original autoloaders
        $loaders = spl_autoload_functions();

        if (is_array($loaders)) {
            foreach ($loaders as $loader) {
                spl_autoload_unregister($loader);
            }
        }

        foreach ($this->loaders as $loader) {
            spl_autoload_register($loader);
        }

        // Restore original include_path
        set_include_path($this->includePath);
    }

    public function testFallbackIsDisabledByDefault()
    {
        $loader = new Autoloader();

        $this->assertFalse($loader->isFallback());
    }

    public function testFallbackStateIsMutable()
    {
        $loader = new Autoloader();

        $loader->setFallback(true);
        $this->assertTrue($loader->isFallback());
        $loader->setFallback(false);
        $this->assertFalse($loader->isFallback());
    }

    public function testNamespacesIsEmptyByDefault()
    {
        $loader = new Autoloader();

        $expected = array();
        $this->assertAttributeEquals($expected, 'namespaces', $loader);
    }

    public function testPrefixesIsEmptyByDefault()
    {
        $loader = new Autoloader();

        $expected = array();
        $this->assertAttributeEquals($expected, 'prefixes', $loader);
    }

    public function testReturnsFalseForMissingFile()
    {
        $loader = new Autoloader();
        $loader->setFallback(true);

        $this->assertFalse($loader->autoload('Some/Invalid/Classname.php'));
    }

    public function testReturnsFalseForInvalidClassname()
    {
        $loader = new Autoloader();
        $loader->setFallback(true);

        $this->assertFalse($loader->autoload('Some\Invalid\Classname\\'));
    }

    public function testCanEnableFallbackMode()
    {
        $loader = new Autoloader();
        $loader->setFallback(true);

        set_include_path(__DIR__ . '/TestLib/' . PATH_SEPARATOR . $this->includePath);
        $loader->autoload('TestNamespace\FallbackCase');
        $this->assertTrue(class_exists('TestNamespace\FallbackCase', false));
    }

    public function testCanRegisterAndUnregister()
    {
        $loader = new Autoloader();
        $loader->register();

        $loaders = spl_autoload_functions();
        $this->assertTrue(count($this->loaders) < count($loaders));

        list($actual_object, $actual_method) = array_pop($loaders);
        $this->assertSame($loader,    $actual_object);
        $this->assertSame('autoload', $actual_method);

        $loader->unregister();
        $loaders = spl_autoload_functions();
        $this->assertEquals($loaders, $this->loaders);
    }

    public function testCanAutoregisterAtInstantiation()
    {
        $loader   = new Autoloader(array('autoregister' => true));
        $r        = new ReflectionClass($loader);

        $file     = $r->getFileName();
        $expected = array('Gleez\\' => dirname(dirname($file)) . DIRECTORY_SEPARATOR);

        $this->assertAttributeEquals($expected, 'namespaces', $loader);
    }

    public function testCanLoadWithUnderscores()
    {
        $loader = new Autoloader();
        $expected = array(
            'Gleez\Tests\Unusual\\' => dirname(__FILE__). DIRECTORY_SEPARATOR .'TestLib' . DIRECTORY_SEPARATOR
        );

        $loader->setNamespaces('Gleez\Tests\Unusual', dirname(__FILE__). DIRECTORY_SEPARATOR .'TestLib');
        $loader->autoload('Gleez\Tests\Unusual\Underscored_Name\Underscored_Class');

        $this->assertEquals($loader->getNamespaces(), $expected);
        $this->assertTrue(class_exists('Gleez\Tests\Unusual\Underscored_Name\Underscored_Class', false));
    }

    public function testCanLoadPrefixedClass()
    {
        $loader = new Autoloader();

        $loader->setPrefixes('Unusual_Prefix', dirname(__FILE__). DIRECTORY_SEPARATOR .'TestLib');

        $loader->autoload('Unusual_Prefix_Prefixed');
        $this->assertTrue(class_exists('Unusual_Prefix_Prefixed', false));
    }

    public function testCanPopulateConfigUsingArray()
    {
        $config = array(
            Autoloader::LOAD_NS => array(
                'Gleez\\' => dirname(__DIR__) . DIRECTORY_SEPARATOR,
            ),
            Autoloader::LOAD_PR   => array(
                'Gleez_' => dirname(__DIR__) . DIRECTORY_SEPARATOR,
            ),
            Autoloader::FALLBACK => true,
        );

        require_once dirname(__FILE__). DIRECTORY_SEPARATOR .'TestLib' . DIRECTORY_SEPARATOR . 'Autoloader.php';
        $loader = new TestLib\Autoloader();
        $loader->setConfig($config);

        $this->assertEquals($loader->getConfig(), $config);
    }

    public function testCanPopulateConfigUsingTraversableObject()
    {
        $namespaces = new \ArrayObject(array(
            'Gleez\\' => dirname(__DIR__) . DIRECTORY_SEPARATOR,
        ));
        $prefixes = new \ArrayObject(array(
            'Gleez_' => dirname(__DIR__) . DIRECTORY_SEPARATOR,
        ));
        $config = new \ArrayObject(array(
            Autoloader::LOAD_NS  => $namespaces,
            Autoloader::LOAD_PR  => $prefixes,
            Autoloader::FALLBACK => true,
        ));

        $loader = new Autoloader();
        $loader->setConfig($config);

        $this->assertEquals((array) $config['namespaces'], $loader->getNamespaces());
        $this->assertEquals((array) $config['prefixes'], $loader->getPrefixes());
        $this->assertTrue($loader->isFallback());
    }

    public function testNotThrowsExceptionWhenInvalidConfig()
    {
        $bar      = new \stdClass();
        $configs  = array('foo', 100, false, time(), '', $bar);
        $expected = array(
            Autoloader::LOAD_NS  => array(),
            Autoloader::LOAD_PR  => array(),
            Autoloader::FALLBACK => false
        );

        $loader = new TestLib\Autoloader();
        foreach ($configs as $config) {
            try {
                $loader->setConfig($config);

                $this->assertEquals($loader->getConfig(), $expected);
            } catch (\Exception $e) {
                $this->fail('Not expected any exception here.');
            }
        }
    }

    public function testNotThrowsExceptionWhenInvalidPrefixes()
    {
        $loader   = new Autoloader();
        $bar      = new \stdClass();
        $prefixes = array('foo', 100, false, time(), '', $bar);
        $expected = array();

        foreach ($prefixes as $prefix) {
            try {
                $loader->setPrefixes($prefix);
            } catch (\Exception $e) {
                $this->fail('Not expected any exception here.');
            }
        }

        $this->assertAttributeEquals($expected, 'prefixes', $loader);
    }

    public function testNotThrowsExceptionWhenInvalidNamespaces()
    {
        $loader     = new Autoloader();
        $bar        = new \stdClass();
        $namespaces = array('foo', 100, false, time(), '', $bar);
        $expected   = array();

        foreach ($namespaces as $namespace) {
            try {
                $loader->setNamespaces($namespace);
            } catch (\Exception $e) {
                $this->fail('Not expected any exception here.');
            }
        }

        $this->assertAttributeEquals($expected, 'namespaces', $loader);
    }
}
