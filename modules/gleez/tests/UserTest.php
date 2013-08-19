<?php
/**
 * Tests the Config lib that's shipped with kohana
 *
 * @group Gleez
 * @group Gleez.core
 * @group Gleez.core.user
 *
 */
class Gleez_Usertest extends Unittest_TestCase
{
	public function providerUsers()
	{
		return array(
			array(array("name" => "admin", "password" => "gleez1co")),
			array(array("name" => "sundar", "password" => "gleez1co"))
		);
	}
	/**
	 * @dataProvider providerUsers
	 */
	public function testValidUsers($info)
	{
		$user = ORM::factory('user');
		$result = $user->login($info);
		$this->assertInstanceOf('Model_user', $result);
	}
	
	/**
	 * @expectedException Validation_Exception
	 */
	public function testInvalidUsers()
	{
		$user = ORM::factory('user');
		$user->login(array('name' => 'sundar1', 'password' => 'gleez1co'));
	}
}
