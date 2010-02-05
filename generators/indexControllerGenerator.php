<?php
class IndexControllerGenerator extends Generator
{
	public function generate()
	{
		$source = <<<SOURCE
<?php
class IndexController Extends Controller
{
	public \$index = "index";
	
	public function index()
	{
		echo '<h1>It works!</h1>';
	}
}
?>
SOURCE;
		$this->source = $source;
		return $this->source;
	}
	
	public function write($filename='', $source=false)
	{
		if('' === $filename)
		{
			$filename = $this->project->controllersPath . $this->project->ds . 'IndexController.php';
		}
		parent::write($filename);
	}
}
?>