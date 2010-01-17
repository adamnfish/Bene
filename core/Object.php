<?php

abstract class Object
{
	protected $created;
	protected $modified;
	
	protected function __construct()
	{
		
	}
	
	/**
	 *	returns a standard object representing this Object instance
	 */
	public function toObject()
	{
		$object;
		foreach($this->GetClassVars() as $var)
		{
			$object->{$var} = $this->{"get" .  strtoupper(substr($var, 0, 1)) . substr($var, 1)}();
		}
	}
	
	protected function getClassVars()
	{
		return get_class_vars($this);
	}
	
	public function populate($data)
	{
		foreach($data as $prop => $value)
		{
			$this->{"set" . Utilities::camelcase($prop, true)}($value);
		}
	}
	
	public function save()
	{
		$mapper = $this->getMapper();
	}
	
	private function getMapper()
	{
		
	}
}
?>