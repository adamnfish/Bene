<?php

/*
 * Contains validation rules for the framework - means they don't need to be inside each object instance :)
 */

class Validator
{
	// default error strings
	public $error_messages = array(
		"required" => "This field is required.",
		"remote" => "Please fix this field.",
		"email" => "Please enter a valid email address.",
		"url" => "Please enter a valid URL.",
		"date" => "Please enter a valid date.",
		"dateISO" => "Please enter a valid date (ISO).",
		"number" => "Please enter a valid number.",
		"digits" => "Please enter only digits.",
		"creditcard" => "Please enter a valid credit card number.",
		"equalTo" => "Please enter the same value again.",
		"accept" => "Please enter a value with a valid extension.",
		"maxlength" => "Please enter no more than {0} characters.",
		"minlength" => "Please enter at least {0} characters.",
		"rangelength" => "Please enter a value between {0} and {1} characters long.",
		"range" => "Please enter a value between {0} and {1}.",
		"max" => "Please enter a value less than or equal to {0}.",
		"min" => "Please enter a value greater than or equal to {0}."
	);
	
	public function __consruct()
	{
		
	}
	
	// a method for each rule?
	// this can be over-riden by the  developer to add custom rules
	// they'd probably add a line to the constructor to add the error message strings to the error_message array'
	
	public function required($value)
	{
		if(strlen($required))
		{
			return true;
		}
		return false;
	}
	
	public function minlength($value, $minlength)
	{
		
	}
}
?>