<?php

require_once("../core/Data.php");
require_once("../core/Error.php");
require_once("../core/Errors.php");
require_once("../core/Object.php");
require_once("../core/Validator.php");
require_once("./TestObject.php");

$db = mysql_connect("127.0.0.1", "root", "");
mysql_select_db("fcc_prefs_dev", $db);

$E = Errors::instance(3);

$testObject = new TestObject(1, "tes'1`?t\\2");
//echo $testObject->getTestPropertyOne();
//echo $testObject->getTestPropertyTwo();
//header('Content-type: text/xml');
/*
var_dump($testObject->toJson("testfn"));
$testObject->setTestPropertyOne(10);
$testObject->setTestPropertyTwo("1234test");
var_dump($testObject->toXml());
$testObject->setTestPropertyOne(-5);
$testObject->setTestPropertyTwo("1234testtolongtoolong");
var_dump($testObject->toArray());

var_dump($testObject->fieldnames());
var_dump($testObject->rules());
var_dump($testObject->rules('testPropertOne'));
*/
$testObject->insert();
echo "\n";
$testObject->update();
echo "\n";
$testObject->find(1);
echo "\n";
$testObject->findAll(10, 0);
echo "\n";
$testObject->delete();
echo "\n";
$testObject->select(array(array("testPropertyOne", 4), array("testPropertyTwo", "LIKE", "%e")));
echo "\n";

echo $E->printErrors();
?>