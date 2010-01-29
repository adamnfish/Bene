<?php
/**
 * This constitutes the outline of a unit test for Object
 */
// core
require_once("../core/Data.php");
require_once("../core/Error.php");
require_once("../core/Errors.php");
require_once("../core/Object.php");
require_once("../core/Validator.php");
require_once("../core/Test.php");
// models
require_once("./TestObject.php");
require_once("./Title_old.php");

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

class ValidatorTest extends Test
{
	public function test_validatorMethods()
	{
		$this->assertTrue(Validator::required("test"), "'test' should satisfy the required requirement");
		$this->assertFalse(Validator::required(""), "'' should not satisfy the required requirement");
		$this->assertFalse(Validator::required(null), "null should not satisfy the required requirement");
		$this->assertFalse(Validator::required(false), "null should not satisfy the required requirement");
		
		$this->assertTrue(Validator::minlength('', 5), "'' should satisfy the minlength requirement, because it isn't 'required'");
		$this->assertTrue(Validator::minlength(null, 5), "null should satisfy the minlength requirement, because it isn't 'required'");
		$this->assertTrue(Validator::minlength(false, 5), "false should satisfy the minlength requirement, because it isn't 'required'");
		$this->assertTrue(Validator::minlength("testtest", 5), "'testtest' should satisfy the minlength requirement");
		$this->assertFalse(Validator::minlength("test", 5), "'test' should not satisfy the minlength requirement");

		$this->assertTrue(Validator::maxlength('', 5), "'' should satisfy the maxlength requirement, because it isn't 'required'");
		$this->assertTrue(Validator::maxlength("test", 5), "'test' should satisfy the maxlength requirement");
		$this->assertFalse(Validator::maxlength("testtest", 5), "'testtest' not should satisfy the maxlength requirement");
		
		$this->assertTrue(Validator::rangelength('', array(3, 5)), "'' should satisfy the rangelength requirement, because it isn't 'required'");
		$this->assertTrue(Validator::rangelength("test", array(3, 5)), "'test' should satisfy the rangelength requirement");
		$this->assertFalse(Validator::rangelength("testtest", array(3, 5)), "'testtest' not should satisfy the rangelength requirement");
		
		$this->assertTrue(Validator::min('', 5), "'' should satisfy the min requirement, because it isn't 'required'");
		$this->assertTrue(Validator::min(10, 5), "10 should satisfy the min requirement of 5");
		$this->assertFalse(Validator::min(4, 5), "4 should not satisfy the min requirement of 5");
		$this->assertFalse(Validator::min('a', 5), "'a' should not satisfy the min requirement of 5");
		
		$this->assertTrue(Validator::max('', 5), "'' should satisfy the max requirement, because it isn't 'required'");
		$this->assertTrue(Validator::max(4, 5), "4 should satisfy the max requirement of 5");
		$this->assertFalse(Validator::max(10, 5), "10 should not satisfy the max requirement of 5");
		$this->assertFalse(Validator::max('a', 5), "'a' should not satisfy the max requirement of 5");
		
		$this->assertTrue(Validator::range('', array(5, 10)), "'' should satisfy the range requirement, because it isn't 'required'");
		$this->assertTrue(Validator::range(7, array(5, 10)), "7 should satisfy the range requirement");
		$this->assertFalse(Validator::range(2, array(5, 10)), "2 should not satisfy the range requirement");
		$this->assertFalse(Validator::range(12, array(5, 10)), "12 should not satisfy the range requirement");
		$this->assertFalse(Validator::range('test', array(5, 10)), "'test' should not satisfy the range requirement");
		
		// this email regexp needs looking at, really
		$this->assertTrue(Validator::email(''), "'' should satisfy the email requirement, because it isn't 'required'");
		$this->assertTrue(Validator::email("adam.fisher@tobias.tv"), "'adam.fisher@tobias.tv' should satisfy the email requirement");
		$this->assertTrue(Validator::email("a+dam.fisher@tobias.tv"), "'a+dam.fisher@tobias.tv' should satisfy the email requirement");
		$this->assertFalse(Validator::email("@@@@@adam.fisher@tobias.tv"), "'@@@@@adam.fisher@tobias.tv' not should satisfy the email requirement");
		$this->assertFalse(Validator::email("test.test@"), "'test.test@' not should satisfy the email requirement");
		$this->assertFalse(Validator::email("testtest"), "'testtest' not should satisfy the email requirement");

		// url regexp similarly needs looking at
		$this->assertTrue(Validator::url(''), "'' should satisfy the url requirement, because it isn't 'required'");
		$this->assertTrue(Validator::url("http://www.example.com"), "'http://www.example.com' should satisfy the url requirement");
		$this->assertTrue(Validator::url("https://www.example.com/mypage.html"), "'https://www.example.com/mypage.html' should satisfy the url requirement");
		$this->assertTrue(Validator::url("https://www.example.com/mypage.html?test=1&test2"), "'https://www.example.com/mypage.html?test=1&test2' should satisfy the url requirement");
		$this->assertTrue(Validator::url("https://www.example.com/mypage.html?test=1&test2#test"), "'https://www.example.com/mypage.html?test=1&test2#test' should satisfy the url requirement");
		$this->assertTrue(Validator::url("https://www.example.com/mypage.html#test"), "'https://www.example.com/mypage.html#test' should satisfy the url requirement");
		$this->assertFalse(Validator::url("test.test@"), "'test.test@' not should satisfy the url requirement");
		$this->assertFalse(Validator::url("testtest"), "'testtest' not should satisfy the url requirement");
		
		$this->assertTrue(Validator::date(''), "'' should satisfy the date requirement, because it isn't 'required'");
		$this->assertTrue(Validator::date('12th November 2001'), "'12th November 2001' should satisfy the date requirement");
		$this->assertTrue(Validator::date('2001/11/07'), "'2001/11/07' should satisfy the date requirement");
		$this->assertFalse(Validator::date('2001/15/07'), "'2001/15/07' should not satisfy the date requirement");
		$this->assertFalse(Validator::date('testtest'), "'testtest' should not satisfy the date requirement");

		$this->assertTrue(Validator::dateISO(''), "'' should satisfy the ISOdate requirement, because it isn't 'required'");
		$this->assertTrue(Validator::dateISO('2001/11/07'), "'2001/11/07' should satisfy the ISOdate requirement");
		$this->assertFalse(Validator::dateISO('12th November 2001'), "'12th November 2001' should not satisfy the ISOdate requirement");
		$this->assertFalse(Validator::dateISO('2001/15/07'), "'2001/15/07' should not satisfy the ISOdate requirement");
		$this->assertFalse(Validator::dateISO('testtest'), "'testtest' should not satisfy the ISOdate requirement");

		$this->assertTrue(Validator::number(''), "'' should satisfy the number requirement, because it isn't 'required'");
		$this->assertTrue(Validator::number(45), "45 should satisfy the number requirement");
		$this->assertTrue(Validator::number(-5.68), "-5.68 should satisfy the number requirement");
		$this->assertTrue(Validator::number("5568"), "'5568' should satisfy the number requirement");
		$this->assertTrue(Validator::number(4e7), "4e7 should satisfy the number requirement");
		$this->assertTrue(Validator::number("1.14e25"), "1.14e25 should satisfy the number requirement");
		$this->assertFalse(Validator::number("testtest"), "'testtest' not should satisfy the number requirement");

		$this->assertTrue(Validator::digits(''), "'' should satisfy the digits requirement, because it isn't 'required'");
		$this->assertTrue(Validator::digits(45), "45 should satisfy the digits requirement");
		$this->assertTrue(Validator::digits(-6), "-6 should satisfy the digits requirement");
		$this->assertTrue(Validator::digits("45"), "'45' should satisfy the digits requirement");
		$this->assertfalse(Validator::digits(7.4), "7.4 should not satisfy the digits requirement");
		$this->assertFalse(Validator::digits("test"), "'test' should not satisfy the digits requirement");
		
		$this->assertTrue(Validator::accept('', array("one", "two")), "'' should satisfy the accepts requirement, because it isn't 'required'");
		$this->assertTrue(Validator::accept("one", array("one", "two")), "'one' should satisfy the accepts requirement");
		$this->assertTrue(Validator::accept("one", "one,two"), "'one' should satisfy the accepts requirement");
		$this->assertTrue(Validator::accept(1, "1,one,two"), "'one' should satisfy the accepts requirement");
		$this->assertFalse(Validator::accept("three", "one,two"), "'three' should not satisfy the accepts requirement");
		$this->assertFalse(Validator::accept("three", array("one", "two")), "'three' should not satisfy the accepts requirement");
		
		$this->assertTrue(Validator::unsigned(''), "'' should satisfy the unsigned requirement, because it isn't 'required'");
		$this->assertTrue(Validator::unsigned(4), "4 should satisfy the unsigned requirement");
		$this->assertTrue(Validator::unsigned("6"), "'6' should satisfy the unsigned requirement");
		$this->assertFalse(Validator::unsigned(-4), "-4 should not satisfy the unsigned requirement");
		
		$this->assertTrue(Validator::pattern('', "/test/"), "'' should satisfy the pattern requirement, because it isn't 'required'");
		$this->assertTrue(Validator::pattern('test', "/test/"), "'test' should satisfy the pattern requirement");
		$this->assertTrue(Validator::pattern('willworkblahblahtestblah', "/test/"), "'willworkblahblahtestblah' should satisfy the pattern requirement");
		$this->assertFalse(Validator::pattern('wontworkblahblahtestblah', "/^test$/"), "'wontworkblahblahtestblah' should not satisfy the pattern requirement");
		$this->assertFalse(Validator::pattern('shouldn\'t match', "/test/"), "'shouldn\'t match' should not satisfy the pattern requirement");
		
		$this->assertTrue(Validator::equalTo('', "test"), "'' should satisfy the equalTo requirement, because it isn't 'required'");
		$this->assertTrue(Validator::equalTo('test', "test"), "'test' should satisfy the equalTo requirement, because it isn't 'required'");
		$this->assertFalse(Validator::equalTo('nottest', "test"), "'nottest' should not satisfy the equalTo requirement, because it isn't 'required'");
	}
	
