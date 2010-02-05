<?php
class objectGeneratorGenerator extends Generator
{
	public function generate()
	{
		$source = <<<SOURCE
<?php
/**
 * Reads the models.jdos file for the project and generates the Objects for each 'table'
 * 
 * TODO add a subfolder for the data_source (named thereafter)
 */

require_once(dirname(__file__) . "/../TestProject.php");
\$project = new TestProject("../www");

require_once(\$project->corePath . \$project->ds . 'Generator.php');
require_once(\$project->generatorsPath . \$project->ds . 'objectGenerator.php');

\$jdos = file_get_contents(\$project->binPath . \$project->ds . 'models.jdos');
\$project->jdos = json_decode(\$jdos, true);

\$generator = new ObjectGenerator(\$project);

foreach(\$project->jdos as \$data_source_name => \$data_source)
{
	\$generator->generateAll(\$data_source_name);
	/*
	foreach(\$data_source["schema"] as \$table_name => \$table)
	{
		\$generator->generate(\$data_source_name, \$table_name);
		\$generator->write();
	}
	*/
}
?>
SOURCE;
		$this->source = $source;
	}
	
	public function write($filename='', $src=false)
	{
		if('' === $filename)
		{
			$filename = $this->project->utilitiesPath . $this->project->ds . 'generateModels.php';
		}
		parent::write($filename);
	}
}
?>