<?php

/*
 * Contains validation rules for the framework - means they don't need to be inside each object instance :)
 */

/**
 * Static class that contains validation methods and default error strings for FCC User preferences
 * Largely based on the jQuery validate plugin validation
 * 
 * Extend this class to add rules or to use custom error messages
 * @author adamf
 *
 */

class Validator
{
	// default error strings
	public $error_messages = array(
		"required" => "This field is required.",
//		"remote" => "Please fix this field.",
		"email" => "Please enter a valid email address.",
		"url" => "Please enter a valid URL.",
		"date" => "Please enter a valid date.",
		"dateISO" => "Please enter a valid date (ISO).",
		"number" => "Please enter a valid number.",
		"digits" => "Please enter only digits.",
		"accept" => "Please enter a an allowed value.",
		"maxlength" => "Please enter no more than {0} characters.",
		"minlength" => "Please enter at least {0} characters.",
		"rangelength" => "Please enter a value between {0} and {1} characters long.",
		"range" => "Please enter a value between {0} and {1}.",
		"max" => "Please enter a value less than or equal to {0}.",
		"min" => "Please enter a value greater than or equal to {0}.",
		"equalTo" => "Please enter the same value again.",
	
		"unsigned" => "Please enter a positive number",
		"pattern" => "Please enter a valid input" // this one shouldn't be used much for front-end - better to create a custom rule
	);
	
	private $rules_with_args = array(
		"minlength",
		"maxlength",
		"rangelenth",
		"min",
		"max",
		"range",
		"accept",
		"pattern"
	);
	
	private function __construct()
	{
		
	}
	
	/**
	 * A layer of abstraction in retrieving error messages
	 * This can be used to add internationalisation to the project, for example
	 * by overiding this method
	 * @param String $type
	 * @return unknown_type
	 */
	public function getErrorMessage($type)
	{
		return $this->error_messages[$type];
	}
	
	/**
	 * Will check a value against an array of rules (basically, like the jQuery validator rule JSON object)
	 * eg.
	 * $rules = array(
	 * 		"required" => true,
	 * 		"minlength" => 6
	 * );
	 * 
	 * @param String $value
	 * @param unknown_type $rules
	 * @return unknown_type
	 */
	public function check($value, $rules)
	{
		$valid = true;
		foreach($rules as $rule => $param)
		{
			if(method_exists(self, $rule))
			{
				// TODO maybe set all the validation methods to accept value, param, required
				// make the default param true for methods that don't need it and ignore it unless it's false
				if(in_array($rule, self::rules_with_args))
				{
					$valid = self::$rule($value, $param) && $valid;
				}
				else
				{
					$valid = self::$rule($value) && $valid;
				}
			}
		}
	}
	
	// a method for each rule?
	// this can(/should) be extended by the developer to add custom rules
	// they'd probably add a line to the constructor to add the error message strings to the error_message array'
	
	// should these return the error message?
	// a bool makes more sense really, but it's being used as a static class
	
	public function required($value)
	{
		return !!strlen($value);
	}
	
	/**
	 * Checks the value has the minimum length if entered
	 * 
	 * @param string $value the value to check
	 * @param int $minlength the minimum length allowed
	 * @param Bool $required (default false) if it isn't required, allows a length of 0
	 * @return Bool
	 */
	public function minlength($value, $minlength, $required=false)
	{
		if(false === $required || true === Validator::required($value))
		{
			return $minlength <= strlen($value);
		}
		return false;
	}
	
	/**
	 * Checks the value uis less than the maximum length if entered
	 * 
	 * @param string $value the value to check
	 * @param int $maxlength the maximum length allowed
	 * @param Bool $required (default false) if it isn't required, allows a length of 0
	 * @return Bool
	 */
	public function maxlength($value, $maxlength, $required=false)
	{
		if(false === $required || true === Validator::required($value))
		{
			return $maxlength >= strlen($value);
		}
		return false;
	}
	
	/**
	 * Checks the value's length is between the min and max
	 * 
	 * @param string $value the value to check
	 * @param array $lengths [minlength, maxlength]
	 * @param Bool $required (default false) if it isn't required, allows a length of 0
	 * @return Bool
	 */
	public function rangelength($value, $lengths, $required=false)
	{
		if(false === $required || true === Validator::required($value))
		{
			$l = strlen($value);
			return $lengths[0] <= $l && $lengths[1] >= $l;
		}
		return false;
	}
	
	/**
	 * Checks the value is numberic and at least the minium supplied
	 * 
	 * @param string $value the value to check
	 * @param int $min the minimum value allowed
	 * @param Bool $required (default false) if it isn't required, allows a length of 0
	 * @return Bool
	 */
	public function min($value, $min, $required=false)
	{
		// better implementation of the required stuff (will make the right error message appear in those cases)
		// TODO use this implementation for all the methods
		// this will mean the required error message is always triggered before eg. 'must be less than 8 chars'
		if(false === $required || true === Validator::required($value))
		{
			if(is_numeric($value))
			{
				return $value >= $min;
			}
		}
		return false;
	}
	
