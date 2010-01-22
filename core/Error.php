<?php
/*
 *	Class to represent an error
 */
class Error
{	
	protected $type;
	protected $message;
	protected $line;
	protected $file;
	protected $details;
	protected $code;
	protected $time;
	
	const ERROR = 1;
	const WARNING = 2;
	const NOTICE = 3;
	protected $types = array(
		1 => "Error",
		2 => "Warning",
		3 => "Notice"
	);
		
	public function __construct($type, $message, $line=-1, $file="", $details="", $code=0)
	{
		$this->type = $type;
		$this->message = $message;
		$this->line = $line;
		$this->file = $file;
		$this->details = $details;
		$this->code = $code;
		$this->time = time();
	}
	
	/**
	 *	@return $log String
	 */
	public function log($html=false)
	{
		$log = $html ? "<p>" : "";
		$log .= $this->types[$this->type] . ": " . $this->message . " on line " . $this->line . " of " . $this->file;
		if($this->code)
		{
			$log .= " with error code " . $code;
		}
		if($this->details)
		{
			$log .= $html ? "<br />\n" : "\n";
			$log .= $this->details;	
		}
		$log .= $html ? "</p>\n" : "\n";
		return $log;
	}
	
	public function paint()
	{
		return $this->log(true);
	}
	
	public function getType()
	{
		return $this->type;
	}
}

?>
