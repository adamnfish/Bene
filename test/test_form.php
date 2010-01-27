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
require_once("../models/Title_old.php");

$db = mysql_connect("127.0.0.1", "root", "");
mysql_select_db("fcc_prefs_dev", $db);
$E = Errors::instance(3);

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
	
	public function test_form2()
	{
		$title = new Title();
		$form = new UserPrefsForm($title);
		$this->assertFalse($form->isValid(), "form should be invalid");
		$this->assertEqual($form->validationError("title"), "This field is required.", "'Required' error message should  have been thrown for title");
		$this->assertEqual($form->validationError("titleId"), '', "Empty primary field shouldn't throw an error");
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
}

$formTest = new FormTest();
$formTest->run();

echo $E->printErrors();
?>