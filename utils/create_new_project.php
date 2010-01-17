<?php
/**
 * Project generation script
 */

class NewProject
{
	private $name;
	private $root;
	private $webroot;
	
	
	public function __construct()
	{
		if(!defined("STDIN"))
		{
			define("STDIN", fopen('php://stdin','r'));
		}
		
		$this->create();
	}
	
	public function create()
	{
		// prompt for name
		$this->name = $this->promptName();
		// prompt for root dir
		$this->root = $this->promptRoot();
		// prompt for webroot dir
		$this->webroot = $this->promptWebroot();
		
	}
	
	// PROMPTS
	
	private function promptName()
	{
		return $name;
	}
	
	private function promptRoot()
	{
		return $root;
	}
	
	private function promptWebroot()
	{
		return $webroot;
	}
	
	// SETUP
	
	private function writeProject()
	{
		// create directory if it doesn't exist
		// copy the newProject_prototype folder contents to the root
	}
	
	private function writeProjectWebroot()
	{
		// create directory if it doesn't exist
		// copy the newProject_prototype folder contents to the root
	}
}
?>