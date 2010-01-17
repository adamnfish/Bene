<?php
/**
 * Mapper will create a connector specified by JDOS
 * 
 * @author adamnfish
 *
 */
class Mapper
{
	private $data;
	
	// give it the data source
	public function __construct($data)
	{
		$this->data = $data;
	}
	
	// does an insert / update depending on if the object exists already
	// will bring back eg. timestamp
	public function save(&$object)
	{
		
	}
	
	// does an insert explicitly
	public function insert(&$object)
	{
		
	}
	
	// does an update explicitly
	public function update(&$object)
	{
		
	}
	
	function delete(&$object)
	{
		
	}
	
	function find(&$object)
	{
		
	}
	
	function findAll($object)
	{
		
	}
}
?>