	public function test_errorMessages()
	{
		$error_messages = "error_messages";
		$errors = Validator::$error_messages;
		$this->assertEqual(Validator::getErrorMessage('required'), $errors['required'], "Required error message should be right");
		$this->assertEqual(Validator::getErrorMessage('equalTo'), $errors['equalTo'], "equalTo error message should be right");
		$this->assertEqual(Validator::getErrorMessage('unsigned'), $errors['unsigned'], "unsigned error message should be right");
		$this->assertEqual(Validator::getErrorMessage('url'), $errors['url'], "url error message should be right");
		$this->assertEqual(Validator::getErrorMessage('email'), $errors['email'], "email error message should be right");
		$this->assertEqual(Validator::getErrorMessage('min', array(4)), vsprintf($errors['min'], array(4)), "min error message should be right");
		$this->assertEqual(Validator::getErrorMessage('max', array(10)), vsprintf($errors['max'], array(10)), "max error message should be right");
		$this->assertEqual(Validator::getErrorMessage('minlength', array(4)), vsprintf($errors['minlength'], array(4)), "minlength error message should be right");
		$this->assertEqual(Validator::getErrorMessage('maxlength', array(10)), vsprintf($errors['maxlength'], array(10)), "maxlength error message should be right");
		$this->assertEqual(Validator::getErrorMessage('range', array(10, 20)), vsprintf($errors['range'], array(10, 20)), "range error message should be right");
		$this->assertEqual(Validator::getErrorMessage('rangelength', array(10, 20)), vsprintf($errors['rangelength'], array(10, 20)), "rangelength error message should be right");
		$this->assertEqual(Validator::getErrorMessage('number', array(10)), vsprintf($errors['number'], array(10)), "number error message should be right");
		$this->assertEqual(Validator::getErrorMessage('digits', array(10)), vsprintf($errors['digits'], array(10)), "digits error message should be right");
		$this->assertEqual(Validator::getErrorMessage('date'), $errors['date'], "date error message should be right");
		$this->assertEqual(Validator::getErrorMessage('dateISO'), $errors['dateISO'], "ISOdate error message should be right");
	}
}

$validator_test = new ValidatorTest();
$validator_test->run();
?>