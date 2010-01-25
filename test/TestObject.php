<?php

/**
 * A test object
 * @author adamf
 *
 */

class TestObject extends Object
{
	protected $properties = array("testPropertyOne", "testPropertyTwo", "testPropertyThree");
	protected $fieldnames = array("testPropertyOne" => "testPropertyOne", "testPropertyTwo" => "testPropertyTwo", "testPropertyThree" => "testPropertyThree");
	protected $rules = array(
		"testPropertyOne" => array(
			"number" => true,
			"unsigned" => true
		),
		"testPropertyTwo" => array(
			"maxlength" => 10,
			"required" => true
		),
		"testPropertyThree" => array(
			"number" => true,
			"range" => array(10, 20)
		)
	);
	protected $key = "testPropertyOne";
	protected $tablename = "TestObject";
	
	public function __construct($testPropertyOne='', $testPropertyTwo='', $testPropertyThree='')
	{
		parent::__construct();
		$this->setTestPropertyOne($testPropertyOne);
		$this->setTestPropertyTwo($testPropertyTwo);
		$this->setTestPropertyThree($testPropertyThree);
	}
	
	/*
	 * Getters
	 */
	
	public function getTestPropertyOne()
	{
		return $this->data->testPropertyOne;
	}
	
	public function getTestPropertyTwo()
	{
		return $this->data->testPropertyTwo;
	}
	
	/*
	 * Setters
	 */
	
	public function setTestPropertyOne($testPropertyOne)
	{
		if($this->checkField("testPropertyOne", $testPropertyOne))
//		if(Validator::unsigned($testPropertyOne))
		{
			$this->data->testPropertyOne = $testPropertyOne;
			return true;
		}
		else
		{
			$this->data->testPropertyOne = null;
			$this->E->throwErr(1, "Invalid property set in setTestPropertyOne");
			return false;
		}
	}
	
	public function setTestPropertyTwo($testPropertyTwo)
	{
		if($this->checkField("testPropertyTwo", $testPropertyTwo))
//		if(Validator::required($testPropertyTwo) && Validator::maxlength($testPropertyTwo, 10))
		{
			$this->data->testPropertyTwo = $testPropertyTwo;
			return true;
		}
		else
		{
			$this->data->testPropertyTwo = null;
			$this->E->throwErr(1, "Invalid property set in setTestPropertyTwo");
			return false;
		}
	}
}

?>