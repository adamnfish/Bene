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
	
	public function __construct($titleId=null, $title=null)
	{
		parent::__construct();
		if(null === $titleId)
		{
			$this->data->titleId = null;
		}
		else
		{
			$this->setTitleId($titleId);
		}
		if(null === $title)
		{
			$this->data->title = null;
		}
		else
		{
			$this->setTitle($title);
		}
	}
}