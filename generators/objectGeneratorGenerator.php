<?php
class objectGeneratorGenerator extends Generator
{
	public function generate()
	{
		$filename = $this->project->projectRoot . $this->project->ds . $this->project->name . ".php";
		$source = <<<SOURCE
<?php
/**
 * Reads the models.jdos file for the project and generates the Objects for each 'table'
 */

require_once("$filename");
\$project = new {$this->project->name}("{$this->project->webrootPath}");

require_once(\$project->corePath . \$project->ds . 'Generator.php');
require_once(\$project->generatorsPath . \$project->ds . 'objectGenerator.php');
require_once(\$project->generatorsPath . \$project->ds . 'datasourceGenerator.php');

\$jdos = file_get_contents(\$project->binPath . \$project->ds . 'models.jdos');
\$project->jdos = json_decode(\$jdos, true);

\$Ob_generator = new ObjectGenerator(\$project);
\$D_generator = new DatasourceGenerator(\$project);

foreach(\$project->jdos as \$data_source_name => \$data_source)
{
	\$Ob_generator->generateAll(\$data_source_name, true);
	\$D_generator->generate(\$data_source_name);
	\$D_generator->write();
	/*
	foreach(\$data_source["schema"] as \$table_name => \$table)
	{
		\$Ob_generator->generate(\$data_source_name, \$table_name);
		\$Ob_generator->write();
	}
	*/
}
?>
SOURCE;
		$this->source = $source;
		return $this->source;
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