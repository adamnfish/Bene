<?php

/**
 * A test object
 * @author adamf
 *
 */

class TestObject extends Object
{
	protected $properties = array("testPropertyOne", "testPropertyTwo");
	protected $fieldnames = array("testPropertyOne" => "testPropertyOne", "testPropertyTwo" => "testPropertyTwo");
	protected $rules = array(
		"testPropertyOne" => array(
			"number" => true,
			"unsigned" => true
		),
		"testPropertTwo" => array(
			"maxlength" => 10,
			"required" => true
		)
	);
	protected $key = "testPropertyOne";
	protected $tablename = "TestObject";
	
	public function __construct($testPropertyOne='', $testPropertyTwo='')
	{
		parent::__construct();
		$this->setTestPropertyOne($testPropertyOne);
		$this->setTestPropertyTwo($testPropertyTwo);
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
		if(Validator::unsigned($testPropertyOne))
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
		if(Validator::required($testPropertyTwo) && Validator::maxlength($testPropertyTwo, 10))
		{
			$this->data->testPropertyTwo = $testPropertyTwo;
			return true;
		}
		else
		{
			$this->data->testPropertyTwo = null;
			$this->E->throwErr(1, "Invalid property set in setTestPropertyOne");
			return false;
		}
	}
}

?>