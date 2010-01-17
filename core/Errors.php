<?php

class Errors
{
	protected $errors = array();
	
	/*
	0	none
	1	errors
	2	warnings
	3	notices
	*/
	protected $display_level;
	protected $log_level;
	
	/**
	 * Sets default levels (suitable for production - should be changed for dev server!)
	 * @param $display_level
	 * @param $log_level
	 * @return unknown_type
	 */
	public function __construct($display_level=0, $log_level=2)
	{
		$this->display_level = $display_level;
		$this->log_level = $log_level;
	}
	
	public function setDisplayLevel($display_level)
	{
		$this->display_level = $display_level;
	}
	
	public function setLogLevel($log_level)
	{
		$this->log_level = $log_level;
	}
	
	public function throwErr($type, $message, $details="", $code=0, $line=-1, $file="")
	{
		$stack = debug_backtrace();
		if(-1 === $line)
		{
			$line = $stack[0]['line'];
		}
		if("" === $file)
		{
			$file = $stack[0]['file'];
		}
		$error = new Error($type, $message, $line, $file, $details, $code);
		$this->errors[] = $error;
		return $error;
	}
	
	/**
	 * Displays errors
	 * @return unknown_type
	 */
	public function printErrors()
	{
		$output = array();
		foreach($this->errors as $error)
		{
			if($error->getType() <= $this->display_level)
			$output[] = $error->paint();
		}
		return implode("", $output);
	}
	
	/**
	 * Write appropriate errors ot the log
	 * @return unknown_type
	 */
	public function logErrors()
	{
		
	}
}

?>
