<?php
class IndexGenerator extends Generator
{
	public function generate()
	{
		$index = <<<INDEX
<?php
require_once("../{$this->project->name}.php");

\$project = new {$this->project->name}(dirname(__file__));
\$project->start();

?>

INDEX;
		$this->source = $index;
		return $this->source;
	}
	
	public function write($filename='', $source=false)
	{
		if('' === $filename)
		{
			$filename = $this->project->projectRoot . $this->project->ds . 'www' . $this->project->ds . 'index.php';
		}
		parent::write($filename);
	}
}
?>