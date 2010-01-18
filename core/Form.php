<?php

/**
 * Base class for forms in the site
 * Extend this to create a representation of a form
 * It handles validation and stuff
 * 
 * @author adamf
 *
 */
abstract class Form
{
	protected $_errors;
	
	// eg
	/*
	protected $_rules = array(
		""
	);
	 */
	protected $_rules;
	
	// fieldnames for this form
	// this is duplicate data really, surely rules could hold this?
	protected $_fields;
	
	// an instance of a validator to run the actual checks through
	protected $_validator;
	protected $_valid = false;
	
	public function __construct()
	{
		$args = func_get_args();
		if(count($args))
		{
			foreach($args as $arg)
			{
				if($arg instanceof Object)
				{
					$this->addObject($arg);
				}
				elseif(is_string($arg))
				{
					$this->addField($arg);
				}
			}
		}
	}
	
	/**
	 * Adds an object to the form
	 * Will add the object's fields to this form, pulling their validation rules as well
	 * The object will already know about the JDOS validation rules, so this let's the form use them
	 * 
	 * @param Object $object
	 * @return Bool success
	 */
	public function addObject($obj)
	{
		if($obj instanceof Object)
		{
			// get the object's properties
			// get rules and name for each property and add to this form's fieldnames and rules
			// rename clashing fieldnames using object's name and a number if they still clash
			// will need some kind of mapping references for the renaming
			return true;
		}
		return false;
	}
	
	/**
	 * Adds a field to the form, with supplied validation rules
	 * If rules are empty, then there are no rules on the field, obviously
	 * 
	 * @param String $name
	 * @param Array $rules
	 * @return bool success
	 */
	public function addField($name, $rules=array())
	{
		
	}
	
	/**
	 * Adds multiple fields from a jQuery Validator-like array 
	 * 
	 * @param Array $validationArray
	 * @return Bool
	 */
	public function addFields($validationArray)
	{
		
	}
	
	/**
	 * Adds rules for a field
	 * Is this really necessary?
	 * 
	 * @param String $field
	 * @param Array $rule
	 * @return Bool
	 */
	public function addRules($field, $rules)
	{
		
	}
	
	/*
	 * populate the form from submission
	 */
	
	/**
	 * Populates the form instance with the form submission data
	 * @param array $data
	 * @return unknown_type
	 */
	public function populate($data)
	{
		foreach($data as $field => $value)
		{
			
		}
	}
	
	public function fillFromObject($obj)
	{
		if($obj instanceof Object)
		{
			// gets all the values from the object and adds them to this form
		}
		return false;
	}
	
	/*
	 * validate methods
	 */
	
	/**
	 * Gets this field's rules and runs the Validator::method for each
	 * 
	 * @param String $field
	 * @return Bool field is valid
	 */
	public function checkField($field)
	{
		
	}
	
	/**
	 * Tells us if the form is valid or not
	 * 
	 * If the form has already been validated returns cached value
	 * If the form has been edited, or has not been validated, calls
	 * checkField on every field to confirm validity
	 * 
	 * @return Bool
	 */
	public function isValid()
	{
		
	}
	
	/*
	 * Errors methods
	 */
	
	/**
	 * gets an array of all errors
	 * this might be used to print a list of errors above a form
	 * or for code that needs to know the error messages
	 * @return unknown_type
	 */
	public function getErrors()
	{
		
	}
	
	/**
	 * Returns the 'first' error for the field
	 * Errors have an order!
	 * 
	 * @param unknown_type $field
	 * @return unknown_type
	 */
	public function getError($field)
	{
		
	}
	
	public function addError($field, $error)
	{
		
	}
}

?>
