<?php
class Title extends Object
{
	protected $properties = array("titleId", "title");
	protected $fieldnames = array("titleId" => "title_id", "title" => "title");
	protected $rules = array(
		"titleId" => array(
			"number" => true,
			"unsigned" => true
		),
		"title" => array(
			"maxlength" => 4,
			"required" => true
		)
	);
	protected $key = "titleId";
	protected $tablename = "titles";
	
	public function __construct($titleId=false, $title=false)
	{
		parent::__construct();
		if(false !== $titleId)
		{
			$this->setTitleId($titleId);
		}
		if(false !== $title)
		{
			$this->setTitle($title);
		}
	}
}