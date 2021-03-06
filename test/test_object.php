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

class ObjectTest extends Test
{
	private $json = '{"testPropertyOne":10,"testPropertyTwo":"1234test","testPropertyThree":19}';
	private $json_callback = '{"testPropertyOne":10,"testPropertyTwo":"1234test","testPropertyThree":19}';
	private $xml = '<?xml version="1.0" encoding="UTF-8"?><TestObject><testPropertyOne>10</testPropertyOne><testPropertyTwo>1234test</testPropertyTwo><testPropertyThree>19</testPropertyThree></TestObject>';
	
	public function test_objectMethods()
	{
		$testObject = new TestObject(1, "tes'1`?t\\2", 15);
		$this->assertEqual($testObject->getTestPropertyOne(), 1, "testPropertyOne should be set to 1");
		$this->assertEqual($testObject->getTestPropertyTwo(), "tes'1`?t\\2", "testPropertyTwo should be set to tes'1`?t\\2");
		$this->assertEqual($testObject->getTestPropertyThree(), 15, "testPropertythree should be set to 15");
		
		$this->assert($testObject->checkField("testPropertyOne"), "testPropertyOne should be valid");
		$this->assert($testObject->checkField("testPropertyTwo"), "testPropertyTwo should be valid");
		$this->assert($testObject->checkField("testPropertyThree"), "testPropertyThree should be valid");
		
		$this->assert($testObject->setTestPropertyOne(10), "should be able to set testPropertyOne to 10");
		$this->assert($testObject->setTestPropertyTwo("1234test"), "should be able to set testPropertyTwo to 1234test");
		$this->assert($testObject->setTestPropertyThree(19), "should be able to set testPropertyTwo to 19");
		
		$this->assert($testObject->toJson(), $this->json, "json representation should be correct");
		$this->assert($testObject->toJson("callback"), $this->json_callback, "json callback representation should be correct");
		$this->assert($testObject->toXml(), $this->xml, "xml representation should be correct");
		$arr = $testObject->toArray();
		$this->assertEqual($arr["testPropertyOne"], 10, "Array representation should have testPropertyOne set to 10");
		$this->assertEqual($arr["testPropertyTwo"], "1234test", "Array representation should have testPropertyTwo set to 1234test");
		$this->assertEqual($arr["testPropertyThree"], 19, "Array representation should have testPropertyThree set to 19");
		$obj = $testObject->toObject();
		$this->assertEqual($obj->testPropertyOne, 10, "Object representation should have testPropertyOne set to 10");
		$this->assertEqual($obj->testPropertyTwo, "1234test", "Object representation should have testPropertyTwo set to 1234test");
		$this->assertEqual($obj->testPropertyThree, 19, "Object representation should have testPropertyThree set to 19");
		$this->assertFalse($testObject->setTestPropertyThree(21), "Validation should stop 21 being set to testPropertyThree");
		$this->assertFalse($testObject->setTestPropertyOne(-4), "Validation should stop -4 being set to testPropertyOne");
		
		$rules = $testObject->rules();
		$this->assertCount($rules, 3, "Rules array should have an entry for each field in TestObject (3 in total)");

		$rules_one = $testObject->rules("testPropertyOne");
		$this->assert($rules, "Should fetch the rules for testPropertyOne", true);
		$this->assertTrue($rules_one['number'], "TestPropertyOne's rules should include 'number' => true");
		$this->assertTrue($rules_one['unsigned'], "TestPropertyOne's rules should include 'unsigned'=> true");
	}
	