	/**
	 * Checks the value is numeric and less than or equal to max
	 * 
	 * @param string $value the value to check
	 * @param int $max the maximum length allowed
	 * @param Bool $required (default false) if it isn't required, allows a length of 0
	 * @return Bool
	 */
	public function max($value, $max, $required=false)
	{
		if(false === $required || true === Validator::required($value))
		{
			if(is_numeric($value))
			{
				return $value >= $max;
			}
		}
		return false;
	}
	
	/**
	 * Checks the value is between (inclusive) min and max
	 * 
	 * @param string $value the value to check
	 * @param array [min, max]
	 * @param Bool $required (default false) if it isn't required, allows a length of 0
	 * @return Bool
	 */
	public function range($value, $range, $required=false)
	{
		if(false === $required || true === Validator::required($value))
		{
			if(is_numeric($value))
			{
				return $value >= $range[0] && $value >= $range[1];
			}
		}
		return false;
	}
	
	/**
	 * Checks the value is an email address
	 * 
	 * @param string $value the value to check
	 * @param Bool $required (default false) if it isn't required, allows a length of 0
	 * @return Bool
	 */
	public function email($value, $required=false)
	{
		if(false === $required || true === Validator::required($value))
		{
			return !!preg_match("/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i", $value);
		}
		return false;
	}
	
	/**
	 * Checks the value is a link
	 * 
	 * @param string $value the value to check
	 * @param Bool $required (default false) if it isn't required, allows a length of 0
	 * @return Bool
	 */
	public function url($value, $required=false)
	{
		if(false === $required || true === Validator::required($value))
		{
			return !!preg_match("/^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i", $value);
		}
		return false;
	}
	
	/**
	 * Checks the value is some kind of valid date string
	 * It does this be seeing if strtotime succeeds
	 * 
	 * @param string $value the value to check
	 * @param Bool $required (default false) if it isn't required, allows a length of 0
	 * @return Bool
	 */
	public function date($value, $required=false)
	{
		if(false === $required || true === Validator::required($value))
		{
			$date = strtotime($value);
			return ($date && -1 !== $date);
		}
		return false;
	}
	
	/**
	 * Checks the value is an ISO date string
	 * 
	 * @param string $value the value to check
	 * @param Bool $required (default false) if it isn't required, allows a length of 0
	 * @return Bool
	 */
	public function dateISO($value, $required=false)
	{
		if(false === $required || true === Validator::required($value))
		{
			return !!preg_match("/^\d{4}[\/-]\d{1,2}[\/-]\d{1,2}$/", $value);
		}
		return false;
	}
	
	/**
	 * Checks the value is a number
	 * does this by calling is_numeric
	 * 
	 * @param string $value the value to check
	 * @param Bool $required (default false) if it isn't required, allows a length of 0
	 * @return Bool
	 */
	public function number($value, $required=false)
	{
		if(false === $required || true === Validator::required($value))
		{
			return is_numeric($value);
		}
		return false;
	}
	
	/**
	 * Checks the value is numeric, and an int
	 * 
	 * @param string $value the value to check
	 * @param Bool $required (default false) if it isn't required, allows a length of 0
	 * @return Bool
	 */
	public function digits($value, $required=false)
	{
		if(false === $required || true === Validator::required($value))
		{
			return $this->number($value, $required) && floor($value) == $value;
		}
		return false;
	}
	
	/**
	 * Checks the value belongs to an array (or comma seperated list) of values
	 * 
	 * @param string $value the value to check
	 * @param mixed $accepts an array or comma seperated list of allwoed values
	 * @param Bool $required (default false) if it isn't required, allows a length of 0
	 * @return Bool
	 */
	public function accept($value, $accepts, $required)
	{
		if(false === $required || true === Validator::required($value))
		{
			if(is_string)
			{
				$accepts = explode(",", $accepts);
				return in_array($value, $accepts);
			}
		}
		return false;
	}
	
	/*
	 * Rules used for the model layer
	 */
	
	/**
	 * Checks value is a positive number
	 * 
	 * @param String $value
	 * @param Bool $required
	 * @return Bool
	 */
	public function unsigned($value, $required=false)
	{
		if(false === $required || true === Validator::required($value))
		{
			return self::number($value) && 0 <= $value;
		}
		return false;
	}

	/**
	 * Checks value matches a reular expression
	 * 
	 * This really should be used sparingly because it isn't very helpful on the front end
	 * However, in the real world (and particularly if front-end isn't a concern) I realise
	 * it can actually be very useful, so it resides here with this notice ;)
	 * 
	 * @param String $value
	 * @param Bool $required
	 * @return Bool
	 */
	public function pattern($value, $pattern, $required=false)
	{
		if(false === $required || true === Validator::required($value))
		{
			return !!preg_match($pattern, $value);
		}
		return false;
	}
}
?>