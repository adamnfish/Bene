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
	}
}
?>