	public function test_databaseInteraction()
	{
		$title = new Titles();
		$this->assert($title->setTitle("Miss"), "Should be able to set Miss as the title");
		$this->assert($title->insert(), "title insert should work");
		$id = $title->getTitleId();
		$this->assert($id, "title should have an id now");
		
		$title2 = new Titles($id, "Mr");
		$this->assertEqual($title2->getTitleId(), $id, "titleId should be $id");
		$this->assertEqual($title2->getTitle(), "Mr", "title should be Mr");
		$this->assert($title2->save(), "Should succesfully save title");
		
		$title3 = new Titles();
		$title3->find($id);
		$this->assertEqual($title3->getTitle(), "Mr", "Should find the correct record, with title, Mr");
		
		$title4 = new Titles();
		$title4->setTitle("Dr");
		$this->assert($title4->save(), "should save title 4");
		
		$title5 = new Titles(null, "Mrs");
		$this->assert($title5->save(), "should save title 5");
		$titles = $title->findAll();
		$this->assertCount($titles, 3, "Should be 3 results");
		$this->assertEqual($titles[0]->getTitle(), "Mr", "first result should be Mr");
		$this->assertEqual($titles[1]->getTitle(), "Dr", "second result should be Dr");
		$this->assertEqual($titles[2]->getTitle(), "Mrs", "third result should be Mrs");
		
		$selectBoxTitles = $title->findSelectOptions("title_id,title");
		$this->assertCount($selectBoxTitles, 3, "Should have found 3 titles for the select box data");
		$this->assertEqual($selectBoxTitles[1], "Mr", "The first found title for select box data should be 1 => 'Mr'");
		$this->assertEqual($selectBoxTitles[2], "Dr", "The second found title for select box data should be 2 => 'Dr'");
		$this->assertEqual($selectBoxTitles[3], "Mrs", "The second found title for select box data should be 3 => 'Mrs'");
		
		$selectBoxTitles = $title->findSelectOptions("title");
		$this->assertCount($selectBoxTitles, 3, "Should have found 3 titles for the select box data");
		$this->assertEqual($selectBoxTitles["Mr"], "Mr", "The first found title for select box data should be 'Mr' => 'Mr'");
		$this->assertEqual($selectBoxTitles["Dr"], "Dr", "The second found title for select box data should be 'Dr' => 'Dr'");
		$this->assertEqual($selectBoxTitles["Mrs"], "Mrs", "The second found title for select box data should be 'Mrs' => 'Mrs'");
		
		$titles_1 = $title->findAll(2, 1);
		$this->assertCount($titles_1, 2, "Find all with a limit of 2 should return 2 records");
		$this->assertEqual($titles_1[1]->getTitle(), "Dr", "second record in paginated findAll should be Dr");
		
		$titles_2 = $title->findAll(2, 2);
		$this->assertCount($titles_2, 1, "Second page of find all with a limit of 2 should return 1 record");
		$this->assertEqual($titles_2[0]->getTitle(), "Mrs", "first record in page 2 of paginated findAll should be Mrs");
		
		$selected = $title->select(array("title_id", ">", 1));
		$this->assertCount($selected, 2, "Select should return 2 records");
	}
	
	public function test_objectIsValid()
	{
		$title = new Titles();
		$this->assertFalse($title->isValid(), "Empty object should be invalid");
		$title->setTitle("Mr");
		$this->assertTrue($title->isValid(), "Object should now be valid");
		$title->setTitle("Mr I have a name that is too long");
		$this->assertFalse($title->isValid(), "Object should be invalid again");
	}
	
	public function test_populate()
	{
		$field_data = array("title_id" => 1, "title" => "Mr");
		$title1 = new Titles();
		$title1->populate($field_data, true);
		$this->assertEqual($title1->getTitleId(), $field_data["title_id"], "Should have correctly set the title id from the field data");
		$this->assertEqual($title1->getTitle(), $field_data["title"], "Should have correctly set the title from the field data");

		$property_data = array("titleId" => 2, "title" => "Mrs");
		$title2 = new Titles();
		$title2->populate($property_data);
		$this->assertEqual($title2->getTitleId(), $property_data["titleId"], "Should have correctly set the title id from the property data");
		$this->assertEqual($title2->getTitle(), $property_data["title"], "Should have correctly set the title from the property data");
	}
	
	public function teardown()
	{
		$data = $this->dataSource = Data::instance();
		$sql = "TRUNCATE TABLE `titles`";
		$sql2 = "ALTER TABLE `titles` AUTO_INCREMENT = 1;";
		$data->query($sql);
		$data->query($sql2);
	}
}

$object_test = new ObjectTest();
$object_test->run();

echo $E->printErrors();
?>