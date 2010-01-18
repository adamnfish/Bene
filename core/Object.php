<?php

abstract class Object
{
	protected $created;
	protected $modified;
	
	// it needs to know which data source to use, this would be genereated from JDOS
	// an instance of Data
	protected $_data;
	// an instance of Validator (or an extension thereof)
	protected $_validator;
	
	// holds validation errors
	protected $_errors = array();
	
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
	// yes, but that kind of forces Bene to use JDOS a little too much?
	// better to have it as just the list of rules, so it can be applied identically to forms
	protected $_validation;
	
	protected function __construct()
	{
		
	}
	
	// for generator
	// function setX($name, $value){
	//		this should run it through a validator as it sets it, too?
	// }
	
	/**
	 *	returns a standard object representing this Object instance
	 *	this is to circumvent protected properties
	 *	basically, returns a bare copy of the object as it currently stands 
	 */
	public function toObject()
	{
		$object;
		foreach($this->GetClassVars() as $var)
		{
			$object->{$var} = $this->{"get" .  strtoupper(substr($var, 0, 1)) . substr($var, 1)}();
		}
	}
	
	/**
	 * Returns an array of the model properties
	 * @return unknown_type
	 */
	protected function getClassVars()
	{
		return get_class_vars($this);
	}
	
	/**
	 * populates the clas properties from an array of data
	 * 
	 * @param Array $data
	 * @return Bool
	 */
	public function populate($data)
	{
		foreach($data as $prop => $value)
		{
			$this->{"set" . Utilities::camelcase($prop, true)}($value);
		}
		return true;
	}
	
	/**
	 * Saves the model to it's data source
	 * This will delegate to Update / Insert as appropriate
	 * @return unknown_type
	 */
	public function save()
	{
		$mapper = $this->getMapper();
	}
	
	/**
	 * Performs an update action on the data source with this record
	 * @return unknown_type
	 */
	public function update()
	{
		
	}
	
	/**
	 * Performs an insert on the data source with this record
	 * @return unknown_type
	 */
	public function insert()
	{
		
	}
	
	/**
	 * Deletes this record from the data source
	 * @return unknown_type
	 */
	public function delete()
	{
		
	}
	
	/**
	 * Retrieves the record with passed id from the data source
	 * @return unknown_type
	 */
	public function find($id)
	{
		
	}
	
	/**
	 * Returns all the records of this type from this data source
	 * (obviously, has pagination and stuff)
	 * @return unknown_type
	 */
	public function findAll()
	{
		
	}
	
	/**
	 * General select
	 * @return unknown_type
	 */
	public function select()
	{
		
	}
	
	// is query really necessary? wouldn't you do that from Data directly?
	// perhaps this just fill;s in the tablename?
	public function query()
	{
		
	}
	
	/*
	// hopefully, mappers are redundant now?
	private function getMapper()
	{
		
	}
	*/
	
	public function isValid()
	{
		// look over each of the fields
		// get the validation rules for the field
		// run it through an instance of validate
	}
	
	/*
	 * These don't really belong here anymore - the Form code kind of takes care of it?
	 * we'll see
	 */
	
	public function validationErrors()
	{
		return $this->_errors;
	}
	
	public function validationError($property)
	{
		return $this->_errors[$property];
	}
}
?>