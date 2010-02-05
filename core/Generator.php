<?php
abstract class Generator
{
	protected $project;
	protected $source;

	public function __construct($project)
	{
		$this->project = $project;
	}
	
	public function write($filename='', $src=false)
	{
		$source = (false === $src) ? $this->source : $src;
		$fh = fopen($filename, "w");
		fwrite($fh, $source);
		fclose($fh);
		return $filename;
	}
	
	protected function classHeader($classname, $extends='', $additionalComments='', $includes=false)
	{
		$classname = Utils::camelcase($classname, true);
		if($extends)
		{
			$extends = ' extends ' . $extends;
		}
		if($additionalComments)
		{
			$additionalComments = "\n * " . str_replace("\n", "\n * ", $additionalComments);
		}
		$include_files = "";
	
		if(is_string($includes))
		{
			$includes = array($includes);
		}
		if(is_array($includes))
		{
			$include_files .= "\n";
			foreach($includes as $include)
			{
				$include_files .= 'require_once(\'' . $include . '\');';
			}
			$include_files .= "\n";
		}
		$time = date("c");
		$header = <<<HEADER
<?php

/**
 * $classname
 * 
 * Automatically generated by Bene at $time$additionalComments
 */	
$include_files
class $classname$extends
{

HEADER;
		return $header;
	}
	
	protected function classFooter()
	{
		$footer = <<<FOOTER

}
?>

FOOTER;
		return $footer;
	}
	
	protected function classVar($fieldname, $value='')
	{
		$fieldname = Utils::camelcase($fieldname);
		if($value)
		{
			return '	public $' . $fieldname . ' = ' . var_export($value, true) . ";\n";
		}
		else
		{
			return '	public $' . $fieldname . ";\n";
		}
	}
}
?>