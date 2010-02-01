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
require_once("../core/Form.php");
require_once("../core/Test.php");
// models
require_once("./TestObject.php");
require_once("./Title_old.php");

/*
// not needed for this test
$db = mysql_connect("127.0.0.1", "root", "");
mysql_select_db("fcc_prefs_dev_test", $db);
*/
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

class FormTest extends Test
{
	public function test_formBasic()
	{
		$title = new Title(1, "Mr");
		$form = new UserPrefsForm($title);
		$this->assertEqual($form->get("title"), "Mr", "form should contain a title field with Mr");
		$this->assertTrue($form->isValid(), "form should be valid");
		
		$form->set("title", "abcdefghijklmnop");
		$this->assertFalse($form->isValid(), "form should now be invalid");
		$this->assertEqual($form->validationError("title"), "Please enter no more than 4 characters.", "'Maxlength' error message should have been thrown for title");
	}
	
	public function test_addingObject()
	{
		$title = new Title();
		$form = new UserPrefsForm($title);
		$this->assertFalse($form->isValid(), "form should be invalid");
		$this->assertEqual($form->validationError("title"), "This field is required.", "'Required' error message should  have been thrown for title");
		$this->assertEqual($form->validationError("titleId"), '', "Empty primary field shouldn't throw an error");
		
		$title2 = new Title("3", "Mr");
		$form2 = new UserPrefsForm($title2);
		$this->assertEqual($form2->get("title"), $title2->getTitle(), "Should have populated the form with the title's title");
		$this->assertEqual($form2->get("titleId"), $title2->getTitleId(), "Should have populated the form with the title's titleId");
	}
	
	public function test_addingField()
	{
		$form = new UserPrefsForm();
		$form->addField("testField", array('required' => true));
		$this->assertFalse($form->isValid(), "The new field should be invalid");
		$this->assertEqual($form->validationError("testField"), "This field is required.", "new field should have a 'required' error");
		$form->set("testField", 1);
		$this->assertTrue($form->isValid(), "The new field should now be valid");
		
		$form->addField("tandc");
		$this->assertTrue($form->isValid(), "Form should still be valid because tandc has no rules");
	}
	
	public function test_populate()
	{
		$title = new Title();
		$form = new UserPrefsForm($title);
		$form->populate(array("titleId" => 1, "title" => "Mr"));
		$this->assertEqual($form->get("title"), "Mr", "Form should have been populated with a title of Mr");
		$this->assertEqual($form->get("titleId"), 1, "Form should have been populated with a titleId of 1");
	}
}

$formTest = new FormTest();
$formTest->run();

echo $E->printErrors();
?>