<?php

class Config
{
	protected $errors;
	
	protected function __construct()
	{
		global $errors;
		$this->errors = $errors;
		$this->Default();
	}
	
	protected function DefaultConfig()
	{
		
	}
}

?>
