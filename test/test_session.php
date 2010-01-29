<?php
/**
 * tests for Session functionality
 */
// core
require_once("../core/Data.php");
require_once("../core/Error.php");
require_once("../core/Errors.php");
require_once("../core/Object.php");
require_once("../core/Validator.php");
require_once("../core/Session.php");
require_once("../core/Test.php");
// models
require_once("./TestObject.php");
require_once("./Title_old.php");

define(USERPREFSSALT, "3.141592654321isnotquitepi");

session_start();

$db = mysql_connect("127.0.0.1", "root", "");
mysql_select_db("fcc_prefs_dev_test", $db);
$E = Errors::instance(3);

// dynamically load models because they might change from time to time
$userprefs_generated_models_dir = "../models/generated";
$userprefs_models_dir = "../models";
function loadFromDir($dir)
{
	$contents = scandir($dir);
	foreach($contents as $file)
	{
		$path = $dir . "/" . $file;
		if(is_file($path))
		{
			if(false !== stripos($file, '.php'))
			{
				require_once($path);
			}
		}
	}
}
loadFromDir($userprefs_generated_models_dir);
loadFromDir($userprefs_models_dir);

class SessionTest extends Test
{
	private $testuser_id;
	
	public function beforeTests()
	{
		$user = new Users(false, "adam.fisher@tobias.tv", "test", 1, 1, 1, 'adamfisher', 2, "answer");
		$user->insert();
		$this->testuser_id = $user->getUserId();
	}
	
	public function test_login()
	{
		$session = new Session();
		$this->assertFalse($session->isLoggedIn(), "before login, session should not be logged in");
		$this->assertTrue($session->login('adam.fisher@tobias.tv', 'test'), "Login in should have worked with test user");
		$this->assertTrue($session->isLoggedIn(), "login should be succesful");
		$this->assertEqual($session->get("userId"), $this->testuser_id, "Session userid should match the user created in beforeTests");
		
		// test that we get the user's details ok from the session
	}
		
	public function test_existingSession()
	{
		$session = new Session();
		$this->assertTrue($session->isLoggedIn(), "session should auto-login");
		$this->assertEqual($session->get("userId"), $this->testuser_id, "Session userid should match the user created in beforeTests");
	}
	
	public function test_logout()
	{
		$session = new Session();
		$this->assertTrue($session->isLoggedIn(), "login should be succesful");
		$this->assertTrue($session->logout(), "logout should be succesful");
		$this->assertFalse($session->isLoggedIn(), "session should reflect succesful logout");
		$this->assertNull($session->get("userId"), "Session userid should have been unset in the logout");
	}
	
	public function test_existingLoggedOutSession()
	{
		$session = new Session();
		$this->assertFalse($session->isLoggedIn(), "new session post-login should be logged out");
	}
	
	public function test_legacyUsernameLogin()
	{
		$session = new Session();
		$this->assertTrue($session->login('adamfisher', 'test'), "should be able to log in using the legacy username");
		$this->assertTrue($session->isLoggedIn(), "login should be succesful");
		$this->assertEqual($session->get("userId"), $this->testuser_id, "Session userid should match the user created in beforeTests");
	}
	
	public function afterTests()
	{
		$session = new Session();
		$session->logout();
		$user = new Users($this->testuser_id);
		$user->delete();
	}
}
ob_start();
$object_test = new SessionTest();
$object_test->run();
ob_end_flush();
echo $E->printErrors();