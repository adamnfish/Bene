<?php
class ControllerGenerator extends Generator
{
	public function generate()
	{
		$source = <<<SOURCE
<?php
abstract class Controller Extends CoreController
{
	public function error_404()
	{
		header('HTTP/1.0 404 Not Found');
		echo '<h1>Page not found</h1>';
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
			$filename = $this->project->projectCorePath . $this->project->ds . 'Controller.php';
		}
		parent::write($filename);
	}
}
?>