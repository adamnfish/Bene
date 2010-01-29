<?php

require_once("../core/Data.php");
require_once("../core/Error.php");
require_once("../core/Errors.php");
require_once("../core/Object.php");
require_once("../core/Validator.php");
require_once("../core/Form.php");
require_once("../core/Test.php");
require_once("./TestObject.php");

class Object_Test extends Test
{
	public function setup()
	{
	}
	
	public function teardown()
	{
	}
	
	public function test_test()
	{
		$this->assert(true, "Assert should pass");
		$this->assertEqual(1, 1, "assertEqual should pass");
		$this->assertCount(3, 3, "assertCount should pass");
		
		$this->assertTrue(false, "Assert should fail", true);
		echo "test";
	}
}

$object_test = new Object_Test();
$object_test->run();

?>