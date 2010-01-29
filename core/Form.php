<?php

/**
 * Base class for forms in the site
 * Extend this to create a representation of a form
 * It handles validation and stuff
 * 
 * @author adamf
 *
 * TODO work out how to manage fieldname clashes
 * 
 */
class UserPrefsForm
{
	// holds the values for the form
	private $data = array();
	
	private $rules = array();
	// eg
	/*
	protected $_rules = array(
		""
	);
	 */

	// fieldnames for this form
	// this is duplicate data really, surely rules could hold this?
	// keep this for performace / simplicity's sake
	private $fields = array();

	// holds the forms errors
	private $errors = array();
	private $valid = false;
	private $validated = false;
	
	// reference to the objects that have been added to the form
	// this might be needed so it can do $fieldname, $fieldname_1, $fieldname_2 etc for duplicates
	// if they are passed by reference then this Form class can directly populate the objects
	// might be a nice peice of sugar? 
	private $objects = array();
	
	/**
	 * class constructor
	 * you can pass in the objects/fields you want to include which sets the form up straight away
	 * @param Mixed Object to populate the form and/or fields to add manually
	 */
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
				elseif(is_array($arg))
				{
					$this->addFields($arg);
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
	 * @param Bool $populate whether or not to populate the form with the object's values as well (defaults to true)
	 * @param String $namespace allows multiple identical objects to exist in the form TODO implement this
	 * @return Bool success
	 */
	public function addObject($obj, $populate=true, $namespace='')
	{
		if($obj instanceof Object)
		{
			// get the object's properties
			// get rules and name for each property and add to this form's fieldnames and rules
			// rename clashing fieldnames using object's name and a number if they still clash
			// will need some kind of mapping references for the renaming
			$new_fields = $obj->toArray();
			foreach($new_fields as $field => &$value)
			{
				// if it doesn't already exist, add it and populate
				if(false === array_key_exists($field, $this->fields))
				{
					// TODO add namespacing here
					// TODO have a class property that contains the namespace for each object
					// $this->fields[] = $field;
					$this->addField($field, $obj->rules($field), $value);
					// this might be better written as
					// $this->addField($field, $obj->get$field, $rules);
					// where we get the rules from the object initially, and then fetch the data after
					// makes more sense, given the goal of this Class 
				}
				else
				{
					// auto namespacing?
				}
				// after namespacing is sorted this will need to be here
//				$this->addField($field, $obj->rules($field), $value);
			}
			return true;
		}
		return false;
	}
	
	/**
	 * Adds a field to the form, with supplied validation rules
	 * If rules are empty, then there are no rules on the field, obviously
	 * 
	 * If the field already exists, this will fail
	 * 
	 * @param String $name
	 * #param String $value
	 * @param Array $rules
	 * @return Bool success
	 */
	public function addField($name, $rules=array(), &$value=null)
	{
		if(!in_array($name, $this->fields))
		{
			$this->fields[] = $name;
			$this->data[$name] = $value;
			$this->rules[$name] = $rules;
			$this->validated = false;
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function addFieldFromObject($obj, $name)
	{
		if($obj instanceof Object)
		{
			if(in_array($name, $obj->properties()))
			{
				$getter = "get" . substr($name, 0, 1) . substr($name, 1);
				$this->addField($name, $obj->rules($name), $obj->$getter());
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Removes a field from the form
	 * @param unknown_type $name
	 * @return unknown_type
	 */
	public function removeField($name)
	{
		$this->fields = array_diff($this->fields, array($name));
		unset($this->rules[$name]);
		unset($this->errors[$name]);
		unset($this->data[$name]);
		$this->validated = false;
	}
	
	/**
	 * Removes an array of supplied fieldnames from the form
	 * This is used by removeObject, but is public in case it's useful
	 * @param unknown_type $fields
	 * @return unknown_type
	 */
	public function removeFields($fields)
	{
		$this->fields = array_diff($this->fields, $fields);
		foreach($fields as $field)
		{
			unset($this->rules[$field]);
			unset($this->errors[$field]);
		}
		$this->validated = false;
	}
	
	/**
	 * Removes an object's fields from the form
	 * @param unknown_type $obj
	 * @return unknown_type
	 */
	public function removeObject($obj)
	{
		if($obj instanceof Object)
		{
			$this->removeFields($obj->fieldnames());
		}
		return false;
	}
	
	/**
	 * Adds multiple fields from a jQuery Validator-like array
	 * of the form,
	 * array(
	 * 		fieldname => rulesarray,
	 * 		fieldname => rulesarray
	 * )
	 * 
	 * @param Array $validationArray
	 * @return Bool
	 */
	public function addFields($validationArray)
	{
		$success = true;
		foreach($validationArray as $fieldname => $rules)
		{
			$success = $this->addField($fieldname, $rules) && $success;
		}
		return $success;
	}
	
	/**
	 * Removes a bunch of fields, according to the provided filters
	 * 
	 * @param unknown_type $filters
	 * @return unknown_type
	 */
	public function filterFields($filters=array('/Id$/', '/^created$/i', '/^modified$/i'), $exceptions=array())
	{
		foreach($this->fields as $field)
		{
			if(false === in_array($field, $exceptions))
			{
				foreach($filters as $filter)
				{
					if(preg_match($filter, $field))
					{
						$this->removeField($field);
						break;
					}
				}
			}
		}
	}
	
	/**
	 * Returns array of current fields
	 * @return unknown_type
	 */
	public function fields()
	{
		return $this->fields;
	}
	
	/**
	 * Adds rules for a field
	 * Is this really necessary?
	 * merges the field's current rules array with the passed one
	 * 
	 * @param String $field
	 * @param Array $rule
	 * @return Bool
	 */
	public function addRules($field, $rules)
	{
		return $this->setRules(array_merge($this->_rules[$field], $rules));
		$this->validated = false;
	}
	
	/**
	 * Sets the passed field's rules to the rules object provided
	 * @param String $field name fo the field
	 * @param Array $rules the rules for this field
	 * @return unknown_type
	 */
	public function setRules($field, $rules)
	{
		$this->rules[$field] = $rules;
		$this->validated = false;
	}
	
	/*
	 * Get/Set ters
	 */
	
	public function set($name, $value)
	{
		if(key_exists($name, $this->data))
		{
			$this->validated = false;
			return $this->data[$name] = $value;
		}
		return false;
	}
	
	public function get($name)
	{
		return $this->data[$name];
	}
	
	/**
	 * Returns the current for data
	 * @return unknown_type
	 */
	public function data()
	{
		return $this->data;
	}
	
	/*
	 * populate the form
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
			if(in_array($field, $this->fields))
			{
				$this->set($field, $value);
			}
		}
	}

	/**
	 * Gets the values from the passed object and adds them to the form
	 * This should be redundant?
	 * addObject should automatically fill the form at the same time
	 * @param unknown_type $obj
	 * @return unknown_type
	 */
	/*
	public function fillFromObject($obj)
	{
		if($obj instanceof Object)
		{
			// gets all the values from the object and adds them to this form
		}
		return false;
	}
	*/
	
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
		$rules = $this->rules[$field];
		foreach($rules as $rule => $param)
		{
			if(false === Validator::$rule($this->data[$field], $param))
			{
				if(!is_array($param))
				{
					$param = array($param);
				}
				$this->errors[$field] = Validator::getErrorMessage($rule, $param);
				return false;
			}
		}
		return true;
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
		// caches the result if nothing has changed
		if(true === $this->validated)
		{
			return $this->valid;
		}
		else
		{
			$this->valid = true;
			foreach($this->fields as $field)
			{
				$this->valid = $this->checkField($field) && $this->valid;
//				var_dump("checkField", $field, $this->checkField($field), $this->valid);
//				var_dump($field, $this->valid);
			}
			$this->validated = true;
		}
		return $this->valid;
	}
	
	/*
	 * Errors methods
	 */
	
	/**
	 * gets an array of all errors
	 * this might be used to print a list of errors above a form
	 * or for code that needs to know the error messages
	 * @return Array
	 */
	public function validationErrors()
	{
		return $this->errors;
	}
	
	/**
	 * Returns the 'first' error for the field
	 * Errors have an order!
	 * 
	 * @param String $field
	 * @return String
	 */
	public function validationError($field)
	{
		return isset($this->errors[$field]) ? $this->errors[$field] : "";
	}
	
	/**
	 * Adds an error to this Form's internal error array
	 * @param String $field
	 * @param String $error
	 * @return null
	 */
	public function addError($field, $error)
	{
		$this->errors[$field] = $error;
	}
}

?>
