<?php

abstract class Object
{
	// this holds the actual model property values
	// it's namespaced like this to prevent clashes with the Object method names
	protected $data;
	
	// validation rules for the object
	// protected $rules;

	// it needs to know which data source to use, this would be genereated from JDOS
	// an instance of Data
	protected $dataSource;
	
	// holds validation errors
	protected $errors = array();
	
	// we want to know the property names and their corresponding fieldname
	// comes from JDOS
	// properties is equal to array_keys($fieldnames) but exists as a performance benefit
	protected $properties;
	protected $fieldnames;
	protected $rules;
		
	// this allows the tablename to be different to the classname
	// comes from JDOS
	protected $tablename;
	// the key filed for this object (used for 'find')
	protected $key;
	
	// holds instance of error class
	protected $E;


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
	// protected $validation;
	// not needed anymore - Validator is a static class :)
	
	/**
	 * constructor for the class - delete this if it isn't being used to improve performance
	 */
	protected function __construct()
	{
		$this->E = Errors::instance();
		$this->dataSource = Data::instance("test");
	}
	
	// for generator
	// function setX($name, $value){
	//		this should run it through a validator as it sets it, too?
	// }
	
	/*
	 * Export methods
	 */
	
	/**
	 * Returns a standard object representing this object's data
	 * @return StdObject a bare object exposing the current data
	 */
	public function toObject()
	{
		return $this->data;
	}
	
	/**
	 * Returns an associative array representing the object's data
	 * @return Array representing object data
	 */
	public function toArray()
	{
		$array = array();
		foreach($this->properties as $property)
		{
			$array[$property] = $this->data->{$property};
		}
		return $array;
	}
	
	/**
	 * Returns an xml representation of the object's data
	 * @return unknown_type
	 */
	public function toXml($header=true)
	{
		$class = get_class($this);
		$xml = array();
		if($header)
		{
			$xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
		}
		$xml[] = "<$class>";
		foreach($this->data as $property => $value)
		{
			$xml[] = "<$property>$value</$property>";
		}
		$xml[] = "</$class>";
		return implode($xml);
	}
	
	/**
	 * Returns a JSON string representing the object's data
	 * @return String a JSON string representing the data
	 */
	public function toJson($callback='')
	{
		if('' === $callback)
		{
			return json_encode($this->data);
		}
		else
		{
			return $callback . "(" . json_encode($this->data) . ");";
		}
	}
	
	/**
	 * Returns an array of the model property names
	 * @return Array fieldnames
	 */
	public function properties()
	{
		return $this->properties;
	}
	
	/**
	 * Return an array of all the 'fieldnames' for this object
	 * Fieldnames may be the same as their corresponding property name, but it is possible
	 * to have a fieldname that differs because of this property
	 * if fieldnames is an empty array, assume they are all the same and return the property name
	 * @return unknown_type
	 */
	public function fieldnames()
	{
		if(count($this->fieldnames))
		{
			return array_values($this->fieldnames);
		}
		else
		{
			return $this->properties;
		}
	}
	
	/**
	 * Returns the fieldname for a specified property
	 * It's likely to be the same, but just in case
	 * if fieldnames is empty, assume they are all the same and just return the property back
	 * @param String $property the name of the property
	 * @return String the corresponding fieldname
	 */
	public function fieldname($property)
	{
		if(count($this->fieldnames))
		{
			return $this->properties[$this->fieldnames];
		}
		else
		{
			return $property;
		}
	}
	
	/**
	 * Returns the object's validation rules
	 * This method will be called by eg. Form when it wants to add these fields
	 * @return unknown_type
	 */
	public function rules($property='')
	{
		if('' === $property)
		{
			return $this->rules;
		}
		else
		{
			return $this->rules[$property];
		}
	}
	
	/**
	 * populates the clas properties from supplied data
	 * 
	 * @param Mixed $data
	 * @return Bool
	 */
	public function populate($data)
	{
		foreach($data as $prop => $value)
		{
			if(in_array($prop, $this->properties))
			{
				$this->{"set" . Utilities::camelcase($prop, true)}($value);
			}
		}
		return true;
	}
	
	/**
	 * Saves the model to it's data source
	 * This will delegate to update / insert as appropriate
	 * @return unknown_type
	 */
	public function save()
	{
		if(isset($this->data[$this->key]))
		{
			return $this->update();
		}
		else
		{
			return $this->insert();
		}
	}
	
	/**
	 * Updates this record on the data source
	 * @return unknown_type
	 */
	public function update()
	{
		$this->dataSource->update($this->tablename, $this->toArray(), array(array($this->key, $this->data->{$this->key})), 1);
	}
	
	/**
	 * Inserts this record into the data source
	 * @return unknown_type
	 */
	public function insert()
	{
		$this->dataSource->insert($this->tablename, $this->toArray());
	}
	
	/**
	 * Deletes this record from the data source
	 * @return unknown_type
	 */
	public function delete()
	{
		$this->dataSource->delete($this->tablename, array(array($this->key, $this->data->{$this->key})), 1);
	}
	
	/**
	 * Retrieves the record with passed id from the data source
	 * @return unknown_type
	 */
	public function find($id)
	{
		$this->dataSource->find($this->tablename, $id, $this->key);
	}
	
	/**
	 * Returns all the records of this type from this data source
	 * (obviously, has pagination and stuff)
	 * @return unknown_type
	 */
	public function findAll($count=0, $page=0)
	{
		$this->dataSource->findAll($this->tablename, $count, $page);
	}
	
	/**
	 * General select
	 * @return unknown_type
	 */
	public function select($conditions=false, $order=false, $desc=false, $count=false, $start='0')
	{
		$this->dataSource->select($this->tablename, "*", $conditions, $order, $desc, $count, $start);
	}
	
	// is query really necessary? wouldn't you do that from Data directly?
	// perhaps this just fill;s in the tablename?
	public function query()
	{
		
	}
	
	/*
	 * Validation methods
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
		return $this->errors;
	}
	
	public function validationError($property)
	{
		return $this->errors[$property];
	}
}
?>