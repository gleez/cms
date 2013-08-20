<?php
/**
 * Tests the Config lib that's shipped with kohana
 *
 * @group Gleez
 * @group Gleez.core
 * @group Gleez.core.acl
 *
 */
class Gleez_Acltest extends Unittest_TestCase
{
	public function providerPerms()
	{
		return array(
			array('administer site', 2),
			array('view page', 1),
		);
	}
	
	/**
	 * @dataProvider providerPerms
	 */
	public function testacl_check($perm, $user_id)
	{
		$user = ORM::factory('user', $user_id);

		if ($user_id == 1)
		{
			$this->assertFalse(ACL::check($perm, $user));
		}
		else
		{
			$this->assertTrue(ACL::check($perm, $user));
		}
		
	}
	
	/**
	 * If Route::cache() was able to restore routes from the cache then
	 * it should return TRUE and load the cached routes
	 *
	 * @test
	 * @covers Route::cache
	 */
	public function test_cache_stores_route_objects()
	{
		$acls = ACL::all();

		// First we create the cache
		ACL::cache(TRUE);

		// Now lets modify the "current" routes
		ACL::set('contact', array(
			'sending mail' => array(
				'title' => __('Sending Mails'),
				'restrict access' => FALSE,
				'description' => __('Ability to send messages for administrators from your site'),
			),
		));

		// Then try and load said cache
		$this->assertTrue(ACL::cache());

		// Check the route cache flag
		$this->assertTrue(ACL::$cache);

		// And if all went ok the nonsensical route should be gone...
		$this->assertEquals($acls, ACL::all());
	}
}

