<?php
/**
 * Tests the Config lib that's shipped with kohana
 *
 * @group Gleez
 * @group Gleez.core
 * @group Gleez.core.config
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @author     Matt Button <matthew@sigswitch.com>
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Gleez_ConfigTest extends Unittest_TestCase {

	public function provider()
	{
		return array(
		    array('dummy'),
		    array('site')
		);
	}
	
	/**
	* @dataProvider provider
	*/
	public function testconfig_get($config_name)
	{	
		$kohana_dummy = Kohana::$config->load($config_name);

		
		$gleez_dummy = Config::get($config_name);
		
		$this->assertSame($kohana_dummy, $gleez_dummy);
	}
	
	
	public function providerConfigvariable()
	{
		return array(
		    array('dummy', 'currency'),
		    array('site', 'date_format'),
		);
	}
	
	/**
	 * @dataProvider providerConfigvariable
	 */
	public function testconfig_get_config_variable($config_name, $config_variable)
	{
		$kohana_dummy_variable = Kohana::$config->load($config_name.'.'.$config_variable);
		
		$kohana_dummy = Kohana::$config->load($config_name);
		$kohana_variable = $kohana_dummy->$config_variable;
		
		$gleez_dummy = Config::get($config_name.'.'.$config_variable);
		
		$this->assertSame($kohana_dummy_variable, $gleez_dummy);
		$this->assertSame($kohana_variable, $gleez_dummy);
	}
	
	public function testkohanaconfig_get_variable_not_available()
	{
		$dummy = Kohana::$config->load('site');
		$this->assertFalse($dummy->novariable);
	}
	
	public function testconfig_get_variable_not_available()
	{
		$variable = Config::get('dummy.novariable');
		$this->assertNull($variable);
	}
	
	public function providerDefaults()
	{
		return array(
		    array('dummy', 'getvariable', 2),
		    array('dummy', 'getvariable', 'default'),
		    array('dummy', 'getvariable', NULL)
		);
	}
	
	/**
	 * @dataProvider providerDefaults
	 */
	public function testkohanaconfig_get_default_for_variable_not_available($config, $variable, $default)
	{
		$dummy = Kohana::$config->load($config);
		$this->assertSame($default, $dummy->get($variable, $default));
	}
	
	/**
	 * @dataProvider providerDefaults
	 */
	public function testconfig_get_default_for_variable_not_available($config, $variable, $default)
	{
		$value = Config::get($config.'.'.$variable, $default);
		$this->assertSame($default, $value);
	}
	
	public function providerSetDefaults()
	{
		return array(
		    array('dummy', 'setvariable', 2),
		    array('dummy', 'setvariable', 'default'),
		    array('dummy', 'setvariable', NULL)
		);
	}
	
	/**
	 * @dataProvider providerSetDefaults
	 */
	public function testkohanadefault_set($config, $variable, $default)
	{
		$dummy = Kohana::$config->load($config);
		$dummy->set($variable, $default);
		
		$this->assertSame($default, config::get($config.'.'.$variable));
	}
	
	public function providerNoconfig()
	{
		return array(
			array('dummy1'),
			array('dummy2'),
		);
	}
	
	/**
	 *@dataProvider providerNoconfig
	 */
	public function testNo_config_present($config_name)
	{
		$this->assertEmpty(Config::get($config_name)->as_array());
	}
}
