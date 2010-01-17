<?php

abstract class Object
{
	protected $created;
	protected $modified;
	
	// it needs to know which data source to use, this would be genereated from JDOS
	// an instance of Data
	protected $_data;
	
	// this allows the tablename to be different to the classname
	// comes from JDOS
	protected $_tablename;
	// we want to know the property names and their corresponding fieldname
	// comes from JDOS
	protected $_fieldnames;
	/*
	 * eg:
	protected $fieldnames = array(
		"id" => "id",
		"name" => "name",
		"property_name" => "fieldname"
	);
	this allows the properties to be named differently
	 */
	
	// holds the validation rules for the object?
	// or it just be easier to hold a reference to this object's JDOS representation?
	protected $_validation;
	
	protected function __construct()
	{
		
	}
	
	/**
	 *	returns a standard object representing this Object instance
	 */
	public function toObject()
	{
		$object;
		foreach($this->GetClassVars() as $var)
		{
			$object->{$var} = $this->{"get" .  strtoupper(substr($var, 0, 1)) . substr($var, 1)}();
		}
	}
	
	protected function getClassVars()
	{
		return get_class_vars($this);
	}
	
	public function populate($data)
	{
		foreach($data as $prop => $value)
		{
			$this->{"set" . Utilities::camelcase($prop, true)}($value);
		}
	}
	
	public function save()
	{
		$mapper = $this->getMapper();
	}
	
	public function update()
	{
		
	}
	
	public function insert()
	{
		
	}
	
	public function delete()
	{
		
	}
	
	public function find()
	{
		
	}
	
	public function findAll()
	{
		
	}
	
	public function select()
	{
		
	}
	
	// is query really necessary? wouldn't you do that from Data directly?
	// perhaps this just fill;s in the tablename?
	public function query()
	{
		
	}
	
	// hopefully, mappers are redundant now?
	private function getMapper()
	{
		
	}
	
	public function isValid()
	{
		// look over each of the fields
		// get the validation rules for the field
		// run it through an instance of validate
	}
}
?>